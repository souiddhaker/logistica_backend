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

        $listAddress = Address::where('user_id', '=', $userId)->where('type','=','3')->orderBy('created_at' , 'desc')->paginate(10);

        $res->message = trans('messages.addresses_list');
        $res->success($listAddress);
        return response()->json($res,200);
    }

    public function store(Request $request)
    {
        $res = new Result();
        $validator = Validator::make($request->all(),
            [
                'primaryName' => 'string|nullable',
                'secondaryName' => 'string|nullable',
                'place_id' => 'string|nullable',
            ]);

        if ($validator->fails()) {
            $res->fail(trans('messages.address_exists'));
            return response()->json($res, 400);
        }
        $input = $request->all();
        $address = Address::where('user_id',Auth::id())->where('place_id',$input['place_id'])->where('type','=','3')->first();
        if(!$address)
        {
            $user = Auth::user();
            $address = new Address();
            $address->primaryName = $input['primaryName'];
            $address->secondaryName = $input['secondaryName'];
            $address->longitude = $input['longitude'];
            $address->lattitude = $input['lattitude'];
            $address->place_id = $input['place_id'];
            $address->type = "3";
            $address->user_id = $user->id;
            $address->save();
        }

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
