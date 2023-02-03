<?php


namespace App\Http\Controllers;

use App\Http\Requests\SlotDealFormRequest;
use App\Models\SlotDeal;
use App\Services\SlotDealService;
use Illuminate\Http\Request;

class SlotDealController extends Controller
{
    public function __construct(private SlotDealService $slotDealService)
    {
    }

    public function paginate(Request $request)
    {
        return $this->slotDealService->paginate($request);
    }

    public function all()
    {
        return response()->json(slotDeal::all(), 200);
    }

    public function store(SlotDealFormRequest $request)
    {
        return $this->slotDealService->store($request->all());
    }

    public function update(SlotDealFormRequest $request, SlotDeal $slotDeals)
    {

        return $this->slotDealService->update($slotDeals, $request->all());
    }

    public function delete(SlotDeal $slotDeals)
    {

        return $this->slotDealService->delete($slotDeals);
    }

}

