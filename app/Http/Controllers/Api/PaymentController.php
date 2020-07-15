<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Libs\Hyperpay;
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
        if ($params['type'] = 0)
        {
            $params['entityId'] = "8a8294174d0595bb014d05d82e5b01d2";
        }else
            $params['entityId'] = "8a8294174d0595bb014d05d82e5b01d2";
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
        $response?$res->success($response):$res->fail(trans('messages.payment_failed'));
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

    public function payTripCost(int $id)
    {
        $trip = Trip::find($id);
        if ($trip && $trip->status == 2)
        {

        }
    }
}
