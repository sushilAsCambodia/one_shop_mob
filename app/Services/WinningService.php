<?php

namespace App\Services;

use App\Models\WinnerDetail;
use Illuminate\Http\JsonResponse;

class WinningService
{

    public function paginate($request): JsonResponse
    {
        try {
            $perPage = $request->rowsPerPage ?: 15;
            $page = $request->page ?: 1;
            $sortBy = $request->sortBy ?: 'created_at';
            $sortOrder = $request->descending == 'true' ? 'desc' : 'asc';

            $query = (new WinnerDetail())->newQuery()->orderBy($sortBy, $sortOrder);
            $results = $query->with(['order','product'])->paginate($perPage, ['*'], 'page', $page);

            return response()->json($results, 200);
        } catch (\Exception $e) {
            \Log::debug($e);
            return generalErrorResponse($e);
        }
    }
}
