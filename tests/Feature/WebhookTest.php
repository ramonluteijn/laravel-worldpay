<?php

use Illuminate\Support\Facades\Event;
use RamonLuteijn\LaravelWorldpay\Events\WorldpayEventReceived;

it('does not register the webhook route when disabled', function () {
    config(['worldpay.webhooks.enabled' => false]);

    $this->postJson('/worldpay/webhooks', ['event' => 'authorized'])->assertNotFound();
});

it('dispatches WorldpayEventReceived when the webhook route is enabled', function () {
    // Routes are registered in the service provider's boot(), which has
    // already run by the time a test body executes - flipping config()
    // here is too late. Set the env var and reboot the app instead, so
    // config/worldpay.php picks it up fresh before boot() runs again.
    putenv('WORLDPAY_WEBHOOKS_ENABLED=true');
    $this->refreshApplication();

    Event::fake([WorldpayEventReceived::class]);

    $this->postJson('/worldpay/webhooks', ['event' => 'authorized', 'paymentId' => 'abc'])
        ->assertNoContent();

    Event::assertDispatched(WorldpayEventReceived::class, fn ($event) => $event->payload['paymentId'] === 'abc');

    putenv('WORLDPAY_WEBHOOKS_ENABLED');
});
