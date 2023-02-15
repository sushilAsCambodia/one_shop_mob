<?php

namespace App\Services;

use App\Models\Address;
use App\Models\Configure;
use App\Models\Customer;
use App\Models\Favorite;
use App\Models\Notification;
use App\Models\OntimePassword;
use App\Models\Order;
use App\Models\OrderProduct;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Session;
use Spatie\Permission\Models\Role;
use stdClass;

class CustomerService
{
    public function sendOTP($data): JsonResponse
    {
        try {
            //save customer data in session
            sendOTP($data['idd'], $data['phone_number']);

            $result['message'] = 'otp_send_successfully';
            $result['statusCode'] = 200;

            return getSuccessMessages($result);
        } catch (\Exception $e) {
            // \Log::debug($e);
            return generalErrorResponse($e);
        }
    }
    public function verifyOTP($data): JsonResponse
    {
        try {
            $type = $data['type'] ?? 'register';
            $verifyResult = verifyOTP($data['idd'], $data['phone_number'], $data['otp'], $type);
            if ($verifyResult['status']) {
                $result['message'] = 'otp_verify_successfully';
                $result['statusCode'] = 200;

                return getSuccessMessages($result);
            } else {
                $result['message'] = $verifyResult['msg'];
                $result['statusCode'] = 400;

                return getSuccessMessages($result, false);
            }
        } catch (\Exception $e) {
            // \Log::debug($e);
            return generalErrorResponse($e);
        }
    }

    public function register($data): JsonResponse
    {
        try {
            //checking if referral code exist
            if (!empty($data['referral_code'])) {
                $customer = Customer::whereReferralCode($data['referral_code'])->first();
                if (!$customer) {
                    $result['message'] = 'referral_code_not_exist';
                    $result['statusCode'] = 400;

                    return getSuccessMessages($result, false);
                }
            }
            $otp = OntimePassword::whereIdd($data['idd'])->wherePhoneNumber($data['phone_number'])->whereType('register')->first();
            if ($otp) {
                if ($otp->is_verify) {
                    $customer = Customer::whereIdd($data['idd'])->wherePhoneNumber($data['phone_number'])->first();
                    if ($customer) {
                        $result['message'] = 'customer_already_registered';
                        $result['statusCode'] = 400;

                        return getSuccessMessages($result, false);
                    }
                    $data['parent_referral_code'] = @$data['referral_code'];
                    $data['referral_code'] = generateReferralCode(6);

                    $nexCustomerId = DB::table('customers')->max('id') + 1;
                    $append = zeroappend($nexCustomerId);
                    $memberID = 'M' . $append . $nexCustomerId;
                    $data['member_ID'] = $memberID . rand(00, 99);

                    //create customer
                    $customer = Customer::create($data);
                    $customer->assignRole('Customer');

                    $result['message'] = 'customer_registered_successfully';
                    $result['statusCode'] = 200;

                    return getSuccessMessages($result);
                } else {
                    $result['message'] = 'OTP_not_yet_verified';
                    $result['statusCode'] = 400;

                    return getSuccessMessages($result, false);
                }
            }
            /* This is a response to the client. */

            $result['message'] = 'customer_registration_failed';
            $result['statusCode'] = 400;

            return getSuccessMessages($result, false);
        } catch (\Exception $e) {
            // \Log::debug($e);
            return generalErrorResponse($e);
        }
    }

    public function login(array $data): JsonResponse
    {
        $loginData = [
            "phone_number" => $data['phone_number'],
            "idd" => $data['idd'],
            "password" => $data['password']
        ];
        // dd($loginData);
        try {
            if (Auth::guard('customer')->attempt($data)) {
                $customer = Auth::guard('customer')->user();
                $customer->tokens()->delete();

                $result['message'] = 'login_successfully';
                $result['statusCode'] = 200;
                $result['data'] = [
                    'customer' => $customer,
                    'notifications' => $customer->notifications(),
                    'token' => $customer->createToken($customer->phone_number)->plainTextToken,
                ];

                return getSuccessMessages($result);
            }
            //check if user not yet registered
            unset($loginData['password']);
            if (!Customer::where($loginData)->first()) {

                $result['message'] = 'This_user_not_yet_registered';
                $result['statusCode'] = 400;

                return getSuccessMessages($result, false);
            }

            $result['message'] = 'Incorrect_Username_or_password';
            $result['statusCode'] = 400;

            return getSuccessMessages($result, false);
        } catch (\Exception $e) {
            \Log::debug($e);
            return generalErrorResponse($e);
        }
    }
    public function forgetPassword(array $data): JsonResponse
    {
        try {
            sendOTP($data['idd'], $data['phone_number'], 'forget_password');

            $result['message'] = 'otp_send_successfully';
            $result['statusCode'] = 200;

            return getSuccessMessages($result);
        } catch (\Exception $e) {
            // \Log::debug($e);
            return generalErrorResponse($e);
        }
    }

