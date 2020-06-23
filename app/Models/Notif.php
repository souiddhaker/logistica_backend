<?php

namespace App\Models;

use Eloquent;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Spatie\Translatable\HasTranslations;

/**
 * Post
 *
 * @mixin Eloquent
 */
class Notif extends Model
{
    use HasTranslations;

    public $translatable = ['title', 'type' , 'description'];

    public function getCreatedAtAttribute($value)
    {
        return Carbon::parse($value)->timestamp;
    }
    public function getUpdatedAtAttribute($value)
    {
        return Carbon::parse($value)->timestamp;
    }
}
