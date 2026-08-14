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
$stmt = $db->prepare("SELECT email FROM users WHERE " . implode(' OR ', $conditions));
$stmt->execute($params);
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);

print_r($users);
