<?php

namespace App\Services;

class GoogleAdsLeadMapper
{
    private const STANDARD_COLUMN_IDS = [
        'FULL_NAME',
        'PHONE_NUMBER',
        'EMAIL',
        'CITY',
        'COUNTRY',
    ];

    public function map(array $payload): array
    {
        $columns = $this->extractColumns($payload['user_column_data'] ?? []);

        $mapped = [
            'google_lead_id' => $this->stringOrNull($payload['lead_id'] ?? null),
            'name' => $columns['standard']['FULL_NAME'] ?? null,
            'phone' => $columns['standard']['PHONE_NUMBER'] ?? null,
            'email' => $columns['standard']['EMAIL'] ?? null,
            'city' => $columns['standard']['CITY'] ?? null,
            'country' => $columns['standard']['COUNTRY'] ?? null,
            'utm_source' => 'google_ads',
            'campaign_name' => $this->stringOrNull($payload['campaign_id'] ?? null),
            'adset_name' => $this->stringOrNull($payload['adgroup_id'] ?? null),
            'ad_name' => $this->stringOrNull($payload['creative_id'] ?? null),
        ];

        $mapped['notes'] = $this->buildNotes($payload, $columns['custom']);

        return array_filter(
            $mapped,
            fn ($value) => $value !== null && $value !== ''
        );
    }

    private function extractColumns(array $columns): array
    {
        $standard = [];
        $custom = [];

        foreach ($columns as $column) {
            if (! is_array($column)) {
                continue;
            }

            $columnId = $this->stringOrNull($column['column_id'] ?? null);
            $value = $this->stringOrNull($column['string_value'] ?? null);

            if ($columnId === null || $value === null) {
                continue;
            }

            $label = $this->stringOrNull($column['column_name'] ?? null) ?? $columnId;

            if (in_array($columnId, self::STANDARD_COLUMN_IDS, true)) {
                $standard[$columnId] = $value;

                continue;
            }

            $custom[] = [
                'label' => $label,
                'value' => $value,
            ];
        }

        return [
            'standard' => $standard,
            'custom' => $custom,
        ];
    }

    private function buildNotes(array $payload, array $customColumns): string
    {
        $segments = array_filter([
            'Google Ads',
            $this->formatSegment('lead_id', $payload['lead_id'] ?? null),
            $this->formatSegment('gcl_id', $payload['gcl_id'] ?? null),
            $this->formatSegment('form_id', $payload['form_id'] ?? null),
            $this->formatSegment('campaign_id', $payload['campaign_id'] ?? null),
            $this->formatSegment('adgroup_id', $payload['adgroup_id'] ?? null),
            $this->formatSegment('creative_id', $payload['creative_id'] ?? null),
            $this->formatSegment('api_version', $payload['api_version'] ?? null),
            $this->formatSegment('is_test', $payload['is_test'] ?? null),
        ]);

        foreach ($customColumns as $customColumn) {
            $label = $this->stringOrNull($customColumn['label'] ?? null);
            $value = $this->stringOrNull($customColumn['value'] ?? null);

            if ($label === null || $value === null) {
                continue;
            }

            $segments[] = "{$label}: {$value}";
        }

        return implode(' | ', $segments);
    }

    private function formatSegment(string $label, mixed $value): ?string
    {
        $stringValue = $this->stringOrNull($value);

        if ($stringValue === null) {
            return null;
        }

        return "{$label}: {$stringValue}";
    }

    private function stringOrNull(mixed $value): ?string
    {
        if ($value === null) {
            return null;
        }

        if (is_bool($value)) {
            return $value ? 'true' : 'false';
        }

        $stringValue = trim((string) $value);

        return $stringValue === '' ? null : $stringValue;
    }
}
