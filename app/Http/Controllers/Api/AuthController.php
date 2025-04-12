<?php

namespace App\Http\Controllers\Api;

use App\Data\LoginData;
use App\Data\RegisterData;
use App\Http\Controllers\Controller;
use App\Services\AuthService;
use Illuminate\Http\Request;

class AuthController extends Controller
{
    protected AuthService $authService;

    public function __construct(AuthService $authService)
    {
        $this->authService = $authService;
    }

    public function register(Request $request)
    {
        $data = RegisterData::from($request);

        $this->authService->register($data);

        return response()->json([
            'message' => 'success'
        ]);
    }

    /**
     * Аутентификация пользователя
     */
    public function login(Request $request)
    {
        $data = LoginData::from($request);

        $user = $this->authService->login($data);

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'access_token' => $token,
        ]);
    }

    public function me(){
        $user = $this->authService->me();

        return response()->json($user);
    }

    /**
     * Выход пользователя (удаление токена)
     */
    public function logout(Request $request)
    {
        $request->user()->tokens()->delete();

        return response()->json([
            'message' => 'Вы успешно вышли из системы.',
        ]);
    }
}
