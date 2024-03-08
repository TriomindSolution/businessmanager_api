<?php

use App\Http\Controllers\SubCategory\SubcategoryController;
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

        
    //    ------------------------product api route-------------------
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

    

   //    ------------------------category api route-------------------------
         Route::controller(CategoryController::class)->group(function () {
         Route::post('/category/store', 'categoryStore');
         Route::put('/category/update/{category_id}', 'categoryUpdate');
         Route::delete('/category/delete/{category_id}', 'destroy');
       });

});

   //    ------------------------subcategory api route-------------------------
          Route::controller(SubcategoryController::class)->group(function () {
          Route::post('/subcategory/store', 'subcategoryStore');
          Route::put('/subcategory/update/{subcategory_id}', 'subcategoryUpdate');
          Route::delete('/subcategory/delete/{subcategory_id}', 'destroy');

});





});
