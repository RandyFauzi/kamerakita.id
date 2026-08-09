<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
use Webklex\IMAP\Facades\Client;

$client = Client::account('default');
$client->connect();
$folder = $client->getFolder('INBOX');
$messages = $folder->query()->all()->get();

foreach ($messages as $msg) {
    echo "Sub: " . $msg->getSubject() . "\n";
    echo "TO: ";
    $to = $msg->getTo();
    if ($to) {
        foreach($to as $t) {
            echo $t->mail . ", ";
        }
    }
    echo "\nAlt Delivered-To: " . ($msg->getAttributes()['delivered_to'] ?? 'none') . "\n";
    echo "Alt Envelope-To: " . ($msg->getAttributes()['envelope_to'] ?? 'none') . "\n";
    echo "X-Forwarded-To: " . ($msg->getAttributes()['x_forwarded_to'] ?? 'none') . "\n";
    
    // print all header keys just in case
    echo "All header keys: " . implode(', ', array_keys($msg->getAttributes())) . "\n";
    echo "--------------------\n";
}
