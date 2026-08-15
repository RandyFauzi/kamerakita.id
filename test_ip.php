<?php
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, 'https://kamerakitaid.site/api/mcp');
curl_setopt($ch, CURLOPT_RESOLVE, ['kamerakitaid.site:443:88.223.91.66']);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($ch, CURLOPT_POST, 1);
$payload = json_encode([
    'jsonrpc' => '2.0',
    'id' => 6,
    'method' => 'tools/call',
    'params' => [
        'name' => 'auto_reconcile_proportional',
        'arguments' => [
            'partner_id' => '019fe630-0785-726d-823b-3d484f36c1c0',
            'total_quota_minutes' => 312
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
