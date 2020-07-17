<?php

namespace App\Http\Controllers\Api;

use App\Models\Account;
use App\Models\Address;
use App\Models\CancelTrip;
use App\Models\CarCategory;
use App\Models\Card;
use App\Models\Document;
use App\Models\Driver;
use App\Models\Notif;
use App\Models\Rating;
use App\Models\Service;
use App\Models\SubService;
use App\Models\Trip;
use App\Http\Controllers\Controller;
use App\Models\Result;
use App\Models\User;
use Carbon\Carbon;
use GoogleMaps;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Validator;
class TripController extends Controller
{
    public $driverController;

    public function __construct()
    {
        $this->driverController = new DriverController();
    }
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

    public function listOfRequest()
    {
        $res = new Result();
        $today = Carbon::parse(Carbon::now())->timestamp;
        $listRequestTrip = Trip::select('trips.id','status','pickup_at','total_price','trips.driver_id','trips.user_id','trips.created_at')
            ->where('status','=','0')
            ->leftJoin('notifs','notifs.trip_id','=','trips.id')
            ->where('notifs.user_id','=',Auth::id())
            ->where('notifs.trip_step','=',1)
            ->where('notifs.driver_id','!=',null)
            ->whereDate('trips.pickup_at', '>', $today)
            ->with('driver','user','addresses')
            ->orderBy('trips.updated_at', 'desc')->paginate(10)->toArray();
        $res->success($listRequestTrip);
        return response()->json($res, 200);
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
                $trips = Trip::select(['id','status','pickup_at','total_price','driver_id','user_id','created_at'])
                    ->where(function($q) use($user) {
                        if ($user->getRoles() === json_encode(['client']))
                            $q->where('user_id', $user->id);
                    })
                    ->where('status','=',$key)
                    ->with('driver','user','addresses')
                    ->orderBy('updated_at', 'desc')->paginate(10)->toArray();
                break;
            case "4":
                $trips = Trip::select(['id','status','pickup_at','total_price','driver_id','user_id','created_at'])
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
                $trips = Trip::select(['id','status','pickup_at','total_price','driver_id','user_id','created_at'])
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
        $trip = Trip::create(['status'=>'0', 'total_price'=>$data['total_price'],'nbr_luggage'=>$data['nbr_luggage'],
        'driver_note'=>$data['note_driver'],'route'=>$data['route'],'trip_duration'=>$data['trip_duration'],'user_id'=>Auth::id(),'pickup_at'=>$data['pickup_at']]);

        $type_car = CarCategory::find($data['type_car_id']);

        if($type_car){
            $trip->type_car()->associate($type_car)->save();
        }

        $payment_method = Card::find($data['payment_method']);

        if($payment_method)
        {
            $trip->payment_method = $payment_method->id;
        }
        $trip->save();

        $this->addAttachementsToTrip($data,$trip);
        $this->attachServices($data,$trip);
        $this->attachAddresses($request,$trip);

        $reslut = $this->getById($trip->id);
        $this->driverController->getListDriverForTrip($trip->id);
        $res->success($reslut);
        return response()->json($res,200);
    }

    public function addAttachementsToTrip(array $data, Trip $trip)
    {
        $listAttachements = $data['attachements'];
        if($listAttachements){
            foreach ($listAttachements as $attachementId){
                $attachement = Document::find($attachementId);
                if($attachement)
                    $trip->attachements()->attach($attachement);
            }
        }
    }

    public function attachServices(array $data,Trip $trip)
    {
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
    }
    public function attachAddresses(Request $request,$trip)
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

        $destination_address = Address::create([
            'primaryName' => request('primaryNameDestination'),
            'secondaryName' => request('secondaryDestination'),
            'place_id' => request('place_idDestination'),
            'longitude' => request('longitudeDestination'),
            'lattitude' => request('lattitudeDestination'),
            'type' => '2',
            'user_id' => Auth::id()
        ]);

