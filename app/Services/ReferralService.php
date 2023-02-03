<?php

namespace App\Services;

use App\Models\Customer;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class ReferralService
{
    public function __construct(private OrderProductService $orderProductService)
    {
    }

    public function getReferral(): JsonResponse
    {
        try {
        
            $lable1Data = Customer::whereParentReferralCode(Auth()->user()->referral_code)->where('parent_referral_code','!=',null)->get();

            $lable1['lable1']['members']           = count($lable1Data);
            $lable1['lable1']['transactionAmount'] = 0;
            $lable1['lable1']['commission']        = 0;
            $lable1['lable1']['memberData']        = $lable1Data;

            $allReferralId = array();
            $lable1Child = array();
            foreach ($lable1Data as $lable1DataVal) {
                array_push($lable1Child, $lable1DataVal->referral_code);
                array_push($allReferralId, $lable1DataVal->id);
            }

            $lable2Data = Customer::whereIn('parent_referral_code', $lable1Child)->get();

            $lable1['lable2']['members']           = count($lable2Data);
            $lable1['lable2']['transactionAmount'] = 0;
            $lable1['lable2']['commission']        = 0;
            $lable1['lable2']['memberData']        = $lable2Data;

            $lable2Child = array();
            foreach ($lable2Data as $lable2DataVal) {
                array_push($lable2Child, $lable2DataVal->referral_code);
                array_push($allReferralId, $lable2DataVal->id);
            }

            $lable3Data = Customer::whereIn('parent_referral_code', $lable2Child)->get();

            $lable1['lable3']['members']           = count($lable3Data);
            $lable1['lable3']['transactionAmount'] = sizeof(getCustomerData(Auth::user()))>0 ? getCustomerData(Auth::user())['total_transaction'] : 0;
            $lable1['lable3']['commission']        = sizeof(getCustomerData(Auth::user()))>0 ? getCustomerData(Auth::user())['total_commissions'] : 0;
            $lable1['lable3']['memberData']        = $lable3Data;

            foreach ($lable3Data as $lable3DataVal) {
                array_push($allReferralId, $lable3DataVal->id);
            }

            $lable1['totalMembers']           = count($allReferralId);
            $lable1['totalTransactionAmount'] = 0;
            $lable1['totalCommission']        = 0;

            // $lable1['memberData'] = Customer::whereIn('id', $allReferralId)->get();

            // foreach($lable1['memberData'] as $key => $memberDataVal){
            //     $lable1['memberData'][$key]->phone_number = substr($memberDataVal->phone_number, 0, 4).'******';
            // }

            return response()->json([
                'messages' => ['Dashboard Count Data'],
                'data'     => $lable1,
            ], 201);
        } catch (\Exception $e) {
            \Log::debug($e);
            return generalErrorResponse($e);
        }
    }
}
