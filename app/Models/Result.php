<?php

namespace App\Models;
use Carbon\Carbon;
use Spatie\Translatable\HasTranslations;

class Result
{
    public $success;
    public $message;
    public $response;

    public function __construct()
    {
        $this->success = false;
        $this->message = "";
        $this->response = array();

    }
    public function success($data = [])
    {

        if (is_array($data)) {
            if (array_key_exists(0, $data)) {
                $this->response = $data;
            } else if (count($data) == 0) {
                $this->response = [];
            } else {
                $this->response = [$data];
            }
        } else {

            array_push($this->response, $data);

        }
        $this->success = true;
        $this->message = ['en'=>'success','ar'=>'success'];
    }

    public function fail($msg)
    {

        $this->success = false;
        if (gettype($msg) != "array")
            $this->message = ['en'=>$msg,'ar'=>$msg];
        else
            $this->message = ['en'=>$msg,'ar'=>$msg];
        $this->time=Carbon::now()->timestamp;;

        $this->response = [];

    }

}
