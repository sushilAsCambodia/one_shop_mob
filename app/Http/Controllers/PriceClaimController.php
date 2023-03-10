<?php

namespace App\Http\Controllers;

use App\Http\Requests\PriceClaimFormRequest;
use App\Models\PriceClaim;
use App\Services\PriceClaimService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PriceClaimController extends Controller
{
    public function __construct(private PriceClaimService $priceClaimService)
    {
    }

    public function paginate(Request $request)
    {
        return $this->priceClaimService->paginate($request);
    }

    public function all()
    {
        return response()->json(PriceClaim::all(), 200);
    }

    public function update(PriceClaimFormRequest $request, PriceClaim $priceClaim)
    {
        return $this->priceClaimService->update($priceClaim, $request->all());
    }

    public function get(PriceClaim $priceClaim)
    {
        return response()->json($priceClaim, 200);
    }

    public function prizeClaimByClaimId(Request $request)
    {
        return $this->priceClaimService->prizeClaimByClaimId($request);
    }
}
