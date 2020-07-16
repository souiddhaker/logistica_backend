<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Libs\Hyperpay;
use App\Models\Account;
use App\Models\Result;
use App\Models\Trip;
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

        $response = $hyperPayApi->getAccessId($params);
        $response?$res->success($response):$res->fail(trans('messages.error_server'));
        return response()->json($res,200);
    }

    public function getCheckoutStatus(Request $request)
    {
        $res = new Result();

        $params = $request->all();
        $validator = Validator::make($params,['checkout_id' => 'required']);
        if ($validator->fails()) {
            $res->fail(trans('messages.error_server'));
            return response()->json($res, 200);
        }
        $hyperPayApi = new Hyperpay();
        $response = $hyperPayApi->getPaymentStatus($params);
        $response["result"]["code"] == "000.000.000"?$res->success = trans('messages.payment_success'):$res->fail(trans('messages.payment_failed'));
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

    public function payTripCost(float $tripCost)
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
                $entityId = "8ac7a4c87314346b0173246ca65e083f";
                break;
            case 2 :
                $entityId = "8ac7a4c87314346b0173246cf25d0844";
                break;
            default:
                $entityId= "8ac7a4c87314346b0173246ca65e083f";
        }
        return $entityId;
    }
}
