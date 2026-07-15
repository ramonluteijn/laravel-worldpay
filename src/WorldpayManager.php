<?php

namespace RamonLuteijn\LaravelWorldpay;

use RamonLuteijn\LaravelWorldpay\Exceptions\WorldpayValidationException;
use RamonLuteijn\LaravelWorldpay\Requests\ApmPaymentRequest;
use RamonLuteijn\LaravelWorldpay\Requests\ApplePayPaymentRequest;
use RamonLuteijn\LaravelWorldpay\Requests\CardPaymentRequest;
use RamonLuteijn\LaravelWorldpay\Requests\GooglePayPaymentRequest;

class WorldpayManager
{
    public function __construct(protected WorldpayClient $client)
    {
    }

    public function card(): CardPaymentRequest
    {
        $this->assertMethodEnabled('card');

        return new CardPaymentRequest($this->client);
    }

    public function applePay(): ApplePayPaymentRequest
    {
        $this->assertMethodEnabled('applepay');

        return new ApplePayPaymentRequest($this->client);
    }

    public function googlePay(): GooglePayPaymentRequest
    {
        $this->assertMethodEnabled('googlepay');

        return new GooglePayPaymentRequest($this->client);
    }

    public function apm(string $method): ApmPaymentRequest
    {
        return new ApmPaymentRequest($this->client, $method);
    }

    public function client(): WorldpayClient
    {
        return $this->client;
    }

    protected function assertMethodEnabled(string $method): void
    {
        if (! ($this->client->config()['payment_methods'][$method]['enabled'] ?? false)) {
            throw new WorldpayValidationException("The '{$method}' payment method is not enabled. Set WORLDPAY_METHOD_".strtoupper($method).'_ENABLED=true in your .env.');
        }
    }
}
