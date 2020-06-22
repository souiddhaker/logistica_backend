<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Account;
use App\Models\Address;
use App\Models\CarCategory;
use App\Models\Document;
use App\Models\Driver;
use App\Models\Rating;
use App\Models\Result;
use App\Models\Trip;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Validator;

class DriverController extends Controller
{
    public function addAttachements(array $attachements)
    {
        $request = new Request();
        foreach ($attachements as $attachement)
        {
            $request['type'] = $attachement['type'];
            $request['document'] = $attachement['document'];
            $response = $this->profileDocument($request);
        }
    }

    public function saveProfileDocuments(Request $request,Driver $user)
    {
        /**
         * id 2
         * car 3
         * licence 2
         */
        $identity1 = new Document();
        $identity2 = new Document();
        $car1 = new Document();
        $car2 = new Document();
        $car3 = new Document();
        $licence1 = new Document();
        $licence2 = new Document();

        if ($request->file('identity1')){
            $file = $request->file('identity1');
            $img = \Image::make($file)->save(public_path('img/profile/') . time() .$file->getClientOriginalName());
            $name = url('/') .'/img/profile/' . time() .$file->getClientOriginalName();
            $identity1->path = $name;
            $identity1->type = 4;
            $identity1->save();
            $user->documents()->attach($identity1);

        }
        if ($request->file('identity2')){
            $file = $request->file('identity2');
            $img = \Image::make($file)->save(public_path('img/profile/') . time() .$file->getClientOriginalName());
            $name = url('/') .'/img/profile/' . time() .$file->getClientOriginalName();
            $identity2->path = $name;
            $identity2->type = 4;
            $identity2->save();
            $user->documents()->attach($identity2);

        }
        if ($request->file('car1')){
            $file = $request->file('car1');
            $img = \Image::make($file)->save(public_path('img/profile/') .time() . $file->getClientOriginalName());
            $name = url('/') .'/img/profile/' . time() .$file->getClientOriginalName();
            $car1->path = $name;
            $car1->type = 5;
            $car1->save();
            $user->documents()->attach($car1);

        }
        if ($request->file('car2')){
            $file = $request->file('car2');
            $img = \Image::make($file)->save(public_path('img/profile/') .time() . $file->getClientOriginalName());
            $name = url('/') .'/img/profile/' . time() .$file->getClientOriginalName();
            $car2->path = $name;
            $car2->type = 5;
            $car2->save();
            $user->documents()->attach($car2);

        }
        if ($request->file('car3')){
            $file = $request->file('car3');
            $img = \Image::make($file)->save(public_path('img/profile/') . time() .$file->getClientOriginalName());
            $name = url('/') .'/img/profile/' . time() .$file->getClientOriginalName();
            $car3->path = $name;
            $car3->type = 5;
            $car3->save();
            $user->documents()->attach($car3);

        }
        if ($request->file('licence1')){
            $file = $request->file('licence1');
            $img = \Image::make($file)->save(public_path('img/profile/').time() . $file->getClientOriginalName());
            $name = url('/') .'/img/profile/' . time() .$file->getClientOriginalName();
            $licence1->path = $name;
            $licence1->type = 6;
            $licence1->save();
            $user->documents()->attach($licence1);

        }
        if ($request->file('licence2')){
            $file = $request->file('licence2');
            $img = \Image::make($file)->save(public_path('img/profile/').time() . $file->getClientOriginalName());
            $name = url('/') .'/img/profile/' . time() .$file->getClientOriginalName();
            $licence2->path = $name;
            $licence2->type = 6;
            $licence2->save();
            $user->documents()->attach($licence2);

        }
        return $user->documents;
    }
    public function register(Request $request)
    {


        $authController = new AuthController();
        $userController = new UserController();
        $response = $authController->register($request)->getData();

        if ($response->success){
            $driver = User::find($response->response[0]->user->id);
            $driver->roles = json_encode(['captain']);
            $driver->save();
            $driverProfile = new Driver();
            $driverProfile->cartype_id = CarCategory::find($request->car_type)->id;
            $driver->profileDriver()->save($driverProfile);
            Auth::login($driver);
            $this->saveProfileDocuments($request,$driverProfile);
            $userController->createAccount($driver->id);
            $response->response[0]->user = $this->getProfile()->getData()->response[0];
        }
        return response()->json($response,200);
    }


