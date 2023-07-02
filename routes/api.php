<?php

use App\Http\Controllers\Auth;
use App\Http\Controllers\Profile;
use Illuminate\Support\Facades\Route;

Route::prefix('auth')->group(function () {
    Route::post('register', Auth\RegisterController::class)->name('auth.register');

    Route::post('login', [Auth\LoginController::class, 'store'])->name('auth.login');
    Route::post('logout', [Auth\LoginController::class, 'destroy'])->name('auth.logout');

    Route::post('forgot-password', Auth\ForgotPasswordController::class)->name('auth.password.forgot');
    Route::post('reset-password', Auth\ResetPasswordController::class)->name('auth.password.reset');

    Route::get('/email/verify/{id}/{hash}', [Auth\EmailVerificationController::class, 'verify'])->name('auth.verification.verify');
    Route::post('/email/resend', [Auth\EmailVerificationController::class, 'resend'])->name('auth.verification.resend');
});

Route::get('profile', [Profile\ProfileController::class, 'show'])->name('profile.show');
Route::patch('profile', [Profile\ProfileController::class, 'update'])->name('profile.update');
Route::patch('profile/password', Profile\PasswordController::class)->name('profile.password.update');
