<?php

namespace App\Services;

use App\Models\Deal;
use App\Jobs\ClearDeals;
use App\Models\OrderProduct;
use App\Models\SlotDeal;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;

class DealService
{
    public function getSlotDeals($deal, $orderId)
    {
        try {

            $orderProducts = OrderProduct::where('product_id', $deal->product_id)->where('customer_id', auth()->user()->id)->get();
            $orderIds = array();
            if($orderId == null){
                array_push($orderIds,$orderProducts->pluck('order_id'));
            }else{
                array_push($orderIds,$orderId);
            }

            if (!$orderIds && empty($orderIds)) {
                return response()->json(['messages' => ['Data Not Found'],], 400);
            }

            $slotDeals = SlotDeal::whereIn('order_id', $orderIds)->where('deal_id',$deal->id)->get();


            return response()->json([
                'slot_deals' => $slotDeals,
            ], 200);
        } catch (\Exception $e) {
            \Log::debug($e);
            return generalErrorResponse($e);
        }
    }
}
