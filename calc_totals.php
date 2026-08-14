<?php
$users_json = json_decode(file_get_contents('C:/Users/LENOVO/.gemini/antigravity/brain/1021b0cd-1056-4f28-b7c0-385585949fe9/.system_generated/steps/100/output.txt'), true);
$reports_json = json_decode(file_get_contents('C:/Users/LENOVO/.gemini/antigravity/brain/1021b0cd-1056-4f28-b7c0-385585949fe9/.system_generated/steps/25/output.txt'), true);

$emails = [
    'diannn@web-library.net',
    'echa17@web-library.net',
    'imul20@web-library.net',
    'imust@web-library.net',
    'indirarara@web-library.net',
    'jessicaapril@web-library.net',
    'maulana0612@web-library.net',
    'nugadp@web-library.net',
    'pta@web-library.net',
    'puanpuan@web-library.net',
    'rlylittley@web-library.net',
    'wawawiwi30@web-library.net',
    'zulianaizzatul@web-library.net'
];

$userMap = []; // partner_id => email
foreach ($users_json as $u) {
    if (in_array($u['email'], $emails) && !empty($u['partner']['id'])) {
        $userMap[$u['partner']['id']] = $u['email'];
    }
}

$totals = [];
foreach ($emails as $e) {
    $totals[$e] = ['submitted_minutes' => 0, 'approved_minutes' => 0];
}

foreach ($reports_json['latest'] as $report) {
    $pid = $report['partner_id'];
    if (isset($userMap[$pid])) {
        $email = $userMap[$pid];
        $date = substr($report['submission_date'], 0, 10);
        if ($date >= '2026-08-05' && $date <= '2026-08-10') {
            $totals[$email]['submitted_minutes'] += $report['submitted_duration_minutes'];
            $totals[$email]['approved_minutes'] += $report['approved_duration_minutes'];
        }
    }
}

echo "Total Hours (August 5 - 10, 2026):\n";
echo str_pad("Email", 30) . " | " . str_pad("Submitted Hours", 15) . " | " . "Approved Hours\n";
echo str_repeat("-", 65) . "\n";
foreach ($emails as $e) {
    $submitted = round($totals[$e]['submitted_minutes'] / 60, 2);
    $approved = round($totals[$e]['approved_minutes'] / 60, 2);
    echo str_pad($e, 30) . " | " . str_pad($submitted . " h", 15) . " | " . $approved . " h\n";
}

