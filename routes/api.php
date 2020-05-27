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
    Route::get('', 'Api\UserController@getUser')->middleware('auth:api');

});

Route::prefix('services')->group(function () {
    Route::post('/car_categories', 'Api\ServiceController@getListCarCategories')->middleware('auth:api');
    Route::post('/list_services', 'Api\ServiceController@getListServices')->middleware('auth:api');
});


Route::prefix('promocode')->group(function () {
    Route::post('/verify', 'Api\PromocodeController@verify');
});


Route::prefix('payment')->group(function () {
    Route::post('/add_card', 'Api\PaymentController@addCard')->middleware('auth:api');
    Route::get('/get_cards', 'Api\PaymentController@getCardsByUser')->middleware('auth:api');

});

Route::prefix('address')->group(function () {
    Route::get('/list', 'Api\AddressController@getAllFavoritesAddress')->middleware('auth:api');
    Route::delete('/remove/{id}', 'Api\AddressController@remove')->middleware('auth:api');
    Route::post('/add', 'Api\AddressController@store')->middleware('auth:api');


});

Route::prefix('notifs')->group(function () {
    Route::get('/list', 'Api\NotifController@getAllNotifs')->middleware('auth:api');
    Route::get('/{id}', 'Api\NotifController@getDetails')->middleware('auth:api');

});


Route::prefix('trip')->group(function () {
    Route::post('/document', 'Api\DocumentController@store')->middleware('auth:api');
    Route::delete('/document/{id}', 'Api\DocumentController@remove')->middleware('auth:api');
    Route::get('/document/{id}', 'Api\DocumentController@getAttachement')->middleware('auth:api');
    Route::post('/note', 'Api\TripController@noteDriver')->middleware('auth:api');

});


Route::middleware('auth:api')->get('/getUser', function (Request $request) {
    return $request->user();
});
