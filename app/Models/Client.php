<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Client extends Model
{
    //
    protected $table = 'clients';


    public function user() {
        return $this->belongsTo('App\Models\User', 'user_id');
    }
    public function trips()
    {
        return $this->hasMany(Trip::class);
    }
}
