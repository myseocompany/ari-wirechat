<?php

return [
    'api_key' => env('OPENAI_API_KEY'),
    'base_url' => env('OPENAI_BASE_URL', 'https://api.openai.com/v1'),
    'model' => env('OPENAI_MODEL', 'gpt-4o-mini'),
    'opportunity_model' => env('OPENAI_OPPORTUNITY_MODEL', 'gpt-4.1-mini'),
    'call_briefing_model' => env('OPENAI_CALL_BRIEFING_MODEL', env('OPENAI_MODEL', 'gpt-4o-mini')),
    'call_briefing_note_limit' => (int) env('OPENAI_CALL_BRIEFING_NOTE_LIMIT', 1200),
    'call_briefing_context_limit' => (int) env('OPENAI_CALL_BRIEFING_CONTEXT_LIMIT', 18000),
    'timeout' => (int) env('OPENAI_TIMEOUT', 30),
];
