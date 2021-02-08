<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BillingAddress extends Model
{
    //
    protected $table = 'billing_address';
    protected $guarded = [];
    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'created_at', 'updated_at','user_id' ,'id'
    ];


    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
