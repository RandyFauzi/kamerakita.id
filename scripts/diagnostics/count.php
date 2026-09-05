<?php
$db = new PDO('mysql:host=127.0.0.1;dbname=kamerakita.id', 'root', '');
echo $db->query("SELECT count(*) FROM video_work_reports")->fetchColumn();
