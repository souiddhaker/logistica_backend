<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Driver extends User
{
    //
    protected $table = 'drivers';

    public function trips()
    {
        return $this->hasMany(Trip::class);
    }

    public function currentTrip()
    {
        return $this->hasOne(Trip::class);
    }
}
