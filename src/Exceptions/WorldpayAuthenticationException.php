<?php

namespace RamonLuteijn\LaravelWorldpay\Exceptions;

/**
 * Thrown when Worldpay responds with 401/403, or when the configured
 * environment has no credentials set for it.
 */
class WorldpayAuthenticationException extends WorldpayException
{
    //
}
