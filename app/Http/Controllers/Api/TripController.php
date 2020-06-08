<?php

namespace App\Http\Controllers\Api;

use App\Models\Address;
use App\Models\CancelTrip;
use App\Models\CarCategory;
use App\Models\Card;
use App\Models\Document;
use App\Models\Driver;
use App\Models\Rating;
use App\Models\Service;
use App\Models\SubService;
use App\Models\Trip;
use App\Http\Controllers\Controller;
use App\Models\Result;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use PhpParser\Node\Expr\Cast\Object_;
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

    public function tripDataFromArray(array $data)
    {
        $arrayModel = [];
        foreach ($data as $elem)
        {
            array_push($arrayModel,$this->getById($elem['id']));
        }
        return $arrayModel;
    }

    public function listTrips()
    {
        $res = new Result();
        $listTrips  = [];
        $currentTrip = Trip::select('id','total_price','driver_id')->where('status','=','1')->where('user_id',Auth::id())->with('driver','addresses')->orderBy('updated_at', 'desc')->paginate(10)->toArray();
        $finishedTrip = Trip::select('id','total_price','driver_id')->where('status','=','2')->where('user_id',Auth::id())->with('driver','addresses')->orderBy('updated_at', 'desc')->paginate(10)->toArray();
        $canceledTrip = Trip::select('id','total_price','driver_id')->where('status','=','3')->where('user_id',Auth::id())->with('driver','addresses')->orderBy('updated_at', 'desc')->paginate(10)->toArray();


        $listTrips['current'] = $currentTrip['data'];
        $listTrips['finished'] = $finishedTrip['data'];
        $listTrips['canceled'] = $canceledTrip['data'];

        $res->success($listTrips);
        return response()->json($res,200);
    }

    public function search(Request $request)
    {
        $res = new Result();

        $key = $request->input('key', "");
        $page = $request->input('page', 1);
        $trips = Trip::select('id','total_price','driver_id')->where('status', '=', $key)->where('user_id','=',Auth::id())->with('driver','addresses')->orderBy('updated_at', 'desc')
            ->paginate(10)
            ->toArray();

//        $trips['data'] = $this->tripDataFromArray($trips['data']);
        foreach ($trips['data']  as &$elem){

            unset($elem['user']['roles']);
        }

        $trips["first_page_url"] = $request->url() . "?key=" . $key . "&page=" . "1";
        $trips["last_page_url"] = $request->url() . "?key=" . $key . "&page=" . $trips['last_page'];

        if ($trips['last_page'] > $page) {
            $nextpage = (int) $page + 1;
            $trips["next_page_url"] = $request->url() . "?key=" . $key . "&page=" . $nextpage;
        }

        $res->success($trips);
        return response()->json($res, 200);
    }

    public function confirmTrip(Request $request)
    {
        $res = new Result();

        $data = $request->all();



        $trip = new Trip();
        $trip->save();
        $trip->status = '1';
        $trip->total_price = $data['total_price'];
        $trip->nbr_luggage = $data['nbr_luggage'];
        $trip->driver_note = $data['note_driver'];
//        $trip->payment_method = $data['note_driver'];

        $type_car = CarCategory::find($data['type_car_id']);

        if($type_car){
            $trip->type_car()->associate($type_car)->save();
        }


        $trip->pickup_at = $data['pickup_at'];

        $promocode = Card::find($data['promocode_id']);
        if($promocode){
            $trip->promocode_id = $promocode->id;
        }

        $payment_method = Card::find($data['payment_method']);

        if($payment_method)
        {
            $trip->payment_method = $payment_method->id;
        }

        $trip->user_id = Auth::id();
        $trip->driver()->save(Driver::find(1));

        $trip->save();

        $listServices = $data['services'];
        foreach ($listServices as $serviceId){

            $service = Service::find($serviceId);

            if ($service)
            {
                $service['price'] = $service['price']*$data['nbr_luggage'];

                $trip->services()->attach($service);
            }


        }
        $listSubServices = $data['sub_services'];

        foreach ($listSubServices as $subServiceId){
            $subService = SubService::find($subServiceId);
            if ($subService)
            {
                $subService['price'] = $subService['price']*$data['nbr_luggage'];

                $trip->subservices()->attach($subService);
            }
        }

        $listAttachements = $data['attachements'];
        if($listServices){
            foreach ($listAttachements as $attachementId){
                $attachement = Document::find($attachementId);
                if($attachement)
                $trip->attachements()->save($attachement);
            }
        }

        $pickup_address = Address::where('place_id','=',request('place_idPickup'))->where('user_id','=',Auth::id())->first();
        $destination_address = Address::where('place_id','=',request('place_idDestination'))->where('user_id','=',Auth::id())->first();

        if(!$pickup_address or (request('place_idPickup')== ""))
        {

            $pickup_address = Address::create([
                'primaryName' => request('primaryNamePickup'),
                'secondaryName' => request('secondaryPickup'),
                'place_id' => request('place_idPickup'),
                'longitude' => request('longitudePickup'),
                'lattitude' => request('lattitudePickup'),
                'type' => '1',
                'user_id' => Auth::id()
            ]);
        }
        if (!$destination_address or (request('place_idDestination')== ""))
        {
            $destination_address = Address::create([
                'primaryName' => request('primaryNameDestination'),
                'secondaryName' => request('secondaryDestination'),
                'place_id' => request('place_idDestination'),
                'longitude' => request('longitudeDestination'),
                'lattitude' => request('lattitudeDestination'),
                'type' => '2',
                'user_id' => Auth::id()
            ]);
        }

        $trip->addresses()->attach($pickup_address);
        $trip->addresses()->attach($destination_address);

        $reslut = $this->getById($trip->id);
        $res->success($reslut);
        return response()->json($res,200);
    }

    public function getById(int $id)
    {
        $trip = Trip::where('id',$id)->with('driver','attachements','addresses','promocode','type_car','cancelTrip','rating')->first();
        if ($trip)
        {
            $services=[];
            $subservicesCollection = collect($trip->subservices)->toArray();

            foreach($trip->services as $service){
                $serviceModel = $service;

                $id = $service->id;
                $serviceModel['sub_services']= array_filter($subservicesCollection, function ($event) use ($id) {
                    return $event['service_id'] === $id;
                });

                array_push($services,$serviceModel);

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


        $trip->services = $services;

        $trip->payement_method = "Cash payment";
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
            $trip->cancelTrip()->save($cancelTrip);
            $trip->save();

        }else{
            $res->fail('trip not found');
            return response()->json($res,200);
        }
        $res->success($cancelTrip);
        $res->message = ['en' => 'Canceled Trip','ar' => 'Canceled Trip'];

        return response()->json($res,200);

    }

    public function rateTrip(Request $request)
    {
        $res = new Result();

        $data = $request->all();

        $rate = new Rating();
        $trip = Trip::find($data['trip_id']);
            if ($trip){
                $rate->value = $data['value'];
                $rate->comment = $data['additionalComment'];
                $rate->user_id = Auth::id();
                $trip->rating()->save($rate);
                $res->success($rate);

            }else{
                $res->fail('Trip not found');
            }
        return response()->json($res,200);
    }


    public function changeStatus(Request $request)
    {
        $res = new Result();
        $data = $request->all();
        $trip = Trip::find($data['trip_id']);
        if ($trip)
        {
            $trip->status = $data['status'];
            $trip->save();
            $res->success($this->getById($trip->id));
        }else{
            $res->fail('trip not found');
        }
        return response()->json($res,200);
    }
}
