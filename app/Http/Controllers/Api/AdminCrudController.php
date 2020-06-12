<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Result;
use App\Models\Trip;
use App\Models\User;
use Illuminate\Http\Request;

class AdminCrudController extends Controller
{

    public function __construct()
    {
//            $this->middleware('auth:admin');
    }

    public function create(string $name, Request $request)
    {

        $res = new Result();
        switch ($name) {
            case "client":
            case "admin":
            case "captain":
                $res = User::createOne($request, $name);
                break;
            default:
                $res->fail("incorrect name");

        }
        $res = response()->json($res, 200);

        return $res;
    }

    public function delete(string $name, int $id)
    {
        $res = new Result();
        switch ($name) {
            case "client":
            case "admin":
            case "captain":
                User::destroy($id);
                $res->success("deleted successfully");
                break;
            default:
                $res->fail("incorrect name");
        }
        $res = response()->json($res, 200);
        return $res;
    }

    public function update(string $name, int $id, Request $request)
    {
        $res = new Result();
        switch ($name) {
            case "client":
            case "admin":
            case "captain":
                $res = User::updateOne($request,$id,$name);
                break;
            default:
                $res->fail("incorrect name");
        }
        $res = response()->json($res, 200);
        return $res;
    }

    public function get(string $name, int $id)
    {

        $res = new Result();
        switch ($name) {
            case "client":
            case "admin":
            case "captain":
                $data = User::where('id', $id)->limit(1)->get();
                $res->success($data);
                break;
            case "trip":
                $data = Trip::with(['driver','user','promocode'])->where('id', $id)->limit(1)->get();
                $res->success($data);
                break;
            default:
                $res->fail("incorrect name");
        }
        $res = response()->json($res, 200);
        return $res;
    }

    public function all(string $name)
    {

        $res = new Result();
        $success = true;
        $list = [];
        switch ($name) {
            case "client":
            case "admin":
            case "captain":
                $list = User::where('roles', json_encode([$name]))->paginate(10);
                break;
            case "trip":
                $list = Trip::with(['driver','user'])->paginate(10);
                break;
            default:
                $success = false;
                $res->fail("incorrect name");
        }
        if ($success) {
            $data = ["data" => $list->items(), "total" => $list->total()];
            $res->success($data);
        }

        return response()->json($res, 200);
    }
}
