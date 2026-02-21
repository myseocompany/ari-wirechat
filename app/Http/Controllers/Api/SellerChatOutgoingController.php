<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreSellerChatOutgoingRequest;
use App\Jobs\ProcessSellerChatOutgoingMessage;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;

class SellerChatOutgoingController extends Controller
{
    public function store(StoreSellerChatOutgoingRequest $request): JsonResponse
    {
        $validated = $request->validated();

        ProcessSellerChatOutgoingMessage::dispatch($validated)
            ->onQueue('sellerchat');

        Log::info('SellerChat outgoing accepted and queued', [
            'external_message_id' => $validated['id'],
            'phone' => $validated['phone'],
            'type' => $validated['type'],
            'crm_user_id' => $validated['crm_user_id'] ?? null,
        ]);

        return response()->json([
            'message' => 'Mensaje sellerChat encolado',
        ], 202);
    }
}
