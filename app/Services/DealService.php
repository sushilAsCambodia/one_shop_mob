<?php

namespace App\Services;

use App\Models\Deal;
use App\Jobs\ClearDeals;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;

class DealService
{

    public function paginate($request): JsonResponse
    {
        try {
            $perPage = $request->rowsPerPage ?: 15;
            $page = $request->page ?: 1;
            $sortBy = $request->sortBy ?: 'created_at';
            $sortOrder = $request->descending == 'true' ? 'desc' : 'asc';

            $query = (new Deal())->newQuery()->orderBy($sortBy, $sortOrder);

            $query->when($request->name, function ($query) use ($request) {
                $query->where('name', 'like', "%$request->name%");
            });
            $results = $query->select('deals.*')->with('customer')->paginate($perPage, ['*'], 'page', $page);

            return response()->json($results, 200);
        } catch (\Exception $e) {
            \Log::debug($e);
            return generalErrorResponse($e);
        }
    }

    public function store(array $data): JsonResponse
    {
        try {
            Deal::create($data);

            return response()->json([
                'messages' => ['Deal created successfully'],
            ], 201);
        } catch (\Exception $e) {
            \Log::debug($e);
            return generalErrorResponse($e);
        }
    }

    public function update($deals, array $data): JsonResponse
    {
        try {

            $deals->update($data);

            return response()->json([
                'messages' => ['Deal updated successfully'],
            ], 200);
        } catch (\Exception $e) {
            \Log::debug($e);
            return generalErrorResponse($e);
        }
    }

    public function delete($deals): JsonResponse
    {
        try {

            $deals->delete();

            return response()->json([
                'messages' => ['Deal deleted successfully'],
            ], 200);
        } catch (\Exception $e) {
            \Log::debug($e);
            return generalErrorResponse($e);
        }
    }
    public function setDeal(array $data) : JsonResponse
    {
        try {
            $deal = Deal::where(['id' => $data['deal_id']])->first();
            $currentDateTime = Carbon::now();
            $newDateTime = $currentDateTime->addHours($data['time_period'])->format('Y-m-d H:i:s');
            $deal->time_period = $data['time_period'];
            $deal->deal_end_at = $newDateTime;
            $deal->save();
            return response()->json([
                'messages' => ['Deal setted successfully'],
            ], 200);
        } catch (\Exception $e) {
            \Log::debug($e);
            return generalErrorResponse($e);
        }
    }
    public function clearDeals()
    {
        try {
            ClearDeals::dispatch();
            return response()->json([
                'messages' => ['Deals updated successfully'],
            ], 200);
        } catch (\Exception $e) {
            \Log::debug($e);
            return generalErrorResponse($e);
        }
    }
}
