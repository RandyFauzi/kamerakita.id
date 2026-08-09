<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
use Webklex\IMAP\Facades\Client;

$client = Client::account('default');
$client->connect();
$folder = $client->getFolder('INBOX');
$messages = $folder->query()->all()->get();

$msg = $messages->last();
echo "Sub: " . $msg->getSubject() . "\n";
echo "Raw Header:\n" . $msg->getHeader()->raw . "\n";
