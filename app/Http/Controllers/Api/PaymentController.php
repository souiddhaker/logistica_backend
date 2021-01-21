<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Libs\Hyperpay;
use App\Models\Account;
use App\Models\BillingAddress;
use App\Models\Result;
use App\Models\Settings;
use App\Models\Trip;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Validator;

class PaymentController extends Controller
{

    public function getCheckoutID(Request $request)
    {
        $res = new Result();
        $hyperPayApi = new Hyperpay();
        $params = $request->all();
        $params['entityId'] =  $this->getEntityIdForType($params['type']);
        //$params['testMode'] =  "EXTERNAL";
        $paymentParams = array_merge($params,$this->getBillingData(Auth::id()));
        $response = $hyperPayApi->getAccessId($paymentParams);
        $response?$res->success($response):$res->fail(trans('messages.error_server'));
        return response()->json($res,200);
    }

    public function getCheckoutStatus(Request $request)
    {
        $res = new Result();

        $params = $request->all();
        $validator = Validator::make($params,['checkout_id' => 'required']);
        $params['entityId'] =  $this->getEntityIdForType($params['type']);

        if ($validator->fails()) {
            $res->fail(trans('messages.error_server'));
            return response()->json($res, 200);
        }
        $hyperPayApi = new Hyperpay();
        $response = $hyperPayApi->getPaymentStatus($params);

        if (isset($response["result"]["code"]) && ($response["result"]["code"] == "000.000.000" || $response["result"]["code"] == "000.100.112" || $response["result"]["code"] == "000.100.110") )
        {
            $res->success = true;
            $res->message = trans('messages.payment_success');
        }else{
            $res->fail(trans('messages.payment_failed'));
        }
        return response()->json($res,200);
    }

    public function countAmount(array $listTrip)
    {
        $response = [];
        $companyPercent = 0;
        $amount = 0;
        foreach ($listTrip as $trip){
            $amount = $amount + $trip['total_price'];
            $companyPercent = $companyPercent + $trip['company_percent'];
        }
        $response['total'] = $amount;
        $response['company_percent'] = $companyPercent;
        return $response;
    }
    public function resume()
    {
        $res = new Result();
        $listTripCash = Trip::where('payment_method', '=', null)
            ->where('driver_id', '=',Auth::id())
            ->get();

        $listOnLine = Trip::where('payment_method', '!=', null)
            ->where('driver_id', '=',Auth::id())
            ->get();

        $response['cash'] = $this->countAmount($listTripCash->toArray());

        $response['payment'] = $this->countAmount($listOnLine->toArray());
        $response['payment']['claim'] = 0;
        $response['payment']['debt'] = 0;
        $res->success($response);
        return response()->json($res,200);
    }

    public function payTripCost(float $tripCost,int $driverId)
    {
        $accountDriver = Account::where('user_id', '=',$driverId)->first();
        $balance = $accountDriver->balance - (($tripCost*Settings::first()->company_percent)/100);
        $accountDriver->update(['balance'=>$balance>=0?$balance:0]);
        return $accountDriver;
    }

    public function payTripCostUser(float $tripCost)
    {
        $account = Account::where('user_id', '=',Auth::id())->first();
        $balance = $account->balance - $tripCost;
        $account->update(['balance'=>$balance>=0?$balance:$balance=0]);
        return $account;
    }

    public function getEntityIdForType(int $type)
    {
        switch ($type){
            case ($type == 0) || ($type ==1):
                $entityId = "8acda4cc7646baf90176dc13826d0212";
                break;
            case 2 :
                $entityId = "8acda4cc7646baf90176dc13e8d80220";
                break;
            default:
                $entityId= "8acda4cc7646baf90176dc13826d0212";
        }
        return $entityId;
    }


    public function getBillingData($idUser)
    {
        $user = Auth::user();
        $billingAddress = BillingAddress::where('user_id',$idUser)->first();
        $data = [];
        $data['billing.street1'] = $billingAddress->street;
        $data['billing.city'] = $billingAddress->city;
        $data['billing.state'] = $billingAddress->state;
        $data['billing.country'] = $billingAddress->country;
        $data['billing.postcode'] = $billingAddress->postcode;
        $data['customer.givenName'] = $user->firstName;
        $data['customer.surname'] = $user->lastName;
        $data['customer.email'] = $user->email;
        //Invoice ID(prefix id user +_)
        $data['merchantTransactionId'] =uniqid($user->id.'_');
         return $data;
    }
}
