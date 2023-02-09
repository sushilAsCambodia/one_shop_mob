<?php

namespace App\Http\Controllers;

use App\Http\Requests\OrderFormRequest;
use App\Models\Order;
use App\Services\OrderService;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    public function __construct(private OrderService $orderService)
    {
    }

    public function paginate(Request $request)
    {
        return $this->orderService->paginate($request);
    }

    public function order($slug)
    {
        return $this->orderService->order($slug);
    }

    public function store(Request $request)
    {
        return $request->all();
        return $this->orderService->store($request->all());
    }

    public function orderGetById($orderId)
    {
        return $this->orderService->orderGetById($orderId);
    }

    public function cancelOrder(Request $request)
    {
        return $this->orderService->cancelOrder($request->all());
    }
    public function orderCompleted(Request $request)
    {
        return $this->orderService->orderCompleted($request->all());
    }
    

}
