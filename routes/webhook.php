<?php

use App\Http\Controllers\Webhook;
use Illuminate\Support\Facades\Route;

Route::post('/paystack', Webhook\PaystackController::class)->name('paystack');
