<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Address;
use App\Models\Result;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Validator;
class AddressController extends Controller
{
    //



    public function getAllFavoritesAddress()
    {
        $res = new Result();

        $userId = Auth::id();

        $listAddress = Address::where('user_id', '=', $userId)->get();

        $res->response = $listAddress;
        $res->message = 'List address stored';
        return response()->json($res,200);
    }

    public function store(Request $request)
    {
        $res = new Result();
        $validator = Validator::make($request->all(),
            [
                'primaryName' => 'required',
                'secondaryName' => 'required',
                'longitude' => 'required',
                'lattitude' => 'required',
            ]);

        if ($validator->fails()) {
            $res->fail($validator->errors());
            return response()->json($res, 400);
        }

        $input = $request->all();
        $user = Auth::user();
        $address = new Address();
        $address->primaryName = $input['primaryName'];
        $address->secondaryName = $input['secondaryName'];
        $address->longitude = $input['longitude'];
        $address->lattitude = $input['lattitude'];
//        $user = $user->addresses($address)->save();
        $address->user_id = $user->id;

        $res->success($address);
        return response()->json($res,200);

    }


    public function remove(int $id)
    {
        $res = new Result();
        $validator = Validator::make(['id'=>$id],
            [
                'id' => 'required|integer|exists:address,id',
            ]);

        if ($validator->fails()) {
            $res->fail($validator->errors());
            return response()->json($res, 400);
        }


        try
        {
            $address = Address::find($id);
            $address->delete();
            $res->success("Success");
            return response()->json($res, 200);

        }catch (\Exception $exception){
            $res->fail($exception->getMessage());
            return response()->json($res, 500);
        }

    }


}
