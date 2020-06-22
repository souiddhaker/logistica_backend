<?php

namespace App\Models;

use App\Libs\Firebase;
use Eloquent;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Mockery\Exception;

/**
 * Post
 *
 * @mixin Eloquent
 */
class Notif extends Model
{
    protected $fillable = ['Title', 'type', 'icon', 'description', 'user_id'];

    //
    public function getCreatedAtAttribute($value)
    {
        return Carbon::parse($value)->timestamp;
    }

    public function getUpdatedAtAttribute($value)
    {
        return Carbon::parse($value)->timestamp;
    }

    static public function validate(array $request, $role = "admin", $create = true): \Illuminate\Validation\Validator
    {

        $roleData =
            [
                'Title' => 'required',
                'type' => 'required',
                'icon' => 'required',
                'description' => 'required',
                'user_id' => 'required'
            ];
        return Validator::make($request, $roleData);
    }

    static public function filterRequest($data)
    {
        $data = array_filter($data, function ($key) {
            $User = new Notif();
            return in_array($key, $User->getFillable());
        }, ARRAY_FILTER_USE_KEY);
        return $data;
    }

    static public function createOne(array $request): Result
    {
        $res = new Result();
        $validator = Notif::validate($request);
        if ($validator->fails()) {
            $res->fail($validator->errors()->all());
            return $res;
        }
        $data = $validator->valid();
        $request['tokens'] = Notif::getTokens($request['user_id']);
        $requestNotif = Notif::submitNotif($request);
        if ($requestNotif['success']) {
            $user = Notif::create(Notif::filterRequest($data));
            $res->success([
                "notif" => $requestNotif['response']
            ]);
        } else {
            $res->fail($requestNotif['response']);
        }
        return $res;
    }

    static public function getTokens($user_id)
    {
        try {
            return [UserFcm::where("user_id", $user_id)->first()["token"]];
        } catch (\Exception $ex) {
            return [];
        }

    }

    static public function submitNotif(array $request)
    {

        try {
            $firebase = new Firebase();
            $message = array('body' => $request['description'], 'title' => $request['Title'], 'vibrate' => 1, 'sound' => 1, 'payload' => '');
            $response = $firebase->sendMultiple($request['tokens'], $message);

            return ["success" => true, "response" => $response];
        } catch (\Exception $ex) {
            return ["success" => false, "response" => $ex->getMessage()];
        }
    }


}
