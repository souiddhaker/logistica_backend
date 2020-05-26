<?php

namespace App\Http\Controllers\Api;

use App\Models\Trip;
use App\Http\Controllers\Controller;
use App\Models\Result;
use Illuminate\Http\Request;
use Validator;
class TripController extends Controller
{
    //

    public function noteDriver(Request $request)
    {
        $res  =  new Result();

        $validator = Validator::make($request->all(),
            [
                'trip_id' => 'required',
                'note' => 'required'
            ]);
        if ($validator->fails()) {

            $res->fail("Trip id and note are missing");

            return response()->json($res, 200);
        }

        $trip = Trip::find($request->trip_id);

        if ($trip)
        {
            $trip->update(['driver_note' => $request->note]);

            $res->response['trip_id']  = $request->trip_id;
            $res->response['note'] = $request->note;
            return response()->json($res,200);
        }else{
            $res->fail('Trip not found');
        }

        return response()->json($res,200);

    }
}
