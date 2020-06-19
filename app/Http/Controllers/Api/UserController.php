<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Libs\Firebase;
use App\Models\Account;
use App\Models\Result;
use App\Models\UserFcm;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Validator;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class UserController extends Controller
{
    //

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request)
    {
        $res = new Result();

        $user = Auth::user();
        $user->update($request->only(['firstName', 'lastName']));
        $res->success($user);
        return response()->json($res, 200);
    }

    public function uploadImage(Request $request)
    {
        $res = new Result();


        $validator = Validator::make($request->all(),
            [
                'photo' => 'required|base64image',
            ]);
        if ($validator->fails()) {
            $res->fail("Toutes les entrées sont requises");
            return response()->json($res, 200);
        }

        try {
            $name = time() . '.' . explode('/', explode(':', substr($request->photo, 0, strpos($request->photo, ';')))[1])[1];

            $img = \Image::make($request->photo)->save(public_path('img/profile/') . $name);
            $name = url('/') .'/img/profile/' . $name;

            $user = Auth::user();
            $user->update(['image_url' => $name]);

            $res->success($user);

            return response()->json($res, 200);

        } catch (Exception $e) {
            $res->fail($e);


            return response()->json($res, 200);
        }


    }

    public function getUser()
    {
        $res = new Result();

        $user = Auth::user();

        $res->success($user);
        $res->message= trans('messages.user_details');

        return response()->json($res,200);
    }

    public function createAccount(int $id)
    {
        $accountDriver = new Account();
        $accountDriver->balance = 0;
        $accountDriver->user_id = $id;
        $accountDriver->save();
    }



    public function userFcmToken(Request $request){

        $res = new Result();

        $validator = Validator::make($request->all(),
            [
                'fcm' => 'required|string'
            ]);
        if ($validator->fails()) {
            $res->fail("FCM token not valid");
            return response()->json($res, 200);
        }
        $user = Auth::user();
        if ($user)
        {
            $fcm = new UserFcm();
            $fcm->user_id = $user->id;
            $fcm->token = $request['fcm'];
            $fcm->save();
            $res->success($fcm);
        }else{
            $res->fail('User Not Found');
        }
        return response()->json($res, 200);

    }


    public function notify(Request $request)
    {

        $notification_payload   = $request['payload'];
        $notification_title     = $request['title'];
        $notification_message   = $request['message'];
        $receiver_id =[];

        if (isset($request['user_id']))
            $receiver_id = [UserFcm::where('user_id',$request['user_id'])->first()];
        else
        {
            $users = UserFcm::select('token')->where('id','>',0)->get()->toArray();
            foreach($users as $user){
                array_push($receiver_id,$user['token']);
            }
        }


        try {


            $firebase = new Firebase();

            $message = array('body' =>  $notification_message , 'title' => $notification_title , 'vibrate' => 1, 'sound' => 1 ,'payload'=>$notification_payload);

            $response = '';

            $response = $firebase->sendMultiple(  $receiver_id,  $message );

            return response()->json( [
                'response' => $response
            ] );

        } catch ( \Exception $ex ) {
            return response()->json( [
                'error'   => true,
                'message' => $ex->getMessage()
            ] );
        }
    }

}
