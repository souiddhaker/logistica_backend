<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Passport\HasApiTokens;

class User extends Authenticatable
{
    use Notifiable, HasApiTokens;



    /**
     * Get the cars for user.
     */
    public function cards()
    {
        return $this->hasMany(Card::class);
    }

    /**
     * Get the cars for user.
     */
    public function addresses()
    {
        return $this->hasMany(Address::class);
    }

    /**
     * Get the cars for user.
     */
    public function documents()
    {
        return $this->hasMany(Document::class);
    }


    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'firstName', 'lastName', 'phone', 'email', 'password','image_url'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token','updated_at','created_at','email_verified_at'
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];
}
