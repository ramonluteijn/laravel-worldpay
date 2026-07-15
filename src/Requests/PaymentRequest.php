<?php

namespace RamonLuteijn\LaravelWorldpay\Requests;

use Illuminate\Support\Str;
use RamonLuteijn\LaravelWorldpay\Exceptions\WorldpayValidationException;
use RamonLuteijn\LaravelWorldpay\Responses\PaymentResponse;
use RamonLuteijn\LaravelWorldpay\WorldpayClient;

/**
 * Builds and sends a POST /api/payments request. Concrete subclasses only
 * need to supply the `instruction.method` discriminator and the
 * `paymentInstrument` payload - everything else (merchant, narrative,
 * value, and config-driven fraud/3DS/settlement blocks) is handled here.
 */
abstract class PaymentRequest
{
    protected string $transactionReference;

    protected ?string $orderReference = null;

    protected array $paymentInstrument = [];

    protected array $instructionOverrides = [];

    public function __construct(protected WorldpayClient $client)
    {
        $this->transactionReference = (string) Str::uuid();
    }

    abstract protected function method(): string;

    /**
     * Escape hatch for anything this builder doesn't model explicitly
     * (customer, shipping, exemption, customerAgreement, fundsTransfer,
     * etc.) - merged directly into the top-level `instruction` object.
     */
    public function withInstruction(array $data): static
    {
        $this->instructionOverrides = array_replace_recursive($this->instructionOverrides, $data);

        return $this;
    }

    /**
     * Replaces the paymentInstrument payload wholesale. Useful when the
     * default shape built by a subclass doesn't match your integration
     * (e.g. a wallet token format that differs from what this package
     * assumes) - you stay unblocked without forking the package.
     */
    public function withPaymentInstrument(array $data): static
    {
        $this->paymentInstrument = array_replace_recursive($this->paymentInstrument, $data);

        return $this;
    }

    public function createToken(bool $consumer = false): static
    {
        $this->instructionOverrides['tokenCreation'] = [
            'type' => $consumer ? 'consumer' : 'merchant',
        ];

        return $this;
    }

    protected function build(int $amount, string $currency, string $reference, ?string $orderReference, array $narrative): array
    {
        $this->transactionReference = $reference;
        $this->orderReference = $orderReference;

        $config = $this->client->config();
        $features = $config['features'] ?? [];

        $instruction = array_replace_recursive([
            'method' => $this->method(),
            'paymentInstrument' => $this->paymentInstrument,
            'narrative' => $narrative ?: ['line1' => $config['entity'] ?? 'default'],
            'value' => [
                'currency' => $currency,
                'amount' => $amount,
            ],
        ], $this->featureDefaults($features), $this->instructionOverrides);

        return array_filter([
            'transactionReference' => $this->transactionReference,
            'orderReference' => $this->orderReference,
            'merchant' => [
                'entity' => $this->client->entity(),
            ],
            'instruction' => $instruction,
        ], fn ($value) => $value !== null);
    }

    /**
     * Translates the package-level on/off feature toggles into the
     * `instruction` sub-objects Worldpay expects. Kept minimal by design -
     * fine-grained control (thresholds, exemption types, etc.) goes through
     * withInstruction() instead of a config flag per nested field.
     */
    protected function featureDefaults(array $features): array
    {
        $defaults = [];

        if ($features['fraudsight'] ?? false) {
            $defaults['fraud'] = ['fraudSightScoreOnly' => false];
        }

        if ($features['three_ds'] ?? false) {
            $defaults['threeDS'] = ['challengePreference' => 'noPreference'];
        }

        if ($features['auto_settle'] ?? false) {
            $defaults['settlement'] = ['auto' => true];
        }

        return $defaults;
    }

    protected function send(int $amount, string $currency, string $reference, ?string $orderReference = null, array $narrative = []): PaymentResponse
    {
        if (empty($this->paymentInstrument)) {
            throw new WorldpayValidationException(static::class.' has no paymentInstrument set - build it via the method-specific arguments or withPaymentInstrument().');
        }

        $payload = $this->build($amount, $currency, $reference, $orderReference, $narrative);
        $response = $this->client->post('/api/payments', $payload);

        return PaymentResponse::fromResponse($response, $this->client);
    }
}
