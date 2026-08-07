<?php

namespace App\Http\Controllers\Api\V1\Integrations;

use App\Http\Controllers\Controller;
use App\Jobs\ProcessAriCrmMirrorDelivery;
use App\Models\AriCrmMirrorDelivery;
use App\Models\AriCrmMirrorIntegration;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Validator;

class AriCrmMirrorMessageController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        $globalKey = 'aricrm-mirror:'.$request->ip();
        if (RateLimiter::tooManyAttempts($globalKey, 120)) {
            return response()->json(['message' => 'Too many requests.'], 429);
        }
        RateLimiter::hit($globalKey, 60);

        $rawBody = $request->getContent();
        $payload = json_decode($rawBody, true);
        $tenantId = is_array($payload) ? ($payload['tenant_id'] ?? null) : null;
        if (! is_array($payload) || ! is_string($tenantId) || ! $this->isUuid($tenantId)) {
            return response()->json(['message' => 'Invalid payload.'], 422);
        }

        $integration = AriCrmMirrorIntegration::query()->where('aricrm_tenant_id', $tenantId)->first();
        if (! $integration || ! $integration->is_active) {
            return response()->json(['message' => 'Unknown integration.'], 401);
        }

        $event = (string) $request->header('X-AriCRM-Event');
        $timestamp = (string) $request->header('X-AriCRM-Timestamp');
        $deliveryId = (string) $request->header('X-AriCRM-Delivery');
        if ($request->header('X-AriCRM-Webhook-Version') !== '1' || ! in_array($event, ['message.inbound', 'message.outbound'], true) || ! ctype_digit($timestamp) || abs(now()->timestamp - (int) $timestamp) > 300 || ! $this->isUuid($deliveryId) || ! $this->validSignature($integration, $timestamp, $rawBody, (string) $request->header('X-AriCRM-Signature'))) {
            return response()->json(['message' => 'Invalid webhook signature.'], 401);
        }

        $validator = Validator::make($payload, $this->rules());
        if ($validator->fails() || $payload['event'] !== $event || $payload['direction'] !== ($event === 'message.inbound' ? 'inbound' : 'outbound')) {
            return response()->json(['message' => 'Invalid webhook payload.'], 422);
        }

        $delivery = AriCrmMirrorDelivery::query()->firstOrCreate(
            ['integration_id' => $integration->id, 'delivery_id' => $deliveryId],
            ['event' => $event, 'aricrm_message_id' => $payload['message_id'], 'payload' => $payload, 'status' => 'accepted'],
        );
        if (! $delivery->wasRecentlyCreated) {
            return response()->json(['accepted' => true, 'duplicate' => true, 'status' => $delivery->status]);
        }

        ProcessAriCrmMirrorDelivery::dispatch($delivery->id)->onQueue('aricrm_mirror');

        return response()->json(['accepted' => true], 202);
    }

    private function validSignature(AriCrmMirrorIntegration $integration, string $timestamp, string $rawBody, string $header): bool
    {
        if (! preg_match('/^sha256=([a-f0-9]{64})$/', $header, $matches)) {
            return false;
        }
        $secrets = [$integration->secret_current];
        if ($integration->previous_secret_expires_at?->isFuture() && $integration->secret_previous) {
            $secrets[] = $integration->secret_previous;
        }
        foreach ($secrets as $secret) {
            if (hash_equals(hash_hmac('sha256', $timestamp.'.'.$rawBody, $secret), $matches[1])) {
                return true;
            }
        }

        return false;
    }

    private function isUuid(string $value): bool
    {
        return (bool) preg_match('/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/i', $value);
    }

    private function rules(): array
    {
        return ['event' => ['required', 'in:message.inbound,message.outbound'], 'tenant_id' => ['required', 'uuid'], 'contact_id' => ['required', 'uuid'], 'conversation_id' => ['required', 'uuid'], 'message_id' => ['required', 'uuid'], 'phone' => ['required', 'string', 'max:40'], 'name' => ['nullable', 'string', 'max:255'], 'direction' => ['required', 'in:inbound,outbound'], 'message_type' => ['required', 'in:text,image,audio,video,document,template,interactive'], 'body' => ['nullable', 'string'], 'author' => ['required', 'array'], 'author.type' => ['required', 'in:contact,user,ai,automation,system'], 'author.id' => ['nullable', 'uuid'], 'author.name' => ['nullable', 'string', 'max:255'], 'sent_at' => ['required', 'date'], 'occurred_at' => ['required', 'date'], 'metadata' => ['nullable', 'array'], 'media' => ['nullable', 'array']];
    }
}
