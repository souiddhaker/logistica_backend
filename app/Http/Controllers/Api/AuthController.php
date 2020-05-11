<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Result;
use App\Models\User;
use Illuminate\Http\Request;
use Validator;
use Twilio\Rest\Client;
use Illuminate\Support\Facades\Mail;

class AuthController extends Controller
{
    public $successStatus = 200;
    use IssueTokenTrait;
    private $client;

    public function __construct()
    {
        $this->client = \Laravel\Passport\Client::where('password_client', 1)->first();
    }

    public function verify(Request $request){
        $res  = new Result();

        $validator = Validator::make($request->all(),
            [
                'userPhone' => 'required|min:6'
            ]);
        if ($validator->fails()) {
            $res->fail($validator->errors()->get('userPhone')[0]);
            return response()->json($res, 200);
        }
        $phone = $request['userPhone'];

        $verifCode = mt_rand(1000, 9999);


        $client = new Client(env('TWILIO_SID'), env('TWILIO_AUTH_TOKEN'));

        $client->messages->create(
            $phone,
            "+12058090405", // REPLACE WITH YOUR TWILIO NUMBER
            ["body" => (string)$verifCode, "from" => "+12058090405"]
        );

        // Find your Account Sid and Auth Token at twilio.com/console
        // DANGER! This is insecure. See http://twil.io/secure
//        $sid    = "AC2ec964c33832184077fdf7ab6567271a";
//        $token  = "7aeb7e7e98acdbac61de801c231d0e3f";
//        $twilio = new Client($sid, $token);
//
//        $message = $twilio->messages
//            ->create("+21650223908", // to
//                ["body" => $verifCode, "from" => "+15005550006"]
//            );


        $res->success([]);
        $res->message = "Le code de vérification a été envoyé";
        return response()->json($res, 200);

    }


    public function verifyCode(Request $request){
        $res  = new Result();

        $validator = Validator::make($request->all(),
            [
                'userPhone' => 'required|min:6',
                'verificationCode' => 'required|min:4'
            ]);
        if ($validator->fails()) {
            if($validator->errors()->has("userPhone")) {
                $res->fail('User phone not valid');

            }
            if($validator->errors()->has("verificationCode")) {
                $res->fail('Verification code not valid');

            }
            return response()->json($res, 200);
        }

        $verifCode = $request['verificationCode'];

        $response = [];
        if ($verifCode === "0001"){
            $user = User::where('phone',$request['userPhone']);
            if ($user) {
                $response['user'] = $user;
                $response['isAlreadyUser'] = true;
            }else {
                $response['user'] = [];
                $response['isAlreadyUser'] = false;
            }
            $res->success($response);
            $res->message = "User check";
            return response()->json($res, 200);
        }

        if ($verifCode === "0002"){
            $res->fail("Code verification expired");
            return response()->json($res, 200);
        }

        if ($verifCode === "0003"){
            $res->fail("Code verification incorrect");
            return response()->json($res, 200);
        }

        $res->fail("Code verification incorrect");
        return response()->json($res, 200);
    }



    public function register(Request $request)
    {
        $res = new Result();

        $validator = Validator::make($request->all(),
            [
                'provider' => 'required|in:facebook,password'
            ]);

        if ($validator->fails()) {
            $res->fail($validator->errors()->get('provider')[0]);
            return response()->json($res, 200);
        }


        $input = $request->all();

        if ($request->provider == "password") {

            $validator = Validator::make($request->all(),
                [
                    'firstName' => 'required',
                    'lastName' => 'required',
                    'email' => 'required|email|unique:users,email',
                    'userPhone' => 'required|unique:users,phone'
                ]);
            if ($validator->fails()) {
                if($validator->errors()->has("email")){
                    $res->fail("This email already exists");
                    return response()->json($res, 200);
                }
                else if($validator->errors()->has("userPhone")){
                    $res->fail("This phone already exists");
                    return response()->json($res, 200);
                }
//                return response()->json($res, 200);
            }


            $user = User::create([
                'firstName' => request('firstName'),
                'lastName' => request('lastName'),
                'email' => request('email'),
                'phone' => request('userPhone'),
                'password' => bcrypt(request('password'))
            ]);
            $result = $this->issueToken($input, 'password');
            $res->success($result);
            return response()->json($res, 200);
        } else {
//            $result = $this->issueToken($input, 'social');
//            if($result==null){
//                $res->fail( "Invalid request");
//                return response()->json($res, 200);
//            }
//            if(!$result['access_token']){
//                $res->fail("Wrong token");
//                return response()->json($res, 200);
//            }
//            $res->success($result);
//            return response()->json($res, 200);
        }
    }

    public function login (){
        $res  = new Result();
        $test = true;
        if ($test == true){

            $res->success(array('userIsallowed'=>true) );

            return response()->json($res, 200);
        }else {
            $res->fail("not found");
            return response()->json($res, 200);
        }
    }




//
//    /**
//     * Resend the email verification notification.
//     *
//     * @param  \Illuminate\Http\Request  $request
//     * @return \Illuminate\Http\Response
//     */
//    public function resend(Request $request)
//    {
//        if ($request->user()->hasVerifiedPhone()) {
//            return redirect($this->redirectPath());
//        }
//
//        $phone = $request->user()->phone_number;
//        $channel = $request->post('channel', 'sms');
//        $verification = $this->verify->startVerification($phone, $channel);
//
//        if (!$verification->isValid()) {
//
//            $errors = new MessageBag();
//            foreach($verification->getErrors() as $error) {
//                $errors->add('verification', $error);
//            }
//
//            return redirect('/verify')->withErrors($errors);
//        }
//
//        $messages = new MessageBag();
//        $messages->add('verification', "Another code sent to {$request->user()->phone_number}");
//
//        return redirect('/verify')->with('messages', $messages);
//    }
}
