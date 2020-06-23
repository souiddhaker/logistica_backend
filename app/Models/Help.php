<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Eloquent;
use Spatie\Translatable\HasTranslations;

/**
 * Post
 *
 * @mixin Eloquent
 */
class Help extends Model
{
    use HasTranslations;

    public $translatable = ['title', 'description'];

    protected $hidden = ['created_at','updated_at'];

}
