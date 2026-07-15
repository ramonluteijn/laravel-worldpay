<?php

namespace RamonLuteijn\LaravelWorldpay\Requests;

use RamonLuteijn\LaravelWorldpay\Responses\PaymentResponse;

class CardPaymentRequest extends PaymentRequest
{
    protected function method(): string
    {
        return 'card';
    }

    /**
     * Pay with a plain card number. $card keys match Worldpay's
     * paymentInstrument (type: plain) schema directly: cardHolderName,
     * cardNumber, expiryDate => ['month' => .., 'year' => ..], cvc, and
     * optionally billingAddress.
     */
    public function pay(int $amount, array $card, string $reference, ?string $currency = null, ?string $orderReference = null, array $narrative = []): PaymentResponse
    {
        $this->paymentInstrument = array_replace_recursive(['type' => 'plain'], $card, $this->paymentInstrument);

        return $this->send($amount, $currency ?? $this->client->config()['default_currency'], $reference, $orderReference, $narrative);
    }

    /**
     * Pay using a PCI-compliant Checkout SDK session instead of a raw PAN.
     */
    public function payWithCheckoutSession(int $amount, string $sessionHref, string $reference, ?string $currency = null, ?string $orderReference = null, array $narrative = []): PaymentResponse
    {
        $this->paymentInstrument = array_replace_recursive(['type' => 'checkout', 'href' => $sessionHref], $this->paymentInstrument);

        return $this->send($amount, $currency ?? $this->client->config()['default_currency'], $reference, $orderReference, $narrative);
    }

    /**
     * Pay (or make a subsequent recurring/CIT) payment using a previously
     * stored Worldpay token or network token.
     */
    public function payWithToken(int $amount, string $tokenHref, string $reference, ?string $cvc = null, ?string $currency = null, ?string $orderReference = null, array $narrative = []): PaymentResponse
    {
        $this->paymentInstrument = array_replace_recursive(array_filter([
            'type' => 'token',
            'href' => $tokenHref,
            'cvc' => $cvc,
        ]), $this->paymentInstrument);

        return $this->send($amount, $currency ?? $this->client->config()['default_currency'], $reference, $orderReference, $narrative);
    }
}
