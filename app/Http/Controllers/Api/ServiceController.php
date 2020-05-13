<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\CarCategory;
use App\Models\Result;
use App\Models\Service;
use Illuminate\Http\Request;

class ServiceController extends Controller
{
    //

    public function getListCarCategories()
    {
        $listCarCategories = CarCategory::all();

        $res  =  new Result();

        $res->success($listCarCategories);
        $res->message = "List of car categories";
        return response()->json($res,200);
    }

    public function getListServices()
    {
        $listServices =  Service::all();

        $res = new Result();

        $res->success($listServices);
        $res->message = "List services";

        return response()->json($res,200);


    }
}
