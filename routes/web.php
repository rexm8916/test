<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('dashboard');
});

Route::resource('products', \App\Http\Controllers\ProductController::class);
Route::resource('customers', \App\Http\Controllers\CustomerController::class);
Route::resource('transactions', \App\Http\Controllers\TransactionController::class);
Route::resource('debts', \App\Http\Controllers\DebtController::class);
Route::resource('inventory', \App\Http\Controllers\InventoryLedgerController::class);
Route::post('/debts/{id}/payment', [App\Http\Controllers\DebtController::class , 'storePayment'])->name('debts.payment.store');
Route::get('/payments/{id}/edit', [App\Http\Controllers\PaymentController::class , 'edit'])->name('payments.edit');
Route::put('/payments/{id}', [App\Http\Controllers\PaymentController::class , 'update'])->name('payments.update');
Route::delete('/payments/{id}', [App\Http\Controllers\PaymentController::class , 'destroy'])->name('payments.destroy');
Route::get('/transactions/{id}/print', [App\Http\Controllers\TransactionController::class , 'print'])->name('transactions.print');

Route::get('/dashboard', [\App\Http\Controllers\DashboardController::class , 'index'])->name('dashboard');
