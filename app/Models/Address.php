<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Address extends Model
{
    /**
     *
     * Address types :
     * 1 : Pickup trip
     * 2 : Destination trip
     * 3 : Position Driver
     *
     */


    protected $table = 'address';
    protected $guarded = [];
    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'created_at', 'updated_at','pivot','user_id'
    ];

    /**
     * Get the user that owns the card.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }


    public function trips(){
        $this->belongsToMany(Trip::class);
    }
}
