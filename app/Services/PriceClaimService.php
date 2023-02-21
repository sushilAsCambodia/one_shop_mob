<?php

namespace App\Services;

use App\Models\PriceClaim;
use App\Models\Shipping;
use App\Models\SlotDeal;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class PriceClaimService
{

    public function __construct(private AddressService $addressService)
    {
    }

    public function paginate($request): JsonResponse
    {
        try {
            // $perPage = $request->rowsPerPage ?: 15;
            // $page = $request->page ?: 1;
            // $sortBy = $request->sortBy ?: 'created_at';
            // $sortOrder = $request->descending == 'true' ? 'desc' : 'asc';

            // $query = (new PriceClaim())->newQuery()->orderBy($sortBy, $sortOrder);

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

            // $query->where('status', '!=', 'completed')->where('status', '!=', 'shipping');

            // $query->where('customer_id', auth()->user()->id);

            // $query->when($request->order_booking_id, function ($query) use ($request) {
            //     $query->leftJoin('slot_deals', 'slot_deals.id', 'price_claims.booking_id')
            //         ->leftJoin('orders', 'orders.id', 'price_claims.order_id')
            //         ->where(function ($q) use ($request) {
            //             $q->where('orders.order_id', 'like', "%$request->order_booking_id%")
            //                 ->orWhere('slot_deals.booking_id', 'like', "%$request->order_booking_id%");
            //         });
            // });

            // $results = $query->select('price_claims.*')->with(['product' , 'orderProduct'])
            //                  ->paginate($perPage, ['*'], 'page', $page);

            // $result['message'] = 'fetch_to_ship_successfully';
            // $result['data'] = $results;
            // $result['statusCode'] = 200;
            // return getSuccessMessages($result);

            $perPage = $request->rowsPerPage ?: 15;
            $page = $request->page ?: 1;
            $sortBy = $request->sortBy ?: 'created_at';
            $sortOrder = $request->descending == 'true' ? 'desc' : 'asc';
            if (!isset($request->descending)) {
                $sortOrder = 'desc';
            }

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

            $query->where('status', '!=', 'completed')->where('status', '!=', 'shipping');

            $query->where('customer_id', auth()->user()->id);

            $query->when($request->order_booking_id, function ($query) use ($request) {
                $query->leftJoin('slot_deals', 'slot_deals.id', 'price_claims.booking_id')
                    ->leftJoin('orders', 'orders.id', 'price_claims.order_id')
                    ->where(function ($q) use ($request) {
                        $q->where('orders.order_id', 'like', "%$request->order_booking_id%")
                            ->orWhere('slot_deals.booking_id', 'like', "%$request->order_booking_id%");
                    });
            });

            $itemsPaginated = $query->select('price_claims.*')
                ->with(['product', 'customer', 'order', 'slot_deals', 'deal'])
                ->paginate($perPage, ['*'], 'page', $page);

            $itemsTransformed = $itemsPaginated
                ->getCollection()
                ->map(function ($item) {
                    $data = [
                        'id' => $item->id,
                        'booking_id' => $item->booking_id,
                        'order_id' => $item->order_id,
                        'deal_id' => $item->deal_id,
                        'product_id' => $item->product_id,
                        'customer_id' => $item->customer_id,
                        'address_id' => $item->address_id,
                        'status' => $item->status,
                        'created_at' => $item->created_at,
                        'updated_at' => $item->updated_at,
                        'deleted_at' => $item->deleted_at,
                        'product' => $item->product,
                        'customer' => $item->customer,
                        'order' => $item->order,
                        'slot_deals' => $item->slot_deals,
                        'deals' => $item->deal,
                        'slotDealsCount' => $this->getTotalBookedSlots($item),
                    ];
                    return $data;
                })->toArray();


            $itemsTransformedAndPaginated = new \Illuminate\Pagination\LengthAwarePaginator(
                $itemsTransformed,
                $itemsPaginated->total(),
                $itemsPaginated->perPage(),
                $itemsPaginated->currentPage(),
                [
                    'path' => \Request::url(),
                    'query' => [
                        'page' => $itemsPaginated->currentPage()
                    ]
                ]
            );
            $result['message'] = 'fetch_to_ship_successfully';
            $result['data'] = $itemsTransformedAndPaginated;
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

    public function prizeClaimByClaimId($request): JsonResponse
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

            $results['price_claims'] = $query->select('price_claims.*')
                ->where('id', '=', $request->claimId)
                ->with(['product', 'order'])->first();

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
            $addresses = $this->addressService->all($request);
            $results['addresses'] = $addresses->original['data'];

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

            $result['message'] = 'Price_Claim_updated_successfully';
            $result['statusCode'] = 200;
            return getSuccessMessages($result);
        } catch (\Exception $e) {
            \Log::debug($e);
            return generalErrorResponse($e);
        }
    }
}
