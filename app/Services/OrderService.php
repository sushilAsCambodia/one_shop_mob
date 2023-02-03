<?php

namespace App\Services;

use App\Http\Controllers\OrderController;
use App\Models\Address;
use App\Models\Deal;
use App\Models\Favorite;
use App\Models\Order;
use App\Models\OrderProduct;
use App\Models\Slot;
use App\Models\SlotDeal;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class OrderService
{
    public function __construct(private OrderProductService $orderProductService)
    {
    }

    public function paginate($request): JsonResponse
    {
        try {
            // die('use another API');
            $perPage = $request->rowsPerPage ?: 20;
            $page = $request->page ?: 1;
            $sortBy = $request->sortBy ?: 'created_at';
            $sortOrder = $request->descending == 'true' ? 'desc' : 'asc';

            $results = Order::where('customer_id', Auth()->user()->id)->with(['orderProducts', 'orderProducts.product'])
                ->orderBy($sortBy, $sortOrder)->paginate($perPage, ['*'], 'page', $page);

            // $query->when($request->name, function ($query) use ($request) {
            //     $query->where('name', 'like', "%$request->name%");
            // });

            // $results = $query->select('orders.*')->paginate($perPage, ['*'], 'page', $page);

            return response()->json($results, 200);
        } catch (\Exception $e) {
            \Log::debug($e);
            return generalErrorResponse($e);
        }
    }

    public function order($slug): JsonResponse
    {
        try {
            $resultData = Order::where('customer_id', Auth()->user()->id)
                ->where('orders.status', $slug)->latest('created_at')->get();
            // dd($resultData); die;
            if (!$resultData && empty($resultData)) {
                return response()->json(['messages' => ['Data Not Found'],], 400);
            }
            // if ($slug == 'reserved') {
            //     $slug = 'reserved';
            // }
            foreach ($resultData as $key1 => $result) {
                // dd($result);
                $orderProductData = OrderProduct::with('product.deal','product.slotDeals')->where('order_id', $result->id)->where('status', $slug)
                    ->with(['product'])->latest('created_at')->get();

                foreach ($orderProductData as $key => $orderProduct) {
                    $deal = Deal::whereProductId($orderProduct->product_id)->where('deals.status','active')->orderBy('created_at', 'desc')->first();
                    if (!empty($deal) && $deal) {
                        $slotsId = $deal->slots()->first()->id;
                        $orderProductData[$key]->slots_deals = SlotDeal::where('order_id', $result->id)
                            ->where('slot_id', $slotsId)->where('is_bot', 0)->get();
                    }
                }

                $resultData[$key1]->order_product = $orderProductData;
            }
            return response()->json([
                'order' => $resultData,
            ], 200);
        } catch (\Exception $e) {
            \Log::debug($e);
            return generalErrorResponse($e);
        }
    }

    public function store(array $data): JsonResponse
    {
        try {
            $data["total_amount"] = 0;
            $data["total_slots"] = 0;
            $data["total_products"] = count($data['product_details']);
            $data["total_quantity"] = count($data['product_details']);

            foreach ($data['product_details'] as $pData) {

                $deal = Deal::whereProductId($pData['product_id'])->whereStatus('active')->orderBy('created_at', 'desc')->first();
                $slots = $deal->slots()->first();

                if ($slots->total_slots < $slots->booked_slots + $pData['slots']) {
                    return response()->json([
                        'data'     => ['product_id' => $pData['product_id'], 'available_slots' => $slots->total_slots - $slots->booked_slots,],
                        'messages' => ['Insufficient Slots'],
                    ], 400);
                } else if ($slots->booked_slots == 0 && $slots->total_slots != $pData['slots']) {
                    // Bot Setting..
                    $custmerIdBot = 6;
                    $dataBot      = array();
                    $dataBot      = ["total_amount"   => 1, "total_slots"    => 1, "total_products" => 1, "total_quantity" => 1];
                    $dataBot['product_details'] = [["product_id" => $pData['product_id'], "amount"     => 1, "slots"      => 1,],];

                    $orderIdBot = Order::create(
                        array_merge($dataBot, array('customer_id' => $custmerIdBot, 'order_id' => getRandomIdGenerate('BD'), 'status' => 'confirmed'))
                    )->id;
                    $this->orderProductService->store($dataBot['product_details'], $custmerIdBot, $orderIdBot, 1);
                }

                $data["total_amount"]   += (int) $pData['amount'];
                $data["total_slots"]    += (int) $pData['slots'];
            }

            $custmerId = Auth()->user()->id;
            $orderId = Order::create(
                array_merge($data, array('customer_id' => $custmerId, 'order_id' => getRandomIdGenerate('BD'), 'status' => 'reserved'))
            )->id;

            $this->orderProductService->store($data['product_details'], $custmerId, $orderId, 0);

            // $result = Order::where('id', $orderId)->with(['orderProduct', 'orderProduct.product'])->first();

            return response()->json([
                'messages' => ['Order created successfully'],
                // 'data'     => $result,
            ], 201);
        } catch (\Exception $e) {
            \Log::debug($e);
            return generalErrorResponse($e);
        }
    }

    public function getDashboardCounts(): JsonResponse
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

            $result['whishlistCount'] = Favorite::where('customer_id', Auth()->user()->id)->count();

            return response()->json([
                'messages' => ['Dashboard Count Data'],
                'data'     => $result,
            ], 201);
        } catch (\Exception $e) {
            \Log::debug($e);
            return generalErrorResponse($e);
        }
    }

    public function orderGetById($orderId): JsonResponse
    {
        try {

            $result = Order::where('id', $orderId)->with(['orderProducts', 'orderProducts.product'])->first();
            foreach ($result->orderProducts as $key => $orderProduct) {
                $deal = Deal::whereProductId($orderProduct->product_id)->whereStatus('active')->orderBy('created_at', 'desc')->first();
                if (!empty($deal) && $deal) {
                    $slotsId = $deal->slots()->first()->id;
                    $result->orderProducts[$key]->slots_deals = SlotDeal::where('order_id', $result->id)
                        ->where('slot_id', $slotsId)->where('is_bot', 0)->get();
                }
            }
            return response()->json([
                'messages' => ['Order Data By Order ID'],
                'data'     => $result,
            ], 201);
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

                    $slotId->update(['booked_slots' => (int)$slotId->booked_slots - (int)$orderProductData->slots]);
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

    public function orderCompleted($request): JsonResponse
    {
        try {
            $resultData = Order::with(['products','delivered_products',])
                                ->where('customer_id', Auth()->user()->id)
                                ->whereIn('status',['loser','completed'])
                                ->get();
            // dd($resultData); die;
            if (!$resultData && empty($resultData)) {
                return response()->json(['messages' => ['Data Not Found'],], 400);
            }
            // if ($slug == 'reserved') {
            //     $slug = 'reserved';
            // }
            foreach ($resultData as $key1 => $result) {
                // dd($result);
                $orderProductData = OrderProduct::where('order_id', $result->id)
                                                // ->whereIn('status',['loser','completed'])
                                                ->with(['product', 'product.deal','product.slotDeals'])->get();

                foreach ($orderProductData as $key => $orderProduct) {
                    $deal = Deal::whereProductId($orderProduct->product_id)->whereStatus('active')->orderBy('created_at', 'desc')->first();
                    if (!empty($deal) && $deal) {
                        $slotsId = $deal->slots()->first()->id;
                        $orderProductData[$key]->slots_deals = SlotDeal::where('order_id', $result->id)
                            ->where('slot_id', $slotsId)->where('is_bot', 0)->get();
                    }
                }

                $resultData[$key1]->order_product = $orderProductData;
            }
            return response()->json([
                'order' => $resultData,
            ], 200);
        } catch (\Exception $e) {
            \Log::debug($e);
            return generalErrorResponse($e);
        }
    }

}
