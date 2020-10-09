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

Route::post('auth/login', 'App\Http\Controllers\EncryptionController@encryptUserCreation');


Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});

Route::post('user/create', 'App\Http\Controllers\EncryptionController@encryptUserCreation');
Route::get('user/get', 'App\Http\Controllers\EncryptionController@getDocument');
