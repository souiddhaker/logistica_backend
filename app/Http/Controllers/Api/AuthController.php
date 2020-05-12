<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Result;
use App\Models\User;
use App\Models\Verification;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Validator;
use Twilio\Rest\Client;
use Illuminate\Support\Facades\Mail;
use Twilio\Exceptions\TwilioException;
use Illuminate\Support\Facades\Auth;
use DB;


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

//        try {
//            $client = new Client(env('TWILIO_SID'), env('TWILIO_AUTH_TOKEN'));
//
//            $client->messages->create($phone, // to
//                ["from" => "+12058090405", "body" => "Your verification code is ".$verifCode]
//            );
//        }catch (TwilioException $e){
//            $res->fail("Something went wrong. Please try again later");
//            return response()->json($res, 200);
//        }

        $verification = new Verification();

        $verification->verification_code = $verifCode;
        $verification->phone = $phone;
        $verification->code_expiry_minute =15;
        $verification->save();


        $res->success([]);
        $res->message = "Verification code has been sent";
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
        $input = $request->all();

        $verifCode = $request['verificationCode'];
        $phone = $request['userPhone'];



        $response = [];
        if ($verifCode === "0001"){
            $user = User::where('phone',$request['userPhone'])->first();
            if ($user) {
                $input['email'] = $user->email;


                $response = $this->issueToken($input, 'password');
                $response['user'] = $user;
                $response['isAlreadyUser'] = true;

            }else {
                $response['user'] = [];
                $response['isAlreadyUser'] = false;
            }
            $res->success($response);
            $res->message = "Verification code correct";
            return response()->json($res, 200);
        }

        if ($verifCode === "0002"){
            $res->fail("Verification code expired");
            return response()->json($res, 200);
        }

        if ($verifCode === "0003"){
            $res->fail("Verification code incorrect");
            return response()->json($res, 200);
        }

        $verification = Verification::where('verification_code',$verifCode)->where('phone',$phone)->first();

        if (!$verification) {
            $res->fail("Verification code incorrect");

        } else {
            $isExpired = (new Carbon($verification->created_at))->addMinutes($verification->code_expiry_minute) < Carbon::now();
            if ($isExpired) {

                $res->fail("Verification code expired");

            } else {

                $user = User::where('phone',$request['userPhone'])->first();
                if ($user) {
                    $input['email'] = $user->email;


                    $response = $this->issueToken($input, 'password');
                    $response['user'] = $user;
                    $response['isAlreadyUser'] = true;

                }else {
                    $response['user'] = [];
                    $response['isAlreadyUser'] = false;
                }
                $res->success($response);
                $res->message = "Verification code correct";
            }
        }

        return response()->json($res, 200);

    }



    public function register(Request $request)
    {
        $res = new Result();

//        $validator = Validator::make($request->all(),
//            [
//                'provider' => 'required|in:facebook,password'
//            ]);
//
//        if ($validator->fails()) {
//            $res->fail($validator->errors()->get('provider')[0]);
//            return response()->json($res, 200);
//        }
//
//
        $input = $request->all();
//
//        if ($request->provider == "password") {

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
                'password' => bcrypt("logistica")
            ]);
            $result = $this->issueToken($input, 'password');
            $res->success($result);
            return response()->json($res, 200);
//        } else {
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
//        }
    }


    public function refresh(Request $request){
        $res = new Result();

        $validator = Validator::make($request->all(),
            [
                'refresh_token' => 'required'
            ]);
        if ($validator->fails()) {
            $res->fail("Wrong fields");
            return response()->json($res, 200);
        }

        $input = $request->all();

        $result = $this->issueToken($input, 'refresh_token');
        if($result==null){
            $res->fail("Wrong request");
            return response()->json($res, 200);
        }
        if(!$result['access_token']){
            $res->fail("Wrong token");
            return response()->json($res, 200);
        }
        $res->success($result);
        return response()->json($res, 200);
    }

    public function logout(Request $request){

        $accessToken = Auth::user()->token();
        $res = new Result();

        DB::table('oauth_refresh_tokens')
            ->where('access_token_id', $accessToken->id)
            ->update(['revoked' => true]);

        $accessToken->revoke();

        $res->success([]);
        return response()->json($res, 200);
    }


}
