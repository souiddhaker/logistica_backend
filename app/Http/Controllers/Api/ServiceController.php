<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\CarCategory;
use App\Models\CategoryServices;
use App\Models\Price;
use App\Models\Result;
use App\Models\Service;
use App\Models\SubService;
use Illuminate\Http\Request;
use Validator;

class ServiceController extends Controller
{

    public function getListCarCategories(Request $request)
    {
        $res  =  new Result();

        $validator = Validator::make($request->all(),
            [
                'distance' => 'required|numeric',
                'nbr_bags' => 'required']);
        if ($validator->fails())
        {
            if($validator->errors()->has("distance"))
            {
                $res->fail(trans('messages.distance_error'));
                return response()->json($res, 200);
            }else{
                $res->fail(trans('messages.distance_error'));
                return response()->json($res, 200);
            }
        }

        $distance = $request['distance'];
        if($distance>=1 && $distance<=10)
        {
            $attr="price_1";
        }
        elseif ($distance>10 && $distance<=100)
        {
            $attr="price_100";
        }
        else
        {
            $attr="price_101";
        }

        $listCarCategories = CarCategory::where('capacity','>=', $request['nbr_bags'])->get();

        $list = [];
        foreach ($listCarCategories as $type){
            $carTypeWithPrice = $type;
            $carTypeWithPrice['price'] = $type->price + ($type[$attr]*$distance);
            array_push($list,$carTypeWithPrice);
        }

        $res->success($list);
        $res->message = trans('messages.list_cars');
        return response()->json($res,200);
    }

    public function getListServices(Request $request)
    {
        $res  =  new Result();

        $validator = Validator::make($request->all(),
            [
                'bags' => 'required'
            ]);
        if ($validator->fails())
        {
            $res->fail(trans('messages.bags_error'));
            return response()->json($res, 200);
        }

        $nbrBags = $request['bags'];
        $listCategory =  CategoryServices::all();
        $listServices =[];
        foreach ($listCategory as $category){
            $listServices[$category->title] = Service::where('category_id', $category->id)->get();
            foreach ($listServices[$category->title] as $service)
            {
                $subServices = SubService::where('service_id',$service->id)->get();
                if (count($subServices)>0)
                {
                    foreach ($subServices as $subservice){
                        $subservice['price'] = $subservice['price']*$nbrBags;
                    }
                    $service['sub_services'] = $subServices;
                }else{
                    $service['price'] = $service['price']*$nbrBags;
                }
            }
        }
        $res->success($listServices);
        $res->message = trans('messages.services_list');

        return response()->json($res,200);
    }

    public function listCar()
    {
        $res = new Result();
        $listCars = CarCategory::all();
        $res->response = $listCars;
        $res->success = true;
        return response()->json($res,200);
    }

    public function services()
    {
        return response()->json(Service::getAll(),200);
    }

    public function update(Request $request)
    {
        $data = $request->all();
        foreach ($data['services'] as $service)
        {
            if (isset($service['sub_services']))
            {
                foreach ($service['sub_services'] as $subservices)
                {
                    SubService::where('id',$subservices['id'])->update(collect($subservices)->except(['category_id'])->all());
                }
            }
            Service::where('id',$service['id'])->update(collect($service)->except(['sub_services','category_id'])->all());
        }
        return response()->json(Service::getAll(),200);
    }
}
