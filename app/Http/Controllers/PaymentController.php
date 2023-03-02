<?php

namespace App\Http\Controllers;

use App\Http\Requests\PaymentFormRequest;
use App\Http\Requests\PaymentResponseFormRequest;
use App\Models\Order;
use App\Models\payment;
use App\Services\PaymentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class PaymentController extends Controller
{
    public function __construct(private PaymentService $paymentService)
    {
    }

    public function paginate(Request $request)
    {
        return $this->paymentService->paginate($request);
    }

    public function all()
    {
        return response()->json(Payment::all(), 200);
    }

    public function store(PaymentFormRequest $request)
    {
        return $this->paymentService->store($request->all());
    }

    public function storeHttp(Request $request)
    {
        $token = $request->bearerToken();
        
        // $token = '22|vIhpapsLK0v711sC6o3eQb8iggSlsThyXZGRLDkV';
        $headers = [
            'Accept' => 'application/json',
            "Authorization" => "Bearer 22|vIhpapsLK0v711sC6o3eQb8iggSlsThyXZGRLDkV"
        ];
        $api_url = 'https://the1shops.com:8080/api/customer/order-payment?lang_id=1';
        $response = Http::withToken($token)->withHeaders($headers)->post($api_url, $request->all());
        // $response = Http::withToken($token)
        //                 ->withHeaders($headers)
        //                 ->send("POST", $api_url)
        //                 ->json();

        return $response;
        // return $this->paymentService->storeHttp($request->all());
    }

    public function paymentResponse(PaymentResponseFormRequest $request)
    {
        return $this->paymentService->paymentResponse($request);
    }
}
