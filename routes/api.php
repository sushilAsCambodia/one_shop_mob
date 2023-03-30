<?php

use App\Http\Controllers\BannerController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\FavoriteController;
use App\Http\Controllers\LanguageController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\SubCategoryController;
use App\Http\Controllers\HomepageController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DealController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\AddressController;
use App\Http\Controllers\CountryController;
use App\Http\Controllers\StateController;
use App\Http\Controllers\CityController;
use App\Http\Controllers\BankAccountController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\DemoController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\PriceClaimController;
use App\Http\Controllers\ShippingController;
use App\Http\Controllers\TransactionController;

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
        Route::get('products/view-all', 'index');
        Route::get('products/{product}', 'get')->where(['product' => '[0-9]+']);
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
        Route::get('get/newHomePageData', 'getHomePageData')->middleware('lang_id');
    });
    // Add To Dorder
    Route::controller(OrderController::class)->group(function () {
        Route::get('orders/paginate/{params?}', 'paginate')->middleware('lang_id');
        Route::post('customer/orders', 'store');
        Route::get('customer/checkout/{slug}', 'order')->middleware('lang_id');
        Route::get('customer/order-details/{orderId}', 'orderGetById')->middleware('lang_id');
        Route::get('customer/order/completed', 'orderCompleted')->middleware('lang_id');
    });
    //Price Claim Route
    Route::controller(PriceClaimController::class)->group(function () {
        Route::get('prize-claim/paginate/{params?}', 'paginate')->middleware('lang_id');
        Route::get('prize-claim-byClaimId', 'prizeClaimByClaimId')->middleware('lang_id');
        Route::post('prize-claim/{priceClaim}', 'update');
    });
    // Deals Routes
    Route::controller(DealController::class)->group(function () {
        Route::get('slot-deals/{deals}/{orderId?}', 'getSlotDeals');
    });
    // Add To favorites
    Route::controller(FavoriteController::class)->group(function () {
        Route::post('favorites/add-to-favorites', 'addToFavorites');
        Route::post('favorites/remove-from-favorites', 'removeFromFavorites');
        Route::get('favorites/list', 'list')->middleware('lang_id');
    });
    // Add To Payment
    Route::controller(PaymentController::class)->group(function () {
        Route::post('payments/payment-response', 'paymentResponse');
        Route::post('customer/order-payment', 'storeHttp');
    });
    Route::controller(NotificationController::class)->group(function () {
        Route::get('notifications/paginate/{params?}', 'paginate')->middleware('lang_id');
        Route::patch('notifications/{notification}', 'update');
    });
    // Shipping Routes
    Route::controller(ShippingController::class)->group(function () {
        Route::get('shipping/paginate/{params?}', 'paginate');
    });
});
// login creadation // Customer routes
Route::controller(CustomerController::class)->group(function () {
    Route::post('customers/send-otp', 'sendOTP');
    Route::post('customers/verify-otp', 'verifyOTP');
    Route::post('customers/register', 'register');
    Route::post('customers/login', 'login');
    Route::post('customers/forget-password', 'forgetPassword');
    Route::post('customers/set-new-password', 'setNewPassword');
});
Route::group(['middleware' => ['auth:sanctum']], function () {
    Route::controller(CustomerController::class)->group(function () {
        Route::get('customers/mlm-earning', 'getCalculationsCustomers');
        Route::post('customers/update-account', 'updateAccount');
        Route::post('customers/update-password', 'updatePassword');
        Route::get('customers/user-details', 'userDetails');
        Route::get('customers/user-wallet', 'userWallet');
        Route::get('customers/get-transactions', 'getTransactions');
        Route::post('customers/logout', 'logout');
    });
    // Address routes
    Route::controller(AddressController::class)->group(function () {
        Route::get('addresses/all', 'all');
        Route::post('add-addresses', 'store');
        Route::post('edit-addresses/{address}', 'update')->where(['address' => '[0-9]+']);
        Route::post('delete-addresses/{address}', 'delete')->where(['address' => '[0-9]+']);
    });
    // Bank Account Route
    Route::controller(BankAccountController::class)->group(function () {
        Route::get('bank-accounts/paginate/{params?}', 'paginate');
        Route::get('bank-accounts/all', 'all');
        Route::post('bank-accounts', 'store');
        Route::post('bank-accounts-edit/{bankAccount}', 'update');
        Route::post('bank-accounts-delete/{bankAccount}', 'delete');
    });
    Route::controller(TransactionController::class)->group(function () {
        Route::post('transactions/withdraw', 'withdraw');
    });
    // Bank Account Route
    Route::controller(CustomerController::class)->group(function () {
        Route::delete('customers/delete-account', 'deleteOwnAccount');
    });
});


// demo push notification
Route::controller(DemoController::class)->group(function () {
    Route::get('demoPushNoti', 'demoPushNoti');
});
