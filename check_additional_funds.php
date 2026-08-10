<?php
require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$rows = Illuminate\Support\Facades\DB::table('additional_funds')->get();
foreach ($rows as $row) {
    echo json_encode($row, JSON_UNESCAPED_SLASHES) . PHP_EOL;
}
