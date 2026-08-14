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
$stmt = $db->prepare("SELECT id, email, full_name as name FROM partners WHERE email IN ($inQuery)");
$stmt->execute($emails);
$partners = $stmt->fetchAll(PDO::FETCH_ASSOC);

if(empty($partners)) {
    echo "No partners found.\n";
    exit;
}

$partnerIds = array_column($partners, 'id');
$inQuery2 = implode(',', array_fill(0, count($partnerIds), '?'));
$params = array_merge($partnerIds, ['2026-08-05', '2026-08-10']);

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
foreach ($partners as $p) {
    $r = $reportsByPartner[$p['id']] ?? null;
    $results[] = [
        'name' => $p['name'],
        'email' => $p['email'],
        'total_submitted_hours' => $r ? round($r['total_submitted']/60, 2) : 0,
        'total_approved_hours' => $r ? round($r['total_approved']/60, 2) : 0,
    ];
}

echo json_encode($results, JSON_PRETTY_PRINT);
