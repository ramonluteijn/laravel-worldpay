<?php

namespace RamonLuteijn\LaravelWorldpay\Requests;

use RamonLuteijn\LaravelWorldpay\Responses\PaymentResponse;

class GooglePayPaymentRequest extends PaymentRequest
{
    protected function method(): string
    {
        return 'googlepay';
    }

    /**
     * $paymentData is the token payload returned by the Google Pay JS API's
     * loadPaymentData(). Forwarded as-is under paymentInstrument - check
     * Worldpay's current wallet payment guide for the exact expected shape
     * for your integration, and adjust via withPaymentInstrument() if it
     * differs from the default merge here.
     */
    public function pay(int $amount, array $paymentData, string $reference, ?string $currency = null, ?string $orderReference = null, array $narrative = []): PaymentResponse
    {
        $this->paymentInstrument = array_replace_recursive(
            ['type' => 'encrypted', 'walletToken' => $paymentData],
            $this->paymentInstrument
        );

        return $this->send($amount, $currency ?? $this->client->config()['default_currency'], $reference, $orderReference, $narrative);
    }
}
