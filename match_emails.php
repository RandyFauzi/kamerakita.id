<?php
$json = file_get_contents('C:/Users/LENOVO/.gemini/antigravity/brain/1021b0cd-1056-4f28-b7c0-385585949fe9/.system_generated/steps/100/output.txt');
$data = json_decode($json, true);
$emails = ['diannn','echa17','imul20','imust','indirarara','jessicaapril','maulana0612','nugadp','pta','puanpuan','rlylittley','wawawiwi30','zulianaizzatul'];

$found = [];
foreach($data as $u) {
    foreach($emails as $e) {
        if(strpos($u['email'], $e) !== false) {
            $found[] = $u['email'];
        }
    }
}
print_r($found);
