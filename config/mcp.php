<?php

return [
    'enabled' => env('MCP_ENABLED', true),
    'oauth_enabled' => env('MCP_OAUTH_ENABLED', true),
    'token_prefix' => env('MCP_TOKEN_PREFIX', 'kkm_'),
    'rate_limit' => env('MCP_RATE_LIMIT', 120),

    /*
    |--------------------------------------------------------------------------
    | Laravel MCP OAuth Settings
    |--------------------------------------------------------------------------
    */
    'redirect_domains' => [
        '*',
    ],

    'custom_schemes' => [
        'claude',
        'cursor',
        'vscode',
    ],

    'authorization_server' => env('MCP_AUTHORIZATION_SERVER'),

    'tool_search' => [
        'max_tool_calls' => 10,
        'max_output_bytes' => 65_536,
    ],

    /*
    |--------------------------------------------------------------------------
    | Dynamic Role -> Ability Mapping
    |--------------------------------------------------------------------------
    | Abilities evaluated dynamically via McpAccess::allows() so new tools
    | follow the user's current role without regenerating existing tokens.
    */
    'role_abilities' => [
        'superadmin' => ['*'],
        'admin' => ['mcp.read', 'mcp.write', 'mcp.critical'],
        'finance' => ['mcp.read'],
    ],

    /*
    |--------------------------------------------------------------------------
    | Legacy Static Clients (Optional Fallback)
    |--------------------------------------------------------------------------
    */
    'clients' => array_filter([
        env('MCP_SECRET_KEY') => [
            'name' => 'Legacy Admin Control Plane',
            'permissions' => ['*'],
        ],
    ], fn ($key) => ! empty($key), ARRAY_FILTER_USE_KEY),
];