    public function setNewPassword(array $data, $type = null): JsonResponse
    {
        try {
            $otp = OntimePassword::whereIdd($data['idd'])->wherePhoneNumber($data['phone_number'])->whereType('forget_password')->first();
            if ($otp) {
                if ($otp->is_verify) {
                    $customer = Customer::whereIdd($data['idd'])->wherePhoneNumber($data['phone_number'])->first();

                    //check if password the same as old password
                    if (Hash::check($data['password'], $customer->password)) {
                        $result['message'] = 'This_is_your_old_password';
                        $result['statusCode'] = 400;

                        return getSuccessMessages($result, false);
                    }
                    $customer->update(['password' => $data['password']]);
                    $result['message'] = 'password_reset_successfully';
                    $result['statusCode'] = 200;

                    return getSuccessMessages($result);
                } else {
                    $result['message'] = 'OTP_not_yet_verified';
                    $result['statusCode'] = 400;

                    return getSuccessMessages($result, false);
                }
            }
            $result['message'] = 'reset_password_failed';
            $result['statusCode'] = 400;

            return getSuccessMessages($result, false);
        } catch (\Exception $e) {
            // \Log::debug($e);
            return generalErrorResponse($e);
        }
    }

    public function logout()
    {
        if (Auth::user()) {
            Auth::user()->tokens()->delete();
            Auth::guard('web')->logout();
        }

        $result['message'] = 'Logout_successful';
        $result['statusCode'] = 200;

        return getSuccessMessages($result);
    }

    /**
     * @description update customer account detail service function
     * @author Phen
     * @return JsonResponse
     * @date 06 Jan 2023
     */
    public function updateAccount($request): JsonResponse
    {
        try {
            $customerData = [
                'first_name' => $request->first_name,
                'last_name' => $request->last_name,
                'display_name' => $request->display_name,
                'email' => $request->email,
            ];
            // if ($request->current_password) {
            //     if (Hash::check($request->current_password, Auth::user()->password)) {
            //         $customerData['password'] = $request->new_password;
            //     } else {
            //         return response()->json([
            //             'status' => false,
            //             'messages' => ['Current password not correct'],

            //         ], 200);
            //     }
            // }
            Auth::user()->update($customerData);

            $result['message'] = 'Account_has_been_saved_successfully';
            $result['statusCode'] = 200;
            return getSuccessMessages($result);
        } catch (\Exception $e) {
            // \Log::debug($e);
            return generalErrorResponse($e);
        }
    }
    public function updatePassword($request): JsonResponse
    {
        try {
            if ($request->current_password) {
                if (Hash::check($request->current_password, Auth::user()->password)) {
                    $customerData['password'] = $request->new_password;
                } else {
                    $result['message'] = 'Current_password_not_correct';
                    $result['statusCode'] = 200;
                    return getSuccessMessages($result, false);
                }
            }
            Auth::user()->update($customerData);

            $result['message'] = 'password_has_been_update_successfully';
            $result['statusCode'] = 200;
            return getSuccessMessages($result);
        } catch (\Exception $e) {
            // \Log::debug($e);
            return generalErrorResponse($e);
        }
    }

    /**
     * @description customer detail using token service function
     * @author Sushil
     * @return JsonResponse
     * @date 06 Jan 2023
     */

