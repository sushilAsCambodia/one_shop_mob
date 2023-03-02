<?php

namespace App\Services;

use App\Http\Controllers\OrderController;
use App\Models\Address;
use App\Models\Deal;
use App\Models\Favorite;
use App\Models\Order;
use App\Models\OrderProduct;
use App\Models\PriceClaim;
use App\Models\Slot;
use App\Models\SlotDeal;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use stdClass;

class OrderService
{
    public function __construct(private OrderProductService $orderProductService)
    {
    }

    public function paginate($request): JsonResponse
    {
        // try {
        // die('use another API');
        $perPage = $request->rowsPerPage ?: 20;
        $page = $request->page ?: 1;
        $sortBy = $request->sortBy ?: 'created_at';
        $sortOrder = $request->descending == 'true' ? 'desc' : 'asc';
        if (!isset($request->descending)) {
            $sortOrder = 'desc';
        }

        $results = Order::where('customer_id', Auth()->user()->id)->with(['orderProducts', 'orderProducts.deal', 'orderProducts.product'])
            ->orderBy($sortBy, $sortOrder)->paginate($perPage, ['*'], 'page', $page);

        // , 'orderProducts.products'

        // $query->when($request->name, function ($query) use ($request) {
        //     $query->where('name', 'like', "%$request->name%");
        // });

        // $results = $query->select('orders.*')->paginate($perPage, ['*'], 'page', $page);

        $result['message'] = 'Orders_fetch_successfully';
        $result['data'] = $results;
        $result['statusCode'] = 200;

        return getSuccessMessages($result);
        // } catch (\Exception $e) {
        //     \Log::debug($e);
        //     return generalErrorResponse($e);
        // }
    }

    public function order($slug): JsonResponse
    {
        try {
            $ops =  OrderProduct::where('status', $slug)
                ->select(
                    'order_product.*',
                    DB::raw("SUM(order_product.slots) as slotDealsCount"),
                    DB::raw("SUM(order_product.amount) as amounts"),
                    DB::raw("GROUP_CONCAT(order_product.id) as ids")
                )
                ->where('customer_id', Auth()->user()->id)
                ->with(['product'])
                ->orderBy('order_product.id', 'desc')
                ->groupBy('product_id')
                ->get();

            if (!$ops && empty($ops)) {
                $result['message'] = 'Data_Not_Found';
                $result['statusCode'] = 201;

                return getSuccessMessages($result, false);
            }
            // $orderIds = $ops->pluck('order_id');
            foreach ($ops as $key => $opData) {
                $deals = Deal::where('product_id', $opData->product_id)->get();
                $dealIds = $deals->pluck('id');
                // echo json_encode($dealIds);exit;
                if ($opData->order_id) {
                    $dealsData = SlotDeal::select('slot_deals.*')->with('deal.slots')
                        ->whereIn('deal_id', $dealIds)
                        ->where('order_id', $opData->order_id)
                        ->groupBy('deal_id')
                        ->first();

                    $orderId =  Order::whereId($opData->order_id)->first()->order_id;
                    // $slotDeals = SlotDeal::whereIn('order_id', $orderIds)->get();
                    $ops[$key]->deals = $dealsData->deal ? $dealsData->deal :  new stdClass();
                    $ops[$key]->orderId = $orderId;
                    $ops[$key]->ids = explode(',', $opData->ids);
                    // $ops[$key]->slotDealsCount = $this->getTotalBookedSlots($dealsData);
                }
            }

            $result['message'] = 'Orders_fetch_successfully';
            $result['data'] = $ops;
            $result['statusCode'] = 200;

            return getSuccessMessages($result);
        } catch (\Exception $e) {
            \Log::debug($e);
            return generalErrorResponse($e);
        }
    }
    public function getTotalBookedSlots($data)
    {
        $slotsCounts = SlotDeal::leftJoin('orders', 'orders.id', 'slot_deals.order_id')
            ->where('orders.customer_id', $data->customer_id)
            ->where('slot_deals.deal_id', $data->deal->id)
            ->groupBy('deal_id')
            ->count();
        return $slotsCounts;
    }
    // public function order($slug): JsonResponse
    // {
    //     try {
    //         $resultData = OrderProduct::with(['product'])
    //                     ->where('customer_id', Auth()->user()->id)
    //                     ->where('status', $slug)->latest('created_at')->get();

    //         if (!$resultData && empty($resultData)) {
    //             $result['message'] = 'Data_Not_Found';
    //             $result['statusCode'] = 201;

    //             return getSuccessMessages($result, false);
    //         }

