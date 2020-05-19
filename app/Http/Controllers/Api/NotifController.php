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

        $res->success($notifs);
        $res->message('List Notifs');
        return response()->json($res,200);
    }

    public function store(Request $request)
    {
        $res = new Result();

        $input = $request->all();

        $notification = new Notif();

//        $notification->
    }
}
