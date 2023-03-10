<?php

namespace App\Services;

use App\Models\BankAccount;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class BankAccountService
{
    public function paginate($request): JsonResponse
    {
        try {
            $perPage = $request->rowsPerPage ?: 15;
            $page = $request->page ?: 1;
            $sortBy = $request->sortBy ?: 'created_at';
            $sortOrder = $request->descending == 'true' ? 'desc' : 'asc';

            $query = (new BankAccount())->newQuery()->orderBy($sortBy, $sortOrder);

            $query->whereHas('customer', function ($query) use ($request) {
                $query->where('customers.id', auth()->id());
            });
            $query->when($request->status, function ($query) use ($request) {
                $query->where('status', $request->status);
            });
            $query->when($request->search, function ($query) use ($request) {
                $query->where('bank_name', 'like', "%$request->search%")
                    ->orWhere('account_name', 'like', "%$request->search%")
                    ->orWhere('account_no', 'like', "%$request->search%")
                    ->orWhere('account_type', 'like', "%$request->search%")
                    ->orWhere('remark', 'like', "%$request->search%");
            });

            $results = $query->select('bank_accounts.*')->paginate($perPage, ['*'], 'page', $page);

            $result['message'] = 'fetch_bank_accounts_data_successfully';
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
            DB::transaction(function () use ($data) {
                $data['member_id'] = Auth::id();
                $bankAccount = BankAccount::create($data);
            });

            $result['message'] = 'Bank_Account_created_successfully';
            $result['statusCode'] = 200;
            return getSuccessMessages($result);
        } catch (\Exception $e) {
            return generalErrorResponse($e);
        }
    }

    public function update($bankAccount, array $data): JsonResponse
    {
        try {
            $bankAccount->update($data);

            $result['message'] = 'Bank_Account_updated_successfully';
            $result['statusCode'] = 200;
            return getSuccessMessages($result);
        } catch (\Exception $e) {
            return generalErrorResponse($e);
        }
    }

    public function delete($bankAccount): JsonResponse
    {
        try {
            //check if bank account in use
            if (sizeof($bankAccount->transactions) > 0) {
                $result['message'] = 'Bank_Account_already_used_can_not_delete';
                $result['statusCode'] = 201;
                return getSuccessMessages($result, false);
            }

            $bankAccount->delete();
            $result['message'] = 'Bank_Account_deleted_successfully';
            $result['statusCode'] = 200;
            return getSuccessMessages($result);
        } catch (\Exception $e) {
            return generalErrorResponse($e);
        }
    }
}
