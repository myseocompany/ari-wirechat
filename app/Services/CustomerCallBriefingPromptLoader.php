<?php

namespace App\Services;

use RuntimeException;

class CustomerCallBriefingPromptLoader
{
    /**
     * @return array{version: string, hash: string, system: string, user: string}
     */
    public function load(): array
    {
        $version = trim((string) app_config('customer_call_briefing_prompt_version', ''));

        if ($version === '') {
            throw new RuntimeException('missing_prompt_version');
        }

        $system = $this->normalize((string) app_config("customer_call_briefing_prompt_{$version}_system", ''));
        $user = $this->normalize((string) app_config("customer_call_briefing_prompt_{$version}_user", ''));

        if ($system === '' || $user === '') {
            throw new RuntimeException('missing_prompt_template');
        }

        preg_match_all('/{{[^}]+}}/', $user, $matches);
        if (substr_count($user, '{{context}}') !== 1 || count($matches[0]) !== 1) {
            throw new RuntimeException('invalid_prompt_placeholders');
        }

        return [
            'version' => $version,
            'hash' => hash('sha256', $system."\n---\n".$user),
            'system' => $system,
            'user' => $user,
        ];
    }

    /**
     * @param  array{version: string, hash: string, system: string, user: string}  $prompt
     */
    public function renderUser(array $prompt, string $context): string
    {
        return str_replace('{{context}}', $context, $prompt['user']);
    }

    private function normalize(string $value): string
    {
        $value = str_replace(["\r\n", "\r"], "\n", str_replace("\0", '', $value));

        if (! class_exists(\Normalizer::class)) {
            throw new RuntimeException('normalizer_unavailable');
        }

        return trim(\Normalizer::normalize($value, \Normalizer::FORM_C) ?: '');
    }
}
