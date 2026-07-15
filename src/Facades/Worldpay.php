<?php

namespace RamonLuteijn\LaravelWorldpay\Facades;

use Illuminate\Support\Facades\Facade;
use RamonLuteijn\LaravelWorldpay\Requests\ApmPaymentRequest;
use RamonLuteijn\LaravelWorldpay\Requests\ApplePayPaymentRequest;
use RamonLuteijn\LaravelWorldpay\Requests\CardPaymentRequest;
use RamonLuteijn\LaravelWorldpay\Requests\GooglePayPaymentRequest;
use RamonLuteijn\LaravelWorldpay\WorldpayClient;

/**
 * @method static CardPaymentRequest card()
 * @method static ApplePayPaymentRequest applePay()
 * @method static GooglePayPaymentRequest googlePay()
 * @method static ApmPaymentRequest apm(string $method)
 * @method static WorldpayClient client()
 *
 * @see \RamonLuteijn\LaravelWorldpay\WorldpayManager
 */
class Worldpay extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return \RamonLuteijn\LaravelWorldpay\WorldpayManager::class;
    }
}
