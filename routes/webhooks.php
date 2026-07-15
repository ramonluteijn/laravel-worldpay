<?php

use Illuminate\Support\Facades\Route;
use RamonLuteijn\LaravelWorldpay\Http\Controllers\WorldpayWebhookController;

Route::post(config('worldpay.webhooks.route_prefix'), WorldpayWebhookController::class)
    ->middleware(config('worldpay.webhooks.middleware', ['api']))
    ->name('worldpay.webhooks');
