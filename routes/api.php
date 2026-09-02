<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::post('/login', [\App\Http\Controllers\Api\AuthController::class, 'login']);

// Client Authentication Routes
Route::post('/client/register', [\App\Http\Controllers\Api\ClientAuthController::class, 'register']);
Route::post('/client/verify-otp', [\App\Http\Controllers\Api\ClientAuthController::class, 'verifyOtp']);
Route::post('/client/resend-otp', [\App\Http\Controllers\Api\ClientAuthController::class, 'resendOtp']);

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user', function (Request $request) {
        return $request->user();
    });
    Route::post('/logout', [\App\Http\Controllers\Api\AuthController::class, 'logout']);
});
