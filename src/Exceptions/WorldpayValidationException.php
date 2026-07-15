<?php

namespace RamonLuteijn\LaravelWorldpay\Exceptions;

/**
 * Thrown for request-shape problems caught before an HTTP call is made -
 * e.g. a disabled payment method/APM, or a missing required field.
 */
class WorldpayValidationException extends WorldpayException
{
    public function __construct(string $message)
    {
        parent::__construct($message);
    }
}
