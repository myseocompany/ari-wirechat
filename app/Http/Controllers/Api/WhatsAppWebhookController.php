<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\WhatsAppWebhookRequest;
use App\Services\WhatsAppInboundMessageService;
use App\Services\WhatsAppWebhookForwarder;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;

class WhatsAppWebhookController extends Controller
{
    public function verify(Request $request): Response
    {
        $mode = $request->query('hub_mode', $request->query('hub.mode'));
        $token = $request->query('hub_verify_token', $request->query('hub.verify_token'));
        $challenge = $request->query('hub_challenge', $request->query('hub.challenge'));

        if ($mode === 'subscribe' && $token === config('whatsapp.verify_token')) {
            return response($challenge, 200);
        }

        return response('Invalid verification token.', 403);
    }

    public function receive(
        WhatsAppWebhookRequest $request,
        WhatsAppInboundMessageService $service,
        WhatsAppWebhookForwarder $forwarder
    ): Response {
        Log::info('WhatsApp webhook received', [
            'has_entry' => $request->has('entry'),
            'payload' => $request->all(),
        ]);

        $service->handle($request->all());
        $forwarder->forward($request->all());

        return response('OK', 200);
    }
}
