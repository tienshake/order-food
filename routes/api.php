<?php

use App\Http\Controllers\API\AuthController;
use App\Http\Controllers\API\CategoryController;
use App\Http\Controllers\API\OrderController;
use App\Http\Controllers\API\PaymentController;
use App\Http\Controllers\API\ProductController;
use App\Http\Controllers\API\SearchController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::post('login', [AuthController::class, 'login']);
Route::post('register', [AuthController::class, 'register']);
Route::apiResource('products', ProductController::class)->only(['index', 'show']);
Route::apiResource('categories', CategoryController::class)->only(['index', 'show']);
Route::get('categories/{category}/products', [CategoryController::class, 'products']);
Route::post('/webhook', [PaymentController::class, 'webhook']);
Route::get('/search', [SearchController::class, 'search'])->name('search');

Route::middleware('auth:sanctum')->group(function () {
    // Route::post('logout', [AuthController::class, 'logout']);
    Route::apiResource('orders', OrderController::class)->only(['store', 'index']);
    Route::post('/create-checkout', [PaymentController::class, 'createCheckoutSession']);
    Route::get('/payment-success', [PaymentController::class, 'getPaymentSuccess']);
});