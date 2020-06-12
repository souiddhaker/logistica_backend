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
    Route::post('/verify', 'Api\PromocodeController@verify')->middleware('auth:api');
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
    Route::get('/list', 'Api\TripController@listTrips')->middleware('auth:api');
    Route::get('/search', 'Api\TripController@search')->middleware('auth:api');

    Route::get('/{id}', 'Api\TripController@getTrip')->middleware('auth:api');

    Route::post('/changeStatus', 'Api\TripController@changeStatus')->middleware('auth:api');
    Route::post('/create', 'Api\TripController@confirmTrip')->middleware('auth:api');
    Route::post('/cancel', 'Api\TripController@cancelTrip')->middleware('auth:api');
    Route::post('/document', 'Api\DocumentController@store')->middleware('auth:api');
    Route::delete('/document/{id}', 'Api\DocumentController@remove')->middleware('auth:api');
    Route::get('/document/{id}', 'Api\DocumentController@getAttachement')->middleware('auth:api');
    Route::post('/note', 'Api\TripController@noteDriver')->middleware('auth:api');
    Route::post('/rate', 'Api\TripController@rateTrip')->middleware('auth:api');

});
Route::prefix('admin')->group(function(){
    Route::post('/create/{name}','Api\AdminCrudController@create');
    Route::delete('/delete/{name}/{id}','Api\AdminCrudController@delete');
    Route::put('/update/{name}/{id}','Api\AdminCrudController@update');
    Route::get('/get/{name}/{id}','Api\AdminCrudController@get');
    Route::get('/all/{name}','Api\AdminCrudController@all');
    Route::prefix('auth')->group(function(){

        Route::post('login', 'Api\AdminAuthController@login');
        Route::post('logout', 'Api\AdminAuthController@logout');
        Route::post('refresh', 'Api\AdminAuthController@refresh');
        Route::post('me', 'Api\AdminAuthController@me');
    });
});


Route::middleware('auth:api')->get('/getUser', function (Request $request) {
    return $request->user();
});
Route::get('/debug-sentry', function () {
//   dd( env('SENTRY_LARAVEL_DSN'));
//    return 'hello world';
    throw new Exception('My first Sentry error!');
});
