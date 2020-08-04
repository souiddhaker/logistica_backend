<?php

namespace App\Models;

use Eloquent;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Spatie\Translatable\HasTranslations;
use Illuminate\Support\Facades\Validator;

/**
 * Post
 *
 * @mixin Eloquent
 */
class Service extends Model
{
    //
       use HasTranslations;

    public $translatable = ['label'];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'created_at', 'updated_at','pivot'
    ];

    public function subservices(){
        return $this->hasMany(SubService::class);
    }

    public function trips(){
        $this->belongsToMany(Trip::class);
    }

    static public function updateOne(Request $request,$id):Result{
        $res = new Result();
        $validator = Validator::make($request->all(),
            [
                'label.ar' => 'required',
                'label.en' => 'required',
                'price' => 'required'
            ]);

        if($validator->fails()){
            $res->fail($validator->errors()->all());
            return $res;
        }
        $id = Service::where('id', $id)->update($request->all());
        $res->success($id);
        return $res;
    }

    static public function getAll()
    {
        $res = new Result();
        $listCategory =  CategoryServices::all();
        $listServices =[];
        foreach ($listCategory as $category){
            $listServices[$category->title] = Service::where('category_id', $category->id)->get();
            foreach ($listServices[$category->title] as $service)
            {
                $subServices = SubService::where('service_id',$service->id)->get();
                if (count($subServices)>0)
                {
                    $service['sub_services'] = $subServices;
                }
            }
        }
        $res->success($listServices);
        return $res;
    }
}
