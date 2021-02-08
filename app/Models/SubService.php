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
class SubService extends Model
{

    use HasTranslations;

    public $translatable = ['label'];
    protected $table = 'subservices';

    //

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'created_at', 'updated_at','pivot'
    ];

    public function service(){
        $this->belongsTo(Service::class);
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
        $id = SubService::where('id', $id)->update($request->all());
        $res->success($id);
        return $res;
    }
}
