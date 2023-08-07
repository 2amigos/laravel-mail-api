<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

#Route::post('/token', \App\Http\Controllers\Api\CreateToken::class);
Route::post('/send-message', [\App\Http\Controllers\Api\SendEmail::class, 'send']);

Route::prefix('/token')->group(function () {
    Route::post('/login', [\App\Http\Controllers\Api\AuthController::class, 'login']);
});

#Route::post('/logout', 'AuthController@logout');
#Route::post('/refresh', 'AuthController@refresh');
