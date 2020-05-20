<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Address;
use App\Models\Result;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AddressController extends Controller
{
    //



    public function getAllFavoritesAddress()
    {
        $res = new Result();

        $userId = Auth::id();

        $listAddress = Address::where('user_id', '=', $userId)->get();

        $res->success($listAddress);
        $res->message('List address stored');
        return response()->json($res,200);
    }


}
