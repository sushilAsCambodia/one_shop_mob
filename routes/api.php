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
        Route::get('search', 'getSearchResult');   //done
        Route::get('get/homePageData', 'getHomePageData');   //done
        Route::get('get/promotional', 'getPromotional');  //done
    });
    Route::controller(CategoryController::class)->group(function () {
        Route::get('categories/all', 'all');  //done
        Route::get('categories/treeView', 'treeView'); //done
    });
    Route::controller(SubCategoryController::class)->group(function () {
        Route::get('subCategories', 'subCategoriesId'); //done
    });

    Route::controller(ProductController::class)->group(function () {
        // Route::get('products/paginate/{params?}', 'paginate');
        Route::get('products/view-all', 'index');  //done
        Route::get('products/{product}', 'get')->where(['product' => '[0-9]+']);  //done
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
        Route::get('get/newHomePageData', 'getHomePageData')->middleware('lang_id');    //done
    });

    // Add To cart
    Route::controller(AddToCartController::class)->group(function () {
        // Route::post('add-to-cart', 'addToCart');
    });

    // Add To Dorder
    Route::controller(OrderController::class)->group(function () {
        Route::get('orders/paginate/{params?}', 'paginate')->middleware('lang_id');  //done
        Route::post('customer/orders', 'store');   //done 1
        // Route::post('customer/orders-cancel', 'cancelOrder');
        Route::get('customer/checkout/{slug}', 'order')->middleware('lang_id'); // done
        Route::get('customer/order-details/{orderId}', 'orderGetById')->middleware('lang_id');  //done
        Route::get('customer/order/completed', 'orderCompleted')->middleware('lang_id');
        // Route::patch('orders/{orders}', 'update');
        // Route::delete('orders/{orders}', 'delete');
    });

    //Price Claim Route
    Route::controller(PriceClaimController::class)->group(function () {
        Route::get('prize-claim/paginate/{params?}', 'paginate')->middleware('lang_id');  //done
        // Route::get('prize-claim/all', 'all');
        Route::get('prize-claim-byClaimId', 'prizeClaimByClaimId')->middleware('lang_id');  //done
        Route::post('prize-claim/{priceClaim}', 'update'); //done 1
        // Route::get('prize-claim/{priceClaim}', 'get');
    });

    // Deals Routes
    Route::controller(DealController::class)->group(function () {
        Route::get('slot-deals/{deals}/{orderId?}', 'getSlotDeals');  //done
    });

    // Add To favorites
    Route::controller(FavoriteController::class)->group(function () {
        Route::post('favorites/add-to-favorites', 'addToFavorites');  //done 1
        Route::post('favorites/remove-from-favorites', 'removeFromFavorites'); //done 1
        Route::get('favorites/list', 'list')->middleware('lang_id');
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
        Route::post('payments/payment-response-data', 'paymentResponse');
        Route::post('final-payment', 'paymentResponse');
        Route::post('customer/order-payment', 'store'); //done 1
    });

    Route::controller(NotificationController::class)->group(function () {
        Route::get('notifications/paginate/{params?}', 'paginate')->middleware('lang_id');
        Route::patch('notifications/{notification}', 'update'); //done 1
    });
    
    // Shipping Routes
    Route::controller(ShippingController::class)->group(function () {
        Route::get('shipping/paginate/{params?}', 'paginate');
        // Route::get('shipping/all', 'all')->name('Shipping: View Shipping');
        // Route::post('shipping', 'store')->name('Shipping: Create Shipping');
        // Route::patch('shipping/{shippings}', 'update')->name('Shipping: Edit/Update Shipping')->where(['shippings' => '[0-9]+']);
        // Route::patch('shipping/update-status/{shippings}', 'updateShippingStatus')->name('Shipping: Update Shipping Status')->where(['shippings' => '[0-9]+']);
        // Route::patch('shipping/update-carrier/{shippings}', 'updateShippingCarrier')->name('Shipping: Update Shipping Carrier')->where(['shippings' => '[0-9]+']);
        // Route::delete('shipping/{shippings}', 'delete')->name('Shipping: Delete Shipping')->where(['shippings' => '[0-9]+']);
        // Route::get('shipping/status/{trackingId}', 'getShippingStatus')->name('Shipping: get ShippingStatus Shipping')->where(['shippings' => '[0-9]+']);
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
}); //done 1

Route::group(['middleware' => ['auth:sanctum']], function () {
    Route::controller(CustomerController::class)->group(function () {
        Route::get('customers/mlm-earning', 'getCalculationsCustomers');
        Route::post('customers/update-account', 'updateAccount');
        Route::post('customers/update-password', 'updatePassword');
        Route::get('customers/user-details', 'userDetails');
        // Route::post('customers/create-bot-customer', 'createBotCustomer');
        Route::post('customers/logout', 'logout');
    }); //done 1

    // Address routes
    Route::controller(AddressController::class)->group(function () {
        Route::get('addresses/all', 'all');
        // Route::get('addresses/{address}', 'get')->where(['address' => '[0-9]+']);
        Route::post('add-addresses', 'store');
        Route::post('edit-addresses/{address}', 'update')->where(['address' => '[0-9]+']);
        Route::post('delete-addresses/{address}', 'delete')->where(['address' => '[0-9]+']);
    }); //done 1
});
