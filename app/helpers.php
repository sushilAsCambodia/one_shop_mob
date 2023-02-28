<?php

use App\Models\Configure;
use App\Models\Customer;
use App\Models\Images;
use App\Models\Language;
use App\Models\OntimePassword;
use App\Models\OrderProduct;
use Carbon\Carbon;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;

const TRANSFER_IN  = 'transfer_in';
const TRANSFER_OUT  = 'transfer_out';

if (!function_exists('getErrorMessages')) {
    function getErrorMessages($messages)
    {
        $errorMessages = [];
        foreach ($messages as $key => $values) {
            foreach ($values as $index => $value) {
                array_push($errorMessages, $value);
            }
        }

        return $errorMessages[0];
    }
}


// $bookingId = "SD-".Carbon::now()->timestamp;

if (!function_exists('uploadImage')) {
    function uploadImage($file, $folder)
    {

        if (!empty($file) && is_file($file)) {
            $md5Name = md5_file($file->getRealPath());
            $md5Name = Carbon::now()->timestamp . $md5Name;
            $guessExtension = $file->guessExtension();
            $uploaded_files = $file->storeAs('public/images/' . $folder, $md5Name . '.' . $guessExtension);
            $uploaded_files = substr(Storage::url($uploaded_files), 1);

            return $uploaded_files;
        }
        $file;
    }
}

if (!function_exists('getArrayCollections')) {

    function getArrayCollections($arrayData)
    {
        $data = [];
        foreach ($arrayData as $key => $dt) {
            foreach ($dt as $d) {
                array_push($data, $d);
            }
        }

        return $data;
    }
}

if (!function_exists('generalErrorResponse')) {
    function generalErrorResponse(Exception $e)
    {
        \Log::debug($e);
        return response()->json([
            'message' => [$e->getMessage()],
            'trace' => [$e->getTrace()],
        ], 400);
    }
}

if (!function_exists('paginate')) {
    function paginate($items, $perPage = 100, $page = null, $options = [])
    {
        $page = $page ?: (Paginator::resolveCurrentPage() ?: 1);
        $items = $items instanceof Collection ? $items : Collection::make($items);

        return new LengthAwarePaginator($items->forPage($page, $perPage), $items->count(), $perPage, $page, $options);
    }
}

if (!function_exists('checkFileType')) {
    function checkFileType($file)
    {
        $type = 'file';
        if (substr($file->getMimeType(), 0, 5) == 'image') {
            $type = 'image';
        }

        return $type;
    }
}

if (!function_exists('getRandomIdGenerate')) {
    function getRandomIdGenerate($prefix = null)
    {
        return $prefix . '-' . Carbon::now()->timestamp . mt_rand(100, 99999);
    }
}
if (!function_exists('generateReferralCode')) {
    function generateReferralCode($codeLength = 6)
    {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $charactersNumber = strlen($characters);

        $code = '';
        while (strlen($code) < 6) {
            $position = rand(0, $charactersNumber - 1);
            $character = $characters[$position];
            $code = $code . $character;
        }

        if (Customer::where('referral_code', $code)->exists()) {
            generateReferralCode($codeLength);
        }

        return $code;
    }
}

if (!function_exists('getSuccessMessages')) {
    function getSuccessMessages($data, $status = true)
    {
        $successMessage = [];
        if (!empty($data['message'])) {
            $successMessage['message'] = $data['message'];
        }
        if (!empty($data['data'])) {
            $successMessage['data'] = $data['data'];
        }
        $successMessage['status'] = $status;

        return response()->json($successMessage, $data['statusCode']);
    }
}

