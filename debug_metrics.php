<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$todayDebts = \App\Models\Debt::whereDate('created_at', today())->get();
$paidDebts = $todayDebts->where('status', 'paid');

echo "Count: " . $paidDebts->count() . "\n";
echo "Total Amount Paid (Raw): " . $paidDebts->sum('amount_paid') . "\n";
echo "Total Bill Amount: " . $paidDebts->sum('amount_total') . "\n\n";

echo "Breakdown:\n";
foreach ($paidDebts as $debt) {
    $cust = $debt->transaction->customer->name ?? 'Walk-in';
    echo "ID: {$debt->id} | Cust: {$cust} | Bill: {$debt->amount_total} | Paid: {$debt->amount_paid} | Change: " . ($debt->amount_paid - $debt->amount_total) . "\n";
}
