<?php

return [
    'verify_token' => env('WHATSAPP_VERIFY_TOKEN'),
    'app_secret' => env('WHATSAPP_APP_SECRET'),
    'system_user_id' => env('WHATSAPP_SYSTEM_USER_ID'),
    'sellerchat_webhook_url' => env('WHATSAPP_SELLERCHAT_WEBHOOK_URL'),
    'welcome_template' => env('WHATSAPP_WELCOME_TEMPLATE', 'drip_01'),
    'welcome_template_language' => env('WHATSAPP_WELCOME_TEMPLATE_LANGUAGE', 'en_US'),
    'meeting_reminder_template' => env('WHATSAPP_MEETING_REMINDER_TEMPLATE', '2026_feria_falta_1hora'),
];
