<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\Translatable\HasTranslations;

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
