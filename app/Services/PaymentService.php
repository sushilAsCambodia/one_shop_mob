<?php

namespace App\Services;

use App\Http\Controllers\PaymentController;
use App\Models\Deal;
use App\Models\Order;
use App\Models\OrderProduct;
use App\Models\Payment;
use App\Models\SlotDeal;
use Illuminate\Http\JsonResponse;
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


    public function store(array $data)
    {
        try {
            $noProductDeal = false;
            DB::transaction(function () use ($data, &$noProductDeal) {
                $orderProductsIds = $data['order_product_ids'];
                $orderProducts = OrderProduct::whereIn('id', $orderProductsIds)->where('customer_id', auth()->user()->id)->get();

                $orderIds = $orderProducts->pluck('order_id');

                if (!$orderIds) {
                    // $noProductDeal = false;
                    $result['message'] = 'The_selected_order_id_is_invalid';
                    $result['statusCode'] = 400;
                    return getSuccessMessages($result);
                }

                $orderIds = array_unique($orderIds->toArray());

                $orderProductsCount = Order::whereIn('id', $orderIds)->withCount('orderProduct')->get()->pluck('order_product_count', 'id')->toArray();

                $orderStatus = ['status' => 'confirmed'];

                foreach ($orderProducts as $order) {

                    $check = Payment::whereOrderId($order->order_id)->where('order_product_ids', $order->id)->first();

                    $orderStatus = ['status' => 'confirmed'];

                    if (!$check) {
                        $dataPayment['payment_id']  = rand();
                        $dataPayment['customer_id'] = Auth()->user()->id;
                        $dataPayment['order_id']    = $order->order_id;
                        $dataPayment['order_product_ids']    = $order->id;
                        $dataPayment['amount']      = $order->amount;
                        $dataPayment['provider']    = 'test';
                        $dataPayment['status']      = 'complete';
                        Payment::create($dataPayment);
                        $deal = Deal::whereProductId($order->product_id)->whereStatus('active')->orderBy('created_at', 'desc')->first();

                        if (!$deal) {
                            $noProductDeal = true;
                            return false;
                        }
                        $slotId = $deal->slots()->first();

                        SlotDeal::where('order_id', $order->order_id)->where('deal_id', $deal->id)->update(['status' => 'confirmed']);


                        if ($slotId->total_slots <= SlotDeal::where('status', 'confirmed')->where('deal_id', $deal->id)->count()) {
                            Deal::whereId($deal->id)->update(['status' => 'inactive']);
                        }

                        OrderProduct::whereId($order->id)->update($orderStatus);
                    }
                    if (array_key_exists($order->order_id, $orderProductsCount)) {

                        if (OrderProduct::whereId($order->id)->whereStatus('confirmed')->count() != $orderProductsCount[$order->order_id]) {
                            $orderStatus = ['status' => 'remaining'];
                        } else if (OrderProduct::whereId($order->id)->whereStatus('confirmed')->count() == 0) {
                            $orderStatus = ['status' => 'pending'];
                        }

                        Order::whereId($order->order_id)->update($orderStatus);
                    }
                }
            });

            if ($noProductDeal) {
                $result['message'] = 'The_selected_order_id_is_invalid';
                $result['statusCode'] = 400;
                return getSuccessMessages($result);
            }

            $result['message'] = 'Payment_created_Successfully';
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
}
