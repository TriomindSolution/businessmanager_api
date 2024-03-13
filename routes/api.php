<?php


use App\Http\Controllers\Backend\Expense\ExpenseController;
use App\Http\Controllers\Backend\Expensecategory\ExpensecategoryController;
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

        //    ------------------------expensecategory api route-------------------------
    Route::controller(ExpensecategoryController::class)->group(function () {
      Route::post('/expensecategory/store', 'expensecategoryStore');
      Route::put('/expensecategory/update/{expensecategory_id}', 'expensecategoryUpdate');
      Route::delete('/expensecategory/delete/{expensecategory_id}', 'destroy');
  
  
     });
  
       //-------------------------------------expense--------------------------------------
    Route::controller(ExpenseController::class)->group(function () {
      Route::post('/expense/store', 'expenseStore');
      Route::put('/expense/update/{expense_id}', 'expenseUpdate');
      Route::delete('/expense/delete/{expense_id}', 'destroy');
  
  
     });


});

    
  


});
