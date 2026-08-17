<?php

use App\Http\Controllers\AuthController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;


//Public route
Route::post('/login', [AuthController::class, 'login']);

//Only Authenticated
Route::middleware('auth:sanctum')->group(function () {
    //Authentication
    Route::get('/user', [AuthController::class, 'user']);
    Route::post('/logout', [AuthController::class, 'logout']);
});