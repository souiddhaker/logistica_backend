<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Account;
use App\Models\Promocode;
use App\Models\Result;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AccountController extends Controller
{
    //
    public function addCredit(Request $request)
    {
        $res = new Result();

        $account = Account::where('user_id',Auth::id())->first();
        if (isset($request->coupon))
        {
            $requestVerif = new Request();
            $requestVerif['promocode'] = $request->coupon;
            $promocodeController = new PromocodeController();
            $isActif = $promocodeController->verify($requestVerif)->getData();
            if (!$isActif->success)
            return response()->json($isActif,200);
            else
                return response()->json($isActif->response[''],200);

        }
        $res->success($account);
        return response()->json($res,200);
    }


    public function getCredit()
    {
        $res = new Result();

        $account = Account::where('user_id',Auth::id())->first();
        $res->success($account);
        return response()->json($res,200);
    }
}
