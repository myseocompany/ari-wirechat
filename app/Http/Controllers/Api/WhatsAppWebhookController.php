<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\WhatsAppWebhookRequest;
use App\Services\WhatsAppInboundMessageService;
use App\Services\WhatsAppWebhookForwarder;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class WhatsAppWebhookController extends Controller
{
    public function verify(Request $request): Response
    {
        $mode = $request->query('hub_mode', $request->query('hub.mode'));
        $token = $request->query('hub_verify_token', $request->query('hub.verify_token'));
        $challenge = $request->query('hub_challenge', $request->query('hub.challenge'));

        Log::info('WhatsApp webhook verify', [
            'mode' => $mode,
            'token' => $token,
            'expected_token' => config('whatsapp.verify_token'),
            'challenge' => $challenge,
            'query' => $request->query(),
        ]);

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
            'raw' => Str::limit($request->getContent(), 5000),
            'signature_present' => $request->hasHeader('X-Hub-Signature-256'),
        ]);

        $service->handle($request->all());
        $forwarder->forward(
            $request->getContent(),
            array_filter([
                'X-Hub-Signature-256' => $request->header('X-Hub-Signature-256'),
                'X-Hub-Signature' => $request->header('X-Hub-Signature'),
                'User-Agent' => $request->header('User-Agent'),
                'X-Forwarded-For' => $request->header('X-Forwarded-For') ?? $request->ip(),
            ])
        );

        return response('OK', 200);
    }
}
