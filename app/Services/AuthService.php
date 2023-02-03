<?php

namespace App\Services;

use App\Models\LoginLog;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class AuthService
{
    public function login(array $data): JsonResponse
    {
        try {
            $loginData = [
                'email' => $data['email'],
                'password' => $data['password']
            ];
            if (Auth::attempt($loginData)) {
                $user = Auth::user();
                $user->tokens()->delete();

                LoginLog::create([
                    'user_id' => $user->id,
                    'user_type' => User::class,
                    'ip_address' => request()->ip(),
                    'user_agent' => request()->userAgent(),
                ]);

                return response()->json([
                    'user' => $user,
                    'applications' => $user->applications,
                    'roles' => $user->roles,
                    'permissions' => $user->getAllPermissions(),
                    'notifications' => $user->notifications(),
                    'token' => $user->createToken($user->email)->plainTextToken,
                ], 200);
            }

            return response()->json([
                'messages' => ['Incorrect Username or password'],
            ], 401);
        } catch (\Exception$e) {
            return generalErrorResponse($e);
        }
    }

    public function user()
    {
        if (Auth::user()) {
            $user = Auth::user();
            // $user->tokens()->delete();

            return response()->json([
                'user' => $user,
                'applications' => $user->applications,
                'roles' => $user->roles,
                'permissions' => $user->getAllPermissions(),
                'token' => $user->createToken($user->email)->plainTextToken,
            ], 200);
        }

        return response()->json(['messages' => ['User not found']], 400);
    }

    public function logout()
    {
        if (Auth::user()) {
            Auth::user()->tokens()->delete();
            Auth::guard('web')->logout();
        }

        return response('Logout successful', 204);
    }
}
