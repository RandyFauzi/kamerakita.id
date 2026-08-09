<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
use Webklex\IMAP\Facades\Client;

$client = Client::account('default');
$client->connect();
$msg = $client->getFolder('INBOX')->query()->all()->get()->first();

$to = $msg->getTo();
echo "Methods:\n";
print_r(get_class_methods($to));
