<?php
$db = new PDO('mysql:host=127.0.0.1;dbname=kamerakita.id', 'root', '');
$stmt = $db->query("SELECT partner_id, submission_date, submitted_duration_minutes FROM video_work_reports LIMIT 10");
print_r($stmt->fetchAll(PDO::FETCH_ASSOC));
