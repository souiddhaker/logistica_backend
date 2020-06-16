<?php

namespace App\Models;

use Eloquent;
use Illuminate\Support\Facades\Validator;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Spatie\Translatable\HasTranslations;

/**
 * Post
 *
 * @mixin Eloquent
 */
class CarCategory extends Model
{
    //
    use HasTranslations;

    protected  $fillable = ['model', 'type', 'capacity', 'price', 'image', 'range_luggage', 'price_100', 'price_101','price_1'];
    public $translatable = ['model'];
    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'created_at', 'updated_at'
    ];

    static public function updateOne(Request $request, int $id): Result
    {

        $data = CarCategory::validate($request);
        if ($data['data']) {
            $id = CarCategory::where('id', $id)->update($data['data']);
            $data['res']->success([
                "typeCar" => $id
            ]);
        }
        return $data['res'];
    }

    static public function validate(Request $request, $create = true)
    {

        $res = new Result();
        $validator = Validator::make($request->all(),
            [
                'label_ar' => 'required',
                'label_en' => 'required',
                'type' => 'required:between:0,2',
                'capacity' => 'required',
                'price' => 'required',
                'image' => 'required',
                'price_100' => 'required',
                'price_101' => 'required',
                'price_1'=>'required'
            ]);
        if ($validator->fails()) {
            $res->fail($validator->errors()->all());
            return ["data" => null, "res" => $res];
        }
        return ["data" => CarCategory::mapInput($request), "res" => $res];
    }

    static public function mapInput(Request $request)
    {
        $arr = array_merge(
            $request->all(),
            [
                'range_luggage' => ($request->get('capacity') < 10 ? '1-' : '') . $request->get('capacity'),
                "model" => ['ar' => $request->get('label_ar'), 'en' => $request->get('label_en')]
            ]);

        return array_filter($arr, function ($key) {
            $attr = ['model', 'type', 'capacity', 'price', 'image', 'range_luggage', 'type', 'price_100', 'price_101','price_1'];
            return in_array($key,$attr);
        },ARRAY_FILTER_USE_KEY);
    }

    static public function createOne(Request $request): Result
    {

        $data = CarCategory::validate($request);
        if ($data['data']) {
            $coupon = CarCategory::create($data['data']);
            $data['res']->success([
                "typeCar" => $coupon
            ]);
        }
        return $data['res'];
    }
}
