<?php

namespace App\Http\Controllers\Api;

use App\Data\ChangePassword;
use App\Http\Controllers\Controller;
use App\Services\UserService;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class UserController extends Controller
{
    protected $userService;

    public function __construct(UserService $userService) {
        $this->userService = $userService;
    }

    public function changePassword(Request $request){

        try {

            $data = ChangePassword::from($request);

            $this->userService->changePassword($data);

            return response()->json(['message' => 'Пароль успешно изменён!']);

        } catch (ValidationException $e) {
            return response()->json(['errors' => $e->errors()], 422);
        }

    }
}
