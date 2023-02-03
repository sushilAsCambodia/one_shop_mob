<?php

namespace App\Services;

use App\Models\Deal;
use App\Models\Product;
use App\Models\Slot;
use App\Models\SlotDeal;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use stdClass;

class SlotDealService
{

    public function storeSlotDeal($orderId, $productId, $slots, $isBot): JsonResponse
    {
        try {
            $deal = Deal::whereProductId($productId)->whereStatus('active')->orderBy('created_at', 'desc')->first();
            $dealId = $deal->id;
            $slotId = $deal->slots()->first();
            $status = $isBot == 1 ? 'confirmed' : 'reserved';
            for ($i = 1; $i <= $slots; $i++) {
                SlotDeal::create([
                    'product_id' => $productId,
                    'order_id' => $orderId,
                    'booking_id' => getRandomIdGenerate('SB'),
                    'deal_id' => $dealId,
                    'slot_id' => $slotId->id,
                    'is_bot' => $isBot,
                    'status' => $status
                ]);
            }

            $slotId->update(['booked_slots' => (int)$slotId->booked_slots + (int)$slots]);

            return response()->json([
                'messages' => ['SlotDeals created successfully'],
            ], 201);
        } catch (\Exception $e) {
            \Log::debug($e);
            return generalErrorResponse($e);
        }
    }
}
