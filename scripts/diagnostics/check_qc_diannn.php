<?php
$reports_json = json_decode(file_get_contents('C:/Users/LENOVO/.gemini/antigravity/brain/1021b0cd-1056-4f28-b7c0-385585949fe9/.system_generated/steps/25/output.txt'), true);
$partnerId = '019fccbe-2ccc-722c-8437-35aa07002a61'; // diannn partner id

$count = 0;
$submitted = 0;
$approved = 0;

foreach ($reports_json['latest'] as $r) {
    if ($r['partner_id'] === $partnerId) {
        $date = substr($r['submission_date'], 0, 10);
        if ($date >= '2026-08-05' && $date <= '2026-08-10') {
            $count++;
            $submitted += $r['submitted_duration_minutes'];
            $approved += $r['approved_duration_minutes'];
        }
    }
}

echo "Partner ID: $partnerId\n";
echo "Found in QC Stats sample: $count reports\n";
echo "Total Submitted: $submitted minutes (" . ($submitted/60) . " jam)\n";
echo "Total Approved: $approved minutes (" . ($approved/60) . " jam)\n";