        $trip->addresses()->attach($pickup_address);
        $trip->addresses()->attach($destination_address);
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
        $trip = Trip::where('id',$id)->with('driver','user','addresses','type_car','cancelTrip')->first();
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
        $user = User::find(Auth::id());
        if ($trip){

            if($trip['status'] == '0' && $user->getRoles() === json_encode(['captain']) )
            {
                $driverTrip = \DB::table('trip_user')->where('user_id', '=', $user->id)
                    ->where('trip_id', '=',$trip['id'])->first();
                $trip['alreadyApplied'] = $driverTrip ? true : false;
            }
            if($trip['status'] == '0' && $user->getRoles() === json_encode(['client']))
            {
                $driverTrip = \DB::table('trip_user')
                    ->where('trip_id', '=',$trip['id'])->first();
                if ($driverTrip)
                $trip['driver_request'] =User::find($driverTrip->user_id);
            }
            $res->success($trip);
        }
        else
            $res->fail(trans('messages.trip_not_found'));
        return response()->json($res,200);
    }

    public function cancelTrip(Request $request)
    {
        $res = new Result();
        $data = $request->all();
        $trip = Trip::find($data['trip_id']);
        $user = User::find(Auth::id());
        if ($trip)
        {
            $cancelTrip = CancelTrip::create(['raison'=>$data['raison'],'by_user'=>$data['canceledByUser']]);
            if (in_array($trip->status, ['-1','1'])){
                $trip->status = '3';
                $trip->cancelTrip()->save($cancelTrip);
                $trip->save();
                $res->success($cancelTrip);
                $res->message = trans('messages.cancel_trip');
                if ($user->getRoles() === json_encode(['client']))
                    $this->driverController->notifyUser($trip->driver_id,5,$trip->id,$trip->driver_id);
                else
                    $this->driverController->notifyUser($trip->user_id,6,$trip->id,$user->id);
            }else {
                if ($user->getRoles() === json_encode(['client'])) {
                    Notif::where('trip_id','=',$trip->id)
                        ->where('driver_id','!=',null)->update(['driver_id'=>null]);
                    $trip->candidates()->detach();
                    $trip->save();
                }
            }
            $res->success($trip);
        }else{
            $res->fail(trans('messages.trip_not_found'));
        }
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
            $res->fail(trans('messages.trip_not_found'));
        }
        return response()->json($res,200);
    }


    public function confirmTripFromUser(Request $request)
    {
        $res = new Result();
        $paymentController = new PaymentController();

        $validator = Validator::make($request->all(), ['trip_id' => 'required', 'driver_id' => 'required', 'accept' => 'required']);
        if ($validator->fails())
        {
            $res->fail(trans('messages.trip_not_found'));
            return response()->json($res, 200);
        }

        $trip = Trip::where('id',$request['trip_id'])
            ->where('status','=','0')->first();
        $driver = User::find($request['driver_id']);
        if ($trip and $driver)
        {
            if ($request['accept'])
            {
                $trip->status = "-1";
                $trip->driver_id = $driver->id;
                $trip->save();
                $trip['driver'] = $driver;
                $this->driverController->notifyUser($driver->id,3,$trip->id,$driver->id);
                Driver::where('user_id' ,'=',Auth::id())->update(['status'=>1]);
                $trip->candidates()->detach();
                Notif::where('trip_id','=',$trip->id)
                    ->where('driver_id','!=',$driver->id)->update(['driver_id'=>null]);
                $paymentController->payTripCost($trip->total_price);
            }
            else{
                $trip->candidates()->wherePivot('user_id',$driver->id)->detach();
                Notif::where('trip_id','=',$trip->id)
                    ->where('trip_step','=',1)
                    ->where('driver_id','=',$driver->id)->update(['driver_id'=>null]);
                Notif::where('trip_id','=',$trip->id)
                    ->where('trip_step','=',9)
                    ->where('driver_id','=',$driver->id)->update(["trip_step"=>-1]);

                $trip['driver'] = null;
                $this->driverController->notifyUser($driver->id,4,$trip->id,$driver->id);
                $nextDriverToNotify = $this->driverController->filterAndGetFirstDriver($trip->id);
                if ($nextDriverToNotify)
                    $this->driverController->notifyUser(Auth::id(),1,$trip->id,$nextDriverToNotify->id);

            }
            $res->success($trip);
        }else{
            $res->fail(trans('messages.trip_not_found'));
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
            $res->fail(trans('messages.trip_not_found'));
        }
        return response()->json($res,200);
    }


    public function uploadReceipt(Request $request)
    {
        $res = new Result();
        $trip = Trip::find($request['trip_id']);
        if ($trip && in_array($trip->status, ['2','1'])) {
            $documentController = new DocumentController();
            $response = $documentController->store($request)->getData();

            if ($response->success) {
                $attachement = Document::find($response->response[0]->id);
                $trip->attachements()->attach($attachement);
                $res->success($attachement);
            }
        }else{
            $res->fail(trans('messages.document_fail_upload'));
        }
        return response()->json($res,200);
    }

    public function driverRequest(Request $request)
    {
        $res = new Result();
        $data = $request->all();
        $validator = Validator::make($data, [
            'driver_id' => 'required',
            'trip_id' => 'required'
        ]);
        if ($validator->fails())
        {
            $res->fail(trans('messages.trip_not_found'));
            return response()->json($res, 200);
        }
        $trip =Trip::find($data['trip_id']);
        $response = [];
        $driver = User::find($data['driver_id']);
        if ($trip && $driver)
        {
            $pickupAddress = array_filter($trip->addresses->toArray(), function($address){
                return $address['type'] === "1";
            })[0];
            $destinationAddress = array_filter($trip->addresses->toArray(), function($address){
                return $address['type'] === "2";
            })[1];
            $distanceMatrixApi = json_decode( GoogleMaps::load('distancematrix')
                ->setParamByKey ('origins' ,$pickupAddress['lattitude'].','.$pickupAddress['longitude'])
                ->setParamByKey ('destinations' ,$destinationAddress['lattitude'].','.$destinationAddress['longitude'])
                ->get());
            $response['trip'] = $this->getById($data['trip_id']);
            $response['user_account'] = Account::where('user_id','=',Auth::id())->first();
            $response['driver'] = $driver;
            $response['driver_rating'] = $this->driverController->getDriverRating($driver->id);
            $response['distance'] = $distanceMatrixApi->rows[0]->elements[0]->distance->text;
            $response['time'] = $distanceMatrixApi->rows[0]->elements[0]->duration->text;

            $res->success($response);
        }else
            $res->fail(trans('messages.trip_not_found'));
        return response()->json($res,200);
    }
}
