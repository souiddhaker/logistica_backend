<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Promocode;
use App\Models\Result;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Validator;


class PromocodeController extends Controller
{
    //

    public function verify(Request $request)
    {
        $res = new Result();

        $validator = Validator::make($request->all(),
            [
                'promocode' => 'required|min:4'
            ]);
        if ($validator->fails()) {
            $res->fail(trans('messages.promocode_invalid'));
            return response()->json($res, 200);
        }

        $promocode = Promocode::where('code',$request['promocode'])->where('end_at', '>', now())->where('status','active')->first();

        if (!$promocode){
            $res->fail(trans('messages.promocode_invalid'));
        }else{
            $res->success($promocode);
            $res->message = trans('messages.promocode_success');
        }
        return response()->json($res ,200);
    }
}
