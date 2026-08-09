<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
use Webklex\IMAP\Facades\Client;
$client = Client::account('default');
$client->connect();
$msg = $client->getFolder('INBOX')->query()->all()->get()->first();

echo "getDate():\n";
var_dump(get_class($msg->getDate()));
echo "getDate()[0]:\n";
var_dump(get_class($msg->getDate()[0]));
echo "toDate()->toDateTimeString():\n";
echo $msg->getDate()->toDate()->toDateTimeString() . "\n";