    public function confirmTrip(Request $request)
    {
        $res = new Result();

        $validator = Validator::make($request->all(),
            [
                'trip_id' => 'required'
            ]);
        if ($validator->fails()) {
            $res->fail(trans('messages.trip_not_found'));
            return response()->json($res, 200);
        }

    }


    public function getProfile()
    {
        $res = new Result();

        $user = User::find(Auth::id());
       if($user){
           $profileDriver = Driver::find($user->profileDriver->id);
           $response = collect($user)->toArray();
           $listDocuments = $profileDriver->documents;
           $response['account'] = Account::where('user_id',$user->id)->first();

           $response['car'] = CarCategory::find($profileDriver->cartype_id);
           $response['attachements']['identity'] = [];
           $response['attachements']['car_photo'] = [];
           $response['attachements']['licence'] = [];
           foreach ($listDocuments as $document){
               if ($document['type'] === "4")
                   array_push($response['attachements']['identity'],$document);
               if ($document['type'] === "5")
                   array_push($response['attachements']['car_photo'],$document);
               if ($document['type'] === "6")
                   array_push($response['attachements']['licence'],$document);
           }

           $res->success($response);
           $res->message= trans('messages.user_details');
       }else{
           $res->fail('user not found');
       }


        return response()->json($res,200);
    }

    public function profileDocument(Request $request)
    {
        $documentController = new DocumentController();
        $response = $documentController->store($request)->getData();

        if ($response->success){
            $driver = User::find(Auth::id())->profileDriver;
            $attachement = Document::find($response->response[0]->id);
            $driver->documents()->attach($attachement);
        }
        return response()->json($response,200);
    }

    public function reviews()
    {
        $res = new Result();
        $user = User::find(Auth::id());
        $listRatings = Rating::select('ratings.*','users.firstName', 'users.lastName', 'users.image_url')
            ->where('ratings.driver_id','=',$user->profileDriver->id)
            ->join('users', 'ratings.user_id', '=', 'users.id')
            ->paginate(2);
        $res->success($listRatings);
        return response()->json($res,200);
    }


    public function updateDriver(Request $request)
    {
        $res = new Result();
        $user = Auth::user();
        $driver = Driver::where('user_id', Auth::id())->first();
        $user->update($request->only(['firstName', 'lastName']));
        $driver->cartype_id = CarCategory::find($request->car_type)->id;
        $driver->save();
        $res->response = [$this->getProfile()->getData()->response[0]];
        $res->success = true;
        return response()->json($res,200);
    }


    public function test()
    {
        $res = new Result();
        $res->success(User::where('roles',"=", json_encode(['captain']))->get());
        $res->success(Driver::all());
        return response()->json($res,200);

    }

    public function acceptTripFromDriver(Request $request)
    {
        $res = new Result();

        $validator = Validator::make($request->all(),
            [
                'trip_id' => 'required'
            ]);
        if ($validator->fails())
        {
            $res->fail(trans('messages.trip_not_found'));
            return response()->json($res, 200);
        }

        $trip = Trip::where('id',$request['trip_id'])
            ->where('status',"=", "0")->first();

        if ($trip)
        {
            $trip->candidates()->attach(User::find( Auth::id()));
            $trip->save();
            $res->success($trip->candidates);
        }else{
            $res->fail(trans('messages.trip_not_found'));
        }

        return response()->json($res,200);
    }

