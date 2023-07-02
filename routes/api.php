<?php

use App\Http\Controllers\Auth;
use Illuminate\Support\Facades\Route;

Route::prefix('auth')->group(function () {
    Route::post('register', Auth\RegisterController::class)->name('auth.register');

    Route::post('login', [Auth\LoginController::class, 'store'])->name('auth.login');
    Route::post('logout', [Auth\LoginController::class, 'destroy'])->name('auth.logout');
});
