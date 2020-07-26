<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Account;
use App\Models\Result;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Validator;
class AccountController extends Controller
{

    public function addCredit(Request $request)
    {
        $res = new Result();

        $user = User::find(Auth::id());
        $account = Account::where('user_id',Auth::id())->first();
        $balance = $account->balance;
        if (isset($request->balance))
        $balance = $account->balance + $request->balance;
        if (isset($request->coupon) or $request->coupon === "")
        {
            $requestVerif = new Request();
            $requestVerif['promocode'] = $request->coupon;
            $promocodeController = new PromocodeController();
            $isActif =$promocodeController->verify($requestVerif)->getData();
            if (!$isActif->success)
                return response()->json($isActif,200);
            else
            {
                if ($promocodeController->usePromocode($isActif->response[0]->id))
                {
                    if ($user->getRoles() === json_encode(['captain']))
                        $balance = (($isActif->response[0]->pourcentage * $request->balance)/100) + $balance;
                    else
                        $balance = $isActif->response[0]->pourcentage + $balance;
                }else{
                    $res->fail('Promocode already used');
                    return response()->json($res,200);
                }
            }

        }
        $account->balance = $balance;
        $account->save();
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
