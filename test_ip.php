<?php
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, 'https://kamerakitaid.site/api/mcp');
curl_setopt($ch, CURLOPT_RESOLVE, ['kamerakitaid.site:443:88.223.91.66']);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($ch, CURLOPT_POST, 1);
$payload = json_encode([
    'jsonrpc' => '2.0',
    'id' => 2,
    'method' => 'tools/call',
    'params' => [
        'name' => 'aggregate_records',
        'arguments' => [
            'resource' => 'video_work_reports',
            'aggregations' => [
                'total_submitted' => ['sum' => 'submitted_duration_minutes'],
                'total_approved' => ['sum' => 'approved_duration_minutes']
            ],
            'filters' => [
                'partner_id' => '019fccbe-2ccc-722c-8437-35aa07002a61',
                'submission_date' => ['between' => ['2026-08-05', '2026-08-10']]
            ]
        ]
    ]
]);
curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
$headers = [
    'Authorization: Bearer kamerakita-mcp-2026',
    'Content-Type: application/json'
];
curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
$result = curl_exec($ch);
if (curl_errno($ch)) {
    echo 'Error:' . curl_error($ch);
}
curl_close($ch);
echo $result;
