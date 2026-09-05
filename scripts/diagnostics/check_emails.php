<?php
$db = new PDO('mysql:host=127.0.0.1;dbname=kamerakita.id', 'root', '');
$stmt = $db->query("SELECT email FROM users");
$emails = $stmt->fetchAll(PDO::FETCH_COLUMN);
echo count($emails) . " users found\n";

$stmt2 = $db->query("SELECT email FROM partners");
$emails2 = $stmt2->fetchAll(PDO::FETCH_COLUMN);
echo count($emails2) . " partners found\n";
