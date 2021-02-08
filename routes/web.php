<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\TestController;
/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/
Route::resource('cards','TestController');


Route::get('terms_and_conditions',function()
{
    return view('terms_and_conditions');
});




