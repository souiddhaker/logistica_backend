<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\CancelTrip;
use App\Models\CarCategory;
use App\Models\Notif;
use App\Models\Promocode;
use App\Models\Result;
use App\Models\Settings;
use App\Models\Trip;
use App\Models\User;
use Illuminate\Http\Request;
use Stripe\Util\Set;

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
            case "coupon":
                $res = Promocode::createOne($request);
                break;
            case "carType":
                $res = CarCategory::createOne($request);
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
            case "coupon":
                Promocode::destroy($id);
                $res->success("deleted successfully");
                break;
            case "carType":
                CarCategory::destroy($id);
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
                $res = User::updateOne($request, $id, $name);
                break;
            case "coupon":
                $res = Promocode::updateOne($request, $id);
                break;
            case "setting":
                $res = Settings::updateOne($request);
                break;
            case "carType":
                $res = CarCategory::updateOne($request,$id);
                break;
            case "claims":
                $res = CancelTrip::updateOne($request,$id);
                break;
            case "tripTransaction":
                $res = Trip::updateOneTransaction($request,$id);
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
            case "captain":
                $data = User::where('id', $id)->limit(1)->get();
                $res->success($data);
                break;
            case "admin":
                $data = User::with(['adminRoles'])->where('id', $id)->limit(1)->get();
                $res->success($data);
                break;
            case "trip":
                $data = Trip::with(['driver', 'user', 'promocode'])->where('id', $id)->limit(1)->get();
                $res->success($data);
                break;
            case "coupon":
                $data = Promocode::where('id', $id)->limit(1)->get();
                $res->success($data);
                break;
            case "setting":
                $data = Settings::limit(1)->get();
                $res->success($data);
                break;
            case "carType":
                $data = CarCategory::where('id',$id)->limit(1)->get();
                $res->success($data);
                break;
            case "claims":
                $data = CancelTrip::with(['trip','trip.driver','trip.user'])->where('id',$id)->limit(1)->get();
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
                $list = Trip::with(['driver', 'user'])->paginate(10);
                break;
            case "couponCaptain":
                $list = Promocode::where('type', 1)->paginate(10);
                break;
            case "couponClient":
                $list = Promocode::where('type', 0)->paginate(10);
                break;
            case "notif":
                $list = Notif::paginate(10);
                break;
            case "carType":
                $list = CarCategory::paginate(10);
                break;
            case "claims":
                $list = CancelTrip::with(['trip','trip.driver','trip.user'])->paginate(10);
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
