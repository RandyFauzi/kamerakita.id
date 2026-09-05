<?php

return [

    /*
    |--------------------------------------------------------------------------
    | MCP Clients Registration
    |--------------------------------------------------------------------------
    |
    | Daftarkan semua token klien yang memiliki akses ke MCP Server di sini.
    | Ini menggunakan model fail-closed. Jika token tidak ada di list ini,
    | koneksi akan otomatis ditolak (401 Unauthorized).
    |
    */

    'clients' => array_filter([
        
        // Admin Master Token (Bisa akses semua tools)
        env('MCP_SECRET_KEY') => [
            'name' => 'Admin Control Plane',
            'permissions' => ['*'], // * berarti semua akses (READ, WRITE, CRITICAL)
        ],

        // Anda bisa menambahkan agen lain di masa depan dengan batasan scope, contoh:
        /*
        env('MCP_READONLY_KEY') => [
            'name' => 'Analytics Agent',
            'permissions' => ['mcp.read'],
        ],
        */
        
    ], function($key) { return !empty($key); }, ARRAY_FILTER_USE_KEY),
    
    // Rate Limiting Config
    'rate_limit' => env('MCP_RATE_LIMIT', 60), // max requests per minute

];
