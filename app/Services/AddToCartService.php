<?php

namespace App\Services;

use App\Models\AddToCart;
use Illuminate\Http\JsonResponse;
use Exception;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class AddToCartService
{
    public function addToCart(array $data): JsonResponse
    {
        try {
            // unset($data['lang_id']);
            foreach ($data as $dataVal) {
                AddToCart::create([
                    'customer_id' => auth()->user()->id,
                    'p_id'        => $dataVal['id'],
                    'p_name'      => $dataVal['pname'],
                    'quantity'    => $dataVal['quantity'],
                    'price'       => $dataVal['price'],
                    'image'       => $dataVal['image'],
                ]);
            }

            return response()->json([
                'status' => true,
                'messages' => ['Your product has been added to cart.'],
            ], 200);

        } catch (\Exception $e) {
            \Log::debug($e);
            return generalErrorResponse($e);
        }
    }
}
