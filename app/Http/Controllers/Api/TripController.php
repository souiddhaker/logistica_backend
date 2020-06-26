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
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Validator;
class TripController extends Controller
{

    public function noteDriver(Request $request)
    {
        $res  =  new Result();

        $validator = Validator::make($request->all(),
            [
                'trip_id' => 'required',
                'note' => 'required'
            ]);
        if ($validator->fails()) {
            $res->fail(trans('messages.note_error'));
            return response()->json($res, 200);
        }

        $trip = Trip::find($request->trip_id);

        if ($trip)
        {
            $trip->update(['driver_note' => $request->note]);
            $res->response['trip_id']  = $request->trip_id;
            $res->response['note'] = $request->note;
        }else{
            $res->fail(trans('messages.trip_not_found'));
        }
        return response()->json($res,200);
    }

    public function listTrips()
    {

        $res = new Result();
        $user = User::find(Auth::id());
        $statusCurrent =['0','1','-1'];

        $listTrips  = [];
        $currentTrip = Trip::select('id','status','pickup_at','total_price','driver_id','user_id','created_at')
            ->where(function($q) use($user,$statusCurrent) {
                if ($user->getRoles() === json_encode(['client'])){
                    $q->where('user_id', $user->id)
                    ->whereIn('status', $statusCurrent);
                }else{
                    $q->where('driver_id', $user->id)
                    ->whereIn('status', ['-1','1']);
                }
            })
            ->with('driver','user','addresses')
            ->orderBy('updated_at', 'desc')->paginate(10)->toArray();
        $finishedTrip = Trip::select('id','status','pickup_at','total_price','driver_id','user_id','created_at')->where('status','=','2')
            ->where(function($q) use($user) {
                if ($user->getRoles() === json_encode(['client']))
                    $q->where('user_id', $user->id);
                else
                    $q->where('driver_id', $user->id);
            })->with('driver','user','addresses')->orderBy('updated_at', 'desc')->paginate(10)->toArray();
        $canceledTrip = Trip::select('id','status','pickup_at','total_price','driver_id','user_id','created_at')->where('status','=','3')
            ->where(function($q) use($user) {
                if ($user->getRoles() === json_encode(['client']))
                    $q->where('user_id', $user->id);
                else
                    $q->where('driver_id', $user->id);
            })->with('driver','user','addresses')->orderBy('updated_at', 'desc')->paginate(10)->toArray();

        $listTrips['current'] = $currentTrip['data'];
        $listTrips['finished'] = $finishedTrip['data'];
        $listTrips['canceled'] = $canceledTrip['data'];

        $res->success($listTrips);
        return response()->json($res,200);
    }

