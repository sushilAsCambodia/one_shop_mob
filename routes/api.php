<?php

use App\Http\Controllers\AuditLogController;
use App\Http\Controllers\BannerController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\CurrencyController;
use App\Http\Controllers\FavoriteController;
use App\Http\Controllers\LanguageController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\PromotionController;
use App\Http\Controllers\SubCategoryController;
use App\Http\Controllers\TagController;
use App\Http\Controllers\HomepageController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\SlotController;
use App\Http\Controllers\DealController;
use App\Http\Controllers\SlotDealController;
use App\Http\Controllers\InventoryController;
use App\Http\Controllers\OrderController;

use App\Http\Middleware\LanguageCurrencyMiddleware;

use App\Http\Controllers\AddToCartController;
use App\Http\Controllers\CountryController;
use App\Http\Controllers\StateController;
use App\Http\Controllers\CityController;

use App\Http\Controllers\AuthController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\PayController;
use App\Http\Controllers\ReferralController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\PriceClaimController;
use App\Http\Controllers\ShippingController;
use App\Http\Controllers\WinningController;

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

// Language Routes
Route::group(['middleware' => ['lang_id']], function () {


    // Homepage
    Route::controller(HomepageController::class)->group(function () {
        Route::get('homepage', 'index');
        Route::get('search', 'getSearchResult');
        Route::get('promotional', 'getPromotional');
    });


    Route::get('media/{path}', function (Request $request) {
        $path = storage_path() . '/app/' . $request->path;
        if (file_exists($path)) {
            $file = File::get($path);
            $type = File::mimeType($path);
            $response = Response::make($file, 200);
            $response->header("Content-Type", $type);
            return $response;
        }
    })->where('path', '.*');

    Route::controller(ProductController::class)->group(function () {
        Route::get('products/paginate/{params?}', 'paginate');
        Route::get('products/view-all', 'index');
        Route::get('products/{product}', 'get')->where(['product' => '[0-9]+']);
        // Route::post('products', 'store')->name('Product: Create Product');
        // Route::patch('products/{product}', 'update')->name('Product: Edit/Update Product');
        // Route::delete('products/{product}', 'delete')->name('Product: Delete Product');
        // Route::post('products/upload', 'upload')->name('Product: Upload Product');
        Route::get('products/get-by-category-slug/{slug}', 'getByCategorySlug');
    });

    // Audit Log
    Route::controller(AuditLogController::class)->group(function () {
        Route::get('audits/paginate/{params?}', 'paginate')->name('Report: View Audit Logs');
        Route::get('audits/models', 'getModels');
    });

    // Category Routes
    Route::controller(CategoryController::class)->group(function () {
        Route::get('categories/paginate/{params?}', 'paginate');
        Route::get('categories/all', 'all');
        Route::get('categories/treeView', 'treeView');
        Route::post('categories', 'store');
        Route::post('categories/upload', 'upload');
        Route::patch('categories/{category}', 'update')->where(['category' => '[0-9]+']);
        Route::delete('categories/{category}', 'delete')->where(['category' => '[0-9]+']);
        Route::get('categories/{category}', 'get')->where(['category' => '[0-9]+']);
    });

    // Sub Category Routes
    Route::controller(SubCategoryController::class)->group(function () {
        Route::get('sub-categories/paginate/{params?}', 'paginate')->name('SubCategory: View SubCategory');
        Route::get('sub-categories/all', 'all')->name('SubCategory: View All SubCategory');
        Route::get('sub-categories/{subCategory}', 'get')->name('SubCategory: View SubCategory')->where(['subCategory' => '[0-9]+']);
        Route::post('sub-categories', 'store')->name('SubCategory: Create SubCategory');
        Route::patch('sub-categories/{subCategory}', 'update')->name('SubCategory: Edit/Update SubCategory')->where(['subCategory' => '[0-9]+']);
        Route::delete('sub-categories/{subCategory}', 'delete')->name('SubCategory: Delete SubCategory')->where(['subCategory' => '[0-9]+']);
    });

    // Tag Routes
    Route::controller(TagController::class)->group(function () {
        Route::get('tags/paginate/{params?}', 'paginate')->name('Tag: View Tag');
        Route::get('tags/all', 'all')->name('Tag: View Tag');
        Route::post('tags', 'store')->name('Tag: Create Tag');
        Route::patch('tags/{tag}', 'update')->name('Tag: Edit/Update Tag')->where(['tag' => '[0-9]+']);
        Route::delete('tags/{tag}', 'delete')->name('Tag: Delete Tag')->where(['tag' => '[0-9]+']);
    });

    // Promotion Routes
    Route::controller(PromotionController::class)->group(function () {
        Route::get('promotions/paginate/{params?}', 'paginate')->name('Promotion: View Promotion');
        Route::get('promotions/all', 'all')->name('Promotion: View Promotion');
        Route::post('promotions', 'store')->name('Promotion: Create Promotion');
        Route::patch('promotions/{promotion}', 'update')->name('Promotion: Edit/Update Promotion')->where(['promotion' => '[0-9]+']);
        Route::delete('promotions/{promotion}', 'delete')->name('Promotion: Delete Promotion')->where(['promotion' => '[0-9]+']);
    });

    // Currency Routes
    Route::controller(CurrencyController::class)->group(function () {
        Route::get('currencies/paginate/{params?}', 'paginate')->name('Currency: View Currency');
        Route::get('currencies/all', 'all')->name('Currency: View Currency');
        Route::post('currencies', 'store')->name('Currency: Create Currency');
        Route::patch('currencies/{currency}', 'update')->name('Currency: Edit/Update Currency')->where(['currency' => '[0-9]+']);
        Route::delete('currencies/{currency}', 'delete')->name('Currency: Delete Currency')->where(['currency' => '[0-9]+']);
    });

    // Banner Routes
    Route::controller(BannerController::class)->group(function () {
        Route::get('banners/paginate/{params?}', 'paginate')->name('Banner: View Banner');
        Route::get('banners/all', 'all');
        Route::post('banners', 'store')->name('Banner: Create Banner');
        Route::patch('banners/{banner}', 'update')->name('Banner: Edit/Update Banner')->where(['banner' => '[0-9]+']);
        Route::delete('banners/{banner}', 'delete')->name('Banner: Delete Banner')->where(['banner' => '[0-9]+']);
    });
    // Role routes
    Route::controller(RoleController::class)->group(function () {
        Route::get('roles/paginate/{params?}', 'paginate')->name('Role: View Role');
        Route::get('roles/{role}/users', 'users')->name('Role: View Role');
        Route::get('roles/all', 'all')->name('Role: View Role');
        Route::get('roles/{role}', 'get')->name('Role: View Role')->where(['role' => '[0-9]+']);
        Route::get('roles', 'roles')->name('Role: View Role');
        Route::post('roles', 'store')->name('Role: Create Role');
        Route::patch('roles/{role}', 'update')->name('Role: Edit/Update Role')->where(['role' => '[0-9]+']);
        Route::delete('roles/{role}/{params?}', 'delete')->name('Role: Delete Role');

        Route::get('permissions/paginate/{params?}', 'paginatePermissions')->name('Permission: View Permission');
        Route::get('permissions/all', 'permissions')->name('Permission: View Permission');
    });
    // });

    // Role routes
    ///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    // Route::controller(MicroserviceCommunicationController::class)->group(function() {
    //     Route::get('getData', 'index');
    //     Route::get('postData', 'postData');
    //     Route::get('updateData', 'updateData');
    //     Route::get('deleteData', 'deleteData');
    // });

    Route::get('media/{path}', function (Request $request) {
        $path = storage_path() . '/app/' . $request->path;
        if (file_exists($path)) {
            $file = File::get($path);
            $type = File::mimeType($path);
            $response = Response::make($file, 200);
            $response->header("Content-Type", $type);
            return $response;
        }
    })->where('path', '.*');

    // Audit Log
    Route::controller(AuditLogController::class)->group(function () {
        Route::get('audits/paginate/{params?}', 'paginate')->name('Report: View Audit Logs');
        Route::get('audits/models', 'getModels');
    });

    // Slots Routes
    Route::controller(SlotController::class)->group(function () {
        Route::get('slots/paginate/{params?}', 'paginate')->name('Slots: View Slots');
        Route::get('slots/all', 'all')->name('Slots: View Slots');
        Route::post('slots', 'store')->name('Slots: Create Slots');
        Route::patch('slots/{slots}', 'update')->name('Slots: Edit/Update Slots')->where(['slots' => '[0-9]+']);
        Route::delete('slots/{slots}', 'delete')->name('Slots: Delete Slots')->where(['slots' => '[0-9]+']);
    });

    // Deals Routes
    Route::controller(DealController::class)->group(function () {
        Route::get('deals/paginate/{params?}', 'paginate')->name('Deals: View Deals');
        Route::get('deals/all', 'all')->name('Deals: View Deals');
        Route::post('deals', 'store')->name('Deals: Create Deals');
        Route::patch('deals/{deals}', 'update')->name('Deals: Edit/Update Deals')->where(['deals' => '[0-9]+']);
        Route::delete('deals/{deals}', 'delete')->name('Deals: Delete Deals')->where(['deals' => '[0-9]+']);
    });

    // Deals Routes
    Route::controller(SlotDealController::class)->group(function () {
        Route::get('slot-deals/paginate/{params?}', 'paginate')->name('SlotDeal: View SlotDeal');
        Route::get('slot-deals/all', 'all')->name('SlotDeal: View SlotDeal');
        Route::post('slot-deals', 'store')->name('SlotDeal: Create SlotDeal');
        Route::patch('slot-deals/{slotdeals}', 'update')->name('SlotDeal: Edit/Update SlotDeal')->where(['slotdeals' => '[0-9]+']);
        Route::delete('slot-deals/{slotdeals}', 'delete')->name('SlotDeal: Delete SlotDeal')->where(['slotdeals' => '[0-9]+']);
    });


    ///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    Route::group(['middleware' => 'whitelist_ip'], function () {
        // Sanctum endpoint to generate cookie
        Route::get('sanctum/csrf-cookie', function () {
            return response('OK', 204);
        });

        Route::controller(AuthController::class)->group(function () {
            Route::post('login', 'login');
            Route::post('register', 'register');
            Route::post('logout', 'logout');
            Route::post('refresh', 'refresh');
        });
    });

    // User routes
    Route::controller(InventoryController::class)->group(function () {
        Route::get('inventories/paginate/{params?}', 'paginate')->name('Inventory: View Inventory');
        Route::get('inventories/all', 'all')->name('Inventory: View Inventory');
        Route::post('inventories', 'store')->name('Inventory: Create Inventory');
        Route::patch('inventories/{inventory}', 'update')->name('Inventory: Edit/Update Inventory')->where(['inventory' => '[0-9]+']);
        Route::delete('inventories/{inventory}', 'delete')->name('Inventory: Delete Inventory')->where(['inventory' => '[0-9]+']);
        Route::get('inventories/get-by-product/{product_id}', 'getByProductId')->name('Inventory: Delete Inventory');
        Route::get('inventories/get-low-stock', 'getLowStock')->name('Inventory: Low Stock Inventory');
    });
    ///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

    // Country Routes
    Route::controller(CountryController::class)->group(function () {
        Route::get('countries/all', 'all');
    });

    // State Routes
    Route::controller(StateController::class)->group(function () {
        Route::get('states/getById/{counteyId}', 'getById');
    });

    // City Routes
    Route::controller(CityController::class)->group(function () {
        Route::get('cities/getById/{stateId}', 'all');
    });
});
///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

