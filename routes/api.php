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

use App\Http\Controllers\AddressController;

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
        // Route::get('homepage', 'index');
        Route::get('search', 'getSearchResult');
        Route::get('get/homePageData', 'getHomePageData');
        Route::get('get/promotional', 'getPromotional');
    });
    Route::controller(CategoryController::class)->group(function () {
        Route::get('categories/all', 'all');
        Route::get('categories/treeView', 'treeView');
    });
    Route::controller(SubCategoryController::class)->group(function () {
        Route::get('subCategories', 'subCategoriesId');
    });

    Route::controller(ProductController::class)->group(function () {
        // Route::get('products/paginate/{params?}', 'paginate');
        Route::get('products/view-all', 'index');
        Route::get('products/{product}', 'get')->where(['product' => '[0-9]+']);
        // Route::get('products/get-by-category-slug/{slug}', 'getByCategorySlug');
    });

    Route::controller(LanguageController::class)->group(function () {
        Route::get('languages/all', 'all')->name('Language: View Language');
    });

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
    // Banner Routes
    Route::controller(BannerController::class)->group(function () {
        Route::get('banners/all', 'all');
    });
});
///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

Route::group(['middleware' => ['auth:sanctum']], function () {

    Route::controller(HomepageController::class)->group(function () {
        // Route::get('homepage', 'index');
        Route::get('get/newHomePageData', 'getHomePageData')->middleware('lang_id');;
    });

    // Add To cart
    Route::controller(AddToCartController::class)->group(function () {
        // Route::post('add-to-cart', 'addToCart');
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
        // Route::post('customer/orders-cancel', 'cancelOrder');
        Route::get('customer/checkout/{slug}', 'order')->middleware('lang_id');
        Route::get('customer/order-details/{orderId}', 'orderGetById')->middleware('lang_id');
        // Route::get('customer/order/completed', 'orderCompleted')->middleware('lang_id');

        // Route::patch('orders/{orders}', 'update');
        // Route::delete('orders/{orders}', 'delete');
    });

    Route::controller(OrderController::class)->group(function () {
        // Route::get('customer/get-dashboard-counts', 'getDashboardCounts');
    });

    Route::controller(ReferralController::class)->group(function () {
        // Route::get('customer/getReferral', 'getReferral');
    });

    Route::controller(WinningController::class)->group(function () {
        // Route::get('winner-list', 'paginate');
    });
    // Add To Payment
    Route::controller(PaymentController::class)->group(function () {
        // Route::post('customer/order-payment', 'store');
    });

    //Price Claim Route
    Route::controller(PriceClaimController::class)->group(function () {
        Route::get('prize-claim/paginate/{params?}', 'paginate');
        // Route::get('prize-claim/all', 'all');
        // Route::patch('prize-claim/{priceClaim}', 'update');
        // Route::get('prize-claim/{priceClaim}', 'get');
    });

    Route::controller(NotificationController::class)->group(function () {
        Route::get('notifications/paginate/{params?}', 'paginate');
        Route::patch('notifications/{notification}', 'update');
    });
});


// login creadation
// Customer routes
Route::controller(CustomerController::class)->group(function () {
    // Route::get('customers-earning', 'getCalculations');
    Route::post('customers/send-otp', 'sendOTP');
    Route::post('customers/verify-otp', 'verifyOTP');
    Route::post('customers/register', 'register');
    Route::post('customers/login', 'login');
    // Route::get('customers/{customer}', 'get')->where(['customer' => '[0-9]+']);
    Route::post('customers/forget-password', 'forgetPassword');
    Route::post('customers/set-new-password', 'setNewPassword');
});

Route::group(['middleware' => ['auth:sanctum']], function () {
    Route::controller(CustomerController::class)->group(function () {
        // Route::get('customers/mlm-earning', 'getCalculationsCustomers');
        Route::post('customers/update-account', 'updateAccount');
        Route::get('customers/user-details', 'userDetails');
        // Route::post('customers/create-bot-customer', 'createBotCustomer');
        Route::post('customers/logout', 'logout');
    });

    // Address routes
    Route::controller(AddressController::class)->group(function () {
        Route::get('addresses/all', 'all');
        // Route::get('addresses/{address}', 'get')->where(['address' => '[0-9]+']);
        Route::post('add-addresses', 'store');
        Route::post('edit-addresses/{address}', 'update')->where(['address' => '[0-9]+']);
        Route::post('delete-addresses/{address}', 'delete')->where(['address' => '[0-9]+']);
    });
});
