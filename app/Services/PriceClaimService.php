<?php

namespace App\Services;

use App\Models\PriceClaim;
use App\Models\Shipping;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class PriceClaimService
{

    public function paginate($request): JsonResponse
    {
        try {
            $perPage = $request->rowsPerPage ?: 15;
            $page = $request->page ?: 1;
            $sortBy = $request->sortBy ?: 'created_at';
            $sortOrder = $request->descending == 'true' ? 'desc' : 'asc';

            $query = (new PriceClaim())->newQuery()->orderBy($sortBy, $sortOrder);

            $query->when($request->dates, function ($query) use ($request) {
                if ($request->dates[0] == $request->dates[1]) {
                    $query->whereDate('price_claims.created_at', Carbon::parse($request->dates[0])->format('Y-m-d'));
                } else {
                    $query->whereBetween('price_claims.created_at', [
                        Carbon::parse($request->dates[0])->startOfDay(),
                        Carbon::parse($request->dates[1])->endOfDay(),
                    ]);
                }
            });

            $query->where('status', '!=', 'completed');

            $query->where('customer_id', auth()->user()->id);

            $query->when($request->order_booking_id, function ($query) use ($request) {
                $query->leftJoin('slot_deals', 'slot_deals.id', 'price_claims.booking_id')
                    ->leftJoin('orders', 'orders.id', 'price_claims.order_id')
                    ->where(function ($q) use ($request) {
                        $q->where('orders.order_id', 'like', "%$request->order_booking_id%")
                            ->orWhere('slot_deals.booking_id', 'like', "%$request->order_booking_id%");
                    });
            });

            $results = $query->select('price_claims.*')->with(['product' , 'product.slotDeals', 'customer', 'slot_deals', 'order'])->paginate($perPage, ['*'], 'page', $page);

            return response()->json($results, 200);
        } catch (\Exception $e) {
            \Log::debug($e);
            return generalErrorResponse($e);
        }
    }

    public function prizeClaimByOrderId($request): JsonResponse
    {
        try {
            $query = (new PriceClaim())->newQuery();

            // $query->when($request->dates, function ($query) use ($request) {
            //     if ($request->dates[0] == $request->dates[1]) {
            //         $query->whereDate('price_claims.created_at', Carbon::parse($request->dates[0])->format('Y-m-d'));
            //     } else {
            //         $query->whereBetween('price_claims.created_at', [
            //             Carbon::parse($request->dates[0])->startOfDay(),
            //             Carbon::parse($request->dates[1])->endOfDay(),
            //         ]);
            //     }
            // });

            $query->where('status', '!=', 'completed');

            // $query->where('customer_id', auth()->user()->id);

            // $query->when($request->order_booking_id, function ($query) use ($request) {
            //     $query->leftJoin('slot_deals', 'slot_deals.id', 'price_claims.booking_id')
            //         ->leftJoin('orders', 'orders.id', 'price_claims.order_id')
            //         ->where(function ($q) use ($request) {
            //             $q->where('orders.order_id', 'like', "%$request->order_booking_id%")
            //                 ->orWhere('slot_deals.booking_id', 'like', "%$request->order_booking_id%");
            //         });
            // });

            $results = $query->select('price_claims.*')->where('booking_id', '=', $request->orderId)
                            ->with(['product' , 'product.slotDeals', 'order'])->first();

            
            $result['message'] = 'fetch_price_claim_successfully';
            $result['data'] = $results;
            $result['statusCode'] = 200;
            return getSuccessMessages($result);

        } catch (\Exception $e) {
            \Log::debug($e);
            return generalErrorResponse($e);
        }
    }
    
    public function update($priceClaim, array $data): JsonResponse
    {
        try {
            DB::transaction(function () use ($priceClaim, $data) {
                $priceClaim->update([
                    'address_id' => $data['address_id'],
                    'status' => $data['status']
                ]);
            });

            return response()->json([
                'messages' => ['Price Claim updated successfully'],
            ], 200);
        } catch (\Exception $e) {
            \Log::debug($e);
            return generalErrorResponse($e);
        }
    }

}
