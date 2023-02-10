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
            $slotDeals = SlotDeal::where('order_id', $orderId)->where('deal_id', $deal)
                        ->select('slot_deals.id','slot_deals.booking_id','slot_deals.status')->get();

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
