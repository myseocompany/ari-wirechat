<?php

namespace App\Services;

use InvalidArgumentException;

class TwilioAccessTokenFactory
{
    public function makeVoiceToken(string $identity): string
    {
        if (! $this->isTwilioEnabled()) {
            throw new InvalidArgumentException('Twilio está deshabilitado en la configuración.');
        }

        $accountSid = (string) config('services.twilio.account_sid');
        $apiKeySid = (string) config('services.twilio.api_key_sid');
        $apiKeySecret = (string) config('services.twilio.api_key_secret');
        $twimlAppSid = (string) config('services.twilio.twiml_app_sid');
        $ttl = (int) config('services.twilio.token_ttl', 3600);

        if ($accountSid === '' || $apiKeySid === '' || $apiKeySecret === '' || $twimlAppSid === '') {
            throw new InvalidArgumentException('La configuración de Twilio Voice está incompleta.');
        }

        $issuedAt = time();
        $expiresAt = $issuedAt + max(60, min($ttl, 86400));

        $header = [
            'alg' => 'HS256',
            'typ' => 'JWT',
            'cty' => 'twilio-fpa;v=1',
        ];

        $payload = [
            'jti' => $apiKeySid.'-'.bin2hex(random_bytes(8)),
            'iss' => $apiKeySid,
            'sub' => $accountSid,
            'exp' => $expiresAt,
            'grants' => [
                'identity' => $identity,
                'voice' => [
                    'incoming' => ['allow' => true],
                    'outgoing' => ['application_sid' => $twimlAppSid],
                ],
            ],
        ];

        return $this->encode($header, $payload, $apiKeySecret);
    }

    /**
     * @param  array<string, mixed>  $header
     * @param  array<string, mixed>  $payload
     */
    protected function encode(array $header, array $payload, string $secret): string
    {
        $encodedHeader = $this->base64UrlEncode(json_encode($header, JSON_THROW_ON_ERROR));
        $encodedPayload = $this->base64UrlEncode(json_encode($payload, JSON_THROW_ON_ERROR));
        $signature = hash_hmac('sha256', $encodedHeader.'.'.$encodedPayload, $secret, true);
        $encodedSignature = $this->base64UrlEncode($signature);

        return $encodedHeader.'.'.$encodedPayload.'.'.$encodedSignature;
    }

    protected function base64UrlEncode(string $value): string
    {
        return rtrim(strtr(base64_encode($value), '+/', '-_'), '=');
    }

    private function isTwilioEnabled(): bool
    {
        return app_feature_enabled('twilio_enabled', (bool) config('services.twilio.enabled', true));
    }
}
