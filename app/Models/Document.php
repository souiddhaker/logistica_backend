<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Document extends Model
{
    //


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
