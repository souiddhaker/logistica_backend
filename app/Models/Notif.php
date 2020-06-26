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

    public $translatable = ['Title', 'type' , 'description'];
    protected $guarded = [];

    protected $hidden = ['seen'];
    public function getCreatedAtAttribute($value)
    {
        return Carbon::parse($value)->timestamp;
    }
    public function getUpdatedAtAttribute($value)
    {
        return Carbon::parse($value)->timestamp;
    }

    public function trip()
    {
        return $this->hasOne(Trip::class);
    }
}
