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

    public function prizeClaimByOrderId(Request $request)
    {
        // dd($request->orderId);
        $priceClaimId = DB::table('price_claims')->where('booking_id', '=', $request->orderId)->first();
        // dd($priceClaimId);
        $priceClaim = PriceClaim::find($priceClaimId->id);
        // print_r($priceClaim); die;
        return response()->json($priceClaim, 200);
        // $result['message'] = 'fetch_price_claim_successfully';
        // $result['data'] = $priceClaim;
        // $result['statusCode'] = 200;
        // return getSuccessMessages($result);
    }
}
