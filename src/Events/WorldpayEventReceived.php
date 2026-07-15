<?php

namespace RamonLuteijn\LaravelWorldpay\Events;

use Illuminate\Foundation\Events\Dispatchable;

class WorldpayEventReceived
{
    use Dispatchable;

    public function __construct(public readonly array $payload)
    {
    }
}
