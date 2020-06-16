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
            if (isset($request->attachements))
            {
                $this->addAttachements($request->attachements);
            }
            $userController->createAccount($driver->id);
            $response->response[0]->user = $this->getProfile()->getData()->response[0];
//            return response()->json($response->response[0]->user,200);
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
        $response['attachements']['identity'] = array_filter($listDocuments->toArray(), function ($event) {
            if ($event['type'] === "4")
            return $event;
        });
        $response['attachements']['car_photo'] = array_filter($listDocuments->toArray(), function ($event) {
            if ($event['type'] === "5")
                return $event;
        });
        $response['attachements']['licence'] = array_filter($listDocuments->toArray(), function ($event) {
            if ($event['type'] === "6")
                return $event;
        });
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
}
