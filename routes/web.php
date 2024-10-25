<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\VerificationController;
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

// Email Verification Routes
Route::get('/email/verify/{id}/{hash}', [VerificationController::class, 'verify'])
     ->middleware(['signed'])
     ->name('verification.verify');

Route::get('/email/verification-notification', [VerificationController::class, 'send'])
     ->middleware(['auth'])
     ->name('verification.send');
