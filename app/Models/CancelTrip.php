<?php

namespace App\Models;

use Eloquent;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

/**
 * Post
 *
 * @mixin Eloquent
 */
class CancelTrip extends Model
{
    //
    protected $fillable=['raison','trip_id','by_user','status','note'];
    protected $casts = [
        'by_user' => 'boolean',
    ];
    public function getCreatedAtAttribute($value)
    {
        return Carbon::parse($value)->timestamp;
    }
    public function getUpdatedAtAttribute($value)
    {
        return Carbon::parse($value)->timestamp;
    }
    public function getByUserAttribute($value): bool
    {
        return $value;
    }

    public function trip()
    {
        return $this->belongsTo(Trip::class);
    }

    static public function updateOne(Request $request,int $id):Result{
        $res = new Result();
        $validator = Validator::make($request->all(),
            [
                'status' => 'required',
                'note' => 'required'
            ]);
        if($validator->fails()){
            $res->fail($validator->errors()->all());
            return $res;
        }
        $data= $validator->valid();
        $id = CancelTrip::where('id',$id)->update($data);
        $res->success([
            "cancelTrip"=>$id
        ]);
        return $res;
    }
}
