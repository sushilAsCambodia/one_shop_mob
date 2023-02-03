<?php

namespace App\Services;

use App\Models\Country;
use App\Models\Currency;
use App\Models\Customer;
use App\Models\Deal;
use App\Models\WhitelistIP;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Artisan;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class MicroserviceCommunicationService
{
    public function index($data): JsonResponse
    {
        try {

            $result = [];

            switch ($data['type']) {
                case 'GET_DATA_PERMISSIONS':
                    // Artisan::call("update:permissions");
                    $result =  Permission::orderBy('name')->get();
                    break;
                case 'GET_DATA_USERS':
                    $userService = new UserService();
                    $result = $userService->paginate($data);
                    break;
                case 'GET_DATA_AUTH_USER':
                    $authUser = new AuthService();
                    $result = $authUser->user();
                    break;
                case 'GET_DATA_ROLES':
                    $result = Role::with('permissions')->orderBy('name')->get();
                    break;
                case 'GET_DATA_LOGS':
                    $auditService = new AuditLogService();
                    $result = $auditService->paginate($data);
                    break;
                case 'GET_DATA_LANGUAGES':
                    $languages = new LanguageService();
                    $result = $languages->paginate($data);
                    break;
                case 'GET_DATA_CURRENCIES':
                    $result = Currency::get();
                    break;
                case 'GET_DATA_COUNTRIES':
                    $result = Country::get();
                    break;
                case 'GET_DATA_COUSTOMERS':
                    $result = Customer::get();
                    break;
                case 'GET_DATA_AUTH_COUSTOMER':
                    $result = Customer::find($data['id']);
                    break;
                case 'GET_DATA_WHITE_LIST_IPS':
                    $result = WhitelistIP::get();
                    break;
                case 'UPDATE_MIGRATION_FRESH':
                    Artisan::call("migrate:fresh --seed");
                    $result =  Permission::orderBy('name')->get();
                    break;
                case 'UPDATE_MIGRATION':
                    Artisan::call("migrate");
                    $result =  Permission::orderBy('name')->get();
                    break;

                    //Get the data from the deals

                case 'GET_DATA_DEALS':
                    $result = Deal::all($data);
                    break;
                case 'GET_DATA_DEALS':
                    $dealService = new DealService();
                    $result = $dealService->paginate($data);
                    break;
            }

            return response()->json($result, 200);
        } catch (\Exception $e) {
            \Log::debug($e);
            return generalErrorResponse($e);
        }
    }
// post data from deal service
    public function postData($data): JsonResponse
    {
        try {

            $result = [];

            switch ($data['type']) {

                case 'POST_DATA_DEALS':
                    $dealService = new DealService();
                    $result = $dealService->store($data);
                    break;
            }

            return response()->json($result, 200);
        } catch (\Exception $e) {
            \Log::debug($e);
            return generalErrorResponse($e);
        }
    }

// update data from deal service
    public function postUpdate($data): JsonResponse
    {
        try {

            $result = [];

            switch ($data['type']) {

                case 'UPDATE_DATA_DEALS':
                    $result = Deal::patch($data);
                    break;
            }

            return response()->json($result, 200);
        } catch (\Exception $e) {
            \Log::debug($e);
            return generalErrorResponse($e);
        }
    }

// delete data from deal service
    public function postDelete($data): JsonResponse
    {
        try {

            $result = [];

            switch ($data['type']) {

                case 'DELETE_DATA_DEALS':
                    $deleteService= new dealService();
                    $result = $deleteService->delete($data);
                    break;
            }

            return response()->json($result, 200);
        } catch (\Exception $e) {
            \Log::debug($e);
            return generalErrorResponse($e);
        }
    }
}
