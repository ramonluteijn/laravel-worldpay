<?php

namespace RamonLuteijn\LaravelWorldpay\Requests;

use Illuminate\Support\Str;
use RamonLuteijn\LaravelWorldpay\Exceptions\WorldpayValidationException;
use RamonLuteijn\LaravelWorldpay\Responses\PaymentResponse;
use RamonLuteijn\LaravelWorldpay\WorldpayClient;

/**
 * Generic builder for Worldpay's APMs API (POST /apmPayments). Each APM has
 * its own paymentInstrument/customer field shape (see
 * docs.worldpay.com/access/products/apms/<method> for the one you're
 * enabling), so - unlike CardPaymentRequest - this class doesn't model
 * those fields itself. It only handles what's common across every APM:
 * config-gating, transactionReference/value/narrative, and dispatch.
 */
class ApmPaymentRequest
{
    protected string $transactionReference;

    public function __construct(protected WorldpayClient $client, protected string $apm)
    {
        $this->transactionReference = (string) Str::uuid();
        $this->assertEnabled();
    }

    public function pay(
        int $amount,
        array $paymentInstrument,
        array $customer,
        string $reference,
        ?string $currency = null,
        ?string $orderReference = null,
        array $narrative = [],
    ): PaymentResponse {
        $payload = array_filter([
            'transactionReference' => $reference,
            'orderReference' => $orderReference,
            'merchant' => [
                'entity' => $this->client->entity(),
            ],
            'instruction' => array_filter([
                'method' => $this->method(),
                'narrative' => $narrative ?: ['line1' => $this->client->entity()],
                'value' => [
                    'currency' => $currency ?? $this->client->config()['default_currency'],
                    'amount' => $amount,
                ],
                'paymentInstrument' => $paymentInstrument,
                'customer' => $customer,
            ]),
        ], fn ($value) => $value !== null);

        $response = $this->client->post('/apmPayments', $payload);

        return PaymentResponse::fromResponse($response, $this->client);
    }

    protected function method(): string
    {
        return $this->client->config()['apms'][$this->apm]['method'] ?? $this->apm;
    }

    protected function assertEnabled(): void
    {
        $apms = $this->client->config()['apms'] ?? [];

        if (! array_key_exists($this->apm, $apms)) {
            throw new WorldpayValidationException("Unknown APM '{$this->apm}'. See the 'apms' key in config/worldpay.php for supported values.");
        }

        if (! ($apms[$this->apm]['enabled'] ?? false)) {
            throw new WorldpayValidationException("The '{$this->apm}' APM is not enabled. Set WORLDPAY_APM_".strtoupper($this->apm).'_ENABLED=true in your .env.');
        }
    }
}
