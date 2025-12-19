<?php

namespace App\Services;

class WhatsAppWebhookParser
{
    /**
     * @return array<int, array{external_message_id: string, wa_id: string, type: string, body: ?string, timestamp: int, raw_payload: array}>
     */
    public function parse(array $payload): array
    {
        $messages = [];
        $entries = $payload['entry'] ?? [];

        foreach ($entries as $entry) {
            $changes = $entry['changes'] ?? [];

            foreach ($changes as $change) {
                $value = $change['value'] ?? [];
                $incoming = $value['messages'] ?? [];

                foreach ($incoming as $message) {
                    $externalMessageId = $message['id'] ?? null;
                    $waId = $message['from'] ?? null;
                    $timestamp = $message['timestamp'] ?? null;

                    if (! $externalMessageId || ! $waId || ! $timestamp) {
                        continue;
                    }

                    $type = $message['type'] ?? 'text';
                    $body = $this->extractBody($message, $type);

                    $messages[] = [
                        'external_message_id' => $externalMessageId,
                        'wa_id' => $waId,
                        'type' => $type,
                        'body' => $body,
                        'timestamp' => (int) $timestamp,
                        'raw_payload' => $payload,
                    ];
                }
            }
        }

        return $messages;
    }

    private function extractBody(array $message, string $type): ?string
    {
        if ($type === 'text') {
            return $message['text']['body'] ?? null;
        }

        return $message[$type]['caption'] ?? null;
    }
}
