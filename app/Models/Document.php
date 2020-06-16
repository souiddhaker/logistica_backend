<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Document extends Model
{
    //

    /**
     * 1 trip attachement
     * 2 trip Hotel reservation
     * 3 trip receipt
     * 4 Driver profile identity
     * 5 Driver car photo
     * 6 Driver licence
     */

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'updated_at','created_at','pivot'
    ];

    /**
     * Get the user that owns the card.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

}