Route::group(['middleware' => ['auth:sanctum']], function () {

    // Add To cart
    Route::controller(AddToCartController::class)->group(function () {
        Route::post('add-to-cart', 'addToCart');
    });

    // Add To favorites
    Route::controller(FavoriteController::class)->group(function () {
        Route::post('favorites/add-to-favorites', 'addToFavorites');
        Route::post('favorites/remove-from-favorites', 'removeFromFavorites');
        Route::get('favorites/list', 'list');
    });

    // Add To Dorder
    Route::controller(OrderController::class)->group(function () {
        Route::get('orders/paginate/{params?}', 'paginate');
        Route::post('customer/orders', 'store');
        Route::post('customer/orders-cancel', 'cancelOrder');
        Route::get('customer/checkout/{slug}', 'order')->middleware('lang_id');
        Route::get('customer/order-details/{orderId}', 'orderGetById')->middleware('lang_id');
        Route::get('customer/order/completed', 'orderCompleted')->middleware('lang_id');

        // Route::patch('orders/{orders}', 'update');
        // Route::delete('orders/{orders}', 'delete');
    });

    Route::controller(OrderController::class)->group(function () {
        Route::get('customer/get-dashboard-counts', 'getDashboardCounts');
    });

    Route::controller(ReferralController::class)->group(function () {
        Route::get('customer/getReferral', 'getReferral');
    });

    Route::controller(DealController::class)->group(function () {
        Route::post('deals/set-deal', 'setDeal')->name('Deal: Create Deal');
        Route::get('deals/clear-deals', 'clearDeals')->name('Deal: Clear Deals');
    });

    Route::controller(WinningController::class)->group(function () {
        Route::get('winner-list', 'paginate');
    });
    // Add To Payment
    Route::controller(PaymentController::class)->group(function () {
        Route::post('customer/order-payment', 'store');
    });


    //Price Claim Route
    Route::controller(PriceClaimController::class)->group(function () {
        Route::get('prize-claim/paginate/{params?}', 'paginate');
        Route::get('prize-claim/all', 'all');
        Route::patch('prize-claim/{priceClaim}', 'update');
        Route::get('prize-claim/{priceClaim}', 'get');
    });

    // Carrier Route
    Route::controller(CarrierController::class)->group(function () {
        Route::get('carriers/paginate/{params?}', 'paginate')->name('Carrier: Views Carrier');
        Route::get('carriers/all', 'all')->name('Carrier: All Carrier');
        Route::post('carriers', 'store')->name('Carrier: Create Carrier');
        Route::patch('carriers/{carriers}', 'update')->name('Carrier: Edit/Update Carrier');
        Route::delete('carriers/{carriers}', 'delete')->name('Carrier: Delete Carrier');
    });

    // Shipping Routes
    Route::controller(ShippingController::class)->group(function () {
        Route::get('shipping/paginate/{params?}', 'paginate')->name('Shipping: View Shipping');
        Route::get('shipping/all', 'all')->name('Shipping: View Shipping');
        Route::post('shipping', 'store')->name('Shipping: Create Shipping');
        Route::patch('shipping/{shippings}', 'update')->name('Shipping: Edit/Update Shipping')->where(['shippings' => '[0-9]+']);
        Route::patch('shipping/update-status/{shippings}', 'updateShippingStatus')->name('Shipping: Update Shipping Status')->where(['shippings' => '[0-9]+']);
        Route::patch('shipping/update-carrier/{shippings}', 'updateShippingCarrier')->name('Shipping: Update Shipping Carrier')->where(['shippings' => '[0-9]+']);
        Route::delete('shipping/{shippings}', 'delete')->name('Shipping: Delete Shipping')->where(['shippings' => '[0-9]+']);
        Route::get('shipping/status/{trackingId}', 'getShippingStatus')->name('Shipping: get ShippingStatus Shipping')->where(['shippings' => '[0-9]+']);
    });


    Route::controller(NotificationController::class)->group(function () {
        Route::get('notifications/paginate/{params?}', 'paginate')->name('Notification: View Notifications');
        Route::patch('notifications/{notification}', 'update')->name('Notification: Read Notification');
    });
});