    //         $result['message'] = 'Orders_fetch_successfully';
    //         $result['data'] = $resultData;
    //         $result['statusCode'] = 200;

    //         return getSuccessMessages($result);
    //     } catch (\Exception $e) {
    //         \Log::debug($e);
    //         return generalErrorResponse($e);
    //     }
    // }

    public function store(array $data): JsonResponse
    {
        try {
            $data["total_amount"] = 0;
            $data["total_slots"] = 0;
            $data["total_products"] = count($data['product_details']);
            $data["total_quantity"] = count($data['product_details']);

            foreach ($data['product_details'] as $pData) {

                $deal = Deal::whereId($pData['deal_id'])->whereStatus('active')->first();
                $slots = $deal->slots()->first();

                if ($slots->total_slots < $slots->booked_slots + $pData['slots'] || empty($slots)) {

                    $result['message'] = 'Insufficient_Slots';
                    $result['data'] = ['deal_id' => $pData['deal_id'], 'available_slots' => $slots->total_slots - $slots->booked_slots,];
                    $result['statusCode'] = 400;

                    return getSuccessMessages($result, false);
                }
                // else if ($slots->booked_slots == 0 && $slots->total_slots != $pData['slots']) {
                //     // Bot Setting..
                //     $custmerIdBot = 6;
                //     $dataBot      = array();
                //     $dataBot      = ["total_amount"   => 1, "total_slots"    => 1, "total_products" => 1, "total_quantity" => 1];
                //     $dataBot['product_details'] = [["deal_id" => $pData['deal_id'], "amount"     => 1, "slots"      => 1,],];

                //     $orderIdBot = Order::create(
                //         array_merge($dataBot, array('customer_id' => $custmerIdBot, 'order_id' => getRandomIdGenerate('BD'), 'status' => 'confirmed'))
                //     )->id;
                //     $this->orderProductService->store($dataBot['product_details'], $custmerIdBot, $orderIdBot, 1);
                // }

                $data["total_amount"] += (int) $pData['amount'];
                $data["total_slots"] += (int) $pData['slots'];
            }

            $custmerId = Auth()->user()->id;
            $orderId = Order::create(
                array_merge($data, array('customer_id' => $custmerId, 'order_id' => getRandomIdGenerate('BD'), 'status' => 'reserved'))
            )->id;

            $this->orderProductService->store($data['product_details'], $custmerId, $orderId, 0);

            // $result = Order::where('id', $orderId)->with(['orderProduct', 'orderProduct.product'])->first();

            $result['message'] = 'Order_created_successfully';
            $result['data'] = ['order_id' => $orderId];
            $result['statusCode'] = 200;

            return getSuccessMessages($result);
        } catch (\Exception $e) {
            \Log::debug($e);
            return generalErrorResponse($e);
        }
    }

    public function orderGetById($orderId): JsonResponse
    {
        try {

            $results = Order::where('id', $orderId)->with(['orderProducts', 'orderProducts.product'])->first();

            $result['message'] = 'Order_Data_By_Order_ID';
            $result['data'] = $results;
            $result['statusCode'] = 200;

            return getSuccessMessages($result);
        } catch (\Exception $e) {
            \Log::debug($e);
            return generalErrorResponse($e);
        }
    }

    public function cancelOrder(array $data): JsonResponse
    {
        try {

            foreach ($data as $dataVal) {
                $order = Order::where('id', $dataVal['order_id'])->where('customer_id', Auth()->user()->id)->firstOrFail();

                foreach ($dataVal['order_product'] as $orderProduct) {
                    $orderProductData = OrderProduct::where('id', $orderProduct['order_product_id'])->where('order_id', $dataVal['order_id'])->firstOrFail();
                    $orderProductData->update(['status' => 'canceled']);

                    $deal = Deal::whereProductId($orderProductData->product_id)->whereStatus('active')->orderBy('created_at', 'desc')->first();
                    $slotId = $deal->slots()->first();

                    $slotId->update(['booked_slots' => (int) $slotId->booked_slots - (int) $orderProductData->slots]);
                    SlotDeal::where('order_id', $order->id)->where('slot_id', $slotId->id)->update(['status' => 'canceled']);
                    SlotDeal::where('order_id', $order->id)->where('slot_id', $slotId->id)->delete();
                }

                $orderProductCount = OrderProduct::where('order_id', $dataVal['order_id'])->count();
                $orderProductCountCancel = OrderProduct::where('order_id', $dataVal['order_id'])->where('status', 'canceled')->count();
                if ($orderProductCountCancel == $orderProductCount) {
                    $orderStatus = ['status' => 'canceled'];
                } else {
                    $orderStatus = ['status' => 'reserved'];
                }

                $order->update($orderStatus);
            }

            return response()->json([
                'messages' => ['Order Canceled Successfully'],
            ], 201);
        } catch (\Exception $e) {
            \Log::debug($e);
            return generalErrorResponse($e);
        }
    }

