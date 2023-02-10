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

    public function store(array $data)
    {
        try {
            $noProductDeal = false;
            DB::transaction(function () use ($data, &$noProductDeal) {

                foreach ($data as $dataVal) {
                    $order = Order::where('id', $dataVal['order_id'])
                        ->where('customer_id', Auth()->user()->id)->firstOrFail();

                    $dataPayment['payment_id'] = rand();
                    $dataPayment['customer_id'] = Auth()->user()->id;
                    $dataPayment['order_id'] = $order['id'];
                    $dataPayment['amount'] = $order['total_amount'];
                    $dataPayment['provider'] = 'test';
                    $dataPayment['status'] = 'complete';

                    Payment::create($dataPayment);

                    foreach ($dataVal['order_product'] as $orderProduct) {
                        $orderProductData = OrderProduct::where('id', $orderProduct['order_product_id'])
                            ->where('order_id', $dataVal['order_id'])->firstOrFail();
                        $orderProductData->update(['status' => 'confirmed']);

                        $deal = Deal::whereProductId($orderProductData->product_id)
                            ->whereStatus('active')->orderBy('created_at', 'desc')->first();
                        //check if deal available
                        if (!$deal) {
                            $noProductDeal = true;
                            return false;
                        }

                        $slotId = $deal->slots()->first();

                        SlotDeal::where('order_id', $order->id)->where('slot_id', $slotId->id)
                            ->update(['status' => 'confirmed']);
                    }

                    $orderProductCount = OrderProduct::where('order_id', $dataVal['order_id'])->count();
                    $orderProductCountConfirm = OrderProduct::where('order_id', $dataVal['order_id'])
                        ->where('status', 'confirmed')->count();
                    if ($orderProductCountConfirm == $orderProductCount) {
                        $orderStatus = ['status' => 'confirmed'];
                    } else {
                        $orderStatus = ['status' => 'reserved'];
                    }

                    $order->update($orderStatus);
                }

            });

            if ($noProductDeal) {
                $result['message'] = 'The_selected_order_product_id_is_invalid';
                $result['data'] = [
                    "order_product.*.order_product_id" =>
                    "product_deal_not_exist"
                ];
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
}