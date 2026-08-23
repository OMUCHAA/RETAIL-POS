<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;


//Public route
Route::post('/login', [AuthController::class, 'login']);

//Only Authenticated
Route::middleware('auth:sanctum')->group(function () {
    //Authentication
    Route::get('/user', [AuthController::class, 'user']);
    Route::post('/logout', [AuthController::class, 'logout']);

    //User management
    Route::middleware('role:admin')->group(function () {
        Route::apiResource('/users', UserController::class)->except(['destroy']);

        Route::patch('/users/{user}/deactivate', [UserController::class, 'deactivate']);

        Route::patch('/users/{user}/reactivate', [UserController::class, 'reactivate']);
    });
});
