<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Driver extends User
{
    //
    protected $table = 'drivers';


    public function documents()
    {
        return $this->belongsToMany(Document::class);
    }

    public function trips()
    {
        return $this->hasMany(Trip::class);
    }

    public function currentTrip()
    {
        return $this->hasOne(Trip::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function carType()
    {
        return $this->hasOne(CarCategory::class);
    }

    public function ratings()
    {
        return $this->hasMany(Rating::class);
    }
}
