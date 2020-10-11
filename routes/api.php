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

//Route::post('auth/login', 'App\Http\Controllers\EncryptionController@encryptUserCreation');

Route::post('user/login', 'App\Http\Controllers\AuthController@login');
Route::get('user/token', 'App\Http\Controllers\AuthController@getToken'); // TODO temporary active

Route::middleware('jwt_validate')->group(function () {

    Route::post('user/create', 'App\Http\Controllers\EncryptionController@encryptUserCreation');
    Route::get('user/get', 'App\Http\Controllers\EncryptionController@getDocument');

    Route::post('user/logout', 'App\Http\Controllers\AuthController@logout');
});

//Route::middleware('jwt_validate')->get('/user', function (Request $request) {
//    return $request->user();
//});

