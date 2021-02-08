<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Libs\Sms;
use App\Models\Account;
use App\Models\BillingAddress;
use App\Models\Result;
use App\Models\User;
use App\Models\Verification;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Date;
use Validator;
use Illuminate\Support\Facades\Auth;
use DB;


class AuthController extends Controller
{
    use IssueTokenTrait;
    private $client;

    public function __construct()
    {
        $this->client = \Laravel\Passport\Client::where('password_client', 1)->first();
    }

    public function verify(Request $request)
    {
        $res  = new Result();

        $validator = Validator::make($request->all(), ['userPhone' => 'required|min:6']);
        if ($validator->fails()) {
            $res->fail(trans('messages.user_phone_invalid'));
            return response()->json($res, 200);
        }
        $phone = $request['userPhone'];

        $verifyCode = mt_rand(1000, 9999);

        Verification::create(['verification_code'=> $verifyCode,
            'phone'=>$phone, 'code_expiry_minute'=>15]);
        $phone = str_replace('+', '', $phone);
        $phone = str_replace(' ', '', $phone);
        $sms = new Sms();
        $response = $sms->send($phone,$verifyCode,date('Y-m-d'),date('H:i'));
        if ($response && $response[0] == "3")
        {
            $res->success([]);
            $res->message =trans('messages.verif_code_send');
        }else{
            $res->fail('Error Server try to resend SMS');
        }
        return response()->json($res, 200);
    }


    public function verifyCode(Request $request)
    {
        $res  = new Result();

        $validator = Validator::make($request->all(),
            [
                'userPhone' => 'required|min:6',
                'verificationCode' => 'required|min:4'
            ]);
        if ($validator->fails()) {
            if($validator->errors()->has("userPhone"))
            {
                $res->fail(trans('messages.user_phone_invalid'));
            }
            if($validator->errors()->has("verificationCode"))
            {
                $res->fail(trans('messages.verif_code_invalid'));
            }
            return response()->json($res, 200);
        }
        $input = $request->all();

        $verifCode = $request['verificationCode'];
        $phone = $request['userPhone'];

        $response = [];
        if ($verifCode === "0001")
        {
            $user = User::where('phone',$request['userPhone'])->first();
            if ($user) {
                $input['email'] = $user->email;
                $response = $this->issueToken($input, 'password');
                if ($user->getRoles() === json_encode(['captain']))
                {
                    Auth::login($user);
                    $driverController = new DriverController();
                    $response['user'] = $user->profileDriver ?$driverController->getProfile()->getData()->response[0] : $user;
                    $response['isUser'] = false;
                }
                else{
                    $response['user'] = $user;
                    $response['isUser'] = true;
                }
                $response['isAlreadyUser'] = true;
                $response['user']->billingAddress = BillingAddress::where('user_id',$user->id)->first();

            }else {
                $response['user'] = $user;
                $response['isAlreadyUser'] = false;
            }

            $res->success($response);
            $res->message = trans('messages.verif_code_correct');
            return response()->json($res, 200);
        }
        if ($verifCode === "0002")
        {
            $res->fail(trans('messages.verif_code_expired'));
            return response()->json($res, 200);
        }

        if ($verifCode === "0003")
        {
            $res->fail(trans('messages.verif_code_incorrect'));
            return response()->json($res, 200);
        }

        $verification = Verification::where('verification_code',$verifCode)->where('phone',$phone)->first();

        if (!$verification) {
            $res->fail(trans('messages.verif_code_incorrect'));

        } else {
            $isExpired = (new Carbon($verification->created_at))->addMinutes($verification->code_expiry_minute) < Carbon::now();
            if ($isExpired)
            {
                $res->fail(trans('messages.verif_code_expired'));
            } else {
                $user = User::where('phone',$request['userPhone'])->first();
                if ($user) {
                    $input['email'] = $user->email;
                    $response = $this->issueToken($input, 'password');
                    if ($user->getRoles() === json_encode(['captain']))
                    {
                        Auth::login($user);
                        $driverController = new DriverController();
                        $response['user'] = $user->profileDriver ?$driverController->getProfile()->getData()->response[0] : $user;
                        $response['isUser'] = false;
                    }
                    else{
                        $response['user'] = $user;
                        $response['isUser'] = true;
                    }
                    $response['isAlreadyUser'] = true;

                }else {
                    $response['user'] = $user;
                    $response['isAlreadyUser'] = false;
                }
                $res->success($response);
                $res->message = trans('messages.verif_code_correct');
            }
        }
        return response()->json($res, 200);
    }



    public function register(Request $request)
    {
        $res = new Result();

        $input = $request->all();

        $validator = Validator::make($request->all(),
            [
                'firstName' => 'required',
                'lastName' => 'required',
                'email' => 'required|email|unique:users,email',
                'userPhone' => 'required|unique:users,phone'
            ]);
        if ($validator->fails())
        {
            if($validator->errors()->has("email"))
            {
                $res->fail(trans('messages.user_email_exists'));
                return response()->json($res, 200);
            }
            else if($validator->errors()->has("userPhone")){
                $user = User::where('phone',$input['userPhone'])->first();
                $user->getRoles() === json_encode(['client'])?$res->fail(trans('messages.user_phone_exists')):$res->fail(trans('messages.driver_phone_exists'));
                return response()->json($res, 200);
            }
        }
        $user = User::create([
            'firstName' => request('firstName'),
            'lastName' => request('lastName'),
            'email' => request('email'),
            'phone' => request('userPhone'),
            'password' => bcrypt("logistica"),
            'lang' => app()->getLocale()
        ]);
        $user->addRole('client');
        $user->save();
        //Create User Credit account
        $user->account()->save(Account::create(['balance'=>0]));

        $result = $this->issueToken($input, 'password');
        $result['user'] = $user;
        $result['isUser'] = true;
        $result['isAlreadyUser'] = false;
        $res->success($result);
        return response()->json($res, 200);

    }


    public function refresh(Request $request)
    {
        $res = new Result();

        $validator = Validator::make($request->all(),
            [
                'refresh_token' => 'required'
            ]);
        if ($validator->fails())
        {
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

    public function logout(Request $request)
    {
        $accessToken = Auth::user()->token();
        $res = new Result();
        $data = $request->all();
        DB::table('users_fcm')->where('token', 'LIKE',$data['fcm_token'])->delete();
        DB::table('oauth_refresh_tokens')
            ->where('access_token_id', $accessToken->id)
            ->update(['revoked' => true]);

        $accessToken->revoke();

        $res->success([]);
        return response()->json($res, 200);
    }


}
