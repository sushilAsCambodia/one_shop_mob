<?php

namespace App\Http\Controllers;

use App\Http\Requests\LoginFormRequest;
use App\Services\AuthService;

class AuthController extends Controller
{
    public function __construct(private AuthService $authService)
    {
    }

    public function login(LoginFormRequest $request)
    {
        return $this->authService->login($request->all());
    }

    public function user()
    {
        return $this->authService->user();
    }

    public function logout()
    {
        return $this->authService->logout();
    }
}