if (!function_exists('getCustomerData')) {
    function getCustomerData(Customer $customer, $level = null)
    {
        $datas = array();
        if ($customer) {
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

            $obj1 = new stdClass();
            $obj2 = new stdClass();
            $obj3 = new stdClass();

            $obj1->members = $L1Customers;
            $obj1->members_count = count($L1Customers);
            $obj1->transaction_amount = $L1OrdersValue;

            if ($L1OrdersValue > 0 && $mlmConfiguration->level_one_status == 'active')
                $obj1->commission = ($mlmConfiguration->commission * $L1OrdersValue) /  100;
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


            $datas['member'] = $customer;
            $datas['level_one'] = $obj1;
            $datas['level_two'] = $obj2;
            $datas['level_three'] = $obj3;
            $datas['total_members'] = count($L1Customers) + count($L2Customers) + count($L3Customers);
            $datas['total_members_data'] = getArrayCollections([$L1Customers, $L2Customers, $L3Customers]);
            $datas['total_transaction'] = $obj1->transaction_amount + $obj2->transaction_amount + $obj3->transaction_amount;
            $datas['total_commissions'] = $obj1->commission + $obj2->commission + $obj3->commission;
        }
        return $datas;
    }
}

if (!function_exists('sendOTP')) {
    function sendOTP($idd = 0, $phoneNumber = 0, $langId = 1, $type = 'register')
    {
        $otpValue = rand(100000, 999999);
        // $otpValue = 123456;
        $landData = Language::where('id', $langId)->first();
        if (!empty($landData->locale_web)) {
            $langIds = $landData->locale_web;
        } else {
            $langIds = "en";
        }
        if ($type == 'register') {
            // $message = trans('message.firstOtpTextReg') . ' ' . $otpValue . ' ' . trans('message.secondOtpTextReg');
            if ($langIds == "ch") {
                $message = $otpValue . ' 是您的一次性密码，输入密码以完成注册';
            } elseif ($langIds == "kh") {
                $message = 'អតិថិជនជាទីគោរព លេខ ' . $otpValue . ' គឺជា OTP ដើម្បីចុះឈ្មោះរបស់អ្នកសម្រាប់ OneShop ។ សូមកុំបង្ហាញនរណា, ក្រុមការងារ OneShop មិនដែលសុំ OTP ទេ។';
            } else {
                $message = 'Dear Customer, ' . $otpValue . ' is the OTP to complete your registration for OneShop. DO NOT disclose it to anyone, OneShop team never asks for OTP.';
            }
        } else {
            if ($langIds == "ch") {
                $message = $otpValue . ' 是您的一次性密码，输入密码以重置密码。如果您没提出此请求，请通过 admin@the1shops.com 联系我们';
            } elseif ($langIds == "kh") {
                $message = 'ប្រើលេខ ' . $otpValue . ' ជា OTP របស់អ្នក ដើម្បីប្តូរពាក្យសម្ងាត់ OneShop របស់អ្នកឡើងវិញ។ ប្រសិនបើអ្នកមិនបានធ្វើសំណើនេះទេ សូមជូនដំណឹងមកយើងតាមរយៈ admin@the1shops.com';
            } else {
                $message = 'Use ' . $otpValue . ' as your OTP to reset your OneShop Password. If you did not make this request, please alert us at admin@the1shops.com';
            }
        }

        // $toPhoneNumber = "855882103199";
        $toPhoneNumber = $idd . $phoneNumber;
        $toPhoneNumber = str_replace(array('+'), '', $toPhoneNumber);

        $serviceUrl = 'http://bizsms.metfone.com.kh:8804/bulkapi?wsdl';
        
        // $serviceID = 'MetfoneT';

        if ($langIds == "en") {
            $contentType = 0;
        } else {
            $contentType = 1;
        }
        // return $contentType;
        $client = new \SoapClient($serviceUrl);
        $params = array(
            "User" => env('SMS_GATEWAY_USERID'),
            "Password" => env('SMS_GATEWAY_PASSWORD'),
            "CPCode" => env('SMS_GATEWAY_CODE'),
            "RequestID" => "1",
            "UserID" => $toPhoneNumber,
            "ReceiverID" => $toPhoneNumber,
            "ServiceID" => env('SMS_GATEWAY_SERVICEDID'),
            "CommandCode" => "bulksms",
            "Content" => $message,
            "ContentType" => $contentType
        );

        $response = $client->__soapCall("wsCpMt", array($params));
        // print_r($response);
        if ($response->return->result === 0) {
            return false;
        }
        //save otp in table
        $otp = OntimePassword::whereIdd($idd)->wherePhoneNumber($phoneNumber)->whereType($type)->whereValue($otpValue)->first();
        if ($otp)
            $otp->update(['value' => $otpValue]);
        else {
            OntimePassword::create([
                'value' => $otpValue,
                'phone_number' => $phoneNumber,
                'idd' => $idd,
                'type' => $type,
            ]);
        }
        return true;
    }
}

