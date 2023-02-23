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

    public function smsTesting()
    {
        $message = "test otp 1234567 local Sushil";
        $toNumbers = "855882103199";
        $serviceUrl = 'http://bizsms.metfone.com.kh:8804/bulkapi?wsdl';
    
        $userId = 'loma_api';
        $pass = 'L0m@T3ch';
        $cpCode = 'LOMA001';
        $serviceID = 'MetfoneT';
    
        $client = new SoapClient($serviceUrl);
        $params = array("User" => $userId,    "Password" => $pass,    "CPCode" => $cpCode,    "RequestID" => "1",    "UserID" => $toNumbers,     "ReceiverID" => $toNumbers,    "ServiceID" => $serviceID,    "CommandCode" => "bulksms",    "Content" => $message,    "ContentType" => "0");
        $response = $client->__soapCall("wsCpMt", array($params));
    
        var_dump($response);
    }
    
}