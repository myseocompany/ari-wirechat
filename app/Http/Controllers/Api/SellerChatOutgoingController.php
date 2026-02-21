<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreSellerChatOutgoingRequest;
use App\Jobs\ProcessSellerChatOutgoingMessage;
use Illuminate\Http\JsonResponse;

class SellerChatOutgoingController extends Controller
{
    public function store(StoreSellerChatOutgoingRequest $request): JsonResponse
    {
        ProcessSellerChatOutgoingMessage::dispatch($request->validated())
            ->onQueue('sellerchat');

        return response()->json([
            'message' => 'Mensaje sellerChat encolado',
        ], 202);
    }
}
