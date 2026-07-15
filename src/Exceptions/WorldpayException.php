<?php

namespace RamonLuteijn\LaravelWorldpay\Exceptions;

use Exception;
use Illuminate\Http\Client\Response;

class WorldpayException extends Exception
{
    public function __construct(
        string $message,
        protected readonly ?Response $response = null,
        protected readonly ?string $correlationId = null,
    ) {
        parent::__construct($message, $response?->status() ?? 0);
    }

    public static function fromResponse(Response $response): static
    {
        $body = $response->json();
        $message = $body['message'] ?? $body['description'] ?? 'Worldpay request failed with status '.$response->status();

        return new static($message, $response, $response->header('WP-CorrelationId'));
    }

    public function correlationId(): ?string
    {
        return $this->correlationId;
    }

    public function errorBody(): ?array
    {
        return $this->response?->json();
    }

    public function response(): ?Response
    {
        return $this->response;
    }
}
