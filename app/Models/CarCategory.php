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
class CarCategory extends Model
{
    //
    use HasTranslations;

    public $translatable = ['model'];
    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'created_at', 'updated_at','type'
    ];
}
