<?php

namespace App\Services;

use App\Models\Promotion;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class PromotionService
{
    public function paginate($request): JsonResponse
    {
        try {
            $perPage = $request->rowsPerPage ?: 15;
            $page = $request->page ?: 1;
            $sortBy = $request->sortBy ?: 'created_at';
            $sortOrder = $request->descending == 'true' ? 'desc' : 'asc';

            $query = (new Promotion())->newQuery()->orderBy($sortBy, $sortOrder);

            $query->when($request->status, function ($query) use ($request) {
                $query->where('status', $request->status);
            });
            $query->when($request->search, function ($query) use ($request) {
                $query->where('name', 'like', "%$request->search%")
                        ->orWhere('slug', 'like', "%$request->search%");
            });

            $results = $query->select('promotions.*')->paginate($perPage, ['*'], 'page', $page);

            return response()->json($results, 200);
        } catch (\Exception$e) {
            return generalErrorResponse($e);
        }
    }

    public function store(array $data): JsonResponse
    {
        try {
            DB::transaction(function () use ($data) {
                $promotion = Promotion::create($data);
            });

            return response()->json([
                'messages' => ['Promotion created successfully'],
            ], 201);
        } catch (\Exception$e) {
            return generalErrorResponse($e);
        }
    }

    public function update($promotion, array $data): JsonResponse
    {
        try {
            DB::transaction(function () use ($promotion, $data) {
                $promotion->update($data);
            });

            return response()->json([
                'messages' => ['Promotion updated successfully'],
            ], 200);
        } catch (\Exception$e) {
            return generalErrorResponse($e);
        }
    }

    public function delete($promotion): JsonResponse
    {
        try {
            $promotion->delete();

            return response()->json([
                'messages' => ['Promotion deleted successfully'],
            ], 200);
        } catch (\Exception$e) {
            return generalErrorResponse($e);
        }
    }

}
