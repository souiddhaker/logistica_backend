<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class Rating extends Model
{
    //
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
        return $this->belongsTo(Trip::class);
    }
}