use App\Http\Controllers\DemoController;

Route::controller(DemoController::class)->group(function () {
    Route::get('demo/botEntry', 'demoWork');
    Route::get('demo/addInterval', 'addIntervalDemoWork');
});


//////////////////////// APS For IOS And Android ////////////////////////


use App\Http\Controllers\Ios\HomepageController as IOSHomepageController;
use App\Http\Controllers\Ios\ProductController as IOSProductController;

Route::group(['prefix' => 'mob', 'middleware' => ['lang_id']], function () {
    Route::controller(IOSHomepageController::class)->group(function () {
        Route::get('get/homePageData', 'getHomePageData');
        Route::get('languages/all', 'getlanguagesAll');
        Route::get('banners/all', 'getBannersAll');
        Route::get('categories/all', 'getCategoriesAll');
        Route::get('promotional', 'getPromotional');
        Route::get('countries/all', 'countriesAll');
    });

    Route::controller(IOSProductController::class)->group(function () {
        // Route::get('products/paginate/{params?}', 'paginate');
        Route::get('products/view-all', 'index');
        // Route::get('products/{product}', 'get')->where(['product' => '[0-9]+']);
        // Route::get('products/get-by-category-slug/{slug}', 'getByCategorySlug');

        Route::get('categories/treeView', 'treeView');

        // Route::get('sub-categories/paginate/{params?}', 'paginate');
        // Route::get('sub-categories/all', 'all');
        // Route::get('sub-categories/{subCategory}', 'get')->where(['subCategory' => '[0-9]+']);

        // Route::post('sub-categories', 'store');
        // Route::patch('sub-categories/{subCategory}', 'update')->where(['subCategory' => '[0-9]+']);
        // Route::delete('sub-categories/{subCategory}', 'delete')->where(['subCategory' => '[0-9]+']);

    });
});
// SubCategoryController
