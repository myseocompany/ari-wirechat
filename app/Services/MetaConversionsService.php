<?php

namespace App\Services;

use App\Models\Customer;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class MetaConversionsService
{
    private string $datasetId;
    private string $accessToken;
    private ?string $testEventCode;

    public function __construct()
    {
        $config = config('services.meta_conversions', []);
        $this->datasetId = (string) ($config['dataset_id'] ?? '');
        $this->accessToken = (string) ($config['access_token'] ?? '');
        $this->testEventCode = $config['test_event_code'] ?? null;
    }

    public function isEnabled(): bool
    {
        return $this->datasetId !== '' && $this->accessToken !== '';
    }

    public function sendLeadEvent(
        Customer $customer,
        string $eventName,
        ?int $eventTime = null,
        array $customData = [],
        array $extraUserData = []
    ): ?array {
        if (! $this->isEnabled()) {
            Log::warning('MetaConversionsService disabled, skipping event dispatch', [
                'event_name' => $eventName,
            ]);
            return null;
        }

        $payload = $this->buildRequestPayload($customer, $eventName, $eventTime, $customData, $extraUserData);

        $response = Http::withToken($this->accessToken)
            ->acceptJson()
            ->asJson()
            ->post($this->getEndpoint(), $payload);

        if ($response->failed()) {
            Log::error('MetaConversionsService sendLeadEvent failed', [
                'status' => $response->status(),
                'body' => $response->body(),
                'payload' => $payload,
            ]);
            $response->throw();
        }

        $json = $response->json();
        Log::info('MetaConversionsService sendLeadEvent succeeded', [
            'event_name' => $eventName,
            'endpoint' => $this->getEndpoint(),
            'dataset_id' => $this->datasetId,
            'response' => $json,
        ]);

        return $json;
    }

    public function buildRequestPayload(
        Customer $customer,
        string $eventName,
        ?int $eventTime = null,
        array $customData = [],
        array $extraUserData = []
    ): array {
        $event = $this->buildEventPayload($customer, $eventName, $eventTime, $customData, $extraUserData);

        $payload = [
            'data' => [$event],
        ];

        if ($this->testEventCode) {
            $payload['test_event_code'] = $this->testEventCode;
        }

        return $payload;
    }

    public function buildEventPayload(
        Customer $customer,
        string $eventName,
        ?int $eventTime = null,
        array $customData = [],
        array $extraUserData = []
    ): array {
        return [
            'event_name' => $eventName,
            'event_time' => $eventTime ?? now()->timestamp,
            'action_source' => 'system_generated',
            'custom_data' => $this->buildCustomData($customData),
            'user_data' => $this->buildUserData($customer, $extraUserData),
        ];
    }

    public function getEndpoint(): string
    {
        return "https://graph.facebook.com/v19.0/{$this->datasetId}/events";
    }

    private function buildCustomData(array $customData): array
    {
        return array_merge([
            'event_source' => 'crm',
        ], $customData);
    }

    private function buildUserData(Customer $customer, array $extra = []): array
    {
        [$firstName, $lastName] = $this->splitName($customer->name ?? '');

        $userData = [
            'em' => $this->hashArray($this->extractEmails($customer)),
            'ph' => $this->hashArray($this->extractPhones($customer)),
            'fn' => $this->hashString($firstName),
            'ln' => $this->hashString($lastName),
            'ct' => $this->hashString($customer->city ?? null),
            'st' => $this->hashString($customer->department ?? null),
            'country' => $this->hashString($customer->country ?? null),
        ];

        if ($customer->facebook_id) {
            $userData['lead_id'] = (string) $customer->facebook_id;
        }

        $userData = array_merge($userData, $extra);

        return Arr::where($userData, function ($value) {
            if (is_array($value)) {
                return ! empty($value);
            }
            return $value !== null && $value !== '';
        });
    }

    private function extractEmails(Customer $customer): array
    {
        $emails = [
            $customer->email ?? null,
            $customer->contact_email ?? null,
        ];

        return array_values(array_filter(array_unique($emails), function ($value) {
            return is_string($value) && filter_var($value, FILTER_VALIDATE_EMAIL);
        }));
    }

    private function extractPhones(Customer $customer): array
    {
        $phones = [
            $customer->phone ?? null,
            $customer->phone2 ?? null,
            $customer->contact_phone2 ?? null,
        ];

        $normalized = [];
        foreach ($phones as $phone) {
            $clean = $this->normalizePhone($phone);
            if ($clean) {
                $normalized[] = $clean;
            }
        }

        return array_values(array_unique($normalized));
    }

    private function normalizePhone(?string $phone): ?string
    {
        if (! is_string($phone)) {
            return null;
        }

        $digits = preg_replace('/\D+/', '', $phone);
        return strlen((string) $digits) >= 6 ? $digits : null;
    }

    private function splitName(?string $name): array
    {
        $name = trim((string) $name);
        if ($name === '') {
            return ['', ''];
        }

        $parts = preg_split('/\s+/', $name, 2) ?: [];
        $first = $parts[0] ?? '';
        $last = $parts[1] ?? '';

        return [$first, $last];
    }

    private function hashArray(array $values): array
    {
        $hashed = [];
        foreach ($values as $value) {
            $hash = $this->hashString($value);
            if ($hash !== null) {
                $hashed[] = $hash;
            }
        }

        return $hashed;
    }

    private function hashString(?string $value): ?string
    {
        if (! is_string($value)) {
            return null;
        }

        $normalized = trim(Str::lower($value));
        return $normalized === '' ? null : hash('sha256', $normalized);
    }
}
