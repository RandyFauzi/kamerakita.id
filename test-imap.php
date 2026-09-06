<?php
require 'vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$client = Webklex\IMAP\Facades\Client::account('default'); 
$client->connect(); 
$folder = $client->getFolder('INBOX'); 
$messages = $folder->query()->whereUid('0:*')->limit(10)->get();
echo "Count: " . $messages->count() . "\n";
foreach($messages as $msg) {
    echo $msg->getUid() . " - " . $msg->getSubject() . " To: " . json_encode($msg->getTo()->all()) . "\n";
}
