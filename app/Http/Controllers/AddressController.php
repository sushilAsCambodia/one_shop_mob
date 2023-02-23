<?php

namespace App\Http\Controllers;

use App\Http\Requests\AddressFormRequest;
use App\Models\Address;
use App\Services\AddressService;
use Illuminate\Http\Request;
use SoapClient;


class AddressController extends Controller
{
    public function __construct(private AddressService $addressService)
    {
        
    }

    public function all(Request $request)
    {
        return $this->addressService->all($request);

    }
    public function get(Address $address)
    {
        return response()->json($address, 200);
    }

    public function store(AddressFormRequest $request)
    {
        return $this->addressService->store($request->all());
    }

    public function update(AddressFormRequest $request, Address $address)
    {
        return $this->addressService->update($address, $request->all());
    }

    public function delete(Address $address)
    {
        return $this->addressService->delete($address);
    }
    
}