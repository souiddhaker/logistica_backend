<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AdminRoles;
use App\Models\CancelTrip;
use App\Models\CarCategory;
use App\Models\Document;
use App\Models\Driver;
use App\Models\Notif;
use App\Models\Promocode;
use App\Models\Result;
use App\Models\Settings;
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
            case "coupon":
                $res = Promocode::createOne($request);
                break;
            case "carType":
                $res = CarCategory::createOne($request);
                break;
            case "notif":
                $res = Notif::createOne($request->all());
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
                Promocode::Cancel($id);
                $res->success("disabled successfully");
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
                $res = Settings::updateOne($request->all());
                break;
            case "carType":
                $res = CarCategory::updateOne($request, $id);
                break;
            case "claims":
                $res = CancelTrip::updateOne($request, $id);
                break;
            case "tripTransaction":
                $res = Trip::updateOneTransaction($request, $id);
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
                $data = User::where('id', $id)->limit(1)->get();
                $allTrips = Trip::where('user_id', $id);
                $data['tripStats'] = [
                    'finished' => $allTrips->where('status', [3, 2])->count('id'),
                    'current' => $allTrips->where('status', [1])->count('id'),
                ];
                $res->success($data);
                break;
            case "captain":
                $data = User::where('id', $id)->with(['profiledriver','profiledriver.documents'])->limit(1)->get();
                $data['tripStats'] = [
                    'finished' => Trip::where('driver_id', $id)->where('status', [3, 2])->count('id'),
                    'current' => Trip::where('driver_id', $id)->where('status', [1])->count('id'),
                ];
                $data['payment'] = [
                    'got' => Trip::where('driver_id', $id)->sum('total_price'),
                    'notPayed' => Trip::where('driver_id', $id)->where('payment_method', '=', null)->sum('total_price'),
                ];
                $res->success($data);
                break;
            case "admin":
                $data = User::where('id', $id)->limit(1)->get();
                $dataRolesCount = AdminRoles::where('user_id', $id)->count();
                if ($dataRolesCount > 0) {
                    $dataRoles = AdminRoles::where('user_id',$id)->first();
                    if ($dataRoles) {
                        $dataRoles = $dataRoles->get('roles');
                        $user['adminRoles'] = $dataRoles[0]['roles'];
                    } else {
                        $user['adminRoles'] = [];
                    }
                }
                $dataRoles = AdminRoles::where('user_id', $id)->first()->get('roles');
                if (count($dataRoles) > 0) {
                    $data[0]['adminRoles'] = $dataRoles[0]['roles'];
                }
                $res->success($data);
                break;
            case "trip":
                $data = Trip::with(['driver', 'user', 'promocode', 'canceltrip'])->where('id', $id)->limit(1)->get();
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
                $data = CarCategory::where('id', $id)->limit(1)->get();
                $res->success($data);
                break;
            case "claims":
                $data = CancelTrip::with(['trip', 'trip.driver', 'trip.user'])->where('id', $id)->limit(1)->get();
                $res->success($data);
                break;
            default:
                $res->fail("incorrect name");
        }
        $res = response()->json($res, 200);
        return $res;
    }

    public function filterDate(&$detailFilter, $key)
    {
        $sql = [];
        if (isset($detailFilter['d_start_at'])) {
            $sql[] = "$key >= '" . addslashes($detailFilter['d_start_at']) . "'";
        }
        if (isset($detailFilter['d_end_at'])) {
            $sql[] = "$key <= '" . addslashes($detailFilter['d_end_at']) . "'";
        }
        unset($detailFilter["d_start_at"]);
        unset($detailFilter["d_end_at"]);
        return $sql;
    }

    public function toQuery(&$detailFilter, $sql, $like = true, $applyOnly = [])
    {
        foreach ($detailFilter as $key => $value) {
            if ((count($applyOnly) > 0 && in_array($key, $applyOnly))
                || count($applyOnly) === 0) {

                $safeValue = addslashes($value);
                if ($like) {
                    $sql[] = "$key like '%$safeValue%'";
                } else {
                    if($key=='transaction_status') {
                        if($safeValue==="0"){

                            $sql[] = "$key IS NULL";
                        }else{

                            $sql[] = "$key IS NOT NULL";
                        }
                    }
                    else{
                        $sql[] = "$key = '$safeValue'";
                    }
                }
                unset($detailFilter[$key]);
            }
        }
        return $sql;
    }

    public function filter(Request $request, string $name)
    {

        $sql = [];
        switch ($name) {
            case "client":
            case "admin":
            case"captain":
                $detailFilter = array_filter($request->all(), function ($value, $key) {
                    return $value && in_array($key, ['firstName', 'lastName', 'phone', 'email']);
                }, ARRAY_FILTER_USE_BOTH);
                break;
            case "trip":
                $detailFilter = array_filter($request->all(), function ($value, $key) {
                    return ($value!==null ) && in_array($key, ['id', 'status', 'nbr_luggage', 'user_id', 'driver_id','transaction_status']);
                }, ARRAY_FILTER_USE_BOTH);
                $sql = $this->toQuery($detailFilter, $sql, false, ['id', 'status', 'nbr_luggage', 'user_id', 'driver_id','transaction_status']);
//                dd($sql);
                break;
            case "couponCaptain":
            case "couponClient":
                $detailFilter = array_filter($request->all(), function ($value, $key) {
                    return $value && in_array($key, ['id', 'status', 'd_start_at', 'd_end_at']);
                }, ARRAY_FILTER_USE_BOTH);
                $sql = $this->filterDate($detailFilter, "end_at");
                $sql = $this->toQuery($detailFilter, $sql, false, ["id", "status"]);
                break;
            case "claims":

                $detailFilter = array_filter($request->all(), function ($value, $key) {
                    return ($value!==null || $value===0) && in_array($key, ['id', 'trip_id', 'by_user', 'status', 'd_start_at', 'd_end_at']);
                }, ARRAY_FILTER_USE_BOTH);
                $sql = $this->filterDate($detailFilter, "created_at");
                $sql = $this->toQuery($detailFilter, $sql, false, ['id', 'trip_id', 'by_user', 'status']);
                break;
            default:
                $detailFilter = [];
        }
        $sql = $this->toQuery($detailFilter, $sql);
        if (count($sql) === 0)
            return '1=1';
        return implode(" and ", $sql);
    }
    public function all(Request $request, string $name)
    {

        $res = new Result();
        $success = true;
        $list = [];
        $detailFilters = $this->filter($request, $name);
        switch ($name) {
            case "client":
            case "admin":
                $list = User::where("roles", json_encode([$name]))->whereRaw($detailFilters);
                break;
            case "captain":
                $list = User::with(['profiledriver'])->where('roles', json_encode([$name]))->whereRaw($detailFilters);
                break;
            case "trip":
                $list = Trip::whereHas('driver', function ($query) use ($request) {
                    if ($request->get("d_user_fname")) {
                        $userName= $request->get("d_user_fname");
                        $query->where("firstName", 'like', "%$userName%");
                    }
                })->whereHas('user', function ($query) use ($request) {
                    if ($request->get("c_user_fname")) {
                        $userName= $request->get("c_user_fname");
                        $query->where("firstName", 'like', "%$userName%");
                    }
                })->with(['user','driver'])->whereRaw($detailFilters);
                break;
            case "couponCaptain":
                $list = Promocode::where('type', 1)->whereRaw($detailFilters);
                break;
            case "couponClient":
                $list = Promocode::where('type', 0)->whereRaw($detailFilters);
                break;
            case "notif":
                $list = new Notif();
                break;
            case "carType":
                $list = new CarCategory();
                break;
            case "claims":
                $list = CancelTrip::whereHas('trip.driver', function ($query) use ($request) {
                    if ($request->get("driver_id")) {
                        $user_id= $request->get("driver_id");
                        $query->where("id", $user_id);
                    }
                })->whereHas('trip.user', function ($query) use ($request) {
                    if ($request->get("user_id")) {
                        $user_id= $request->get("user_id");
                        $query->where("id", $user_id);
                    }
                })->with(['trip', 'trip.driver', 'trip.user'])->whereRaw($detailFilters);
                break;
            default:
                $success = false;
                $res->fail("incorrect name");
        }
        if ($success) {
            if($request->get("page")==="0"){
                $res->success(["data" =>$list->get(), "total" => $list->count()]);
            }else{
                $list=$list->paginate(10);
                $data = ["data" => $list->items(), "total" => $list->total()];
                $res->success($data);
            }
        }

        return response()->json($res, 200);
    }
}
