<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$total = \App\Models\Payment::whereDate('payment_date', today())->sum('amount');
$details = \App\Models\Payment::whereDate('payment_date', today())->with('debt.transaction.customer')->get();

echo "Total Payments Today: " . number_format($total, 0, ',', '.') . "\n";
echo "Details:\n";
foreach ($details as $p) {
    if ($p->debt && $p->debt->transaction) {
        $customer = $p->debt->transaction->customer ? $p->debt->transaction->customer->name : 'Walk-in';
        echo "- Amount: " . number_format($p->amount, 0, ',', '.') . " | Customer: " . $customer . " | Trx ID: " . $p->debt->transaction->id . "\n";
    }
}
