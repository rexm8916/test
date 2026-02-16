<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        // Get hourly sales data for today
        $salesData = \App\Models\Transaction::where('type', 'sale')
            ->whereDate('created_at', today())
            ->get()
            ->groupBy(function ($date) {
            return \Carbon\Carbon::parse($date->created_at)->format('H');
        });

        $hours = [];
        $sales = [];

        for ($i = 0; $i < 24; $i++) {
            $hour = str_pad($i, 2, '0', STR_PAD_LEFT);
            $hours[] = $hour . ':00';

            if (isset($salesData[$hour])) {
                $sales[] = $salesData[$hour]->sum('total_amount');
            }
            else {
                $sales[] = 0;
            }
        }

        // 2. Metric: Purchases Today
        $todayPurchases = \App\Models\Transaction::where('type', 'purchase')
            ->whereDate('transaction_date', today())
            ->sum('total_amount');

        // 3. Metric: Paid Today (Total Cash In from Sales)
        // A. Direct Sales (Fully Paid, no debt)
        $fullyPaidSales = \App\Models\Transaction::where('type', 'sale')
            ->whereDate('transaction_date', today())
            ->doesntHave('debt')
            ->sum('total_amount');

        // B. Payments on Debts (Partial or Pay Later)
        $debtPayments = \App\Models\Payment::whereDate('payment_date', today())
            ->sum('amount');

        $todayPaid = $fullyPaidSales + $debtPayments;

        return view('dashboard', compact('hours', 'sales', 'todayPurchases', 'todayPaid'));
    }
}
