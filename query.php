<?php

$db = new PDO('mysql:host=127.0.0.1;dbname=kamerakita.id', 'root', '');

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

$inQuery = implode(',', array_fill(0, count($emails), '?'));

$stmt = $db->prepare("SELECT id, email, name FROM users WHERE email IN ($inQuery)");
$stmt->execute($emails);
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (empty($users)) {
    echo "No users found.\n";
    exit;
}

$userIds = array_column($users, 'id');
$inQuery2 = implode(',', array_fill(0, count($userIds), '?'));

// Assuming table is `video_work_reports` and date column is `submission_date`, hours in `submitted_duration_minutes` or `approved_duration_minutes`
$params = array_merge($userIds, ['2026-08-05', '2026-08-10']);
$sql = "SELECT partner_id, SUM(submitted_duration_minutes) as total_submitted, SUM(approved_duration_minutes) as total_approved 
        FROM video_work_reports 
        WHERE partner_id IN ($inQuery2) AND DATE(submission_date) >= ? AND DATE(submission_date) <= ? 
        GROUP BY partner_id";

$stmt2 = $db->prepare($sql);
$stmt2->execute($params);
$reports = $stmt2->fetchAll(PDO::FETCH_ASSOC);

$reportsByPartner = [];
foreach ($reports as $r) {
    $reportsByPartner[$r['partner_id']] = $r;
}

$results = [];
foreach ($users as $u) {
    $r = $reportsByPartner[$u['id']] ?? null;
    $total_submitted = $r ? $r['total_submitted'] : 0;
    $total_approved = $r ? $r['total_approved'] : 0;
    $results[] = [
        'email' => $u['email'],
        'name' => $u['name'],
        'total_submitted_hours' => round($total_submitted / 60, 2),
        'total_approved_hours' => round($total_approved / 60, 2),
    ];
}

echo json_encode($results, JSON_PRETTY_PRINT);

