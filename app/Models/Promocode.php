<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Eloquent;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

/**
 * Post
 *
 * @mixin Eloquent
 */
class Promocode extends Model
{
    //


    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'code', 'pourcentage', 'status', 'end_at','type'
    ];
    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'created_at', 'updated_at', 'nbr_uses'
    ];

    protected $dates = ['end_at'];

    public function getEndAtAttribute($value)
    {
        return Carbon::parse($value)->timestamp;
    }

    public function getCreatedAtAttribute($value)
    {
        return Carbon::parse($value)->timestamp;
    }

    public function getUpdatedAtAttribute($value)
    {
        return Carbon::parse($value)->timestamp;
    }

    public function users()
    {
        return $this->belongsToMany(User::class);
    }


    static public function updateOne(Request $request, int $id): Result
    {

        $data = Promocode::validate($request);
        if ($data['data']) {
            $id = Promocode::where('id', $id)->update($data['data']);
            $data['res']->success([
                "coupon" => $id
            ]);
        }
        return $data['res'];
    }

    static public function validate(Request $request, $create = true)
    {

        $res = new Result();
        $validator = Validator::make($request->all(),
            [
                'code' => 'required|min:4',
                'pourcentage' => 'required',
                'status' => 'required',
                'end_at' => 'required',
                'type' => 'required'
            ]);
        if ($validator->fails()) {
            $res->fail($validator->errors()->all());
            return ["data" => null, "res" => $res];
        }
        return ["data" => $validator->valid(), "res" => $res];
    }

    static public function createOne(Request $request): Result
    {

        $data = Promocode::validate($request);
        if ($data['data']) {
//            dd($data['data']);
            $coupon = Promocode::create($data['data']);
            $data['res']->success([
                "coupon" => $coupon
            ]);
        }
        return $data['res'];
    }
}
