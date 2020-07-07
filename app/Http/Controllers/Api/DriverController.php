<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Account;
use App\Models\Address;
use App\Models\CarCategory;
use App\Models\Document;
use App\Models\Driver;
use App\Models\Notif;
use App\Models\Rating;
use App\Models\Result;
use App\Models\Trip;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use Mockery\Matcher\Not;
use PhpParser\Node\Expr\Cast\Object_;
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
            $response->response[0]->isUser = false;
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
            ->paginate(10);
        $res->success($listRatings);
        return response()->json($res,200);
    }


    public function updateDriver(Request $request)
    {
        $res = new Result();
        $user = Auth::user();
        $driver = Driver::where('user_id', Auth::id())->first();
        if ($driver){
            $user->update($request->only(['firstName', 'lastName']));

            if (isset($request['car_type']))
            $driver->cartype_id = CarCategory::find($request->car_type)->id;
            $driver->save();
            $res->response = [$this->getProfile()->getData()->response[0]];
            $res->success = true;
        }else{
            $res->fail('driver not found');
        }

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
            if (count($trip->candidates)==0){
                $this->notifyUser($trip->user_id,9,$trip->id,Auth::id());
            }
            $trip->candidates()->attach(User::find( Auth::id()));
            $trip->save();
            $res->success($trip);
        }else{
            $res->fail(trans('messages.trip_not_found'));
        }
        return response()->json($res,200);
    }

    public function refuseTripFromDriver(Request $request)
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
            $trip->candidates()->wherePivot('user_id',Auth::id())->detach();
            $trip->save();
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
        $validator = Validator::make($request->all(),
            [
                'longitude' => 'required',
                'lattitude' => 'required'
            ]);
        if ($validator->fails())
        {
            $res->fail(trans('messages.address_update_error'));
            return response()->json($res, 200);
        }
        $data['user_id'] = Auth::id();

        $driverposition  = Address::where('user_id', Auth::id())->where('type', '=', '4')->first();
        $data['type'] = "4";
        if ($driverposition)
            $driverposition->update($data);
        else
            $driverposition = Address::create($data);

        $res ->success($driverposition);
        return response()->json($res,200);
    }

    public function getListDriverForTrip(int $trip_id)
    {

        $trip = Trip::where('id','=',$trip_id)
        ->where('status', '=','0')->first();
        if ($trip)
        {
            $pickupAddress = array_filter($trip->addresses->toArray(), function($address){
                return $address['type'] === "1";
            })[0];

            $listDriver = \DB::select('SELECT users.id, ( 6371 * acos ( cos ( radians(?) ) * cos( radians( address.lattitude ) )
                * cos( radians( address.longitude ) - radians(?) ) + sin ( radians(?) )
                * sin( radians( address.lattitude ) ) ) ) AS distance
                FROM `users` JOIN `address` ON address.user_id = users.id
                JOIN drivers ON drivers.user_id = users.id
                WHERE `address`.`type` = 4 AND users.roles LIKE "%captain%" AND drivers.status = 0 ORDER BY distance',
                [$pickupAddress['lattitude'],$pickupAddress['longitude'],$pickupAddress['lattitude']]);
            foreach ($listDriver as $driver)
                $this->notifyUser($driver->id,1,$trip_id);
            if (count($listDriver)>0)
            $this->notifyUser(Auth::id(),9,$trip_id,$listDriver[0]->id);
            return $listDriver;
        }else{

            return null;
        }


    }


    public function filterAndGetFirstDriver(int $trip_id)
    {
        $trip =Trip::find($trip_id);
        $pickupAddress = array_filter($trip->addresses->toArray(), function($address){
            return $address['type'] === "1";
        })[0];

        $arrayListDriver = collect($trip->candidates);

        $driver =  $arrayListDriver->sortBy('distance')->sortBy('average_rating')->take(1);
        $driver['average_rating'] = $this->getDriverRating($driver->id);

        return $driver;
    }

    public function getDriverRating(int $id)
    {
        return Rating::where('driver_id', '=', $id)->avg('value') ? null : 0;
    }


    public function pickupTrip(int $trip_id)
    {
        $res = new Result();
        $trip = Trip::where('id', '=',$trip_id)
            ->where('status','=' ,'-1')
            ->where('driver_id','=',Auth::id())->first();
        if($trip)
        {
            $trip->update(['status'=>'1']);
            $this->notifyUser($trip->user_id,5,$trip->id);
            $res->success($trip);
        }else{
            $res->fail(trans('messages.trip_not_found'));
        }
        return response()->json($res,200);
    }

    public function finishedTrip(int $trip_id)
    {
        $res = new Result();
        $trip = Trip::where('id', '=',$trip_id)
            ->where('status','=' ,'1')->first();
        if($trip)
        {
            $trip->update(['status'=>'2']);
            $res->success($trip);
            $this->notifyUser($trip->user_id,8,$trip->id);
            Driver::where('user_id' ,'=',Auth::id())->update(['status'=>1]);
        }else{
            $res->fail(trans('messages.trip_not_found'));
        }
        return response()->json($res,200);
    }

    public function notifyUser(int $id, int $step,int $trip_id = null,int $driver =null)
    {
        $msgAr = "";
        $msgEn = "";
        $titleAr = "";
        $titleEn = "";
        $request = new Request();
        switch ($step){
            case 1 :
                $request['title'] = trans('messages.notif_new_trip_inform_captain_title');
                $request['message'] =  trans('messages.notif_new_trip_inform_captain');
                App::setLocale('en');
                $msgEn = trans('messages.notif_new_trip_inform_captain');
                $titleEn = trans('messages.notif_new_trip_inform_captain_title');
                App::setLocale('ar');
                $msgAr = trans('messages.notif_new_trip_inform_captain');
                $titleAr = trans('messages.notif_new_trip_inform_captain_title');
                break;
            case 2 :
                $request['title'] = trans('messages.notif_user_when_driver_confirm_title');
                $request['message'] =  trans('messages.notif_user_when_driver_confirm');
                App::setLocale('en');
                $msgEn = trans('messages.notif_user_when_driver_confirm');
                $titleEn = trans('messages.notif_user_when_driver_confirm_title');
                App::setLocale('ar');
                $msgAr = trans('messages.notif_user_when_driver_confirm');
                $titleAr = trans('messages.notif_user_when_driver_confirm_title');
                break;
            case 3 :
                $request['title'] = trans('messages.notif_driver_when_user_accept_title');
                $request['message'] =  trans('messages.notif_driver_when_user_accept');
                App::setLocale('en');
                $msgEn =  trans('messages.notif_driver_when_user_accept');
                $titleEn = trans('messages.notif_driver_when_user_accept_title');
                App::setLocale('ar');
                $msgAr =  trans('messages.notif_driver_when_user_accept');
                $titleAr = trans('messages.notif_driver_when_user_accept_title');
                break;
            case 4 :
                $request['title'] = trans('messages.notif_driver_when_user_refuse_title');
                $request['message'] =  trans('messages.notif_driver_when_user_refuse');
                App::setLocale('en');
                $msgEn =  trans('messages.notif_driver_when_user_refuse');
                $titleEn = trans('messages.notif_driver_when_user_refuse_title');
                App::setLocale('ar');
                $msgAr =  trans('messages.notif_driver_when_user_refuse');
                $titleAr = trans('messages.notif_driver_when_user_refuse_title');
                break;
            case 5 :
                $request['title'] = trans('messages.notif_user_when_trip_started_title');
                $request['message'] =  trans('messages.notif_user_when_trip_started');
                App::setLocale('en');
                $msgEn =  trans('messages.notif_user_when_trip_started');
                $titleEn = trans('messages.notif_user_when_trip_started_title');
                App::setLocale('ar');
                $msgAr =  trans('messages.notif_user_when_trip_started');
                $titleAr = trans('messages.notif_user_when_trip_started_title');
                break;
            case 6 :
                $request['title'] = trans('messages.notif_user_when_driver_cancel_trip_title');
                $request['message'] =  trans('messages.notif_user_when_driver_cancel_trip');
                App::setLocale('en');
                $msgEn =  trans('messages.notif_user_when_driver_cancel_trip');
                $titleEn = trans('messages.notif_user_when_driver_cancel_trip_title');
                App::setLocale('ar');
                $msgAr =  trans('messages.notif_user_when_driver_cancel_trip');
                $titleAr = trans('messages.notif_user_when_driver_cancel_trip_title');
                break;
            case 7 :
                $request['title'] = trans('messages.notif_driver_when_user_cancel_trip_title');
                $request['message'] =  trans('messages.notif_driver_when_user_cancel_trip');
                App::setLocale('en');
                $msgEn =   trans('messages.notif_driver_when_user_cancel_trip');
                $titleEn = trans('messages.notif_driver_when_user_cancel_trip_title');
                App::setLocale('ar');
                $msgAr =   trans('messages.notif_driver_when_user_cancel_trip');
                $titleAr = trans('messages.notif_driver_when_user_cancel_trip_title');
                break;
            case 8 :
                $request['title'] = trans('messages.notif_user_when_driver_finish_trip_title');
                $request['message'] =  trans('messages.notif_user_when_driver_finish_trip');
                App::setLocale('en');
                $msgEn =   trans('messages.notif_user_when_driver_finish_trip');
                $titleEn = trans('messages.notif_user_when_driver_finish_trip_title');
                App::setLocale('ar');
                $msgAr =   trans('messages.notif_user_when_driver_finish_trip');
                $titleAr = trans('messages.notif_user_when_driver_finish_trip_title');
                break;

            case 9 :
                $request['title'] = trans('messages.notif_user_when_requested_driver_title');
                $request['message'] =  trans('messages.notif_user_when_requested_driver');
                App::setLocale('en');
                $msgEn =   trans('messages.notif_user_when_requested_driver');
                $titleEn = trans('messages.notif_user_when_requested_driver_title');
                App::setLocale('ar');
                $msgAr =   trans('messages.notif_user_when_requested_driver');
                $titleAr = trans('messages.notif_user_when_requested_driver_title');
                break;
            default :
                break;
        }
        $translationsTitle = [
            'en' => $titleEn,
            'ar' => $titleAr
        ];
        $translationsDiscription = [
            'en' => $msgEn,
            'ar' => $msgAr
        ];
        $translationsType = [
            'en' => "Trip",
            'ar' => "توصيلة"
        ];

        $notif = new Notif();
        $notif->Title = $translationsTitle;
        $notif->description = $translationsDiscription;
        $notif->type = $translationsType;
        $notif->trip_id = $trip_id;
        $notif->icon = 'https://logistica.wi-mobi.com/img/icon/icon.png';
        $notif->driver_id = $driver;
        $user = User::find($id);
        $user->notifs()->save($notif);
        $userController = new UserController();
        $request['payload'] = \GuzzleHttp\json_encode(["trip_id"=> $trip_id,"driver"=>$driver,"step"=>$step]);
        $request['user_id'] = $id;
        $notify = $userController->notify($request);
        return $notify;
    }

    public function notifyMe(int $id){
        // TODO description colomun and route colomun database
        return response()->json($this->notifyUser($id,2,60,4),200);
    }
}
