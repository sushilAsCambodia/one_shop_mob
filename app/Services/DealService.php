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
            // $slotDeals = SlotDeal::where('order_id', $orderId)->where('deal_id', $deal)
            //             ->select('slot_deals.id','slot_deals.booking_id','slot_deals.status')->get();

            // $result['message'] = 'slotDeals_fetch_successfully';
            // $result['data'] = $slotDeals;
            // $result['statusCode'] = 200;
            // return getSuccessMessages($result);


            $orderProducts = OrderProduct::where('deal_id', $deal->id)->whereIn('status', ['confirmed', 'winner', 'loser'])
                ->select('slot_deals.id', 'slot_deals.booking_id', 'slot_deals.status')
                ->where('customer_id', auth()->user()->id)->get();
                
            $orderIds = array();
            if ($orderId == null) {
                $orderIds = collect($orderProducts->pluck('order_id'));
            } else {
                array_push($orderIds, $orderId);
            }

            if (!$orderIds && empty($orderIds)) {
                return response()->json(['messages' => ['Data Not Found'],], 400);
            }

            $slotDeals = SlotDeal::whereIn('order_id', $orderIds)->whereIn('status', ['confirmed', 'winner', 'loser'])->where('deal_id', $deal->id)->get();

            $result['message'] = 'slotDeals_fetch_successfully';
            $result['data'] = $slotDeals;
            $result['statusCode'] = 200;
            return getSuccessMessages($result);
        } catch (\Exception $e) {
            \Log::debug($e);
            return generalErrorResponse($e);
        }
    }
}
