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
Route::prefix('driver')->group(function(){
    Route::prefix('auth')->group(function(){
        Route::post('/register','Api\DriverController@register');
        Route::post('/login','Api\DriverController@register');
    });
    Route::prefix('trip')->group(function(){
        Route::get('/list','Api\TripController@listTrips');
        Route::post('/login','Api\DriverController@register');
        Route::get('/search', 'Api\TripController@search')->middleware('auth:api');
    });
    Route::prefix('profile')->group(function(){
        Route::put('/update','Api\UserController@update')->middleware('auth:api');;
        Route::get('','Api\DriverController@getProfile')->middleware('auth:api');;
    });
    Route::prefix('document')->group(function(){
        Route::post('/upload','Api\DriverController@profileDocument')->middleware('auth:api');;
        Route::delete('/{id}', 'Api\DocumentController@remove')->middleware('auth:api');
    });
    Route::prefix('reviews')->group(function(){
        Route::get('/','Api\DriverController@reviews')->middleware('auth:api');;
    });

    Route::prefix('account')->group(function(){
        Route::post('/addcredit','Api\AccountController@addCredit')->middleware('auth:api');;
        Route::get('/','Api\AccountController@getCredit')->middleware('auth:api');;
    });

});
Route::get('/listcar','Api\ServiceController@listCar');

Route::prefix('document')->group(function(){
    Route::post('/upload', 'Api\DocumentController@store')->middleware('auth:api');
    Route::delete('/{id}', 'Api\DocumentController@remove')->middleware('auth:api');
});

Route::prefix('trip')->group(function () {
    Route::get('/list', 'Api\TripController@listTrips')->middleware('auth:api');
    Route::get('/search', 'Api\TripController@search')->middleware('auth:api');

    Route::get('/{id}', 'Api\TripController@getTrip')->middleware('auth:api');

    Route::post('/changeStatus', 'Api\TripController@changeStatus')->middleware('auth:api');
    Route::post('/create', 'Api\TripController@createTrip')->middleware('auth:api');
    Route::post('/cancel', 'Api\TripController@cancelTrip')->middleware('auth:api');
    Route::get('/document/{id}', 'Api\DocumentController@getAttachement')->middleware('auth:api');
    Route::post('/note', 'Api\TripController@noteDriver')->middleware('auth:api');
    Route::post('/rate', 'Api\TripController@rateTrip')->middleware('auth:api');
    Route::post('/receipt', 'Api\TripController@uploadReceipt')->middleware('auth:api');
});

Route::prefix('admin')->group(function(){
    Route::post('/create/{name}','Api\AdminCrudController@create');
    Route::delete('/delete/{name}/{id}','Api\AdminCrudController@delete');
    Route::put('/update/{name}/{id}','Api\AdminCrudController@update');
    Route::get('/get/{name}/{id}','Api\AdminCrudController@get');
    Route::get('/all/{name}','Api\AdminCrudController@all');

    Route::prefix('auth')->group(function(){
        Route::post('login', 'Api\AdminAuthController@login');
        Route::post('logout', 'Api\AuthController@logout');
        Route::post('refresh', 'Api\AuthController@refresh');
        Route::post('me', 'Api\UserController@getUser');
        Route::post('register', 'Api\AdminAuthController@register');
    });
});


Route::middleware('auth:api')->get('/getUser', function (Request $request) {
    return $request->user();
});
