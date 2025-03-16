<?php

namespace App\Services;

use App\Data\LoginData;
use App\Data\RegisterData;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthService
{
    public function register(RegisterData $data): User
    {
        return User::create([
            'email' => $data->email,
            'password' => Hash::make($data->password),
        ]);
    }

    public function login(LoginData $data): User
    {
        $user = User::where('email', $data->email)->first();

        if (!$user || !Hash::check($data->password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['Неверные учетные данные.'],
            ]);
        }

        return $user;
    }

    public function logout(User $user): void
    {
        $user->tokens()->delete();
    }
}