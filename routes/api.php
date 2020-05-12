<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/
Route::prefix('auth')->group(function () {
    Route::post('/verifPhone', "Api\AuthController@verify");
    Route::post('/verifCode', "Api\AuthController@verifyCode");
    Route::post('/register', "Api\AuthController@register");
    Route::post('refresh', 'Api\AuthController@refresh');
    Route::post('logout', 'Api\AuthController@logout')->middleware('auth:api');

});

Route::prefix('user')->group(function () {
    Route::put('', 'Api\UserController@update')->middleware('auth:api');
    Route::post('/profileImage', 'Api\UserController@uploadImage')->middleware('auth:api');

});
Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});
