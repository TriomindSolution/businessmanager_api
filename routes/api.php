<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\Backend\Seller\SellerController;
use App\Http\Controllers\Backend\Product\ProductController;
use App\Http\Controllers\Backend\Category\Categorycontroller;

Route::group(["middleware" => ["api"]], function () {

    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/register', [AuthController::class, 'register']);

    Route::group(["middleware" => ["auth:api"]], function () {

        Route::controller(ProductController::class)->group(function () {
            Route::post('/product/store', 'productStore');
        });

        Route::controller(SellerController::class)->group(function () {
            Route::get('/seller-list', 'sellerList');
            Route::get('/seller-retrieve/{seller_id}', 'sellerRetrieve');
            Route::post('/seller/store', 'sellerStore');
            Route::put('/seller/update/{seller_id}', 'sellerUpdate');
            Route::delete('/seller/delete/{seller_id}', 'destroy');
        });

    });
});



Route::apiResource('category', Categorycontroller::class);
