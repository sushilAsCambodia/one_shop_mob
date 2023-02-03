<?php

namespace App\Http\Controllers;

use App\Services\MicroserviceCommunicationService;
use Illuminate\Http\Request;

class MicroserviceCommunicationController extends Controller
{
    public function __construct(private MicroserviceCommunicationService $microserviceCommunicationService)
    {
    }

    public function index(Request $request)
    {
        return $this->microserviceCommunicationService->index($request->all());
    }
}
