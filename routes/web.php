<?php

use Illuminate\Support\Facades\Route;

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

Route::get('/', function () {
    return view('welcome');
});


Auth::routes();

Route::group(['middleware' => ['auth']], function() {
    Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');
    Route::get('/product', [App\Http\Controllers\Master\ProductController::class, 'index']);
    Route::get('/product/{id}', [App\Http\Controllers\Master\ProductController::class, 'show']);
    Route::post('/product/{id}', [App\Http\Controllers\Master\ProductController::class, 'addToCart']);

    Route::get('/transaction', [App\Http\Controllers\Sales\TransactionController::class, 'index']);
    Route::post('/transaction', [App\Http\Controllers\Sales\TransactionController::class, 'store']);
});
