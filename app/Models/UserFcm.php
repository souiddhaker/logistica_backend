<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserFcm extends Model
{
    protected $table = 'users_fcm';

    public function user()
    {
        return $this->belongsTo(User::class,'user_id');
    }
}
