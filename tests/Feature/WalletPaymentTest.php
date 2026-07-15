<?php

use Illuminate\Support\Facades\Http;
use RamonLuteijn\LaravelWorldpay\Exceptions\WorldpayValidationException;
use RamonLuteijn\LaravelWorldpay\Facades\Worldpay;

it('sends an apple pay payment', function () {
    Http::fake(['try.access.worldpay.com/*' => Http::response(['outcome' => 'authorized'], 201)]);

    Worldpay::applePay()->pay(amount: 500, paymentData: ['token' => 'abc'], reference: 'order-ap');

    Http::assertSent(fn ($request) => $request['instruction']['method'] === 'applepay'
        && $request['instruction']['paymentInstrument']['type'] === 'encrypted'
        && $request['instruction']['paymentInstrument']['walletToken'] === ['token' => 'abc']);
});

it('sends a google pay payment', function () {
    Http::fake(['try.access.worldpay.com/*' => Http::response(['outcome' => 'authorized'], 201)]);

    Worldpay::googlePay()->pay(amount: 500, paymentData: ['token' => 'xyz'], reference: 'order-gp');

    Http::assertSent(fn ($request) => $request['instruction']['method'] === 'googlepay'
        && $request['instruction']['paymentInstrument']['walletToken'] === ['token' => 'xyz']);
});

it('throws before making a request when apple pay is disabled', function () {
    config(['worldpay.payment_methods.applepay.enabled' => false]);
    Http::fake();

    Worldpay::applePay();
})->throws(WorldpayValidationException::class);

it('lets withPaymentInstrument override the default wallet shape', function () {
    Http::fake(['try.access.worldpay.com/*' => Http::response(['outcome' => 'authorized'], 201)]);

    Worldpay::googlePay()
        ->withPaymentInstrument(['type' => 'encrypted', 'protocolVersion' => 'ECv2'])
        ->pay(amount: 500, paymentData: ['token' => 'xyz'], reference: 'order-gp2');

    Http::assertSent(fn ($request) => $request['instruction']['paymentInstrument']['protocolVersion'] === 'ECv2');
});
