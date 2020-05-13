<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\CarCategory;
use App\Models\Result;
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
}
