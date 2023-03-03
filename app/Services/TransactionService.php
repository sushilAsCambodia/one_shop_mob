<?php

namespace App\Services;

use App\Models\Currency;
use App\Models\Customer;
use App\Models\Transaction;
use App\Models\WithdrawDetail as WithDrawDetail;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class TransactionService
{
    public function withdraw($request): JsonResponse
    {
        try {
            $currencyId = 1;
            $currency = Currency::find($request->currency_id);
            if ($currency)
                $currencyId = $currency->id;

            //check customer balance availability
            if (withDrawAmount() + $request['amount'] > customerRemainAmount()) {
                $result['message'] = 'You_cannot_rquest_withdraw_you_don\'t_have_enough_balance';
                $result['statusCode'] = 201;
                return getSuccessMessages($result, false);
            }
            $data['status'] = 'Review';
            $data['transaction_ID'] = getRandomIdGenerate('TR');
            $data['member_id'] = auth()->id();
            $data['amount'] = $request->amount;
            $data['bank_account_id'] = $request->bank_account_id;
            $data['transaction_type'] = 'withdraw';
            $data['currency_id'] = $currencyId;
            $data['message'] = "{{Customer withdrawed Amount}}: " . $data['amount'];

            $transaction = Transaction::create($data);
            saveFiles($transaction, 'image', @$data['file']);
            $result['message'] = 'withdrawal_request_submitted';
            $result['data'] = ['transaction_ID' => $data['transaction_ID']];
            $result['statusCode'] = 200;
            return getSuccessMessages($result);
        } catch (\Exception $e) {
            \Log::error($e);
            return generalErrorResponse($e);
        }
    }
}
