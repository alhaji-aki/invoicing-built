<?php

return [
    'public_key' => env('PAYSTACK_PUBLIC_KEY'),

    'secret_key' => env('PAYSTACK_SECRET_KEY'),

    'currency' => env('PAYSTACK_CURRENCY'),

    'channels' => ['card', 'mobile_money'],

    'payment_url' => 'https://paystack.com/pay',
];
