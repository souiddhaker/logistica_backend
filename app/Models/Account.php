<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Eloquent;

/**
 * Post
 *
 * @mixin Eloquent
 */
class Account extends Model
{
    //
    protected $guarded = [];

    protected $fillable = ['balance'];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'updated_at','created_at','id'
    ];
}
