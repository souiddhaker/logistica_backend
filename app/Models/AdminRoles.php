<?php

namespace App\Models;

use Eloquent;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

/**
 * Post
 *
 * @mixin Eloquent
 */
class AdminRoles extends Model
{
    //
    protected $fillable = ['roles', 'user_id'];

    protected $casts = [
        'roles' => 'array',
    ];
    protected $hidden = ['user_id', 'created_at', 'updated_at','id'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    static public function updateOne(Request $request, int $id): Result
    {
        $res = new Result();
        $validator = Validator::make($request->all(),
            [
                'roles' => 'required'
            ]);
        if ($validator->fails()) {
            $res->fail($validator->errors()->all());
            return $res;
        }
        $data = $validator->valid();
        $id = AdminRoles::updateOrCreate(['user_id' => $id], ["roles" => $data['roles']]);
        $res->success([
            "adminRoles" => $id
        ]);
        return $res;
    }
}
