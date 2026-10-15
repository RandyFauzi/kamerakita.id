<?php

return [
    // Public, curated snapshots only. Never combine Atlas recordings with manual
    // reports: they can describe the same production and would double-count it.
    'verified_at' => '2026-10-15',
    'atlas' => [
        'recorded_minutes' => 151751, // User-provided Atlas dashboard: 2,529h 11m.
        'approved_minutes' => 112415, // Same dashboard: 1,873h 35m.
    ],
    // Read-only aggregate_records queries against the production MCP endpoint.
    'registered_contributors' => 529,
    'reports' => [
        'count' => 1174,
        'submitted_minutes' => 209678,
        'approved_minutes' => 43716,
        'approved_count' => 392,
        'finalized_count' => 405,
        'monthly_hours' => ['2026-07' => 445.42, '2026-08' => 385.52, '2026-09' => 1162.33, '2026-10' => 1501.37],
    ],
    'minute_data' => ['submitted_minutes' => 38563, 'approved_minutes' => 32447],
    'monthly_target_hours' => 2000, // Commercial target, not historical output.
    'contact_whatsapp' => env('PORTFOLIO_WHATSAPP', '6287886272647'),
];
