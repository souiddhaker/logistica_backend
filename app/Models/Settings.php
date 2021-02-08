<?php

namespace App\Models;

use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Model;
use Eloquent;
use Illuminate\Support\Facades\Validator;
/**
 * Post
 *
 * @mixin Eloquent
 */
class Settings extends Model
{

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'company_percent','abort_percent_client','abort_percent_captain','percent_from','coverage_range'
    ];
    protected $hidden = ['created_at','updated_at','abort_percent_client','abort_percent_captain'];

    static public function updateOne(array $request):Result{
        $res = new Result();
        $validator = Validator::make($request,
            [
                'company_percent' => 'required',
                'percent_from' => 'required',
                'coverage_range' => 'required'
            ]);

        if($validator->fails()){
            $res->fail($validator->errors()->all());
            return $res;
        }
        $data= $validator->valid();
        Settings::updateOrCreate(["id"=>1],$data);
        $res->success([]);
        return $res;
    }
}
