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

        $notifs = Notif::where('user_id', '=', $userId)
            ->paginate(10)
            ->toArray();
        foreach ($notifs['data'] as $notif)
        {
            Notif::find($notif['id'])->update(['seen'=>true]);
        }
        $res->success($notifs);
        return response()->json($res,200);
    }

    public function store(Request $request)
    {
        $res = new Result();
        return response()->json($res,200);
    }


    public function getDetails(int $id)
    {
        $res = new Result();

        $notif = Notif::find($id);
        if ($notif)
        {
            $res->success($notif);
        }else{
            $res->fail(trans('messages.notif_not_found'));
        }
        return response()->json($res,200);
    }

    public function getUnread()
    {
        $res = new Result();
        $userId = Auth::id();
        $countUnRead = Notif::where('user_id', '=', $userId)
            ->where('seen','=',null)->count('id');
        $res->success(['unread'=>$countUnRead]);
        return response()->json($res,200);
    }

}
