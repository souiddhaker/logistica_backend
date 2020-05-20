<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Address;
use App\Models\Result;
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
