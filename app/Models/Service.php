<?php

namespace App\Models;

use Eloquent;
use Illuminate\Database\Eloquent\Model;
use Spatie\Translatable\HasTranslations;

/**
 * Post
 *
 * @mixin Eloquent
 */
class Service extends Model
{
    //
       use HasTranslations;

    public $translatable = ['label'];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'created_at', 'updated_at','pivot'
    ];

    public function subservices(){
        return $this->hasMany(SubService::class);
    }

    public function trips(){
        $this->belongsToMany(Trip::class);
    }
}
