<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\CarCategory;
use App\Models\CategoryServices;
use App\Models\Price;
use App\Models\Result;
use App\Models\Service;
use App\Models\SubService;
use App\Models\Trip;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Validator;

class ServiceController extends Controller
{
    //

    public function getListCarCategories(Request $request)
    {
        $res  =  new Result();

        $validator = Validator::make($request->all(),
            [
                'distance' => 'required']);
        if ($validator->fails()) {

            $res->fail("Please choose a pickup address");

            return response()->json($res, 200);
        }

        $distance = $request['distance'];

        $priceKm = Price::where('from','<=',$distance)->where('to','>=',$distance)->first();
        $listCarCategories = CarCategory::all();

        $list = [];

        foreach ($listCarCategories as $type){
            $carTypeWithPrice = $type;
            $carTypeWithPrice['price'] = $type->price + ($priceKm->cost * $distance);
            array_push($list,$carTypeWithPrice);
        }


        $res->success($list);
        $res->message = "List of car categories";
        return response()->json($res,200);
    }

    public function getListServices(Request $request)
    {


        $res  =  new Result();

        $validator = Validator::make($request->all(),
            [
                'bags' => 'required'
            ]);
        if ($validator->fails()) {

            $res->fail("Please choose a pickup address");

            return response()->json($res, 200);
        }

        $user = Auth::user();

        $oldTrip = Trip::where('user_id',$user->id)->where('status','temp')->get();

        foreach ($oldTrip as $trip)
        {
            $trip->delete();
        }

        $trip = new Trip();
        $trip->status = 'temp';
        $trip->save();


        $nbrBags = $request['bags'];
        $listCategory =  CategoryServices::all();
        $listServices =[];
        foreach ($listCategory as $category){
            $listServices[$category->title] = Service::where('category_id', $category->id)->get();
            $arraySubServices = [];
            foreach ($listServices[$category->title] as $service)
            {
                $subServices = SubService::where('service_id',$service->id)->get();
                if (count($subServices)>0){
                    foreach ($subServices as $subservice){
                        $subservice['price'] = $subservice['price']*$nbrBags;
                    }
                    $service['sub_services'] = $subServices;

                }else{
                    $service['price'] = $service['price']*$nbrBags;

                }
            }
        }
        $listServices['trip_id'] = $trip->id;
        $res->success($listServices);
        $res->message = "List services";

        return response()->json($res,200);
    }
}
