<?php

namespace App\Services;

use App\Http\Controllers\PaymentController;
use App\Models\Deal;
use App\Models\Order;
use App\Models\OrderProduct;
use App\Models\Payment;
use App\Models\Slot;
use App\Models\SlotDeal;
use App\Models\Transaction;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class PaymentService
{
    public function paginate($request): JsonResponse
    {
        try {
            $perPage = $request->rowsPerPage ?: 15;
            $page = $request->page ?: 1;
            $sortBy = $request->sortBy ?: 'created_at';
            $sortOrder = $request->descending == 'true' ? 'desc' : 'asc';

            $query = (new Payment())->newQuery()->orderBy($sortBy, $sortOrder);

            $query->when($request->name, function ($query) use ($request) {
                $query->where('name', 'like', "%$request->name%");
            });
            $results = $query->select('orders.*')->paginate($perPage, ['*'], 'page', $page);

            return response()->json($results, 200);
        } catch (\Exception $e) {
            \Log::debug($e);
            return generalErrorResponse($e);
        }
    }

    // public function store(array $data)
    // {
    //     try {
    //         $noProductDeal = false;
    //         DB::transaction(function () use ($data, &$noProductDeal) {

    //             foreach ($data as $dataVal) {
    //                 $order = Order::where('id', $dataVal['order_id'])
    //                     ->where('customer_id', Auth()->user()->id)->first();
    //                 if(empty($order)){
    //                     $result['message'] = 'The_selected_order_id_is_invalid';
    //                     $result['data'] = $dataVal['order_id'];
    //                     $result['statusCode'] = 400;
    //                     return getSuccessMessages($result); 
    //                 }
    //                 $dataPayment['payment_id'] = rand();
    //                 $dataPayment['customer_id'] = Auth()->user()->id;
    //                 $dataPayment['order_id'] = $order['id'];
    //                 $dataPayment['amount'] = $order['total_amount'];
    //                 $dataPayment['provider'] = 'test';
    //                 $dataPayment['status'] = 'complete';

    //                 Payment::create($dataPayment);

    //                 foreach ($dataVal['order_product'] as $orderProduct) {
    //                     $orderProductData = OrderProduct::where('id', $orderProduct['order_product_id'])
    //                         ->where('order_id', $dataVal['order_id'])->firstOrFail();
    //                     $orderProductData->update(['status' => 'confirmed']);

    //                     $deal = Deal::whereProductId($orderProductData->product_id)
    //                         ->whereStatus('active')->orderBy('created_at', 'desc')->first();
    //                     if (!$deal) {
    //                         $noProductDeal = true;
    //                         return false;
    //                     }

    //                     $slotId = $deal->slots()->first();

    //                     SlotDeal::where('order_id', $order->id)->where('slot_id', $slotId->id)
    //                         ->update(['status' => 'confirmed']);
    //                 }

    //                 $orderProductCount = OrderProduct::where('order_id', $dataVal['order_id'])->count();
    //                 $orderProductCountConfirm = OrderProduct::where('order_id', $dataVal['order_id'])
    //                     ->where('status', 'confirmed')->count();
    //                 if ($orderProductCountConfirm == $orderProductCount) {
    //                     $orderStatus = ['status' => 'confirmed'];
    //                 } else {
    //                     $orderStatus = ['status' => 'reserved'];
    //                 }

    //                 $order->update($orderStatus);
    //             }

    //         });

    //         if ($noProductDeal) {
    //             $result['message'] = 'The_selected_order_product_id_is_invalid';
    //             $result['data'] = [
    //                 "order_product.*.order_product_id" =>
    //                 "product_deal_not_exist"
    //             ];
    //             $result['statusCode'] = 400;
    //             return getSuccessMessages($result);
    //         }

    //         $result['message'] = 'Payment_created_Successfully';
    //         $result['statusCode'] = 200;

    //         return getSuccessMessages($result);
    //     } catch (\Exception $e) {
    //         \Log::debug($e);
    //         return generalErrorResponse($e);
    //     }
    // }


    // public function store(array $data)
    // {
    //     try {
    //         $noProductDeal = false;
    //         DB::transaction(function () use ($data, &$noProductDeal) {
    //             $orderProductsIds = $data['order_product_ids'];
    //             $orderProducts = OrderProduct::whereIn('id', $orderProductsIds)->where('customer_id', auth()->user()->id)->get();

    //             $orderIds = $orderProducts->pluck('order_id');

    //             if (!$orderIds) {
    //                 // $noProductDeal = false;
    //                 $result['message'] = 'invalid_product';
    //                 $result['statusCode'] = 400;
    //                 return getSuccessMessages($result);
    //             }

    //             $orderIds = array_unique($orderIds->toArray());

    //             $orderProductsCount = Order::whereIn('id', $orderIds)->withCount('orderProduct')->get()->pluck('order_product_count', 'id')->toArray();

    //             $orderStatus = ['status' => 'confirmed'];

    //             foreach ($orderProducts as $order) {

    //                 $check = Payment::whereOrderId($order->order_id)->where('order_product_ids', $order->id)->first();

    //                 $orderStatus = ['status' => 'confirmed'];

    //                 if (!$check) {
    //                     $dataPayment['payment_id']  = rand();
    //                     $dataPayment['customer_id'] = Auth()->user()->id;
    //                     $dataPayment['order_id']    = $order->order_id;
    //                     $dataPayment['order_product_ids']    = $order->id;
    //                     $dataPayment['amount']      = $order->amount;
    //                     $dataPayment['provider']    = 'test';
    //                     $dataPayment['status']      = 'complete';
    //                     Payment::create($dataPayment);
    //                     $deal = Deal::whereId($order->deal_id)->whereStatus('active')->orderBy('created_at', 'desc')->first();

    //                     if (!$deal) {
    //                         $noProductDeal = true;
    //                         return false;
    //                     }
    //                     $slotId = $deal->slots()->first();

    //                     SlotDeal::where('order_id', $order->order_id)->where('deal_id', $order->deal_id)->update(['status' => 'confirmed']);


    //                     if ($slotId->total_slots <= SlotDeal::where('status', 'confirmed')->where('deal_id', $order->deal_id)->count()) {
    //                         Deal::whereId($order->deal_id)->update(['status' => 'inactive']);
    //                     }

    //                     OrderProduct::whereId($order->id)->update($orderStatus);
    //                 }
    //                 if (array_key_exists($order->order_id, $orderProductsCount)) {

    //                     if (OrderProduct::whereId($order->id)->whereStatus('confirmed')->count() != $orderProductsCount[$order->order_id]) {
    //                         $orderStatus = ['status' => 'remaining'];
    //                     } else if (OrderProduct::whereId($order->id)->whereStatus('confirmed')->count() == 0) {
    //                         $orderStatus = ['status' => 'pending'];
    //                     }

    //                     Order::whereId($order->order_id)->update($orderStatus);
    //                 }
    //             }
    //         });

    //         if ($noProductDeal) {
    //             $result['message'] = 'invalid_product';
    //             $result['statusCode'] = 400;
    //             return getSuccessMessages($result);
    //         }

    //         $result['message'] = 'payment_successfully';
    //         $result['statusCode'] = 200;
    //         return getSuccessMessages($result);
    //     } catch (\Exception $e) {
    //         \Log::debug($e);
    //         return generalErrorResponse($e);
    //     }
    // }


    public function store(array $data)
    {
        try {
            $orderProductsIds = array();
            $noProductDeal = false;
            $orderProductsIds = $data['order_product_ids'];
            $currencyId = 1;
            $customer = Auth::user();
            // $totalPayAmount = OrderProduct::whereIn('id', $orderProductsIds)->sum('amount');
            // $customerRemainAmount = 0;
            // if(@$customer->wallets)
            //     $customerRemainAmount = @$customer->wallets->where('currency_id', $currencyId)->first()->amount ?? 0;
            // $paymentType = @$data['payment_type']??'w';
            // if($paymentType == 'w') {
            //     if ($customerRemainAmount < $totalPayAmount) {
            //         // return false;
            //         return response()->json([
            //             'message' => 'insufficient balance in your wallet',
            //         ], 422);
            //     }
            // }
            $payWithThirdPart = false;
            $responseData = [];
            DB::transaction(function () use ($data, &$noProductDeal, $orderProductsIds, $currencyId, $customer, &$payWithThirdPart, &$responseData) {

                $orderProducts = OrderProduct::whereIn('id', $orderProductsIds)->where('customer_id', auth()->user()->id)->get();

                $orderIds = $orderProducts->pluck('order_id');
                //checking user balance
                //ending checking user balance
                $orderIds = array_unique($orderIds->toArray());
                $orderStatus = ['status' => 'confirmed'];
                foreach ($orderProducts as $order) {
                    $check = Payment::whereOrderId($order->order_id)->where('order_product_ids', $order->id)->first();
                    $deal = Deal::whereProductId($order->product_id)->orderBy('created_at', 'desc')->first();
                    $orderStatus = ['status' => 'confirmed'];



                    $paymentWalletBalance = 0;
                    $remainingPayAmount = 0;
                    $wallets = $customer->wallets();
                    if ($wallets && $data['payment_type'] == 'w') {
                        $wallet =  $customer->wallets()->where('currency_id', $currencyId)->first();
                        if ($wallet) {
                            if ($wallet->amount < $order->amount) {
                                $paymentWalletBalance = $wallet->amount;
                                $remainingPayAmount = $order->amount - $paymentWalletBalance;
                            } else if ($wallet->amount == 0)
                                $remainingPayAmount = $order->amount;

                            else
                                $paymentWalletBalance = $order->amount;
                        }
                    }
                    if ($paymentWalletBalance)
                        $wallet->update([ //update balance in user wallet
                            'amount' => $wallet->amount - $paymentWalletBalance
                        ]);

                    //add transaction record
                    $transaction = Transaction::create([
                        'transaction_ID' => getRandomIdGenerate('TR'),
                        'member_id' => auth()->user()->id,
                        'transaction_type' => TRANSFER_OUT,
                        'order_id' => $order->order_id,
                        'amount' => $order->amount,
                        'wallet_amount' => $paymentWalletBalance,
                        'third_party_amount' => $remainingPayAmount,
                        'currency_id' => $currencyId,
                        'status' => $remainingPayAmount ? "Review" : "Debit",
                        'message' => "Payment transaction",
                    ]);
                    $dataPayment = array();
                    if (!$check) {
                        $dataPayment['payment_id']  = rand();
                        $dataPayment['customer_id'] = Auth()->user()->id;
                        $dataPayment['order_id']    = $order->order_id;
                        $dataPayment['order_product_ids']    = $order->id;
                        $dataPayment['amount']      = $order->amount;
                        $dataPayment['provider']    = 'test';
                        $dataPayment['status']      = 'complete';
                        $dataPayment['transaction_id']      = $transaction->id;
                        $dataPayment['message']      = @$data['message'];
                        $dataPayment['wallet_amount'] = $paymentWalletBalance;

                        $paymentType = $data['payment_type'];
                        if ($paymentWalletBalance && $remainingPayAmount) {
                            $paymentType = 'b';
                            $payWithThirdPart = true;
                        } else if ($paymentWalletBalance)
                            $paymentType = 'w';
                        else {
                            $paymentType = 'p';
                            $payWithThirdPart = true;
                        }
                        $dataPayment['payment_type']      = $paymentType;

                        if ($remainingPayAmount) {
                            if (sizeof($responseData) <= 0) {
                                $externalOrderID = getRandomIdGenerate('EO');
                                $responseData = [
                                    'external_order_id' => $externalOrderID,
                                    'amount' => $remainingPayAmount,
                                ];
                            } else {
                                $responseData['amount'] = $responseData['amount'] + $remainingPayAmount;
                            }
                        }

                        $dataPayment['external_order_ID']      = @$externalOrderID;

                        if ($payWithThirdPart) {
                            //pay with third party api
                            $dataPayment['request_data']      = ['amount' => $remainingPayAmount];
                        }
                        \Log::debug("Payment::create");
                        \Log::debug($dataPayment);
                        Payment::create($dataPayment);
                        $dataPayment['wallet_amount'] = 0;
                        $paymentWalletBalance = 0;

                        \Log::debug("deal->status");
                        if ($deal->status != 'active') {
                            $noProductDeal = true;
                            // return false;
                        } else {
                            $slotId = $deal->slots()->first();
                            SlotDeal::where('order_id', $order->order_id)->where('deal_id', $deal->id)->update(['status' => 'confirmed']);
                            if ($slotId->total_slots <= SlotDeal::where('status', 'confirmed')->where('deal_id', $deal->id)->count()) {
                                Deal::whereId($deal->id)->update(['status' => 'inactive']);
                            }
                            OrderProduct::whereId($order->id)->update($orderStatus);
                        }
                    }
                    \Log::debug("'status' => 'remaining'");
                    if (OrderProduct::whereId($order->id)->whereStatus('confirmed')->count() != OrderProduct::whereId($order->id)->count()) {
                        $orderStatus = ['status' => 'remaining'];
                    } else if (OrderProduct::whereId($order->id)->whereStatus('confirmed')->count() == 0) {
                        $orderStatus = ['status' => 'reserved'];
                    }
                    \Log::debug("'status' => 'confirmed'");
                    Order::whereId($order->order_id)->update($orderStatus);
                    $ct = SlotDeal::where('deal_id', $deal->id)->where(['status' => 'confirmed'])->count();
                    if ($ct > $slotId->total_slots) {
                        $ct = $slotId->total_slots;
                    }
                    \Log::debug("'status' => 'booked_slots'");

                    Slot::where(['id' => $deal->slot_id])->update(['booked_slots' => $ct]);
                }
            });

            if ($noProductDeal) {
                $result['message'] = 'invalid_product';
                $result['statusCode'] = 400;
                return getSuccessMessages($result);
            }

            $result['message'] = 'payment_successfully';
            $result['data'] = $responseData;
            $result['statusCode'] = 200;
            return getSuccessMessages($result);
        } catch (\Exception $e) {
            \Log::debug($e);
            return generalErrorResponse($e);
        }
    }

    // public function store(array $data)
    // {
    //     try {
    //         $noProductDeal = false;
    //         DB::transaction(function () use ($data, &$noProductDeal) {
    //             $orderProductsIds = $data['order_product_ids'];
    //             $orderProducts = OrderProduct::whereIn('id', $orderProductsIds)->where('customer_id', auth()->user()->id)->get();

    //             $orderIds = $orderProducts->pluck('order_id');

    //             if (!$orderIds) {
    //                 $result['message'] = 'The_selected_order_id_is_invalid';
    //                 $result['statusCode'] = 400;
    //                 return getSuccessMessages($result);
    //             }

    //             $orderIds = array_unique($orderIds->toArray());

    //             $orderProductsCount = Order::whereIn('id', $orderIds)->withCount('orderProduct')->get()->pluck('order_product_count', 'id')->toArray();

    //             $orderStatus = ['status' => 'confirmed'];

    //             foreach ($orderProducts as $order) {

    //                 $check = Payment::whereOrderId($order->order_id)->where('order_product_ids', $order->id)->first();

    //                 $orderStatus = ['status' => 'confirmed'];

    //                 if (!$check) {
    //                     $dataPayment['payment_id']  = rand();
    //                     $dataPayment['customer_id'] = Auth()->user()->id;
    //                     $dataPayment['order_id']    = $order->order_id;
    //                     $dataPayment['order_product_ids']    = $order->id;
    //                     $dataPayment['amount']      = $order->amount;
    //                     $dataPayment['provider']    = 'test';
    //                     $dataPayment['status']      = 'complete';
    //                     Payment::create($dataPayment);
    //                     $deal = Deal::whereProductId($order->product_id)->whereStatus('active')->orderBy('created_at', 'desc')->first();

    //                     if (!$deal) {
    //                         $noProductDeal = true;
    //                         return false;
    //                     }
    //                     $slotId = $deal->slots()->first();

    //                     SlotDeal::where('order_id', $order->order_id)->where('deal_id', $deal->id)->update(['status' => 'confirmed']);


    //                     if ($slotId->total_slots <= SlotDeal::where('status', 'confirmed')->where('deal_id', $deal->id)->count()) {
    //                         Deal::whereId($deal->id)->update(['status' => 'inactive']);
    //                     }

    //                     OrderProduct::whereId($order->id)->update($orderStatus);
    //                 }
    //                 if (array_key_exists($order->order_id, $orderProductsCount)) {

    //                     if (OrderProduct::whereId($order->id)->whereStatus('confirmed')->count() != $orderProductsCount[$order->order_id]) {
    //                         $orderStatus = ['status' => 'remaining'];
    //                     } else if (OrderProduct::whereId($order->id)->whereStatus('confirmed')->count() == 0) {
    //                         $orderStatus = ['status' => 'pending'];
    //                     }

    //                     Order::whereId($order->order_id)->update($orderStatus);
    //                 }
    //             }
    //         });

    //         if ($noProductDeal) {
    //             $result['message'] = 'The_selected_order_id_is_invalid';
    //             $result['statusCode'] = 400;
    //             return getSuccessMessages($result);
    //         }
    //         $result['message'] = 'Payment_created_Successfully';
    //         $result['statusCode'] = 200;
    //     } catch (\Exception $e) {
    //         \Log::debug($e);
    //         return generalErrorResponse($e);
    //     }
    // }



    public function paymentResponse($request): JsonResponse
    {
        try {

            DB::transaction(function () use ($request) {
                $status = $request->status;
                $externalOrderID = $request->external_order_ID;

                $payments = Payment::whereExternalOrderId($externalOrderID)->get();
                $paymentStatus = 'fail';
                if ($status == 'success')
                    $paymentStatus = 'complete';
                foreach ($payments as $key => $payment) {
                    DB::table('payments')->whereId($payment->id)->update(['status' => $paymentStatus]);
                    // Payment::find($payment->id)->update(['status' => $paymentStatus]);
                    //update transaction
                    $transactionStatus = 'Debit';
                    if ($paymentStatus == 'fail')
                        $transactionStatus = 'Reject';
                    Transaction::find($payment->transaction_id)->update(['status' => $transactionStatus]);
                }
            });

            $result['message'] = 'updated_successfully';
            $result['statusCode'] = 200;

            return getSuccessMessages($result);
        } catch (\Exception $e) {
            \Log::error($e);
            return generalErrorResponse($e);
        }
    }
}
