# Laravel Worldpay

A Laravel wrapper for [Access Worldpay](https://docs.worldpay.com/access) — cards, Apple Pay, Google Pay, and Alternative Payment Methods (iDEAL, PayPal, Klarna, Bancontact, Bizum, and more), all toggleable per project from `.env`.

Targets Worldpay's modern REST **Payments API** (`/api/payments`) for card/Apple Pay/Google Pay and the **APMs API** (`/apmPayments`) for everything else.

## Installation

```bash
composer require ramonluteijn/laravel-worldpay
php artisan vendor:publish --tag=worldpay-config
```

## Configuration

Everything lives in `.env` — nothing needs to change in code between projects.

```dotenv
# Environment: try (test) or live
WORLDPAY_ENVIRONMENT=try
WORLDPAY_ENTITY=default
WORLDPAY_API_VERSION=2024-06-01
WORLDPAY_TIMEOUT=30
WORLDPAY_DEFAULT_CURRENCY=EUR

# Credentials — separate pairs per environment, from your Worldpay Implementation Manager
WORLDPAY_TRY_USERNAME=
WORLDPAY_TRY_PASSWORD=
WORLDPAY_LIVE_USERNAME=
WORLDPAY_LIVE_PASSWORD=

# Value-added features orchestrated by the Payments API
WORLDPAY_FRAUDSIGHT_ENABLED=false
WORLDPAY_3DS_ENABLED=false
WORLDPAY_AUTO_SETTLE=true

# Payment methods — each must be explicitly enabled
WORLDPAY_METHOD_CARD_ENABLED=true
WORLDPAY_METHOD_APPLEPAY_ENABLED=false
WORLDPAY_METHOD_GOOGLEPAY_ENABLED=false

# Alternative Payment Methods — enable only what you need
WORLDPAY_APM_IDEAL_ENABLED=false
WORLDPAY_APM_PAYPAL_ENABLED=false
WORLDPAY_APM_KLARNA_ENABLED=false
WORLDPAY_APM_BANCONTACT_ENABLED=false
WORLDPAY_APM_BIZUM_ENABLED=false
WORLDPAY_APM_BLIK_ENABLED=false
WORLDPAY_APM_SEPA_DIRECT_DEBIT_ENABLED=false
WORLDPAY_APM_SWISH_ENABLED=false
WORLDPAY_APM_TRUSTLY_ENABLED=false
WORLDPAY_APM_PRZELEWY24_ENABLED=false
WORLDPAY_APM_WECHATPAY_ENABLED=false
WORLDPAY_APM_ALIPAY_ENABLED=false
# ... see config/worldpay.php for the full APM catalogue (25+ methods)

# Optional inbound webhook for Worldpay event notifications
WORLDPAY_WEBHOOKS_ENABLED=false
WORLDPAY_WEBHOOKS_ROUTE_PREFIX=worldpay/webhooks
```

A payment method or APM that isn't enabled throws a `WorldpayValidationException` *before* any HTTP call is made — a half-configured wallet can't silently leak into production.

## Usage

### Card payment

```php
use RamonLuteijn\LaravelWorldpay\Facades\Worldpay;

$response = Worldpay::card()->pay(
    amount: 4200, // minor units, e.g. cents
    card: [
        'cardHolderName' => 'Sherlock Holmes',
        'cardNumber' => '4000000000002701',
        'expiryDate' => ['month' => 5, 'year' => 2035],
        'cvc' => '123',
        'billingAddress' => [
            'address1' => '221B Baker Street',
            'postalCode' => 'SW1 1AA',
            'city' => 'London',
            'countryCode' => 'GB',
        ],
    ],
    reference: (string) $order->id,
);

$response->paymentId();
$response->outcome();      // e.g. "authorized"
$response->correlationId(); // log this for Worldpay support requests
```

### Stored card / token

```php
Worldpay::card()->payWithToken(amount: 4200, tokenHref: $storedToken, reference: $ref);
```

### Apple Pay / Google Pay

```php
Worldpay::applePay()->pay(amount: 4200, paymentData: $applePayToken, reference: $ref);
Worldpay::googlePay()->pay(amount: 4200, paymentData: $googlePayToken, reference: $ref);
```

> Worldpay's exact wallet `paymentInstrument` field names can vary by integration — verify against Worldpay's current wallet guide. If it doesn't match this package's default shape, override it with `->withPaymentInstrument([...])` before calling `pay()`.

### Alternative Payment Methods (APM)

```php
$response = Worldpay::apm('ideal')->pay(
    amount: 4200,
    paymentInstrument: [
        'type' => 'directDebit',
        'shopperBankCode' => 'ABNANL2A',
    ],
    customer: [
        'firstName' => 'Sherlock',
        'lastName' => 'Holmes',
        'email' => 'sherlock@example.com',
    ],
    reference: $ref,
);

$response->redirectUrl(); // redirect the customer here to complete payment
```

`paymentInstrument`/`customer` keys are APM-specific — see Worldpay's per-APM guide at `docs.worldpay.com/access/products/apms/<method>` (e.g. `.../ideal`, `.../paypal`) for the exact fields your chosen method expects.

### Managing an existing payment

Both APIs return hypermedia `_actions` links; `PaymentResponse` follows them for you:

```php
$response->cancel();
$response->settle();               // full settlement
$response->settle(amount: 1000);   // partial settlement
$response->refund();
$response->action('someOtherAction', ['...']); // escape hatch for anything not wrapped above
```

### Extending a request

Every `Payments API` request builder (`card()`, `applePay()`, `googlePay()`) supports:

```php
Worldpay::card()
    ->withInstruction(['customer' => ['email' => $email]]) // merges into `instruction`
    ->withPaymentInstrument(['someField' => 'value'])       // merges into `paymentInstrument`
    ->createToken(consumer: true)                            // store the card after this payment
    ->pay(amount: 4200, card: [...], reference: $ref);
```

### Webhooks

Enable `WORLDPAY_WEBHOOKS_ENABLED=true`, point Worldpay's event notification config at `POST /{WORLDPAY_WEBHOOKS_ROUTE_PREFIX}`, then listen for the raw payload:

```php
use RamonLuteijn\LaravelWorldpay\Events\WorldpayEventReceived;

Event::listen(function (WorldpayEventReceived $event) {
    // $event->payload
});
```

## Testing

```bash
composer test
```

Uses Orchestra Testbench + `Http::fake()` — no live Worldpay credentials required.
