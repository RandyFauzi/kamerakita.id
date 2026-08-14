<?php
$db = new PDO('mysql:host=127.0.0.1;dbname=kamerakita.id', 'root', '');
$stmt = $db->query('SHOW TABLES');
$tables = $stmt->fetchAll(PDO::FETCH_COLUMN);

echo "Tables:\n";
print_r($tables);

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

$names = array_map(function($e) { return explode('@', $e)[0]; }, $emails);

$inQuery = implode(',', array_fill(0, count($names), '?'));
$stmt = $db->prepare("SELECT id, email, name FROM users WHERE email IN (" . implode(',', array_fill(0, count($emails), '?')) . ") OR name IN ($inQuery) OR email LIKE ?");
$params = array_merge($emails, $names, ['%web-library.net%']);
$stmt->execute($params);
print_r($stmt->fetchAll(PDO::FETCH_ASSOC));
