<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\Backend\Admin\AdminDashboardController;
use App\Http\Controllers\Backend\Admin\CompanyProfileController;
use App\Http\Controllers\Backend\Categories\CategoriesController;
use App\Http\Controllers\Backend\Customer\CustomerController;
use App\Http\Controllers\Backend\Expensecategory\ExpenseCategoryController;
use App\Http\Controllers\Backend\Expense\ExpenseController;
use App\Http\Controllers\Backend\Order\OrderController;
use App\Http\Controllers\Backend\Product\ProductController;
use App\Http\Controllers\Backend\Seller\SellerController;
use App\Http\Controllers\Backend\UserController;
use App\Http\Controllers\RolePermission\ParentPermissionController;
use App\Http\Controllers\RolePermission\PermissionController;
use App\Http\Controllers\RolePermission\RoleController;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Route;

Route::get('/optimize', function () {
    $exitCode = Artisan::call('optimize');
    return '<h1>Optimized class loader</h1>';
});

Route::get('/storage-link', function () {
    Artisan::call('storage:link');
    return '<h1>Storage link created successfully</h1>';
});

Route::group(["middleware" => ["api"]], function () {

    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/register', [AuthController::class, 'register']);

    Route::group(["middleware" => ["auth:api"]], function () {
        //    ------------------------product api route-------------------
        Route::controller(ProductController::class)->group(function () {
            Route::post('/product/store', 'productStore');
            Route::get('/product-list', 'productList');
            Route::post('/product/update', 'productUpdate');
            Route::put('/product/update/{product_id}', 'productUpdate');
            Route::get('/product-retrieve/{product_id}', 'productRetrieve');
            Route::delete('/product/product_variant/{product_variant_id}/delete', 'productVariantDestroy');
            Route::delete('/product-delete', 'deleteProduct');

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

        Route::controller(ExpenseCategoryController::class)->group(function () {
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

        Route::controller(OrderController::class)->group(function () {
            Route::post('/order/store', 'orderStore');
            Route::get('/order-retrieve/{order_id}', 'orderRetrieve');
            Route::get('/order-list', 'orderList');
            Route::get('/customer-retrieve/{customer_id}', 'customerRetrieve');
            Route::put('/order/update/{order_id}', 'orderUpdate');
            Route::delete('/order/order_variant/{order_variant_id}/delete', 'orderVariantDestroy');
            Route::delete('/order-delete', 'deleteOrder');
            Route::get('/customer-info', 'customerInfo');
        });

        Route::controller(AdminDashboardController::class)->group(function () {
            Route::get('/admin/dashboard-information', 'adminDashboardInformation');
            Route::post('/change-password', [UserController::class, 'changePassword']);
            Route::get('/get-order-stats', 'getOrderStats');

            Route::controller(UserController::class)->group(function () {
                Route::post('profile-image/upload', 'profileImageUpdate');
                Route::put('profile-update', 'updateProfile');
                // ----- parent permission-----
                Route::get('parent-permission/list', 'parentPermissionList');
                Route::post('/parent-permission/store', 'parentPermissionStore');

            });
            Route::controller(ParentPermissionController::class)->group(function () {
                // ----- parent permission-----
                Route::get('parent-permission/list', 'parentPermissionList');
                Route::post('/parent-permission/store', 'parentPermissionStore');
                Route::get('/parent-permission/{parentPermission_id}', 'parentPermissionRetrieve');
                Route::put('/parent-permission/update/{parentPermission_id}', 'parentPermissionUpdate');
                Route::delete('/parent-permission/{parentPermission_id}/delete', 'destroy');
            });

            Route::controller(PermissionController::class)->group(function () {
                // ----- parent permission-----
                Route::get('permission-list', 'permissionList');
                Route::post('/permission/store', 'permissionStore');
                Route::get('/permission/{permission_id}', 'permissionRetrieve');
                Route::put('/permission/update/{permission_id}', 'permissionUpdate');
                Route::delete('/permission/{permission_id}/delete', 'destroy');
            });

            Route::controller(RoleController::class)->group(function () {
                // ----- parent permission-----
                Route::get('role-list', 'roleList');
                Route::post('/role/store', 'roleStore');
                Route::get('/role/{role_id}', 'roleRetrieve');
                Route::put('/role/update/{role_id}', 'roleUpdate');
                Route::delete('/role/{role_id}/delete', 'destroy');
                Route::get('/parent-permissions-with-role/{role_id}', 'getParentPermissionsWithRole');
                Route::post('/assign_permission-to-role', 'assignPermissionsToRole');
            });
        });

        Route::controller(CompanyProfileController::class)->group(function () {
            // Route::post('/company-profile/store', 'companyProfileStore');
            // Route::get('/order-retrieve/{order_id}', 'orderRetrieve');
            Route::get('/company-profile-list', 'CompanyProfileList');
            // Route::get('/customer-retrieve/{customer_id}', 'customerRetrieve');
            Route::post('/company-profile/update/{company_profile_id?}', 'saveOrUpdate');
            Route::delete('/company-profile/{company_profile_id}/delete', 'destroy');
        });
    });

});
