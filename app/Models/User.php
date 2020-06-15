<?php

namespace App\Models;

use Eloquent;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Http\Request;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Validator;
use Laravel\Passport\HasApiTokens;
/**
 * Post
 *
 * @mixin Eloquent
 */
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
     * Get the promocodes for user.
     */
    public function promocodes()
    {
        return $this->belongsToMany(Promocode::class);
    }

    /**
     * Get the addresses for user.
     */
    public function addresses()
    {
        return $this->hasMany(Address::class);
    }

    /**
     * Get the documents for user.
     */
    public function documents()
    {
        return $this->hasMany(Document::class);
    }
    public function trips()
    {
        return $this->hasMany(Trip::class);
    }

    public function profileDriver()
    {
        return $this->hasOne(Driver::class);
    }

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'firstName', 'lastName', 'phone', 'email', 'password','image_url','roles'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token','updated_at','created_at','email_verified_at','roles','profileDriver'
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];
    static public function createOne(Request $request,$role="admin"):Result{
        $res = new Result();
        $validator = Validator::make($request->all(),
            [
                'firstName' => 'required',
                'lastName' => 'required',
                'email' => 'required|email|unique:users,email',
                'phone' => 'required|unique:users,phone'
            ]);
        if($validator->fails()){
            $res->fail($validator->errors()->all());
            return $res;
        }
        $data= $validator->valid();
        $data['password']=bcrypt("logistica");
        $data['roles']=json_encode([$role]);
        $user = User::create($data);
        $res->success([
            "user"=>$user
        ]);
        return $res;
    }
    static public function updateOne(Request $request,int $id,$role="admin"):Result{
        $res = new Result();
        $validator = Validator::make($request->all(),
            [
                'firstName' => 'required',
                'lastName' => 'required',
                'email' => 'required',
                'phone' => 'required'
            ]);
        if($validator->fails()){
            $res->fail($validator->errors()->all());
            return $res;
        }
        $data= $validator->valid();
        $data['password']=bcrypt("logistica");
        $data['roles']=json_encode([$role]);
        $id = User::where('id',$id)->update($data);
        $res->success([
            "user"=>$id
        ]);
        return $res;
    }

    /***
     * @param string $role
     * @return $this
     */
    public function addRole(string $role)
    {
        $roles = $this->getRoles();
        $roles[] = $role;

        $roles = array_unique($roles);
        $this->setRoles($roles);

        return $this;
    }

    /**
     * @param array $roles
     * @return $this
     */
    public function setRoles(array $roles)
    {
        $this->setAttribute('roles', $roles);
        return $this;
    }

    /***
     * @param $role
     * @return mixed
     */
    public function hasRole($role)
    {
        return in_array($role, $this->getRoles());
    }

    /***
     * @param $roles
     * @return mixed
     */
    public function hasRoles($roles)
    {
        $currentRoles = $this->getRoles();
        foreach($roles as $role) {
            if ( ! in_array($role, $currentRoles )) {
                return false;
            }
        }
        return true;
    }

    /**
     * @return array
     */
    public function getRoles()
    {
        $roles = $this->getAttribute('roles');

        if (is_null($roles)) {
            $roles = [];
        }

        return $roles;
    }
}
