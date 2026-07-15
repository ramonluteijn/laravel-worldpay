<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Environment
    |--------------------------------------------------------------------------
    |
    | 'try' talks to https://try.access.worldpay.com (Worldpay's test
    | environment), 'live' talks to https://access.worldpay.com. Each has
    | its own credential pair below - Worldpay does not share credentials
    | between the two.
    |
    */

    'environment' => env('WORLDPAY_ENVIRONMENT', 'try'),

    /*
    |--------------------------------------------------------------------------
    | Merchant entity
    |--------------------------------------------------------------------------
    |
    | Identifies which of your onboarded Worldpay entities a request is for.
    | 'default' works for merchants with a single entity.
    |
    */

    'entity' => env('WORLDPAY_ENTITY', 'default'),

    /*
    |--------------------------------------------------------------------------
    | API version
    |--------------------------------------------------------------------------
    |
    | Sent as the WP-Api-Version header on every request, per Worldpay's
    | "latest standard" versioning scheme (a release date, not a semver).
    |
    */

    'api_version' => env('WORLDPAY_API_VERSION', '2024-06-01'),

    /*
    |--------------------------------------------------------------------------
    | Request timeout (seconds)
    |--------------------------------------------------------------------------
    */

    'timeout' => (int) env('WORLDPAY_TIMEOUT', 30),

    /*
    |--------------------------------------------------------------------------
    | Credentials
    |--------------------------------------------------------------------------
    |
    | Worldpay authenticates with a single HTTP Basic Auth pair per
    | environment. Leave the 'live' pair blank until you're actually ready
    | to go live - the client refuses to build a live request without them.
    |
    */

    'credentials' => [
        'try' => [
            'username' => env('WORLDPAY_TRY_USERNAME'),
            'password' => env('WORLDPAY_TRY_PASSWORD'),
        ],
        'live' => [
            'username' => env('WORLDPAY_LIVE_USERNAME'),
            'password' => env('WORLDPAY_LIVE_PASSWORD'),
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Default currency
    |--------------------------------------------------------------------------
    |
    | ISO 4217 currency code used when a request doesn't specify one.
    |
    */

    'default_currency' => env('WORLDPAY_DEFAULT_CURRENCY', 'EUR'),

    /*
    |--------------------------------------------------------------------------
    | Features
    |--------------------------------------------------------------------------
    |
    | Value-added services the Payments API can orchestrate alongside the
    | payment itself. Toggling these on adds the corresponding block
    | (riskAssessment / authentication / settlement) to every request built
    | through this package, without the caller having to know its shape.
    |
    */

    'features' => [
        'fraudsight' => (bool) env('WORLDPAY_FRAUDSIGHT_ENABLED', false),
        'three_ds' => (bool) env('WORLDPAY_3DS_ENABLED', false),
        'auto_settle' => (bool) env('WORLDPAY_AUTO_SETTLE', true),
    ],

    /*
    |--------------------------------------------------------------------------
    | Payment methods
    |--------------------------------------------------------------------------
    |
    | Each method must be explicitly enabled here before it can be used -
    | calling Worldpay::applePay() while it's disabled throws before any
    | HTTP call is made, so a half-configured wallet can't silently reach
    | production.
    |
    */

    'payment_methods' => [
        'card' => [
            'enabled' => (bool) env('WORLDPAY_METHOD_CARD_ENABLED', true),
        ],
        'applepay' => [
            'enabled' => (bool) env('WORLDPAY_METHOD_APPLEPAY_ENABLED', false),
        ],
        'googlepay' => [
            'enabled' => (bool) env('WORLDPAY_METHOD_GOOGLEPAY_ENABLED', false),
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Alternative Payment Methods (APMs)
    |--------------------------------------------------------------------------
    |
    | Every APM Worldpay's APMs API offers, each off by default. Enable the
    | ones your project actually needs from .env - Worldpay::apm('ideal')
    | throws for any key that's missing here or not enabled, so the .env
    | file is the single source of truth for which APMs are live.
    |
    | 'method' is the exact value Worldpay expects in instruction.method for
    | that APM. The single-word ones below are confirmed against Worldpay's
    | docs (docs.worldpay.com/access/products/apms/<key>, e.g. .../ideal);
    | the compound ones are a best-guess camelCase - double check the
    | dedicated guide for that APM before going live and adjust here if it
    | differs, since this is the only place that needs to change.
    |
    */

    'apms' => [
        'ach' => ['enabled' => (bool) env('WORLDPAY_APM_ACH_ENABLED', false), 'method' => 'ach'],
        'alipay' => ['enabled' => (bool) env('WORLDPAY_APM_ALIPAY_ENABLED', false), 'method' => 'alipaycn'],
        'alipay_hk' => ['enabled' => (bool) env('WORLDPAY_APM_ALIPAY_HK_ENABLED', false), 'method' => 'alipayhk'],
        'alipay_plus' => ['enabled' => (bool) env('WORLDPAY_APM_ALIPAY_PLUS_ENABLED', false), 'method' => 'alipayplus'],
        'bancomat_pay' => ['enabled' => (bool) env('WORLDPAY_APM_BANCOMAT_PAY_ENABLED', false), 'method' => 'bancomatpay'],
        'bancontact' => ['enabled' => (bool) env('WORLDPAY_APM_BANCONTACT_ENABLED', false), 'method' => 'bancontact'],
        'bizum' => ['enabled' => (bool) env('WORLDPAY_APM_BIZUM_ENABLED', false), 'method' => 'bizum'],
        'blik' => ['enabled' => (bool) env('WORLDPAY_APM_BLIK_ENABLED', false), 'method' => 'blik'],
        'china_unionpay' => ['enabled' => (bool) env('WORLDPAY_APM_CHINA_UNIONPAY_ENABLED', false), 'method' => 'chinaunionpay'],
        'eft' => ['enabled' => (bool) env('WORLDPAY_APM_EFT_ENABLED', false), 'method' => 'eft'],
        'euteller' => ['enabled' => (bool) env('WORLDPAY_APM_EUTELLER_ENABLED', false), 'method' => 'euteller'],
        'ideal' => ['enabled' => (bool) env('WORLDPAY_APM_IDEAL_ENABLED', false), 'method' => 'ideal'],
        'klarna' => ['enabled' => (bool) env('WORLDPAY_APM_KLARNA_ENABLED', false), 'method' => 'klarna'],
        'klarna_recurring' => ['enabled' => (bool) env('WORLDPAY_APM_KLARNA_RECURRING_ENABLED', false), 'method' => 'klarnarecurring'],
        'konbini' => ['enabled' => (bool) env('WORLDPAY_APM_KONBINI_ENABLED', false), 'method' => 'konbini'],
        'multibanco' => ['enabled' => (bool) env('WORLDPAY_APM_MULTIBANCO_ENABLED', false), 'method' => 'multibanco'],
        'mybank' => ['enabled' => (bool) env('WORLDPAY_APM_MYBANK_ENABLED', false), 'method' => 'mybank'],
        'open_banking' => ['enabled' => (bool) env('WORLDPAY_APM_OPEN_BANKING_ENABLED', false), 'method' => 'openbanking'],
        'paypal' => ['enabled' => (bool) env('WORLDPAY_APM_PAYPAL_ENABLED', false), 'method' => 'paypal'],
        'paysafecard' => ['enabled' => (bool) env('WORLDPAY_APM_PAYSAFECARD_ENABLED', false), 'method' => 'paysafecard'],
        'pix' => ['enabled' => (bool) env('WORLDPAY_APM_PIX_ENABLED', false), 'method' => 'pix'],
        'przelewy24' => ['enabled' => (bool) env('WORLDPAY_APM_PRZELEWY24_ENABLED', false), 'method' => 'przelewy24'],
        'safetypay' => ['enabled' => (bool) env('WORLDPAY_APM_SAFETYPAY_ENABLED', false), 'method' => 'safetypay'],
        'satispay' => ['enabled' => (bool) env('WORLDPAY_APM_SATISPAY_ENABLED', false), 'method' => 'satispay'],
        'sepa_direct_debit' => ['enabled' => (bool) env('WORLDPAY_APM_SEPA_DIRECT_DEBIT_ENABLED', false), 'method' => 'sepadirectdebit'],
        'swish' => ['enabled' => (bool) env('WORLDPAY_APM_SWISH_ENABLED', false), 'method' => 'swish'],
        'trustly' => ['enabled' => (bool) env('WORLDPAY_APM_TRUSTLY_ENABLED', false), 'method' => 'trustly'],
        'wechatpay' => ['enabled' => (bool) env('WORLDPAY_APM_WECHATPAY_ENABLED', false), 'method' => 'wechatpay'],
    ],

    /*
    |--------------------------------------------------------------------------
    | Webhooks
    |--------------------------------------------------------------------------
    |
    | Optional inbound route for Worldpay's payment/payout event
    | notifications. Off by default; enabling it registers
    | POST {route_prefix} which fires a WorldpayEventReceived event with the
    | raw payload for your app to handle.
    |
    */

    'webhooks' => [
        'enabled' => (bool) env('WORLDPAY_WEBHOOKS_ENABLED', false),
        'route_prefix' => env('WORLDPAY_WEBHOOKS_ROUTE_PREFIX', 'worldpay/webhooks'),
        'middleware' => ['api'],
    ],

];
