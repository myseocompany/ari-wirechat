<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreWhatsAppCrmOutgoingRequest;
use App\Jobs\ProcessWhatsAppCrmOutgoingMessage;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;

class WhatsAppCrmOutgoingController extends Controller
{
    public function store(StoreWhatsAppCrmOutgoingRequest $request): JsonResponse
    {
        $validated = $request->validated();

        ProcessWhatsAppCrmOutgoingMessage::dispatch($validated)
            ->onQueue('whatsapp_outgoing');

        Log::info('WhatsApp CRM outgoing accepted and queued', [
            'external_message_id' => $validated['id'],
            'phone' => $validated['phone'],
            'type' => $validated['type'],
            'instance_key' => $validated['instance_key'] ?? null,
            'crm_user_id' => $validated['crm_user_id'] ?? null,
            'crm_customer_id' => $validated['crm_customer_id'] ?? null,
        ]);

        return response()->json([
            'message' => 'Mensaje WhatsApp CRM encolado',
        ], 202);
    }
}
