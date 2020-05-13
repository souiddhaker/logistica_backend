<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class Promocode extends Model
{
    //

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'created_at', 'updated_at','nbr_uses'
    ];

    protected $dates  = ['end_at'];
    public function getEndAtAttribute($value)
    {
        return Carbon::parse($value)->timestamp;
    }
}