    public function userDetails(): JsonResponse
    {
        try {

            $result['orderCount']   = Order::where('customer_id', Auth()->user()->id)->where('status', 'confirmed')->count();

            $query =  (new Address())->newQuery();
            $modelData = Auth::user();
            $query->when($modelData, function ($query) use ($modelData) {
                $query->whereAddressableType(Customer::class)
                    ->whereAddressableId($modelData->id);
            });
            $result['addressCount'] = $query->count();

            // $result['whishlistDetails'] = Favorite::where('customer_id', Auth()->user()->id)->get();
            $result['whishlistCount'] = $query->select('favorites.*')
                ->whereHas('products', function ($query) {
                    $query->whereHas('deal', function ($query) {
                        $query->whereNotIn('deals.status', ['settled', 'inactive']);
                    });
                })
                ->with('product.deal.slots')->count();

            $result['customer'] = Auth()->user();

            // notification start
            $sortBy = 'created_at';
            $sortOrder = 'desc';
            $query = (new Notification())->newQuery()->orderBy($sortBy, $sortOrder);

            $result['latest_notification'] = $query->where(['notifiable_id' => auth()->user()->id])
                ->select('id', 'type', 'read_at', 'notifiable_id', 'data')
                ->first();

            $results['message'] = 'fetch_user_details_successfully';
            $results['data'] = $result;
            $results['statusCode'] = 200;

            return getSuccessMessages($results);
        } catch (\Exception $e) {
            // \Log::debug($e);
            return generalErrorResponse($e);
        }
    }

    public function getCalculations($request, $customer): JsonResponse
    {
        try {
            $mlmConfiguration = json_decode(Configure::where('type', 'MLM')->select('data')->first()->data);
            $L1Customers = Customer::withSum('order', 'amount')->where('parent_referral_code', $customer->referral_code)->get();
            $L1CustomersId = collect($L1Customers->pluck('referral_code'));

            $L2Customers = Customer::withSum('order', 'amount')->whereIn('parent_referral_code', $L1CustomersId)->get();
            $L2CustomersId = collect($L2Customers->pluck('referral_code'));

            $L3Customers = Customer::withSum('order', 'amount')->whereIn('parent_referral_code', $L2CustomersId)->get();

            $L3CustomersId = collect($L3Customers->pluck('referral_code'));

            $L1OrdersValue = OrderProduct::whereIn('customer_id', $L1CustomersId)->where(function ($q) {
                $q->where('status', 'confirmed')->orWhere('status', 'winner');
            })->sum('amount');
            $L2OrdersValue = OrderProduct::whereIn('customer_id', $L2CustomersId)->where(function ($q) {
                $q->where('status', 'confirmed')->orWhere('status', 'winner');
            })->sum('amount');
            $L3OrdersValue = OrderProduct::whereIn('customer_id', $L3CustomersId)->where(function ($q) {
                $q->where('status', 'confirmed')->orWhere('status', 'winner');
            })->sum('amount');

            $result = [];

            $obj1 = new stdClass();
            $obj2 = new stdClass();
            $obj3 = new stdClass();

            $obj1->members = $L1Customers;
            $obj1->members_count = count($L1Customers);
            $obj1->transaction_amount = $L1OrdersValue;
            if ($L1OrdersValue > 0 && $mlmConfiguration->level_one_status == 'active')
                $obj1->commission = ($mlmConfiguration->level_one_commission * $L1OrdersValue) /  100;
            else
                $obj1->commission = 0;

            $obj2->members = $L2Customers;
            $obj2->members_count = count($L2Customers);
            $obj2->transaction_amount = $L1OrdersValue;
            if ($L2OrdersValue > 0 && $mlmConfiguration->level_two_status == 'active')
                $obj2->commission = ($mlmConfiguration->level_two_commission  *  $L2OrdersValue) / 100;
            else
                $obj2->commission = 0;


            $obj3->members = $L3Customers;
            $obj3->members_count = count($L3Customers);
            $obj3->transaction_amount = $L3OrdersValue;
            if ($L3OrdersValue > 0 && $mlmConfiguration->level_three_status == 'active')
                $obj3->commission = ($mlmConfiguration->level_three_commission * $L3OrdersValue) /  100;
            else
                $obj3->commission = 0;


            $result['member'] = $customer;
            $result['level_one'] = $obj1;
            $result['level_two'] = $obj2;
            $result['level_three'] = $obj3;

            $result['total_members'] = count($L1Customers) + count($L2Customers) + count($L3Customers);
            $result['total_members_data'] = getArrayCollections([$L1Customers, $L2Customers, $L3Customers]);
            $result['total_transaction'] = $obj1->transaction_amount + $obj2->transaction_amount + $obj3->transaction_amount;
            $result['total_commissions'] = $obj1->commission + $obj2->commission + $obj3->commission;


            return response()->json($result, 200);
        } catch (\Exception $e) {
            return generalErrorResponse($e);
        }
    }
}
