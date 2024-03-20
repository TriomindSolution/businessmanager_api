<?php


use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\Backend\Seller\SellerController;
use App\Http\Controllers\Backend\Expense\ExpenseController;
use App\Http\Controllers\Backend\Product\ProductController;
use App\Http\Controllers\Backend\Categories\CategoriesController;
use App\Http\Controllers\Backend\Expensecategory\ExpensecategoryController;
use App\Http\Controllers\Backend\Customer\CustomerController;


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
         Route::controller(CategoriesController::class)->group(function () {
         Route::post('/category/store', 'categoryStore');
         Route::get('/category-list', 'categoryList');
         Route::put('/category/update/{category_id}', 'categoryUpdate');
         Route::delete('/category/delete/{category_id}', 'destroy');
         Route::get('/category-retrieve/{category_id}', 'categoryRetrieve');
       });
        //    ------------------------expensecategory api route-------------------------
    Route::controller(ExpensecategoryController::class)->group(function () {
      Route::post('/expensecategory/store', 'expensecategoryStore');
      Route::put('/expensecategory/update/{expensecategory_id}', 'expensecategoryUpdate');
      Route::delete('/expensecategory/delete/{expensecategory_id}', 'destroy');
      Route::get('/expensecategory-list', 'expenseCatgoeryList');
      Route::get('/expensecategory-retrieve/{expensecategory_id}', 'expenseCategoryRetrieve');


     });
       //-------------------------------------expense--------------------------------------
    Route::controller(ExpenseController::class)->group(function () {
      Route::post('/expense/store', 'expenseStore');
      Route::put('/expense/update/{expense_id}', 'expenseUpdate');
      Route::delete('/expense/delete/{expense_id}', 'destroy');
      Route::get('/expense-list', 'expenseList');
      Route::get('/expense-retrieve/{expense_id}', 'expenseRetrieve');




     });

        //-------------------------------------customer--------------------------------------
        Route::controller(CustomerController::class)->group(function () {
            Route::post('/customer/store', 'customerStore');
            Route::put('/customer/update/{customer_id}', 'customerUpdate');
            Route::delete('/customer/delete/{customer_id}', 'destroy');
            Route::get('/customer-list', 'customerList');
            Route::get('/customer-retrieve/{customer_id}', 'customerRetrieve');




        });




});

});
