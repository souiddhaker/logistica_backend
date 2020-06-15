<?php

namespace App\Http\Controllers\Api;

use App\Models\Result;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use GuzzleHttp\Client;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use GuzzleHttp\Exception\BadResponseException;
use Illuminate\Routing\UrlGenerator;
use Carbon\Carbon;

trait IssueTokenTrait{

    public function issueToken($request, $grantType, $scope = ""){

        $params = [
            'grant_type' => $grantType,
            'client_id' => $this->client->id,
            'client_secret' => $this->client->secret,
            'scope' => $scope,
            'provider' => "password"
        ];
        $params['username'] = $request['email'];
        if($grantType !== 'social' && $grantType!=="refresh_token" && !isset($request['isAdmin'])){
            $request['password'] = "logistica";
        }

        $request =  array_merge($params, $request );
        $http = new Client;
        try{
            $response = $http->request('POST', url("/")."/oauth/token", [
                'form_params' => $request ,
                'headers' => [
                    'Accept'     => 'application/json']
            ]);
            $result = json_decode((string) $response->getBody(), true);
            $result['expiration_date']=Carbon::now()->addSeconds($result['expires_in'])->timestamp;;

            return $result;
        }catch(BadResponseException $ex){
            return null;
        }
    }

}
