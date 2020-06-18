<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Account;
use App\Models\CarCategory;
use App\Models\Document;
use App\Models\Driver;
use App\Models\Rating;
use App\Models\Result;
use App\Models\Trip;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class DriverController extends Controller
{
    //
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
            $name = url('/') .'img/profile/' . time() .$file->getClientOriginalName();
            $identity1->path = $name;
            $identity1->type = 4;
            $identity1->save();
            $user->documents()->attach($identity1);

        }
        if ($request->file('identity2')){
            $file = $request->file('identity2');
            $img = \Image::make($file)->save(public_path('img/profile/') . time() .$file->getClientOriginalName());
            $name = url('/') .'img/profile/' . time() .$file->getClientOriginalName();
            $identity2->path = $name;
            $identity2->type = 4;
            $identity2->save();
            $user->documents()->attach($identity2);

        }
        if ($request->file('car1')){
            $file = $request->file('car1');
            $img = \Image::make($file)->save(public_path('img/profile/') .time() . $file->getClientOriginalName());
            $name = url('/') .'img/profile/' . time() .$file->getClientOriginalName();
            $car1->path = $name;
            $car1->type = 5;
            $car1->save();
            $user->documents()->attach($car1);

        }
        if ($request->file('car2')){
            $file = $request->file('car2');
            $img = \Image::make($file)->save(public_path('img/profile/') .time() . $file->getClientOriginalName());
            $name = url('/') .'img/profile/' . time() .$file->getClientOriginalName();
            $car2->path = $name;
            $car2->type = 5;
            $car2->save();
            $user->documents()->attach($car2);

        }
        if ($request->file('car3')){
            $file = $request->file('car3');
            $img = \Image::make($file)->save(public_path('img/profile/') . time() .$file->getClientOriginalName());
            $name = url('/') .'img/profile/' . time() .$file->getClientOriginalName();
            $car3->path = $name;
            $car3->type = 5;
            $car3->save();
            $user->documents()->attach($car3);

        }
        if ($request->file('licence1')){
            $file = $request->file('licence1');
            $img = \Image::make($file)->save(public_path('img/profile/').time() . $file->getClientOriginalName());
            $name = url('/') .'img/profile/' . time() .$file->getClientOriginalName();
            $licence1->path = $name;
            $licence1->type = 6;
            $licence1->save();
            $user->documents()->attach($licence1);

        }
        if ($request->file('licence2')){
            $file = $request->file('licence2');
            $img = \Image::make($file)->save(public_path('img/profile/').time() . $file->getClientOriginalName());
            $name = url('/') .'img/profile/' . time() .$file->getClientOriginalName();
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
            $driver->addRole('captain');
            $driver->save();
            $driverProfile = new Driver();
            $driverProfile->cartype_id = CarCategory::find($request->car_type)->id;
            $driver->profileDriver()->save($driverProfile);
            Auth::login($driver);
//            if (isset($request->attachements))
//            {
//                $this->addAttachements($request->attachements);
//            }
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
        $profileDriver = Driver::find($user->profileDriver->id);
        $response = collect($user)->toArray();
        $listDocuments = $profileDriver->documents;

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
        $listRatings = Rating::where('driver_id','=',$user->profileDriver->id)->paginate(2);
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
        $res->success();
        return response()->json($res,200);
    }

    public function test()
    {
        $trip = Trip::find(441);
        $trip1 = Trip::find(447);
        $trip2 = Trip::find(456);
        $trip3 = Trip::find(453);

        $attachement1 = Document::find(29);
        $attachement2 = Document::find(30);
        $attachement3 = Document::find(31);
            $trip->attachements()->detach($attachement1);
            $trip->attachements()->detach($attachement2);

        $trip1->attachements()->detach($attachement1);
        $trip1->attachements()->detach($attachement3);

        $trip2->attachements()->detach($attachement2);
        $trip2->attachements()->detach($attachement3);


            return response()->json($trip->attachements,200);
    }
}
