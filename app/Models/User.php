<?php

namespace App\Models;

use Eloquent;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Http\Request;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Validator;
use Laravel\Passport\HasApiTokens;
use Tymon\JWTAuth\Contracts\JWTSubject;

/**
 * Post
 *
 * @mixin Eloquent
 */
class User extends Authenticatable implements JWTSubject
{
    use Notifiable, HasApiTokens,SoftDeletes;



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
    /**
     * Get admin role
     */
    public function adminRoles()
    {
        return $this->hasOne(AdminRoles::class);
    }
    public function trips()
    {
        return $this->hasMany(Trip::class);
    }

    public function profileDriver()
    {
        return $this->hasOne(Driver::class);
    }

    public function fcmUser(){
        return $this->hasOne(UserFcm::class);
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
        'password', 'remember_token','updated_at','created_at','email_verified_at','roles','profileDriver','deleted_at'
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];
    static public function validate(Request $request,$role="admin",$create = true):\Illuminate\Validation\Validator{

        $roleData=
            [
                'firstName' => 'required',
                'lastName' => 'required',
                'email' => 'required|email|unique:users,email',
                'phone' => 'required|unique:users,phone'
            ];
        if(!$create){
            $roleData['email']='required';
            $roleData['phone']='required';
        }
        if($role === "admin"){
            $roleData['roles']='required';
        }
        if($role === "captain"){
            $roleData['is_active']='required';
        }
        return Validator::make($request->all(),$roleData);
    }
    static public function filterRequest($data){
        $data= array_filter($data,function ($key){
            $User = new User();
           return in_array($key,$User->getFillable());
        },ARRAY_FILTER_USE_KEY);
        return $data;
    }
    static public function createOne(Request $request,$role="admin"):Result{
        $res = new Result();
        $validator = User::validate($request,$role);
        if($validator->fails()){
            $res->fail($validator->errors()->all());
            return $res;
        }
        $data= $validator->valid();
        $data['password']=bcrypt("logistica");
        $data['roles']=json_encode([$role]);
        $user = User::create(User::filterRequest($data));
        if($role=="captain"){
            $user->profileDriver()->create(["is_active"=>$data['is_active']]);
        }
        if($role==="admin"){
            AdminRoles::updateOne($request->all(),$user['id']);
        }
        $res->success([
            "user"=>$user
        ]);
        return $res;
    }

    static public function updateOne(Request $request,int $id,$role="admin"):Result{
        $res = new Result();
        $validator = User::validate($request,$role,false);
        if($validator->fails()){
            $res->fail($validator->errors()->all());
            return $res;
        }
        $data= $validator->valid();
        $data['password']=bcrypt("logistica");
        $data['roles']=json_encode([$role]);
        $idUser=User::where('id',$id)->update(User::filterRequest($data));
        if($role=="captain"){
            Driver::updateOrCreate(['user_id'=>$id],['is_active'=>$data['is_active']]);
        }
        if($role==="admin"){
                AdminRoles::updateOne($request->all(),$id);
        }
        $res->success([
            "user"=>$idUser
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

    /**
     * Get the identifier that will be stored in the subject claim of the JWT.
     *
     * @return mixed
     */
    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    /**
     * Return a key value array, containing any custom claims to be added to the JWT.
     *
     * @return array
     */
    public function getJWTCustomClaims()
    {
        return [];
    }
}
