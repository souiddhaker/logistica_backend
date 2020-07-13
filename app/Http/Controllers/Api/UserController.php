<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Libs\Firebase;
use App\Models\Account;
use App\Models\Result;
use App\Models\UserFcm;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Validator;

class UserController extends Controller
{
    public function update(Request $request)
    {
        $res = new Result();

        $user = Auth::user();
        $user->update($request->only(['firstName', 'lastName']));
        $res->success($user);
        return response()->json($res, 200);
    }

    public function uploadImage(Request $request)
    {
        $res = new Result();
        $validator = Validator::make($request->all(), ['photo' => 'required|base64image']);

        if ($validator->fails()) {
            $res->fail("Toutes les entrées sont requises");
            return response()->json($res, 200);
        }

        try {
            $name = time() . '.' . explode('/', explode(':', substr($request->photo, 0, strpos($request->photo, ';')))[1])[1];
            $img = \Image::make($request->photo)->save(public_path('img/profile/') . $name);
            $name = url('/') .'/img/profile/' . $name;
            $user = Auth::user();
            $user->update(['image_url' => $name]);
            $res->success($user);
            return response()->json($res, 200);
        } catch (Exception $e) {
            $res->fail($e);
            return response()->json($res, 200);
        }
    }

    public function getUser()
    {
        $res = new Result();

        $user = Auth::user();

        $res->success($user);
        $res->message= trans('messages.user_details');

        return response()->json($res,200);
    }

    public function createAccount(int $id)
    {
        return Account::create(['balance'=>0]);
    }

    public function userFcmToken(Request $request)
    {
        $res = new Result();

        $validator = Validator::make($request->all(),
            ['fcm' => 'required|string']);
        if ($validator->fails()) {
            $res->fail("FCM token not valid");
            return response()->json($res, 200);
        }
        $userFCM = new UserFcm();
        $userFCM->user_id = Auth::id();
        $userFCM->token = $request['fcm'];
        $userFCM->save();

        $res->success($userFCM);

        return response()->json($res, 200);

    }

    public function notify(Request $request)
    {
        $notification_payload   = $request['payload'];
        $notification_title     = $request['title'];
        $notification_message   = $request['message'];
        $receiver_id =[];
        $users = UserFcm::where('user_id',$request['user_id'])->get();

        foreach($users as $user){
            array_push($receiver_id,$user['token']);
        }
        try {
            $firebase = new Firebase();
            $message = array('body' =>  $notification_message , 'title' => $notification_title , 'vibrate' => 1, 'sound' => 1 ,'payload'=>$notification_payload);
            return $firebase->sendMultiple(  $receiver_id,  $message );
        } catch ( Exception $ex ) {
            return false;
        }
    }

}
