<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\API\RegisterController;
use App\Http\Controllers\API\ProductController;
use App\Http\Controllers\API\UsersController;

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

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::controller(RegisterController::class)->group(function(){
    Route::post('register', 'register');
    Route::post('login/admin', 'loginAdmin'); 
    Route::post('login/customer', 'loginCustomer');
});

Route::controller(UsersController::class)->group(function(){
    Route::post('customers', 'store');
    Route::put('customers/{customer}', 'update');
});
     
Route::middleware('auth:sanctum')->group( function () {
    Route::resource('products', ProductController::class);
});
