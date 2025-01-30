<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\Auth\AuthController;
use App\Http\Controllers\Api\Profile\PasswordController;



Route::post('auth/register', [AuthController::class, 'register']);
Route::post('auth/login', [AuthController::class, 'login']);
Route::post('auth/verify_user_email', [AuthController::class, 'verifyUserEmail']);
Route::post('auth/resend_email_verification_link', [AuthController::class, 'resendEmailVerificationLink']);

Route::middleware(['auth'])->group(function () {
    Route::post('change_password', [PasswordController::class, 'changeUserPassword']);
    Route::post('logout', [AuthController::class, 'logout']);
});
