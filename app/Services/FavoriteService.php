<?php

namespace App\Services;

use App\Models\Favorite;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Hash;

class FavoriteService
{


    public function list($request): JsonResponse
    {

        try {
            $perPage = $request->rowsPerPage ?: 15;
            $page = $request->page ?: 1;

            $query = (new Favorite())->newQuery()->whereCustomerId(Auth::id());

            // $results = $query->select('favorites.*')->with('products')->paginate($perPage, ['*'], 'page', $page);
            $results = $query->select('favorites.*')
                ->whereHas('product', function ($query) {
                    $query->whereHas('deal', function ($query) {
                        $query->whereNotIn('deals.status', ['settled', 'inactive']);
                    });
                })
                ->with('product.deal.slots')
                ->paginate($perPage, ['*'], 'page', $page);

            $result['message'] = 'favorite_fetch_successfully';
            $result['data'] = $results;
            $result['statusCode'] = 200;

            return getSuccessMessages($result);
        } catch (\Exception $e) {
            \Log::debug($e);
            return generalErrorResponse($e);
        }
    }

    public function addToFavorites(array $data): JsonResponse
    {
        try {
            $data['customer_id'] = auth()->user()->id;
            if (Favorite::where('customer_id', auth()->user()->id)->where('product_id', $data['product_id'])->exists()) {
                $result['message'] = 'All_Ready_Exists';
                $result['statusCode'] = 400;

                return getSuccessMessages($result, false);
            }
            DB::transaction(function () use ($data) {
                Favorite::create($data);
            });

            $result['message'] = 'Product_added_to_favorite_successfully';
            $result['statusCode'] = 200;

            return getSuccessMessages($result);
        } catch (\Exception $e) {
            \Log::debug($e);
            return generalErrorResponse($e);
        }
    }

    public function removeFromFavorites(array $data): JsonResponse
    {
        try {
            $data['customer_id'] = auth()->user()->id;
            DB::transaction(function () use ($data) {
                Favorite::where('product_id', $data['product_id'])->where('customer_id', $data['customer_id'])->delete();
            });

            $result['message'] = 'Product_Removed_from_favorite_successfully';
            $result['statusCode'] = 200;

            return getSuccessMessages($result);
        } catch (\Exception $e) {
            \Log::debug($e);
            return generalErrorResponse($e);
        }
    }
}
