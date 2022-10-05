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

//API route for register new user
Route::post('/register', [App\Http\Controllers\API\AuthController::class, 'register']);
//API route for login user
Route::post('/login', [App\Http\Controllers\API\AuthController::class, 'login']);

//Protecting Routes
Route::group(['middleware' => ['auth:sanctum']], function () {
    // API route for logout user
    Route::post('/logout', [App\Http\Controllers\API\AuthController::class, 'logout']);
    // API route for profile user
    Route::get('/profile', [App\Http\Controllers\API\AuthController::class, 'profile']);

    //only owner can crud kos
    Route::group(['middleware' => ['role:owner']], function () {
        // API route for owner
        Route::apiResource('/owner-kos', App\Http\Controllers\API\Kos\KosController::class);
        // API route for dashboard
        Route::get('/owner-kos-dashboard', [App\Http\Controllers\API\Kos\KosController::class, 'dashboard']);
    });

    //only regular / premium user can access
    Route::group(['middleware' => ['role:regular|premium']], function () {
        // API route for room availibility
        Route::get('/room-availibility/{id}', [App\Http\Controllers\API\Kos\KosController::class, 'roomAvailibility']);
    });
});

// Api route for users
Route::get('/kost-list', [App\Http\Controllers\API\Kos\KosController::class, 'indexUser']);
Route::get('/kost-list/{id}', [App\Http\Controllers\API\Kos\KosController::class, 'showUser']);
