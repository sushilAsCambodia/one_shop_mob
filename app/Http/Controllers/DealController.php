<?php

namespace App\Http\Controllers;

use App\Http\Requests\DealFormRequest;
use App\Models\Deal;
use App\Services\DealService;
use Illuminate\Http\Request;


class DealController extends Controller
{
    public function __construct(private DealService $dealService)
    {
    }

    public function getSlotDeals(Request $request,Deal $deals, $orderId = null)
    {
        return $this->dealService->getSlotDeals($request->all(),$deals, $orderId);
    }
}
