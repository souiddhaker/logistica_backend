<?php

namespace App\Models;
use Carbon\Carbon;

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
        $this->message = trans('messages.success');
    }

    public function fail($msg)
    {

        $this->success = false;

        $this->message = $msg;
        $this->time=Carbon::now()->timestamp;;

        $this->response = [];

    }

}
