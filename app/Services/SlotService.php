<?php

namespace App\Services;

use App\Models\Slot;
use App\Http\Controllers\SlotController;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class SlotService
{

    public function paginate($request): JsonResponse
    {
        try {
            $perPage = $request->rowsPerPage ?: 15;
            $page = $request->page ?: 1;
            $sortBy = $request->sortBy ?: 'created_at';
            $sortOrder = $request->descending == 'true' ? 'desc' : 'asc';

            $query = (new Slot())->newQuery()->orderBy($sortBy, $sortOrder);

            $query->when($request->name, function ($query) use ($request) {
                $query->where('name', 'like', "%$request->name%");
            });
            $results = $query->select('slots.*')->paginate($perPage, ['*'], 'page', $page);

            return response()->json($results, 200);
        } catch (\Exception $e) {
            \Log::debug($e);
            return generalErrorResponse($e);
        }
    }

    public function store(array $data): JsonResponse
    {
        try {
            Slot::create($data);

            return response()->json([
                'messages' => ['Slot created successfully'],
            ], 201);
        } catch (\Exception $e) {
            \Log::debug($e);
            return generalErrorResponse($e);
        }
    }

    public function update($slots, array $data): JsonResponse
    {
        try {

            $activeDeal = $slots->deal->where('deals.status','active')->first();
            if($activeDeal)
                return response()->json([
                    'messages' => ['Slot cannot update while deal is active'],
                    'status' => false,
                ], 200);
            $slots->update($data);

            return response()->json([
                'messages' => ['Slot updated successfully'],
            ], 200);
        } catch (\Exception $e) {
            \Log::debug($e);
            return generalErrorResponse($e);
        }
    }

    public function delete($slots): JsonResponse
    {
        try {

            $slots->delete();

            return response()->json([
                'messages' => ['Slot deleted successfully'],
            ], 200);
        } catch (\Exception $e) {
            \Log::debug($e);
            return generalErrorResponse($e);
        }
    }
}
