<?php

namespace App\Http\Controllers;

use App\Http\Requests\CarrierFormRequest;
use App\Models\Carrier;
use App\Services\CarrierService;
use Illuminate\Http\Request;

class CarrierController extends Controller
{
    public function __construct(private CarrierService $carrierService)
    {
    }

    public function paginate(Request $request)
    {
        return $this->carrierService->paginate($request);
    }

    public function all()
    {
        return response()->json(Carrier::all(), 200);
    }

    public function store(CarrierFormRequest $request)
    {
        return $this->carrierService->store($request->all());
    }

    public function update(CarrierFormRequest $request, Carrier $carriers)
    {
        return $this->carrierService->update($carriers, $request->all());
    }

    public function delete(Carrier $carriers)
    {
        return $this->carrierService->delete($carriers);
    }

}
