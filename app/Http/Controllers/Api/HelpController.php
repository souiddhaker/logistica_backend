<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Help;
use App\Models\Result;
use Illuminate\Http\Request;
use Validator;
class HelpController extends Controller
{
    private $res;

    public function __construct()
    {
        $this->res = new Result();
    }
    public function create(Request $request)
    {
        $data = $request->all();
        $validator = Validator::make($data,[
            'content' => 'required'
        ]);

        if ($validator->fails()) {
            $this->res->fail($validator->errors());
            return response()->json($this->res, $this->res->status);
        }
        Help::truncate();
        $help = Help::create($data);
        $this->res->success($help);
        return response()->json($this->res,200);
    }

    public function getAbout()
    {
        $this->res->success(Help::first());
        return response()->json($this->res,200);
    }
}
