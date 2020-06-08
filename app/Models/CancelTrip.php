<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class CancelTrip extends Model
{
    //

    protected $casts = [
        'by_user' => 'boolean',
    ];
    public function getCreatedAtAttribute($value)
    {
        return Carbon::parse($value)->timestamp;
    }
    public function getUpdatedAtAttribute($value)
    {
        return Carbon::parse($value)->timestamp;
    }
    public function getByUserAttribute($value): bool
    {
        return $value;
    }

    public function trip()
    {
        return $this->belongsTo(Trip::class);
    }
}
