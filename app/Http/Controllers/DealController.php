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

    public function paginate(Request $request)
    {
        return $this->dealService->paginate($request);
    }

    public function all()
    {
        return response()->json(Deal::all(), 200);
    }

    public function store(DealFormRequest $request)
    {
        return $this->dealService->store($request->all());
    }

    public function update(DealFormRequest $request, Deal $deals)
    {

        return $this->dealService->update($deals, $request->all());
    }

    public function delete(Deal $deals)
    {

        return $this->dealService->delete($deals);
    }
    public function setDeal(DealFormRequest $request)
    {
        return $this->dealService->setDeal($request->all());
    }
    public function clearDeals()
    {
        return $this->dealService->clearDeals();
    }
}
