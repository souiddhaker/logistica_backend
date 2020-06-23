<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Help;
use App\Models\Result;
use Illuminate\Http\Request;

class HelpController extends Controller
{
    public function getAllQuestions()
    {
        $res = new Result();
        $res->message = 'Questions list';
        $res->response = Help::all();
        $res->success = true;
        return response()->json($res,200);
    }
}
