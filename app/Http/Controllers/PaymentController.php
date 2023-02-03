<?php

namespace App\Http\Controllers;

use App\Http\Requests\PaymentFormRequest;
use App\Models\Order;
use App\Models\payment;
use App\Services\PaymentService;
use Illuminate\Http\Request;



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

}
