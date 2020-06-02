<?php

namespace App\Http\Controllers\Api;

use App\Models\Address;
use App\Models\CancelTrip;
use App\Models\CarCategory;
use App\Models\Card;
use App\Models\Service;
use App\Models\SubService;
use App\Models\Trip;
use App\Http\Controllers\Controller;
use App\Models\Result;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Validator;
class TripController extends Controller
{
    //

    public function noteDriver(Request $request)
    {
        $res  =  new Result();

        $validator = Validator::make($request->all(),
            [
                'trip_id' => 'required',
                'note' => 'required'
            ]);
        if ($validator->fails()) {

            $res->fail("Trip id and note are missing");

            return response()->json($res, 200);
        }

        $trip = Trip::find($request->trip_id);

        if ($trip)
        {
            $trip->update(['driver_note' => $request->note]);

            $res->response['trip_id']  = $request->trip_id;
            $res->response['note'] = $request->note;
            return response()->json($res,200);
        }else{
            $res->fail('Trip not found');
        }

        return response()->json($res,200);

    }


    public function listTrips()
    {
        $res = new Result();
        $listTrips  = [];
        $currentTrip = Trip::where('status','=','1')->where('user_id',Auth::id())->with('driver','user')->paginate(5);
        $finishedTrip = Trip::where('status','=','2')->where('user_id',Auth::id())->paginate(5);
        $canceledTrip = Trip::where('status','=','3')->where('user_id',Auth::id())->paginate(5);
        $listTrips['current'] = $currentTrip;
        $listTrips['finished'] = $finishedTrip;
        $listTrips['canceled'] = $canceledTrip;

        $res->success($listTrips);
        return response()->json($res,200);
    }

    public function confirmTrip(Request $request)
    {
        $res = new Result();

        $data = $request->all();

        $trip = Trip::where('user_id',Auth::id())->where('status','=','temp')->first();


        $trip->status = '1';
        $trip->total_price = $data['total_price'];
        $trip->nbr_luggage = $data['nbr_luggage'];

        $type_car = CarCategory::find($data['type_car_id']);

        if($type_car){
            $trip->type_car_id = $type_car->id;
        }

        $trip->pickup_at = $data['pickup_at'];

        $promocode = Card::find($data['promocode_id']);
        if($promocode){
            $trip->promocode_id = $promocode->id;
        }

        $payment_method = Card::find($data['payment_method']);
        if($payment_method){
            $trip->payment_method = $payment_method->id;
        }
        $trip->user_id = Auth::id();
        $trip->driver_id = 1;

        $trip->save();

        $listServices = $data['services'];
        $services = [];

        foreach ($listServices as $serviceId){

            $service = Service::find($serviceId);
            $trip->services()->attach($service);
            $subServices = SubService::where('service_id',$serviceId)->get();
            if (count($subServices)>0){
                foreach ($subServices as $subservice){
                    $subservice['price'] = $subservice['price']*$data['nbr_luggage'];
                }
                $service['sub_services'] = $subServices;

            }else{
                $service['price'] = $service['price']*$data['nbr_luggage'];
            }
            array_push($services,$service);
        }

         $pickup_address = Address::create([
             'primaryName' => request('primaryNamePickup'),
             'secondaryName' => request('secondaryPickup'),
             'place_id' => request('place_idPickup'),
             'type' => '1',
             'user_id' => Auth::id()
         ]);
        $destination_address = Address::create([
            'primaryName' => request('primaryNameDestination'),
            'secondaryName' => request('secondaryDestination'),
            'place_id' => request('place_idDestination'),
            'type' => '2',
            'user_id' => Auth::id()
        ]);
        $trip->addresses()->attach($pickup_address);
        $trip->addresses()->attach($destination_address);

        $reslut = $this->getById($trip->id);
        $res->success($reslut);
        return response()->json($res,200);
    }

    public function getById(int $id)
    {
        $trip = Trip::find($id);
        if ($trip)
        {
        $services = [];
        foreach ($trip->services as $item){

            $service = Service::find($item->id);

            $trip->services()->attach($service);
            $subServices = SubService::where('service_id',$item->id)->get();

            if (count($subServices)>0){
                foreach ($subServices as $subservice){
                    $subservice['price'] = $subservice['price']*$trip->nbr_luggage;
                }
                $service['sub_services'] = $subServices;

            }else{
                $service['price'] = $service['price']*$trip->nbr_luggage;
            }
            array_push($services,$service);
        }
        $addresses = [];

        foreach ($trip->addresses as $address){
            $tripAddress = Address::find($address->id);
            if ($tripAddress->type == '1'){
                $addresses['pickup'] = $tripAddress;
            }

            if ($tripAddress->type == '2'){
                $addresses['destination'] = $tripAddress;
            }
        }
        return $trip;

        }else
            return null;

    }

    public function getTrip(int $id)
    {
        $res = new Result();
        $trip = $this->getById($id);
        if ($trip)
            $res->success($trip);
        else
            $res->fail('trip not found');
        return response()->json($res,200);

    }

    public function cancelTrip(Request $request)
    {

        $res = new Result();
        $data = $request->all();

        $trip = Trip::find($data['trip_id']);

        if ($trip)
        {
            $cancelTrip = new CancelTrip();
            $cancelTrip->raison = $data['raison'];
            $cancelTrip->by_user = $data['canceledByUser'];
            $trip->status = '3';
            $trip->cancelRaison()->save($cancelTrip);

        }else{
            $res->fail('trip not found');
            return response()->json($res,200);
        }
        return response()->json($res,200);

    }
}
