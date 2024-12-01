<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\API\RegisterController;
use App\Http\Controllers\API\UsersController;
use App\Http\Controllers\API\DashboardController;
use App\Http\Controllers\API\CustomerController;
use App\Http\Controllers\API\OrderController;
use App\Http\Controllers\API\ProductController;
use App\Http\Controllers\API\ProfileController;
use App\Http\Controllers\API\RefillController;
use App\Http\Controllers\API\BorrowController;
use App\Http\Controllers\API\ReturnController;
use App\Http\Controllers\API\GallonDeliveryController;
use App\Http\Controllers\API\AnnouncementController;
use App\Http\Controllers\API\ConcernController;
use App\Http\Controllers\API\NotificationController;
use App\Http\Controllers\API\BusinessHourController;
use App\Http\Controllers\API\OtpController;
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
    Route::post('/forgot-password/send-otp', [OtpController::class, 'sendOtp']);
    Route::post('/forgot-password/verify-otp', [OtpController::class, 'verifyOtp']);
    Route::post('forgot-password/delete-otp', [OtpController::class, 'deleteOtp']);
    Route::post('forgot-password/reset-password', [OtpController::class, 'resetPassword']);

});

Route::middleware('auth:sanctum')->group(function () {
    Route::post('logout', [RegisterController::class, 'logout']);

    Route::prefix('user')->group(function () {
        Route::get('display', [ProfileController::class, 'show']);
        Route::put('update', [ProfileController::class, 'update']);
        Route::post('update-image', [ProfileController::class, 'updateImage']);
        Route::post('change-password', [ProfileController::class, 'changePassword']);
    });
    Route::post('validate', [CustomerController::class, 'validateUser']);
    Route::get('/concern/{id}/replies', [ConcernController::class, 'getRepliesForConcern']);
});
 
// Admin routes
Route::middleware('auth.admin')->group(function () {
    Route::get('/admin/dashboard-data', [DashboardController::class, 'getDashboardData']);
  
    Route::controller(UsersController::class)->group(function(){
        Route::post('customers', 'store');
        Route::get('customers', 'index');
        Route::get('customers/soft-deleted', 'trashed');
        
        Route::get('customers/{customer}', 'show');
        Route::put('customers/{customer}', 'update');
    
        Route::post('admin/validate', 'validateUser');
        Route::put('customers/{customer}/reset-password', 'resetPassword');
        Route::put('customers/{customer}/deactivate', 'deactivate');
        Route::post('customers/{customer}/reactivate', 'reactivate');
    });
    Route::post('/admin/walk-in-request', [UsersController::class, 'storeWalkInRequest']);
    Route::get('admin/products', [ProductController::class, 'index']);
    Route::put('admin/products/{product}', [ProductController::class, 'update']);

    Route::get('/gallon-delivery', [GallonDeliveryController::class, 'index']);
    Route::get('/gallon-delivery/{delivery_status}', [GallonDeliveryController::class, 'showRequests']);
    Route::put('/gallon-delivery/{id}/decline', [GallonDeliveryController::class, 'declineRequest']);
    Route::put('/gallon-delivery/{id}/queueing', [GallonDeliveryController::class, 'acceptRequest']);
    Route::put('/gallon-delivery/{id}/completed', [GallonDeliveryController::class, 'completedRequest']);
    Route::post('/admin/gallon-request', [GallonDeliveryController::class, 'createGallonRequest']);
    Route::get('/admin/borrowed-gallons/{id}',  [GallonDeliveryController::class, 'getBorrowedGallons']); 

    Route::get('/admin/announcement', [AnnouncementController::class, 'getAllAnnouncementsForAdmin']);
    Route::put('/admin/announcement', [AnnouncementController::class, 'store']);
    Route::put('/admin/announcement/{id}', [AnnouncementController::class, 'update']);
    Route::delete('/admin/announcement/{id}', [AnnouncementController::class, 'destroy']);
    Route::get('admin/announcement/archived', [AnnouncementController::class, 'getArchivedAnnouncements']);
    Route::put('admin/announcement/restore/{id}', [AnnouncementController::class, 'restore']);

    Route::get('/admin/concern', [ConcernController::class, 'getAllConcerns']);
    Route::put('/admin/concern/{id}/read', [ConcernController::class, 'markConcernAsRead']);
    Route::post('/concern/{id}/reply', [ConcernController::class, 'storeReply']);

    Route::get('/admin/notifications', [NotificationController::class, 'indexForAdmin']);
    Route::post('/admin/notifications/read', [NotificationController::class, 'markAsReadAdmin']);

    Route::get('/admin/business-hours', [BusinessHourController::class, 'index']);
    Route::post('/admin/business-hours', [BusinessHourController::class, 'update']);

    Route::get('/admin/borrow-limits', [BorrowController::class, 'getBorrowLimits']);
    Route::post('/admin/borrow-limits', [BorrowController::class, 'updateBorrowLimits']);
});

// Customer routes
Route::middleware('auth.customer')->group(function () {
    Route::get('/customer/dashboard-data', [DashboardController::class, 'getCustomerDashboardData']);
    Route::resource('customer/orders', OrderController::class);
    Route::get('/products', [ProductController::class, 'index']);
    Route::controller(RefillController::class)->group(function() {
        Route::get('/refill', 'index'); 
        Route::post('/refill', 'store'); 
    });
    Route::controller(BorrowController::class)->group(function() {
        Route::get('/borrow', 'index'); 
        Route::post('/borrow', 'store'); 
        Route::get('/borrowed-gallons', 'getBorrowedGallons'); 
    });
    Route::controller(ReturnController::class)->group(function() {
        Route::get('/returned', 'index'); 
        Route::post('/returned', 'store'); 
    });
    Route::get('/user/address/check', [CustomerController::class, 'checkUserAddress']);
    Route::get('/customer/transactions', [CustomerController::class, 'showRequestsTransaction']);
    Route::get('/customer/announcement', [AnnouncementController::class, 'getAnnouncementsWithReadStatus']);
    Route::post('/customer/announcement/{announcement}/read', [AnnouncementController::class, 'markAsRead']);

    Route::get('/customer/concern', [ConcernController::class, 'getCustomerConcerns']);
    Route::post('/customer/concern', [ConcernController::class, 'store']);

    Route::get('/customer/notifications', [NotificationController::class, 'indexForCustomer']);
    Route::put('/customer/notifications/{type}/read', [NotificationController::class, 'markAsReadCustomer']);

    Route::get('/customer/borrow-limits', [BorrowController::class, 'getBorrowLimits']);

    Route::get('download-qr/{qrCode}', [CustomerController::class, 'downloadQRCode']);
});