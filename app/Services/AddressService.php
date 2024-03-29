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

    public function all($request, $addressId=null): JsonResponse
    {
        try {
            $query =  (new Address())->newQuery()->orderBy('updated_at', 'DESC');

            $modelData = Auth::user();

            $query->when($modelData, function ($query) use ($modelData) {
                $query->whereAddressableType(Customer::class)
                    ->whereAddressableId($modelData->id);
            });
            if($addressId){
                $query->whereId($addressId)->withTrashed();
            }
            $results = $query->select(
                'addresses.id',
                'addresses.street_address_1',
                'addresses.street_address_2',
                'addresses.pincode',
                'addresses.country_id',
                'addresses.state_id',
                'addresses.city_id'
            )->get();

            $result['message'] = 'Address_fetch_successfully';
            $result['data'] = $results;
            $result['statusCode'] = 200;
            return getSuccessMessages($result);
        } catch (\Exception $e) {
            return generalErrorResponse($e);
        }
    }

    public function store(array $data): JsonResponse
    {
        try {
            $data['type'] = 'shipping';
            $data['pincode'] = ' ';
            DB::transaction(function () use ($data) {
                $address = new Address($data);

                Auth::user()->addresses()->save($address);
            });

            $result['message'] = 'created_successfully';
            $result['statusCode'] = 200;
            return getSuccessMessages($result);
        } catch (\Exception $e) {
            return generalErrorResponse($e);
        }
    }

    public function update($address, array $data): JsonResponse
    {
        try {
            $data['pincode'] = ' ';
            DB::transaction(function () use (&$address, $data) {
                $address->update($data);
            });

            $result['message'] = 'updated_successfully';
            $result['statusCode'] = 200;
            return getSuccessMessages($result);
        } catch (\Exception $e) {
            return generalErrorResponse($e);
        }
    }

    public function delete($address): JsonResponse
    {
        try {
            $address->delete();

            $result['message'] = 'deleted_successfully';
            $result['statusCode'] = 200;
            return getSuccessMessages($result);
        } catch (\Exception $e) {
            return generalErrorResponse($e);
        }
    }
}
