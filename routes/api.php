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
Route::group(['middleware' => 'auth:api'], function () {
    Route::prefix('user')->group(function () {
        Route::put('', 'Api\UserController@update');
        Route::post('/profileImage', 'Api\UserController@uploadImage');
        Route::get('', 'Api\UserController@getUser');
        Route::post('/fcm', 'Api\UserController@userFcmToken');
        Route::post('/notify', 'Api\UserController@notify');
    });
    Route::prefix('services')->group(function () {
        Route::post('/car_categories', 'Api\ServiceController@getListCarCategories');
        Route::post('/list_services', 'Api\ServiceController@getListServices');
    });
    Route::prefix('account')->group(function () {
        Route::post('/', 'Api\ServiceController@getListCarCategories');
        Route::post('/list_services', 'Api\ServiceController@getListServices');
    });
    Route::prefix('promocode')->group(function () {
        Route::post('/verify', 'Api\PromocodeController@verify');
    });
    Route::prefix('payment')->group(function () {
        Route::post('/add_card', 'Api\PaymentController@addCard');
        Route::get('/get_cards', 'Api\PaymentController@getCardsByUser');

    });
    Route::prefix('address')->group(function () {
        Route::get('/list', 'Api\AddressController@getAllFavoritesAddress');
        Route::delete('/remove/{id}', 'Api\AddressController@remove');
        Route::post('/add', 'Api\AddressController@store');
    });
    Route::prefix('notifs')->group(function () {
        Route::get('/list', 'Api\NotifController@getAllNotifs');
        Route::get('/unread', 'Api\NotifController@getUnread');
        Route::get('/{id}', 'Api\NotifController@getDetails');

    });
    Route::prefix('document')->group(function(){
        Route::post('/upload', 'Api\DocumentController@store');
        Route::delete('/{id}', 'Api\DocumentController@remove');
    });
    Route::prefix('trip')->group(function(){
        Route::get('/list', 'Api\TripController@listTrips');
        Route::get('/search', 'Api\TripController@search');
        Route::post('/request','Api\TripController@driverRequest');

        Route::get('/{id}', 'Api\TripController@getTrip');

        Route::post('/changeStatus', 'Api\TripController@changeStatus');
        Route::post('/create', 'Api\TripController@createTrip');
        Route::post('/cancel', 'Api\TripController@cancelTrip');
        Route::get('/document/{id}', 'Api\DocumentController@getAttachement');
        Route::post('/note', 'Api\TripController@noteDriver');
        Route::post('/rate', 'Api\TripController@rateTrip');
        Route::post('/responseToDriver', 'Api\TripController@confirmTripFromUser');

    });
});

Route::prefix('/driver/auth')->group(function(){
    Route::post('/register','Api\DriverController@register');
    Route::post('/login','Api\DriverController@register');
});
Route::group(['prefix' => 'driver','middleware' => ['auth:api']],function(){

    Route::prefix('trip')->group(function(){
        Route::get('/list','Api\TripController@listTrips');
        Route::get('/list_request','Api\TripController@listOfRequest');
        Route::get('/search', 'Api\TripController@search');
        Route::post('/accept', 'Api\DriverController@acceptTripFromDriver');
        Route::post('/refuse', 'Api\DriverController@refuseTripFromDriver');
        Route::post('/pickup/{id}', 'Api\DriverController@pickupTrip');
        Route::post('/finished/{id}', 'Api\DriverController@finishedTrip');
        Route::post('/receipt', 'Api\TripController@uploadReceipt');
    });
    Route::prefix('profile')->group(function(){
        Route::put('/update','Api\DriverController@updateDriver');
        Route::get('','Api\DriverController@getProfile');
    });
    Route::prefix('document')->group(function(){
        Route::post('/upload','Api\DriverController@profileDocument');
        Route::delete('/{id}', 'Api\DocumentController@remove');
        Route::put('/update', 'Api\DocumentController@updateDocument');
    });
    Route::prefix('reviews')->group(function(){
        Route::get('/','Api\DriverController@reviews');
    });

    Route::prefix('notifs')->group(function(){
        Route::get('/list', 'Api\NotifController@getAllNotifs');
        Route::get('/unread', 'Api\NotifController@getUnread');
        Route::get('/{id}', 'Api\NotifController@getDetails');
    });

    Route::prefix('account')->group(function(){
        Route::post('/addcredit','Api\AccountController@addCredit');
        Route::get('/','Api\AccountController@getCredit');
    });
    Route::prefix('payment')->group(function(){
        Route::get('/resume','Api\PaymentController@resume');
    });
    Route::post('/updateposition', 'Api\DriverController@updatePosition');

    Route::prefix('help')->group(function(){
        Route::get('/list', 'Api\HelpController@getAllQuestions');
    });

});
Route::get('/listcar','Api\ServiceController@listCar');

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
Route::post('/test/{id}', 'Api\DriverController@getListDriverForTrip');
Route::post('/notifyme/{id}','Api\DriverController@notifyMe');
Route::middleware('auth:api')->get('/getUser', function (Request $request) {
    return $request->user();
});