    public function confirmTripFromUser(Request $request)
    {
        $res = new Result();

        $validator = Validator::make($request->all(),
            [
                'trip_id' => 'required',
                'driver_id' => 'required',
                'accept' => 'required'
            ]);
        if ($validator->fails())
        {
            $res->fail(trans('messages.trip_not_found'));
            return response()->json($res, 200);
        }

        $trip = Trip::where('id',$request['trip_id'])->with('driver')->first();
        $driver = User::find($request['driver_id']);
        if ($trip and $driver)
        {
            if ($request['accept'])
            {
                $trip->status = "1";
                $trip->driver_id = $driver->id;
                $trip->save();
            }
            else{
                $trip->candidates()->wherePivot('user_id',$driver->id)->detach();
            }
            $res->success($trip);
        }else{
            $res->fail(trans('messages.trip_not_found'));
        }

        return response()->json($res,200);
    }

    public function updatePosition(Request $request)
    {
        $res = new Result();
        $data = $request->all();
        $data['user_id'] = Auth::id();
        $validator = Validator::make($request->all(),
            [
                'primaryName' => 'string|nullable',
                'secondaryName' => 'string|nullable',
                'longitude' => 'double|nullable',
                'lattitude' => 'double|nullable',
                'place_id' => 'required',
            ]);

        if ($validator->fails()) {
            $res->fail(trans('messages.address_exists'));
            return response()->json($res, 400);
        }

        $driverposition  = Address::where('user_id', Auth::id())->where('type', '=', '4')->first();

        if ($driverposition)
            $driverposition->update($data);
        else
            $driverposition = Address::create($data);

        $res ->success($driverposition);
        return response()->json($res,200);
    }

    public function getListDriverForTrip(Request $request)
    {
        $res = new Result();

        $trip = Trip::find($request['trip_id']);
        $pickupAddress = array_filter($trip->addresses->toArray(), function($address){
           return $address['type'] === "1";
        })[0];

        $listDriver  = User::with('profileDriver')
            ->join('address', 'address.user_id', '=', 'users.id')
            ->join('drivers', 'drivers.user_id', '=', 'users.id')
            ->where('address.type' , '=', '4')
            ->where('users.roles',"=", json_encode(['captain']))->where('drivers.status', '=', '0')->get();
        $listDriverFiltered= [];
        foreach ($listDriver as $driver){
            $driverposition  = Address::where('user_id', $driver->user_id)->where('type', '=', '4')->first();
            $modelDriver = $driver;
            $modelDriver['addressPickup'] = $driverposition;
            $modelDriver['position'] = $this->haversineGreatCircleDistance(
                $pickupAddress['lattitude'],$pickupAddress['longitude'],
                $driverposition->lattitude,$driverposition->longitude);
            $modelDriver['average_rating'] = $this->getDriverRating($driver->id);
            array_push($listDriverFiltered,$modelDriver);

        }
        $listDriverFiltered = collect($listDriverFiltered);
        $listDriverFiltered->sortBy('position')->sortBy('average_rating');
        $res->success($listDriverFiltered);
        return response()->json($listDriverFiltered, 200);
    }


    public function getDriverRating(int $id)
    {
        return Rating::where('driver_id', '=', $id)->avg('value') ? null : 0;
    }
    function haversineGreatCircleDistance(
        $latitudeFrom, $longitudeFrom, $latitudeTo, $longitudeTo, $earthRadius = 6371000)
    {
        // convert from degrees to radians
        $latFrom = deg2rad($latitudeFrom);
        $lonFrom = deg2rad($longitudeFrom);
        $latTo = deg2rad($latitudeTo);
        $lonTo = deg2rad($longitudeTo);

        $latDelta = $latTo - $latFrom;
        $lonDelta = $lonTo - $lonFrom;

        $angle = 2 * asin(sqrt(pow(sin($latDelta / 2), 2) +
                cos($latFrom) * cos($latTo) * pow(sin($lonDelta / 2), 2)));
        return $angle * $earthRadius;
    }


}
