<?php
$db = new PDO('mysql:host=127.0.0.1;dbname=kamerakita.id', 'root', '');
$emails = [
    'diannn',
    'echa17',
    'imul20',
    'imust',
    'indirarara',
    'jessicaapril',
    'maulana0612',
    'nugadp',
    'pta',
    'puanpuan',
    'rlylittley',
    'wawawiwi30',
    'zulianaizzatul'
];

$inQuery = implode(',', array_fill(0, count($emails), '?'));
$params = [];
$conditions = [];
foreach($emails as $email) {
    $conditions[] = "email LIKE ?";
    $params[] = $email . '%';
}
$stmt = $db->prepare("SELECT id, email, full_name as name FROM partners WHERE " . implode(' OR ', $conditions));
$stmt->execute($params);
$partners = $stmt->fetchAll(PDO::FETCH_ASSOC);

if(empty($partners)) {
    echo "No partners found with those prefixes.\n";
    exit;
}

$partnerIds = array_column($partners, 'id');
$inQuery2 = implode(',', array_fill(0, count($partnerIds), '?'));
$params2 = array_merge($partnerIds, ['2026-08-05', '2026-08-10']);

$sql = "SELECT partner_id, SUM(submitted_duration_minutes) as total_submitted, SUM(approved_duration_minutes) as total_approved 
        FROM video_work_reports 
        WHERE partner_id IN ($inQuery2) AND DATE(submission_date) >= ? AND DATE(submission_date) <= ? 
        GROUP BY partner_id";
$stmt2 = $db->prepare($sql);
$stmt2->execute($params2);
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
