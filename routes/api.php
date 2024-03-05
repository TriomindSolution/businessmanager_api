<?php

use App\Http\Controllers\Backend\Category\Categorycontroller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\Backend\Product\ProductController;

Route::group(["middleware" => ["api"]], function () {
    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/register', [AuthController::class, 'register']);

    Route::group(["middleware" => ["auth:api"]], function () {
        Route::controller(ProductController::class)->group(function () {
            Route::post('/product/store', 'productStore');
        });

      
        });

          //    ------------------------category route-------------------------
          Route::controller(CategoryController::class)->group(function () {
            Route::post('/category/store', 'categoryStore');
            Route::put('/category/update/{category_id}', 'categoryUpdate');
            Route::delete('/category/delete/{category_id}', 'destroy');
        });


});



