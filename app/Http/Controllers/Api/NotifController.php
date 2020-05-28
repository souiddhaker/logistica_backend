<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Notif;
use App\Models\Result;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NotifController extends Controller
{
    //

    public function getAllNotifs()
    {
        $res = new Result();

        $userId = Auth::id();

        $notifs = Notif::where('user_id', '=', $userId)->get();


//        $res->message = 'List Notifs';
        $res->response = $notifs;
//        $res->success($notifs);
        $res->success = true;
        return response()->json($res,200);
    }

    public function store(Request $request)
    {
        $res = new Result();

        $input = $request->all();

        $notification = new Notif();

    }


    public function getDetails(int $id)
    {
        $res = new Result();

        $notif = Notif::find($id);
        if ($notif)
        {
            $res->success($notif);
            return response()->json($res,200);
        }else{
            $res->fail('Notif not found');
            return response()->json($res,200);
        }


    }


}