    public function search(Request $request)
    {
        $res = new Result();
        $user = User::find(Auth::id());
        $key = $request->input('key', "");
        $page = $request->input('page', 1);
        switch ($key) {
            case "0":
                $trips = Trip::select('id','status','pickup_at','total_price','driver_id','user_id','created_at')
                    ->where(function($q) use($user) {
                        if ($user->getRoles() === json_encode(['client']))
                            $q->where('user_id', $user->id);
                    })
                    ->where('status','=',$key)
                    ->with('driver','user','addresses')
                    ->orderBy('updated_at', 'desc')->paginate(10)->toArray();
                break;
            case "4":
                $trips = Trip::select('id','status','pickup_at','total_price','driver_id','user_id','created_at')
                    ->where(function($q) use($user) {
                        if ($user->getRoles() === json_encode(['client'])){
                            $q->where('user_id', $user->id)
                                ->whereIn('status', ['-1','1','0']);
                        }else{
                            $q->where('driver_id', $user->id)
                                ->whereIn('status', ['-1','1']);
                        }
                    })
                    ->with('driver','user','addresses')
                    ->orderBy('updated_at', 'desc')->paginate(10)->toArray();
                break;
            default:
                $trips = Trip::select('id','status','pickup_at','total_price','driver_id','user_id','created_at')
                    ->where(function($q) use($user) {
                        if ($user->getRoles() === json_encode(['client'])){
                            $q->where('user_id', $user->id);
                        }else{
                            $q->where('driver_id', $user->id);
                        }
                    })
                    ->where('status','=',$key)
                    ->with('driver','user','addresses')
                    ->orderBy('updated_at', 'desc')->paginate(10)->toArray();
        }

        foreach ($trips['data']  as &$elem)
        {
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

    public function createTrip(Request $request)
    {
        $res = new Result();

        $data = $request->all();
        $trip = new Trip();
        $trip->save();
        // status 0 not confirmed by driver
        $trip->status = '0';
        $trip->total_price = $data['total_price'];
        $trip->nbr_luggage = $data['nbr_luggage'];
        $trip->driver_note = $data['note_driver'];
        $trip->route = $data['route'];
        $type_car = CarCategory::find($data['type_car_id']);

        if($type_car){
            $trip->type_car()->associate($type_car)->save();
        }

        $trip->pickup_at = $data['pickup_at'];


        $payment_method = Card::find($data['payment_method']);

        if($payment_method)
        {
            $trip->payment_method = $payment_method->id;
        }

        $trip->user_id = Auth::id();
        $trip->driver_id = null;

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
        if($listAttachements){
            foreach ($listAttachements as $attachementId){
                $attachement = Document::find($attachementId);
                if($attachement)
                $trip->attachements()->attach($attachement);
            }
        }

        $pickup_address = Address::where('place_id','=',request('place_idPickup'))->where('type','=', '1')->where('user_id','=',Auth::id())->first();
        $destination_address = Address::where('place_id','=',request('place_idDestination'))->where('type','=', '2')->where('user_id','=',Auth::id())->first();

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
        $driverController = new DriverController();
        $driverController->getListDriverForTrip($trip->id);
        $res->success($reslut);
        return response()->json($res,200);
    }


    public function attachAddressse()
    {
        //TODO : create trip with addresses
    }

    public function tripAttachements(array $attachementsCollection)
    {

        $documents = [];
        $documents['attachements']=[];
        $documents['reservation_hotel']=null;
        $documents['receipt']=null;
        foreach ($attachementsCollection as $document)
        {
            switch ($document['type']){
                case "1" : array_push($documents['attachements'],$document);
                    break;
                case "2" : $documents['reservation_hotel'] = $document;
                    break;
                case "3" : $documents['receipt'] = $document;
                    break;
            }
        }
        return $documents;
    }

    public function listServicesWithSubServices(Trip $trip)
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
        return $services;
    }


    public function getById(int $id)
    {
        $trip = Trip::where('id',$id)->with('driver','user:','addresses','type_car','cancelTrip')->first();
        if ($trip)
        {
            $trip->services = $this->listServicesWithSubServices($trip);
            $trip->payement_method = "Cash payment";
            $trip->rating = Rating::find($trip->rating_id);
            $attachementsCollection = collect($trip->attachements)->toArray();
            return array_merge($trip->toArray(),$this->tripAttachements($attachementsCollection));
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
            $res->fail(trans('message.trip_not_found'));
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
            $res->fail(trans('message.trip_not_found'));
            return response()->json($res,200);
        }
        $res->success($cancelTrip);
        $res->message = trans('message.cancel_trip');

        return response()->json($res,200);

    }

    public function rateTrip(Request $request)
    {
        $res = new Result();

        $data = $request->all();

        $rate = new Rating();
        $trip = Trip::find($data['trip_id']);
        if ($trip)
        {

            $rate->value = $data['value'];
            $rate->comment = $data['additionalComment'];
            $rate->user_id = Auth::id();
            $userDriver = Driver::where('user_id',$trip->driver_id)->first();
            $userDriver->ratings()->save($rate);
            $trip->rating_id = $rate->id;
            $trip->save();
            $res->success($rate);

        }else{
            $res->fail(trans('message.trip_not_found'));
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
            $res->fail(trans('message.trip_not_found'));
        }
        return response()->json($res,200);
    }


    public function uploadReceipt(Request $request)
    {
        $res = new Result();
        $trip = Trip::find($request['trip_id']);
        if ($trip && $trip->status == 2) {
            $documentController = new DocumentController();
            $response = $documentController->store($request)->getData();

            if ($response->success) {
                $attachement = Document::find($response->response[0]->id);
                $trip->attachements()->attach($attachement);
                $res->success = true;
            }
        }else{
            $res->fail('Fail to upload');
        }
        return response()->json($res,200);
    }
}
