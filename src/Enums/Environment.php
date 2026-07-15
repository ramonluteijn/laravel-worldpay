<?php

namespace RamonLuteijn\LaravelWorldpay\Enums;

enum Environment: string
{
    case Try = 'try';
    case Live = 'live';

    public function baseUrl(): string
    {
        return match ($this) {
            self::Try => 'https://try.access.worldpay.com',
            self::Live => 'https://access.worldpay.com',
        };
    }
}
