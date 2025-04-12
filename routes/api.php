<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\GameController;
use App\Http\Controllers\Api\UserController;
use Illuminate\Support\Facades\Route;

Route::controller(AuthController::class)->group(function () {

    Route::post('/login', 'login');
    Route::post('/register', 'register');
    Route::post('/logout', 'logout')->middleware(['auth:sanctum']);
    Route::get('/user', 'me')->middleware(['auth:sanctum']);

});

Route::controller(GameController::class)->group(function () {

    Route::get('/games', 'getAll');
    Route::get('/game/{id}', 'show');

});

Route::controller(UserController::class)->group(function () {

    Route::post('/change/password', 'changePassword')->middleware(['auth:sanctum']);

});