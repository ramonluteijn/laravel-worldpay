<?php

namespace RamonLuteijn\LaravelWorldpay;

use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use RamonLuteijn\LaravelWorldpay\Enums\Environment;
use RamonLuteijn\LaravelWorldpay\Exceptions\WorldpayAuthenticationException;
use RamonLuteijn\LaravelWorldpay\Exceptions\WorldpayException;

class WorldpayClient
{
    public function __construct(protected array $config)
    {
    }

    public function environment(): Environment
    {
        return Environment::from($this->config['environment'] ?? 'try');
    }

    public function entity(): string
    {
        return $this->config['entity'] ?? 'default';
    }

    public function config(): array
    {
        return $this->config;
    }

    public function post(string $uriOrUrl, array $payload): Response
    {
        return $this->handle($this->pendingRequest()->post($uriOrUrl, $payload));
    }

    public function get(string $uriOrUrl): Response
    {
        return $this->handle($this->pendingRequest()->get($uriOrUrl));
    }

    protected function pendingRequest(): PendingRequest
    {
        $credentials = $this->credentials();

        return Http::baseUrl($this->environment()->baseUrl())
            ->withBasicAuth($credentials['username'], $credentials['password'])
            ->withHeaders([
                'WP-Api-Version' => $this->config['api_version'] ?? '2024-06-01',
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
            ])
            ->timeout($this->config['timeout'] ?? 30);
    }

    protected function credentials(): array
    {
        $environment = $this->environment();
        $credentials = $this->config['credentials'][$environment->value] ?? [];

        if (empty($credentials['username']) || empty($credentials['password'])) {
            throw new WorldpayAuthenticationException(
                "No Worldpay credentials configured for the '{$environment->value}' environment. ".
                'Set WORLDPAY_'.strtoupper($environment->value).'_USERNAME and WORLDPAY_'.strtoupper($environment->value).'_PASSWORD.'
            );
        }

        return $credentials;
    }

    protected function handle(Response $response): Response
    {
        if ($response->failed()) {
            throw match ($response->status()) {
                401, 403 => WorldpayAuthenticationException::fromResponse($response),
                default => WorldpayException::fromResponse($response),
            };
        }

        return $response;
    }
}
