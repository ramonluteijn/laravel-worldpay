<?php

use Illuminate\Support\Facades\Http;
use RamonLuteijn\LaravelWorldpay\Exceptions\WorldpayValidationException;
use RamonLuteijn\LaravelWorldpay\Facades\Worldpay;

it('sends an ideal payment to the apmPayments endpoint', function () {
    Http::fake([
        'try.access.worldpay.com/apmPayments' => Http::response([
            'paymentId' => 'apm-payment-1',
            'redirect' => 'https://issuer.example/redirect',
            '_actions' => [],
        ], 201),
    ]);

    $response = Worldpay::apm('ideal')->pay(
        amount: 1000,
        paymentInstrument: ['type' => 'directDebit', 'shopperBankCode' => 'ABNANL2A'],
        customer: ['firstName' => 'Sherlock', 'lastName' => 'Holmes'],
        reference: 'order-ideal',
    );

    expect($response->paymentId())->toBe('apm-payment-1');
    expect($response->redirectUrl())->toBe('https://issuer.example/redirect');

    Http::assertSent(function ($request) {
        return $request->url() === 'https://try.access.worldpay.com/apmPayments'
            && $request['instruction']['method'] === 'ideal'
            && $request['instruction']['paymentInstrument']['shopperBankCode'] === 'ABNANL2A'
            && $request['instruction']['customer']['firstName'] === 'Sherlock';
    });
});

it('throws before making a request for an unknown apm', function () {
    Http::fake();

    Worldpay::apm('not-a-real-apm');
})->throws(WorldpayValidationException::class);

it('throws before making a request for a disabled apm', function () {
    config(['worldpay.apms.paypal.enabled' => false]);
    Http::fake();

    Worldpay::apm('paypal');
})->throws(WorldpayValidationException::class);

it('follows the reverse action link for an apm payment', function () {
    Http::fake([
        'try.access.worldpay.com/apmPayments' => Http::response([
            'paymentId' => 'apm-payment-2',
            '_actions' => [
                'reverse' => ['href' => 'https://try.access.worldpay.com/apmPayments/apm-payment-2/reversals', 'method' => 'POST'],
            ],
        ], 201),
        'try.access.worldpay.com/apmPayments/apm-payment-2/reversals' => Http::response(['lastEvent' => 'cancelled'], 200),
    ]);

    $response = Worldpay::apm('ideal')->pay(
        amount: 1000,
        paymentInstrument: ['type' => 'directDebit'],
        customer: ['firstName' => 'Sherlock', 'lastName' => 'Holmes'],
        reference: 'order-ideal-2',
    );

    $cancelled = $response->cancel();

    expect($cancelled->lastEvent())->toBe('cancelled');
});
