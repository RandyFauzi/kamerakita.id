<?php
$payload = [
    'method' => 'tools/call',
    'params' => [
        'name' => 'aggregate_records',
        'arguments' => [
            'resource' => 'video_work_reports',
            'aggregations' => [
                'total_submitted' => ['sum' => 'submitted_duration_minutes']
            ],
            'filters' => [
                'submission_date' => ['between' => ['2026-08-05', '2026-08-10']]
            ]
        ]
    ]
];

$ch = curl_init('http://127.0.0.1/api/mcp');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
    'Authorization: Bearer kamerakita-mcp-2026'
]);
$result = curl_exec($ch);
echo $result;
