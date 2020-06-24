<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

use Eloquent;

/**
 * Post
 *
 * @mixin Eloquent
 */
class UserFcm extends Model
{
    protected $table = 'users_fcm';

    public function user()
    {
        return $this->belongsTo(User::class,'user_id');
    }
}
