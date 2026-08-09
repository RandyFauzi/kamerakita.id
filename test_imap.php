<?php

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Webklex\IMAP\Facades\Client;

$client = Client::account('default');
$client->connect();
$folder = $client->getFolder('INBOX');
$message = $folder->query()->all()->get()->first();

echo "Subject: " . $message->getSubject() . "\n";
echo "To raw:\n";
var_dump($message->getTo());
echo "From raw:\n";
var_dump($message->getFrom());
