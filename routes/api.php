<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\GameController;
use Illuminate\Support\Facades\Route;

Route::controller(AuthController::class)->group(function () {

    Route::post('/login', 'login');
    Route::post('/register', 'register');
    Route::post('/logout', 'logout')->middleware(['auth:sanctum']);

});

Route::controller(GameController::class)->group(function () {

    Route::get('/games', 'getAll');
    Route::get('/game/{id}', 'show');

});
