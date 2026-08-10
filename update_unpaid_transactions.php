<?php

require __DIR__.'/vendor/autoload.php';

$app = require __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$count = \App\Models\Transaction::where('payment_status', 'belum_bayar')
    ->where('transaction_number', '!=', 'TRXK5RAYT20260805231642')
    ->update(['payment_status' => 'lunas']);

echo "UPDATED: $count\n";
