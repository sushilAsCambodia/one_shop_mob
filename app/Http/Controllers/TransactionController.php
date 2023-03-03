<?php

namespace App\Http\Controllers;

use App\Http\Requests\TransactionFormRequest;
use App\Http\Requests\UpdateTransactionStatusFormRequest;
use App\Http\Requests\WithdrawFormRequest;
use App\Models\Transaction;
use App\Services\TransactionService;
use Illuminate\Http\Request;

class TransactionController extends Controller
{
    public function __construct(private TransactionService $transactionService)
    {
    }
    
    public function withdraw(WithdrawFormRequest $request)
    {
        return $this->transactionService->withdraw($request);
    }

}
