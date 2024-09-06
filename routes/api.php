<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\API\RegisterController;
use App\Http\Controllers\API\UsersController;
use App\Http\Controllers\API\AdminController;
use App\Http\Controllers\API\CustomerController;
use App\Http\Controllers\API\OrderController;
use App\Http\Controllers\API\ProductController;

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


// Admin routes
Route::middleware('auth.admin')->group(function () {
    Route::get('/admin/dashboard', [AdminController::class, 'dashboard']);
    Route::resource('admin/products', ProductController::class);
});

// Customer routes
Route::middleware('auth.customer')->group(function () {
    Route::get('/customer/dashboard', [CustomerController::class, 'dashboard']);
    Route::resource('customer/orders', OrderController::class);
});

Route::controller(UsersController::class)->group(function(){
    Route::post('customers', 'store');
    Route::put('customers/{customer}', 'update');
});
