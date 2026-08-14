<?php
$json = json_decode(file_get_contents('C:/Users/LENOVO/.gemini/antigravity/brain/1021b0cd-1056-4f28-b7c0-385585949fe9/.system_generated/steps/100/output.txt'), true);
foreach($json as $u) {
    if($u['email'] === 'diannn@web-library.net') {
        print_r($u);
    }
}
