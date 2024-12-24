<?php

use App\Http\Controllers\API\AuthController;
use App\Http\Controllers\API\CategoryController;
use App\Http\Controllers\API\OrderController;
use App\Http\Controllers\API\ProductController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect('/admin');
});
