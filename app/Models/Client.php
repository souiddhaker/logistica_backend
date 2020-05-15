<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Client extends User
{
    //

    public function trips()
    {
        return $this->hasMany(Trip::class);
    }
}
