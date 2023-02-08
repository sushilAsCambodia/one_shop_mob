<?php

namespace App\Services;

use App\Models\Address;
use App\Models\Customer;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class AddressService
{

    public function all($request): JsonResponse
    {
        try {
            $query =  (new Address())->newQuery();

            $modelData = Auth::user();

            $query->when($modelData, function ($query) use ($modelData) {
                $query->whereAddressableType(Customer::class)
                        ->whereAddressableId($modelData->id);

            });
            $results = $query->get();

            return response()->json($results, 200);
        } catch (\Exception$e) {
            return generalErrorResponse($e);
        }
    }

    public function store(array $data): JsonResponse
    {
        try {
            DB::transaction(function () use ($data) {
                $address = new Address($data);

                Auth::user()->addresses()->save($address);
            });

            return response()->json([
                'messages' => ['Address created successfully'],
            ], 201);
        } catch (\Exception$e) {
            return generalErrorResponse($e);
        }
    }

    public function update($address, array $data): JsonResponse
    {
        try {
            DB::transaction(function () use (&$address, $data) {
                $address->update($data);
            });

            return response()->json([
                'messages' => ['Address updated successfully'],
            ], 200);
        } catch (\Exception$e) {
            return generalErrorResponse($e);
        }
    }

    public function delete($address): JsonResponse
    {
        try {
            $address->delete();

            return response()->json([
                'messages' => ['Address deleted successfully'],
            ], 200);
        } catch (\Exception$e) {
            return generalErrorResponse($e);
        }
    }

}
