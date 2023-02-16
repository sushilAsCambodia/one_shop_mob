<?php

namespace App\Services;

use App\Models\Shipping;
use App\Http\Controllers\ShippingController;
use App\Models\Customer;
use App\Models\OrderProduct;
use App\Models\PriceClaim;
use App\Models\ShippingLog;
use App\Models\SlotDeal;
use App\Models\User;
use Illuminate\Support\Facades\Notification;
use App\Notifications\CustomerNotification;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class ShippingService
{

    public function paginate($request): JsonResponse
    {
        try {
            // $perPage = $request->rowsPerPage ?: 15;
            // $page = $request->page ?: 1;
            // $sortBy = $request->sortBy ?: 'created_at';
            // $sortOrder = $request->descending == 'true' ? 'desc' : 'asc';
            // $query = (new Shipping())->newQuery()->where('customer_id', Auth::id())->orderBy($sortBy, $sortOrder);

            // $query->when($request->dates, function ($query) use ($request) {
            //     if ($request->dates[0] == $request->dates[1]) {
            //         $query->whereDate('created_at', Carbon::parse($request->dates[0])->format('Y-m-d'));
            //     } else {
            //         $query->whereBetween('created_at', [
            //             Carbon::parse($request->dates[0])->startOfDay(),
            //             Carbon::parse($request->dates[1])->endOfDay(),
            //         ]);
            //     }
            // });

            // $query->where('status', '!=', 'Delivered');

            // $query->when($request->search, function ($query) use ($request) {
            //     $query->where('shipping_id', 'like', "%$request->search%");
            //     $query->orWhere('booking_id', 'like', "%$request->search%");
            //     $query->orWhere('tracking_id', 'like', "%$request->search%");
            // });

            // $results = $query->with(['shippingLogs', 'shippingLogs.user', 'products','slotDeal:id,deal_id,booking_id','slotDeal.deal:id,deal_price'])
            //                  ->paginate($perPage, ['*'], 'page', $page);

            // $result['message'] = 'fetch_to_receive_successfully';
            // $result['data'] = $results;
            // $result['statusCode'] = 200;
            // return getSuccessMessages($result);


            $perPage = $request->rowsPerPage ?: 15;
            $page = $request->page ?: 1;
            $sortBy = $request->sortBy ?: 'created_at';
            $sortOrder = $request->descending == 'true' ? 'desc' : 'asc';
            if(!isset($request->descending)){
                $sortOrder = 'desc';
            }
            $query = (new Shipping())->newQuery()->where('customer_id', Auth::id())->orderBy($sortBy, $sortOrder);

            $query->when($request->dates, function ($query) use ($request) {
                if ($request->dates[0] == $request->dates[1]) {
                    $query->whereDate('created_at', Carbon::parse($request->dates[0])->format('Y-m-d'));
                } else {
                    $query->whereBetween('created_at', [
                        Carbon::parse($request->dates[0])->startOfDay(),
                        Carbon::parse($request->dates[1])->endOfDay(),
                    ]);
                }
            });

            $query->where('status', '!=', 'Delivered');

            $query->when($request->search, function ($query) use ($request) {
                $query->where('shipping_id', 'like', "%$request->search%");
                $query->orWhere('booking_id', 'like', "%$request->search%");
                $query->orWhere('tracking_id', 'like', "%$request->search%");
            });
            $results = $query->with(['shippingLogs', 'shippingLogs.user', 'products', 'slotDeal:id,deal_id,booking_id', 'slotDeal.deal:id,deal_price'])
                ->paginate($perPage, ['*'], 'page', $page);

            // return response()->json($results, 200);
            $result['message'] = 'fetch_to_receive_successfully';
            $result['data'] = $results;
            $result['statusCode'] = 200;
            return getSuccessMessages($result);
        } catch (\Exception $e) {
            // \Log::debug($e);
            return generalErrorResponse($e);
        }
    }

    public function store(array $data): JsonResponse
    {
        try {

            /* Bot Validation  */
            $where = [
                'order_id' => $data['order_id'],
                'booking_id' => $data['booking_id']
            ];

            $slotDeal = SlotDeal::where($where)->first();

            if ($slotDeal->status != 'confirmed') {
                return response()->json([
                    'messages' => ['Slot Deal not confirmed yet'],
                ], 200);
            } elseif ($slotDeal->is_bot == '1') {
                return response()->json([
                    'messages' => ['Winner is a Bot cannot make shipment'],
                ], 200);
            }

            $where += [
                'customer_id' => $data['customer_id'],
                'status' => 'claimed'
            ];

            $priceClaim = PriceClaim::where($where)->first();
            if (empty($priceClaim)) {
                return response()->json([
                    'messages' => ['Price not claimed yet'],
                ], 200);
            } else {
                $data['shipping_id'] = getRandomIdGenerate('SHIP');
                $data['address_id'] = $priceClaim->address_id;
                if (empty($priceClaim->address_id)) {
                    return response()->json([
                        'messages' => ['Address not added to ship this customer'],
                    ], 200);
                }
                $shippingId = Shipping::create($data)->id;
                $shipLogData = [
                    'shipping_id' => $shippingId,
                    'user_id' => auth()->user()->id,
                    'created_at' => Carbon::now()->format('Y-m-d H:i:s'),
                ];
                ShippingLog::create($shipLogData);
                OrderProduct::where(['order_id' => $data['order_id']])->update(['status' => 'shipping']);

                $notificationData = [
                    'data' =>
                    [
                        'shipping_id' => $data['shipping_id'],
                        'address_id' =>  $data['address_id'],
                        'customer_id' =>  $data['customer_id']
                    ],
                    "message" => 'Shipping started'
                ];
                $customer = Customer::where('id', $data['customer_id'])->first();
                Notification::send($customer, new CustomerNotification($notificationData));


                return response()->json([
                    'messages' => ['Shipping created successfully'],
                ], 201);
            }
        } catch (\Exception $e) {
            // \Log::debug($e);
            return generalErrorResponse($e);
        }
    }

    public function update($shippings, array $data): JsonResponse
    {
        try {

            $shippings->update($data);
            $log = [
                'shipping_id' =>  $shippings->id,
                'user_id' =>  auth()->user()->id,
                'status' => $data['status'],
            ];
            ShippingLog::create($log);



            $notificationData = [
                'data' =>
                [
                    'shipping_id' => $shippings->shipping_id,
                    'address_id' =>  $shippings->address_id,
                    'customer_id' =>  $shippings->customer_id
                ],
                "message" => 'Shipping Status ' . $data['status'] . 'updated '
            ];

            $customer = User::where('id', 1)->first();
            Notification::send($customer, new CustomerNotification($notificationData));



            return response()->json([
                'messages' => ['Shipping updated successfully'],
            ], 200);
        } catch (\Exception $e) {
            // \Log::debug($e);
            return generalErrorResponse($e);
        }
    }
    public function delete($shippings): JsonResponse
    {
        try {
            $shippings->delete();

            return response()->json([
                'messages' => ['Shipping deleted successfully'],
            ], 200);
        } catch (\Exception $e) {
            // \Log::debug($e);
            return generalErrorResponse($e);
        }
    }

    public function getShipingbyBookingID($id): JsonResponse
    {
        try {
            $result = Shipping::where('booking_id', $id)->first();
            if ($result) {
                return response()->json([$result], 200);
            }
            return response()->json([], 200);
        } catch (\Exception $e) {
            // \Log::debug($e);
            return generalErrorResponse($e);
        }
    }
    public function updateShipping($shippings, $data): JsonResponse
    {
        try {
            $shippings->status = $data['status'];
            $shippings->save();

            $log = [
                'shipping_id' =>  $shippings->id,
                'user_id' =>  auth()->user()->id,
                'status' => $data['status'],
            ];
            ShippingLog::create($log);

            $notificationData = [
                'data' =>
                [
                    'shipping_id' => $shippings->shipping_id,
                    'address_id' =>  $shippings->address_id,
                    'customer_id' =>  $shippings->customer_id
                ],
                "message" => 'Shipping ' . $data['status']
            ];

            $customer = Customer::where('id',  $shippings->customer_id)->first();
            Notification::send($customer, new CustomerNotification($notificationData));



            return response()->json([
                'messages' => ['Shipping status updated successfully'],
            ], 200);
        } catch (\Exception $e) {
            // \Log::debug($e);
            return generalErrorResponse($e);
        }
    }
    public function updateShippingCarrier($shippings, $data): JsonResponse
    {
        try {
            $shippings->carrier_id = $data['carrier_id'];
            if (!empty($data['tracking_id'])) {
                $shippings->tracking_id = $data['tracking_id'];
            }
            $shippings->save();

            $notificationData = [
                'data' =>
                [
                    'shipping_id' => $shippings->shipping_id,
                    'address_id' =>  $shippings->address_id,
                    'customer_id' =>  $shippings->customer_id
                ],
                "message" => 'Shipping tracking id updated successfully'
            ];

            $customer = Customer::where('id',  $shippings->customer_id)->first();
            Notification::send($customer, new CustomerNotification($notificationData));


            return response()->json([
                'messages' => ['Shipping Carrier updated successfully'],
            ], 200);
        } catch (\Exception $e) {
            // \Log::debug($e);
            return generalErrorResponse($e);
        }
    }

    public function getShippingStatus($request, $trackingId): JsonResponse
    {
        try {
            $perPage = $request->rowsPerPage ?: 15;
            $page = $request->page ?: 1;
            $sortBy = $request->sortBy ?: 'created_at';
            $sortOrder = $request->descending == 'true' ? 'desc' : 'asc';
            if(!isset($request->descending)){
                $sortOrder = 'desc';
            }

            $query = (new Shipping())->newQuery()->orderBy($sortBy, $sortOrder);

            $query->where('tracking_id', $trackingId);
            $results = $query->select('shippings.*')->paginate($perPage, ['*'], 'page', $page);

            return response()->json($results, 200);
        } catch (\Exception $e) {
            \Log::debug($e);
            return generalErrorResponse($e);
        }
    }
}
