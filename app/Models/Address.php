<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Address extends Model
{
    protected $table = 'address';

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'created_at', 'updated_at','longitude','lattitude'
    ];

    /**
     * Get the user that owns the card.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
