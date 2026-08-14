<?php
$url = 'https://kamerakitaid.site/api/mcp';
$data = [
    'jsonrpc' => '2.0',
    'id' => 1,
    'method' => 'tools/call',
    'params' => [
        'name' => 'fetch_records',
        'arguments' => [
            'resource' => 'users',
            'filters' => [
                'email' => 'diannn@web-library.net'
            ],
            'relations' => ['partner']
        ]
    ]
];

$options = [
    'http' => [
        'header'  => "Content-type: application/json\r\nAuthorization: Bearer kamerakita-mcp-2026\r\n",
        'method'  => 'POST',
        'content' => json_encode($data),
        'ignore_errors' => true
    ]
];

$context  = stream_context_create($options);
$result = file_get_contents($url, false, $context);
echo $result;
