<?php
$partnerId = '019fccbe-2ccc-722c-8437-35aa07002a61';

// Test query directly via PDO local DB if any
$db = new PDO('mysql:host=127.0.0.1;dbname=kamerakita.id', 'root', '');
$stmt = $db->prepare("SELECT COUNT(*) as count, SUM(submitted_duration_minutes) as sum_sub, SUM(approved_duration_minutes) as sum_app FROM video_work_reports WHERE partner_id = ? AND submission_date BETWEEN '2026-08-05' AND '2026-08-10'");
$stmt->execute([$partnerId]);
print_r($stmt->fetch(PDO::FETCH_ASSOC));
