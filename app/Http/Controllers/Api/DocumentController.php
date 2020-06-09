<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Document;
use App\Models\Result;
use App\Models\Trip;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Validator;
use Illuminate\Support\Facades\File;


class DocumentController extends Controller
{
    //


    public function store(Request $request)
    {

        $res = new Result();
        $validator = Validator::make($request->all(),
            [
                'document' => 'required|base64image',
            ]);
        if ($validator->fails()) {
            $res->fail(trans('messages.document_empty'));
            return response()->json($res, 200);
        }


        try {

            $name = time() . '.' . explode('/', explode(':', substr($request->document, 0, strpos($request->document, ';')))[1])[1];

            $img = \Image::make($request->document)->save(public_path('img/attachement/') . $name);
            $name = url('/') .'/img/attachement/' . $name;

            $user = Auth::user();

            $document = new Document();

            $document->path = $name;
            $document->type = $request->type;

            $document->save();

            $res->success($document);

            return response()->json($res, 200);

        } catch (Exception $e) {
            $res->fail($e);


            return response()->json($res, 200);
        }

    }

    public function getAttachement(int $id)
    {
        $res = new Result();

        $trip = Trip::find($id)->get();

        if ($trip)
        {
            $documents = Document::where('trip_id',$id)->get();
            $res->response = $documents;

        }else{
            $res->fail(trans('messages.trip_not_found'));
        }


        return response()->json($res,200);
    }

    public function remove(int $id)
    {
        $res  = new Result();

        $document = Document::find($id);
        if ($document)
        {
            try {
                $position  = strpos($document->path,'img/attachement/',0);
                $image_path = public_path('img/attachement/').'/'.substr($document->path,$position+16,strlen($document->path));
                unlink($image_path);
                $res->success();
            }catch (\ErrorException $e){
                $res->fail(trans('messages.document_remove_fail'));

            }
            $document->delete();
        }else{
            $res->fail(trans('messages.document_not_found'));
        }

        return response()->json($res,200);
    }
}
