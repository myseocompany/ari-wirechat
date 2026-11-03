<?php

return [
    // Audiencia fija para las respuestas del formulario POE (nunca viene del request).
    'audience_id' => 67,

    // Enlaces sin firma (los ya enviados por WhatsApp) siguen válidos hasta esta fecha.
    // Después de ella solo funcionan los enlaces firmados del botón "POA".
    'legacy_links_until' => env('POE_LEGACY_LINKS_UNTIL', '2026-12-10'),

    'submissions_per_minute' => (int) env('POE_SUBMISSIONS_PER_MINUTE', 5),

    'max_value_length' => 2000,
];
