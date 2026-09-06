<?php
require 'vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

Auth::loginUsingId(1); // assume user 1 is admin
$start = microtime(true);
$request = Illuminate\Http\Request::create('/dashboard', 'GET');
$controller = app(\App\Http\Controllers\RenderDashboardOverviewController::class);
$controller->__invoke($request);
echo 'Time: ' . (microtime(true) - $start) . 's';
