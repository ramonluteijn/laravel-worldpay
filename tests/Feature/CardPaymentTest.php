<?php

use Illuminate\Support\Facades\Http;
use RamonLuteijn\LaravelWorldpay\Exceptions\WorldpayValidationException;
use RamonLuteijn\LaravelWorldpay\Facades\Worldpay;

it('sends a card payment with the correct url, headers, and body', function () {
    Http::fake([
        'try.access.worldpay.com/api/payments' => Http::response([
            'outcome' => 'authorized',
            'paymentId' => 'payment-123',
            '_actions' => [
                'cancelPayment' => ['href' => 'https://try.access.worldpay.com/api/payments/xyz/cancellations', 'method' => 'POST'],
            ],
        ], 201, ['WP-CorrelationId' => 'corr-1']),
    ]);

    $response = Worldpay::card()->pay(
        amount: 4200,
        card: [
            'cardHolderName' => 'Sherlock Holmes',
            'cardNumber' => '4000000000002701',
            'expiryDate' => ['month' => 5, 'year' => 2035],
            'cvc' => '123',
        ],
        reference: 'order-1',
        currency: 'GBP',
    );

    expect($response->outcome())->toBe('authorized');
    expect($response->paymentId())->toBe('payment-123');
    expect($response->correlationId())->toBe('corr-1');

    Http::assertSent(function ($request) {
        return $request->url() === 'https://try.access.worldpay.com/api/payments'
            && $request->hasHeader('WP-Api-Version', '2024-06-01')
            && $request->hasHeader('Content-Type', 'application/json')
            && $request['transactionReference'] === 'order-1'
            && $request['merchant']['entity'] === 'default'
            && $request['instruction']['method'] === 'card'
            && $request['instruction']['paymentInstrument']['type'] === 'plain'
            && $request['instruction']['paymentInstrument']['cardNumber'] === '4000000000002701'
            && $request['instruction']['value'] === ['currency' => 'GBP', 'amount' => 4200];
    });
});

it('uses the default currency when none is given', function () {
    Http::fake(['try.access.worldpay.com/*' => Http::response(['outcome' => 'authorized'], 201)]);

    Worldpay::card()->pay(amount: 100, card: ['cardNumber' => '4000000000002701'], reference: 'order-2');

    Http::assertSent(fn ($request) => $request['instruction']['value']['currency'] === 'EUR');
});

it('throws before making a request when the card method is disabled', function () {
    config(['worldpay.payment_methods.card.enabled' => false]);
    Http::fake();

    Worldpay::card();
})->throws(WorldpayValidationException::class);

it('follows the cancelPayment action link', function () {
    Http::fake([
        'try.access.worldpay.com/api/payments' => Http::response([
            'outcome' => 'authorized',
            '_actions' => [
                'cancelPayment' => ['href' => 'https://try.access.worldpay.com/api/payments/xyz/cancellations', 'method' => 'POST'],
            ],
        ], 201),
        'try.access.worldpay.com/api/payments/xyz/cancellations' => Http::response(['lastEvent' => 'Sent for Cancellation'], 200),
    ]);

    $response = Worldpay::card()->pay(amount: 100, card: ['cardNumber' => '4000000000002701'], reference: 'order-3');
    $cancelled = $response->cancel();

    expect($cancelled->lastEvent())->toBe('Sent for Cancellation');
    Http::assertSent(fn ($request) => $request->url() === 'https://try.access.worldpay.com/api/payments/xyz/cancellations');
});

it('throws a WorldpayException with the correlation id on a failed request', function () {
    Http::fake([
        'try.access.worldpay.com/api/payments' => Http::response(['message' => 'Invalid card number'], 400, ['WP-CorrelationId' => 'corr-err']),
    ]);

    try {
        Worldpay::card()->pay(amount: 100, card: ['cardNumber' => 'bad'], reference: 'order-4');
        $this->fail('Expected exception was not thrown.');
    } catch (\RamonLuteijn\LaravelWorldpay\Exceptions\WorldpayException $e) {
        expect($e->getMessage())->toBe('Invalid card number');
        expect($e->correlationId())->toBe('corr-err');
    }
});
