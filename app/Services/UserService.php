<?php

namespace App\Services;

use App\Data\ChangePassword;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class UserService
{
    public function changePassword(ChangePassword $data)
    {
        $user = User::find(Auth::user()->id);

        if (!Hash::check($data->old_password, $user->password)) {
            throw ValidationException::withMessages([
                'old_password' => ['Неверно введён старый пароль'],
            ]);
        }


        $user->password = Hash::make($data->new_password);
        $user->save();

        return true;
    }
}