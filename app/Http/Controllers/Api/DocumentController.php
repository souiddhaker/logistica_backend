<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Document;
use App\Models\Result;
use App\Models\Trip;
use App\Models\User;
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
                'type' => 'required',
            ]);
        if ($validator->fails()) {
            $res->fail(trans('messages.document_empty'));
            return response()->json($res, 200);
        }
        try {
            $name = time() . '.' . explode('/', explode(':', substr($request->document, 0, strpos($request->document, ';')))[1])[1];
            $imageFolder =  '/img/attachement/';

            $img = \Image::make($request->document)->save(public_path($imageFolder) . $name);
            $name = url('/') .$imageFolder . $name;

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
        $trip = Trip::where('id',$id)->first();
        if ($trip)
        {
            $res->success = true;
            $res->response  = $trip->attachements;
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
                $imageFolder =  '/img/profile/';
                $position  = stripos($document->path,$imageFolder);
                $image_path = public_path($imageFolder).substr($document->path,$position+strlen($imageFolder),strlen($document->path));
                unlink($image_path);
                $res->success();
                $document->delete();

            }catch (\ErrorException $e){
                $res->fail(trans('messages.document_remove_fail'));

            }
        }else{
            $res->fail(trans('messages.document_not_found'));
        }

        return response()->json($res,200);
    }

    public function updateDocument(Request $request)
    {
        $res = new Result();
        $res->success = true;
        if (isset($request['id']))
        $res = $this->remove($request['id'])->getData();
        if ($res->success)
        $res = $this->store($request)->getData();
        if ($res->success){
            $driver = User::find(Auth::id())->profileDriver;
            $attachement = Document::find($res->response[0]->id);
            $driver->documents()->attach($attachement);
        }

        return response()->json($res,200);
    }
}
