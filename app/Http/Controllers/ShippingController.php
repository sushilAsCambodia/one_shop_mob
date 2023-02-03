<?php

namespace App\Http\Controllers;

use App\Http\Requests\ShippingCarrierRequest;
use App\Models\Shipping;
use App\Services\ShippingService;
use Illuminate\Http\Request;
use App\Http\Requests\ShippingFormRequest;
use App\Http\Requests\ShippingRequest;

class ShippingController extends Controller
{
    public function __construct(private ShippingService $shippingService)
    {
    }

    public function paginate(Request $request)
    {
        return $this->shippingService->paginate($request);
    }

    public function all()
    {
        return response()->json(Shipping::all(), 200);
    }

    public function store(ShippingFormRequest $request)
    {
        return $this->shippingService->store($request->all());
    }

    public function update(Request $request, Shipping $shippings)
    {

        return $this->shippingService->update($shippings, $request->all());
    }

    public function delete(Shipping $shippings)
    {
        return $this->shippingService->delete($shippings);
    }

    public function getShipingbyBookingID(Request $request)
    {
        return $this->shippingService->getShipingbyBookingID($request->id);
    }

    public function updateShippingStatus(Shipping $shippings,ShippingRequest $shipping)
    {
        return $this->shippingService->updateShipping($shippings,$shipping);
    }
    public function updateShippingCarrier(Shipping $shippings,ShippingCarrierRequest $shipping)
    {
        return $this->shippingService->updateShippingCarrier($shippings,$shipping);
    }

    public function getShippingStatus(Request $request, $trackingId)
    {
        return $this->shippingService->getShippingStatus($request, $trackingId);
    }
}
  