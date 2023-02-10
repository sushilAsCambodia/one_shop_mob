<?php

namespace App\Services;

use App\Models\Address;
use App\Models\PriceClaim;
use App\Models\Shipping;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
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

    public function prizeClaimByBookingId($request): JsonResponse
    {
        // try {
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

            $results['price_claims'] = $query->select('price_claims.*')->where('booking_id', '=', $request->bookingId)
                            ->with(['product' , 'order'])->first();

            // foreach ($results as $key1 => $result) {
            //     // dd($result);
            //     $orderProductData = OrderProduct::with('product.deal', 'product.slotDeals')->where('order_id', $result->id)->where('status', $slug)
            //         ->with(['product'])->latest('created_at')->get();

            //     foreach ($orderProductData as $key => $orderProduct) {
            //         $deal = Deal::whereProductId($orderProduct->product_id)->where('deals.status', 'active')->orderBy('created_at', 'desc')->first();
            //         if (!empty($deal) && $deal) {
            //             $slotsId = $deal->slots()->first()->id;
            //             $orderProductData[$key]->slots_deals = SlotDeal::where('order_id', $result->id)
            //                 ->where('slot_id', $slotsId)->where('is_bot', 0)->count();
            //         }
            //     }

            //     $results[$key1]->order_product = $orderProductData;
            // }
            
            $query2 =  (new Address())->newQuery();

            $modelData = Auth::user();

            $query2->when($modelData, function ($query2) use ($modelData) {
                $query2->whereAddressableType(Customer::class)
                    ->whereAddressableId($modelData->id);
            });
            $results['addresses'] = $query2->select(
                'addresses.id',
                'addresses.street_address_1',
                'addresses.street_address_2',
                'addresses.pincode',
                'addresses.country_id',
                'addresses.state_id',
                'addresses.city_id'
            )->get();


            $result['message'] = 'fetch_price_claim_successfully';
            $result['data'] = $results;
            $result['statusCode'] = 200;
            return getSuccessMessages($result);

        // } catch (\Exception $e) {
        //     \Log::debug($e);
        //     return generalErrorResponse($e);
        // }
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
