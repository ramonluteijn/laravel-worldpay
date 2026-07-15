<?php

namespace RamonLuteijn\LaravelWorldpay\Responses;

use Illuminate\Http\Client\Response;
use RamonLuteijn\LaravelWorldpay\Exceptions\WorldpayValidationException;
use RamonLuteijn\LaravelWorldpay\WorldpayClient;

/**
 * Wraps a Payments API or APMs API response. Both APIs return a `_actions`
 * hypermedia map, just under different key names (e.g. `cancelPayment` vs
 * `reverse`) - the convenience methods below try both so callers don't need
 * to know which underlying API produced this response.
 */
class PaymentResponse
{
    protected array $data;

    protected array $actions;

    public function __construct(protected WorldpayClient $client, protected Response $response)
    {
        $this->data = $response->json() ?? [];
        $this->actions = $this->data['_actions'] ?? [];
    }

    public static function fromResponse(Response $response, WorldpayClient $client): static
    {
        return new static($client, $response);
    }

    public function paymentId(): ?string
    {
        return $this->data['paymentId'] ?? null;
    }

    public function outcome(): ?string
    {
        return $this->data['outcome'] ?? null;
    }

    public function lastEvent(): ?string
    {
        return $this->data['lastEvent'] ?? null;
    }

    /**
     * APM payments only: URL to redirect the customer to (or a base64 QR
     * code) to complete the payment on the APM's own site/app.
     */
    public function redirectUrl(): ?string
    {
        return $this->data['redirect'] ?? null;
    }

    public function correlationId(): ?string
    {
        return $this->response->header('WP-CorrelationId');
    }

    public function raw(): array
    {
        return $this->data;
    }

    public function hasAction(string $name): bool
    {
        return isset($this->actions[$name]);
    }

    public function cancel(): static
    {
        return $this->performFirstAvailable(['cancelPayment', 'reverse']);
    }

    public function settle(?int $amount = null, array $payload = []): static
    {
        if ($amount !== null) {
            return $this->performFirstAvailable(['partiallySettlePayment', 'partialSettle'], array_replace_recursive(['value' => ['amount' => $amount]], $payload));
        }

        return $this->performFirstAvailable(['settlePayment', 'settle'], $payload);
    }

    public function refund(?int $amount = null, array $payload = []): static
    {
        if ($amount !== null) {
            return $this->performFirstAvailable(['partiallyRefundPayment'], array_replace_recursive(['value' => ['amount' => $amount]], $payload));
        }

        return $this->performFirstAvailable(['refundPayment'], $payload);
    }

    public function confirm(array $payload = []): static
    {
        return $this->performFirstAvailable(['confirmPayment'], $payload);
    }

    /**
     * Escape hatch: call any `_actions` entry by its exact key name, with
     * whatever payload that action needs.
     */
    public function action(string $name, array $payload = []): static
    {
        if (! isset($this->actions[$name])) {
            throw new WorldpayValidationException("Action '{$name}' is not available for this payment. Available actions: ".implode(', ', array_keys($this->actions)));
        }

        $response = $this->client->post($this->actions[$name]['href'], $payload);

        return static::fromResponse($response, $this->client);
    }

    protected function performFirstAvailable(array $names, array $payload = []): static
    {
        foreach ($names as $name) {
            if (isset($this->actions[$name])) {
                return $this->action($name, $payload);
            }
        }

        throw new WorldpayValidationException('None of the actions ['.implode(', ', $names).'] are available for this payment.');
    }
}
