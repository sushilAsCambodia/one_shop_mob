<?php

namespace App\Services;

use App\Models\Deal;
use App\Models\Order;
use App\Models\OrderProduct;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class OrderProductService
{
    public function store($data, $custmerId, $orderId, $isBot): JsonResponse
    {
        try {
            $slotDealService = new SlotDealService();
            foreach ($data as $dataVal) {
                if($isBot == 1){
                    $status = 'confirmed';
                }else{
                    $status = 'reserved';
                }
                
                $deal = Deal::whereId($dataVal['deal_id'])->whereStatus('active')->first();

                OrderProduct::create(array_merge($dataVal, array('product_id' => $deal->product_id, 'customer_id' => $custmerId, 'order_id' => $orderId, 'status' => $status)));
                $slotDealService->storeSlotDeal($orderId, $dataVal['deal_id'], $dataVal['slots'], $isBot);
            }

            return response()->json([
                'messages' => ['Order Product created successfully'],
            ], 201);
        } catch (\Exception $e) {
            \Log::debug($e);
            return generalErrorResponse($e);
        }
    }
}
