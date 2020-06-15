<?php

namespace App\Models;

use Carbon\Carbon;
use Eloquent;
use Illuminate\Database\Eloquent\Model;

/**
 * Post
 *
 * @mixin Eloquent
 */
class Trip extends Model
{
    //

    /**
     * Trip Status :
     * 0 : not confirmed by driver
     * 1 : current
     * 2 : finished
     * 3 : canceled
     *
     */

    protected $dates = ['created_at', 'updated_at', 'pickup_at'];

    public function getCreatedAtAttribute($value)
    {
        return Carbon::parse($value)->timestamp;
    }
    public function getUpdatedAtAttribute($value)
    {
        return Carbon::parse($value)->timestamp;
    }
    public function getPickupAtAttribute($value)
    {
        return Carbon::parse($value)->timestamp;
    }
    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'created_at', 'updated_at','total_amount','type_car_id','promocode_id','user_id','driver_id','payment_method','subservices'
    ];
    public function services()
    {
        return $this->belongsToMany(Service::class);
    }

    public function subservices()
    {
        return $this->belongsToMany(SubService::class);
    }

    public function addresses()
    {
        return $this->belongsToMany(Address::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
    public function driver()
    {
        return $this->belongsTo(User::class);
    }

    public function attachements()
    {
        return $this->belongsToMany(Document::class);
    }

    public function cancelTrip()
    {
        return $this->hasOne(CancelTrip::class);
    }

    public function promocode()
    {
        return $this->belongsTo(Promocode::class);
    }

    public function type_car()
    {
        return $this->belongsTo(CarCategory::class);
    }


}