    // public function orderCompleted($request): JsonResponse
    // {
    //     try {
    //         $ops =  OrderProduct::whereIn('status', ['loser', 'completed'])
    //             ->select('order_product.*', DB::raw("SUM(order_product.slots) as slots"))
    //             ->where('customer_id', Auth()->user()->id)
    //             ->with(['product'])
    //             ->groupBy('product_id')
    //             ->get();

    //         if (!$ops && empty($ops)) {
    //             return response()->json(['messages' => ['Data Not Found'],], 400);
    //         }


    //         foreach ($ops as $key =>  $opData) {
    //             $deals = Deal::where('product_id', $opData->product_id)->get();
    //             $dealIds = $deals->pluck('id');
    //             $dealsData = SlotDeal::select('slot_deals.*')->with('deal.slots')
    //                 ->whereIn('deal_id', $dealIds)
    //                 ->where('order_id', $opData->order_id)
    //                 ->groupBy('deal_id')
    //                 ->first();
    //             $winner = PriceClaim::where(['customer_id' => $opData['customer_id'], 'deal_id' => $dealsData->deal_id])->first();
    //             if (!empty($winner)) {
    //                 $ops[$key]->status = 'Completed';
    //             }
    //             $orderId =  Order::whereId($opData->order_id)->first()->order_id;

    //             $opData->orderId = $orderId;

    //         }

    //         $result['message'] = 'fetch_completed_successfully';
    //         $result['data'] = $ops;
    //         $result['statusCode'] = 200;

    //         return getSuccessMessages($result);
    //     } catch (\Exception $e) {
    //         \Log::debug($e);
    //         return generalErrorResponse($e);
    //     }
    // }

    public function orderCompleted($request): JsonResponse
    {
        try {
            $ops =  OrderProduct::whereIn('status', ['loser', 'completed'])
                ->select('order_product.*', DB::raw("SUM(order_product.slots) as slotDealsCount"), DB::raw("GROUP_CONCAT(order_product.status) as all_status"))
                ->where('customer_id', Auth()->user()->id)
                ->with(['product'])
                ->groupBy('deal_id')
                ->get();

            if (!$ops && empty($ops)) {
                $result['message'] = 'data_not_found';
                $result['statusCode'] = 400;

                return getSuccessMessages($result, false);
            }


            $orders = [];

            // $orderIds = $ops->pluck('order_id');

            foreach ($ops as $key =>  $opData) {
                // $deals = Deal::where('product_id', $opData->product_id)->get();
                $dealIds = $opData->deal_id;
                $dealsData = SlotDeal::select('slot_deals.*')->with('deal.slots')
                    ->where('deal_id', $dealIds)
                    ->where('order_id', $opData->order_id)
                    ->groupBy('deal_id')
                    ->first();
                $opData->checked_status = 'loser';
                $opData->winnerSlotId = null;
                $winner = PriceClaim::where(['customer_id' => $opData['customer_id'], 'deal_id' => $opData->deal_id])->where('status', '!=', 'completed')->first();
                $winnerNew = PriceClaim::where(['customer_id' => $opData['customer_id'], 'deal_id' => $opData->deal_id])->where('status', '=', 'completed')->first();
                if (!empty($winner)) {
                    $ops[$key]->status = 'completed';
                    // $opData->winnerSlotId = $winnerNew->booking_id;
                }
                if (in_array('completed', explode(',', $opData->all_status))) {
                    $opData->checked_status = 'completed';
                    $opData->winnerSlotId = $winnerNew->booking_id;
                }

                $orderId =  Order::whereId($opData->order_id)->first()->order_id;
                $opData->orderId = $orderId;
                $opData->deals = $dealsData->deal;

                if ($winner) {
                    if ($winner->deal_id != $opData->deal_id) {
                        array_push($orders, $opData);
                    }
                } else {
                    array_push($orders, $opData);
                }
            }

            $result['message'] = 'fetch_completed_successfully';
            $result['data'] = $orders;
            $result['statusCode'] = 200;

            return getSuccessMessages($result);
        } catch (\Exception $e) {
            \Log::debug($e);
            return generalErrorResponse($e);
        }
    }
}
