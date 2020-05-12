<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Result;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Validator;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    //

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request)
    {
        $res = new Result();

        $user = Auth::user();
        $user->update($request->only(['firstName', 'lastName']));
//        unset( $user->roles);

        $res->success($user);
        return response()->json($res, 200);
    }

    public function uploadImage(Request $request)
    {
        $res = new Result();


        $validator = Validator::make($request->all(),
            [
                'photo' => 'required|base64image',
            ]);
        if ($validator->fails()) {
            $response['success'] = false;
            $response['message'] = 'Toutes les entrées sont requises';
            $response['response'] = [];

            $res->fail("Toutes les entrées sont requises");

            return response()->json($res, 200);
        }



        try {

            $name = time() . '.' . explode('/', explode(':', substr($request->photo, 0, strpos($request->photo, ';')))[1])[1];

            $img = \Image::make($request->photo)->save(public_path('img/profile/') . $name);

            $user = Auth::user();
            $user->update(['image_url' => $name]);

//            unset( $user->roles);

            $res->success($user);

            return response()->json($res, 200);

        } catch (Exception $e) {
            $response['success'] = false;
            $response['message'] = $e;
            $response['response'] = [];

            return response()->json($response, 200);
        }


    }
}
