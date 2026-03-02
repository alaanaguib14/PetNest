<?php

use App\Http\Controllers\OrderController;
use App\Http\Controllers\Admin\OrderController as AdminOrderController;
use App\Http\Controllers\Admin\ProductController as AdminProductController;
use App\Http\Controllers\Admin\CategoryController as AdminCategoryController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\Admin\AdminAuthController;
use App\Http\Controllers\AuthController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;


Route::get('/', function(){
    return response()->json([
        'success' => true,
        'message' => 'welcome to PetNest! to start get to Register or login via https://petnest-production.up.railway.app/api/register or https://petnest-production.up.railway.app/api/login'
    ]);
});

Route::post('/login', [AuthController::class, 'login']);
Route::post('/register', [AuthController::class, 'register']);
Route::post('/forgot-password', [AuthController::class, 'forgotPassword']);
Route::post('/reset-password', [AuthController::class, 'resetPassword'])->name('password.reset');;

Route::get('/email/verify/{id}/{hash}', [AuthController::class, 'emailVerification'])
    ->middleware(['signed', 'throttle:6,1'])
    ->name('verification.verify');

Route::get('/categories', [CategoryController::class, 'index']);
Route::get('/categories/{id}', [CategoryController::class, 'show']);
Route::get('/products', [ProductController::class, 'index']);
Route::get('/products/{id}', [ProductController::class, 'show']);


Route::middleware('auth:api')->group(function () {
    Route::post('/logout',  [AuthController::class, 'logout']);
    Route::post('/refresh', [AuthController::class, 'refresh']);
    Route::get('/profile', function (Request $request) {
        return $request->user();
    });
    Route::get('/orders',[OrderController::class, 'index']);
    Route::get('/orders/{id}',[OrderController::class, 'show']);
    Route::post('/orders',[OrderController::class, 'store']);
});

// admin
Route::prefix('admin')->middleware(['auth:api', 'role:admin'])->group(function () {
    Route::post('/register', [AdminAuthController::class, 'registerAdmin']);

    Route::get('/categories', [AdminCategoryController::class, 'index']);
    Route::get('/categories/{id}', [AdminCategoryController::class, 'show']);
    Route::post('/categories', [AdminCategoryController::class, 'store']);
    Route::patch('/categories/{id}',[AdminCategoryController::class, 'update']);
    Route::delete('/categories/{id}',[AdminCategoryController::class, 'destroy']);
    Route::post('/categories/{id}/restore',[AdminCategoryController::class, 'restore']);

Route::get('/products', [AdminProductController::class, 'index']);
Route::get('/products/{id}', [AdminProductController::class, 'show']);
Route::post('/products',[AdminProductController::class, 'store']);
Route::patch('/products/{id}',[AdminProductController::class, 'update']);
Route::delete('/products/{id}',[AdminProductController::class, 'destroy']);
Route::post('/products/{id}/restore',[AdminProductController::class, 'restore']);

Route::get('/orders',[AdminOrderController::class, 'index']);
Route::get('/orders/{id}',[AdminOrderController::class, 'show']);
Route::patch('/orders/{id}/status',[AdminOrderController::class, 'updateStatus']);
Route::delete('/orders/{id}', [AdminOrderController::class, 'destroy']);
Route::post('/orders/{id}/restore', [AdminOrderController::class, 'restore']);
});