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
            $res->fail($validator->errors()->get('promocode')[0]);
            return response()->json($res, 200);
        }

        $promocode = Promocode::where('code',$request['promocode'])->where('end_at', '>', now())->where('status','active')->first();

        if (!$promocode){
            $res->fail('Promocode invalid');
        }else{
            $res->success($promocode);
            $res->message = ['en' => 'Promocode correct','ar' => 'Promocode correct'];
        }
        return response()->json($res ,200);
    }
}