if (!function_exists('verifyOTP')) {
    function verifyOTP($idd = 0, $phoneNumber = 0, $otpValue = 0, $type = 'register')
    {
        $otp = OntimePassword::whereIdd($idd)->whereValue($otpValue)->wherePhoneNumber($phoneNumber)->whereType($type)->orderBy('id', 'DESC')->first();

        if ($otp) {
            if ($otp->expire_at && strtotime($otp->expire_at) < strtotime(now()))
                return ['status' => false, 'msg' => 'otp_expired'];

            $otp->update(['is_verify' => true]);
            return ['status' => true];
        } else
            return ['status' => false, 'msg' => 'incorrect_otp'];
    }
}


if (!function_exists('zeroappend')) {
    function zeroappend($LastNumber)
    {
        $count = (int) log10(abs($LastNumber)) + 1;
        if ($count == 1) {
            return $append = '000000';
        } elseif ($count == 2) {
            return $append = '00000';
        } elseif ($count == 3) {
            return $append = '0000';
        } elseif ($count == 4) {
            return $append = '000';
        } elseif ($count == 5) {
            return $append = '00';
        } elseif ($count == 6) {
            return $append = '0';
        } elseif ($count == 7) {
            return $append = '';
        } else {
            return $append = '';
        }
    }
}

if (!function_exists('formatIdd')) {
    function formatIdd($idd)
    {
        $idd = str_replace('+', '', $idd);
        $idd = '+' . $idd;

        return $idd;
    }
}

/**
 * @desc soft delete relationship
 * @param $resource
 * @param $relations_to_cascade
 * @return mixed
 * @date 08 Feb 2023
 * @author Suhsil Gupta
 */
if (!function_exists('softDeleteRelations')) {
    function softDeleteRelations($resource, $relations_to_cascade)
    {
        if ($relations_to_cascade && is_array($relations_to_cascade)) {
            foreach ($relations_to_cascade as $relation) {
                if ($resource->{$relation}) {
                    if ($relation == 'file' or $relation == 'files' or $relation == 'image' or $relation == 'images') {
                        try {
                            foreach ($resource->{$relation}()->get() as $item) {
                                $data = $item->storage_path;
                                $trash_data = TRASH_FOLDER . $data;
                                //if is file, will move file to trash folder (safe delete can restore file later)
                                Storage::move($data, $trash_data);
                                $item->delete();
                            }
                        } catch (\Exception $e) {
                            Log::error("Delete relationship of table " . $resource->getTable() . " error: for relation name: " . $relation);
                            Log::error($e->getMessage());
                        }
                    } else {
                        try {
                            foreach ($resource->{$relation}()->get() as $item) {
                                $item->delete();
                                Log::debug("Deleted: " . $item->getTable());
                            }
                        } catch (\Exception $e) {
                            Log::error("Delete relationship of table " . $resource->getTable() . " error: for relation name: " . $relation);
                            Log::error($e->getMessage());
                        }
                    }
                }
            }
        }
    }
}
/**
 * @desc restore soft delete relationship
 * @param $resource
 * @param $relations_to_cascade
 * @return mixed
 * @date 08 Feb 2023
 * @author Suhsil Gupta
 */
