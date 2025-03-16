<?php

namespace App\Http\Controllers\Api;

use App\Data\LoginData;
use App\Data\RegisterData;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\AuthService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

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
