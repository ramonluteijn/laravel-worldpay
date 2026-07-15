<?php

namespace RamonLuteijn\LaravelWorldpay\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use RamonLuteijn\LaravelWorldpay\Events\WorldpayEventReceived;

/**
 * Worldpay's event notifications don't have a single fixed payload shape
 * across every event type, so this just forwards the decoded body as a
 * WorldpayEventReceived event - listen for it in your app to act on
 * specific event types.
 */
class WorldpayWebhookController extends Controller
{
    public function __invoke(Request $request): Response
    {
        WorldpayEventReceived::dispatch($request->json()->all());

        return response()->noContent();
    }
}
