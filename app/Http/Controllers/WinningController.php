<?php

namespace App\Http\Controllers;

use App\Http\Requests\WinningRequest;
use Illuminate\Http\Request;
use App\Services\WinningService;

class WinningController extends Controller
{
    public function __construct(private WinningService $winningService)
    {
    }

    public function paginate(Request $request)
    {
        return $this->winningService->paginate($request);
    }

}
