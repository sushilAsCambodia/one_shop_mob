<?php


namespace App\Http\Controllers;

use App\Http\Requests\SlotFormRequest;
use App\Models\slot;
use App\Services\SlotService;
use Illuminate\Http\Request;

class SlotController extends Controller
{
    public function __construct(private SlotService $slotService)
    {
    }

    public function paginate(Request $request)
    {
        return $this->slotService->paginate($request);
    }

    public function all()
    {
        return response()->json(Slot::all(), 200);
    }

    public function store(SlotFormRequest $request)
    {
        return $this->slotService->store($request->all());
    }

    public function update(SlotFormRequest $request, Slot $slots)
    {

        return $this->slotService->update($slots, $request->all());
    }

    public function delete(Slot $slots)
    {

        return $this->slotService->delete($slots);
    }

}
