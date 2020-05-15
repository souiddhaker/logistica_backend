<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Trip extends Model
{
    //


    public function client()
    {
        return $this->belongsTo(Client::class);
    }
    public function driver()
    {
        return $this->belongsTo(Driver::class);
    }
}
