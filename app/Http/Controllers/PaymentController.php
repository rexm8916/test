<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Payment;
use App\Models\Debt;
use Illuminate\Support\Facades\DB;

class PaymentController extends Controller
{
    public function edit($id)
    {
        $payment = Payment::with('debt.transaction.customer')->findOrFail($id);
        return view('payments.edit', compact('payment'));
    }

    public function update(Request $request, $id)
    {
        $payment = Payment::findOrFail($id);
        $debt = $payment->debt;

        // Validation - max amount is remaining + current payment amount
        $maxAmount = ($debt->amount_total - $debt->amount_paid) + $payment->amount;

        $request->validate([
            'amount' => 'required|numeric|min:1|max:' . $maxAmount,
            'payment_date' => 'required|date',
            'notes' => 'nullable|string',
        ]);

        DB::transaction(function () use ($request, $payment, $debt) {
            // Revert old payment amount from debt
            $debt->decrement('amount_paid', $payment->amount);

            // Update payment
            $payment->update([
                'amount' => $request->amount,
                'payment_date' => $request->payment_date,
                'notes' => $request->notes,
            ]);

            // Add new payment amount to debt
            $debt->increment('amount_paid', $request->amount);

            // Update Debt Status
            if ($debt->amount_paid >= $debt->amount_total) {
                $debt->update(['status' => 'paid']);
            }
            elseif ($debt->amount_paid > 0) {
                $debt->update(['status' => 'partial']);
            }
            else {
                $debt->update(['status' => 'unpaid']);
            }
        });

        return redirect()->route('debts.show', $debt->id)
            ->with('success', 'Payment updated successfully.');
    }

    public function destroy($id)
    {
        $payment = Payment::findOrFail($id);
        $debt = $payment->debt;

        DB::transaction(function () use ($payment, $debt) {
            // Check if this payment is linked to a transaction directly (e.g. initial payment)
            // Ideally we allow deleting any payment, but we must update debt.

            // Revert payment amount from debt
            $debt->decrement('amount_paid', $payment->amount);

            // Update Debt Status
            if ($debt->amount_paid >= $debt->amount_total) {
                $debt->update(['status' => 'paid']);
            }
            elseif ($debt->amount_paid > 0) {
                $debt->update(['status' => 'partial']);
            }
            else {
                $debt->update(['status' => 'unpaid']);
            }

            $payment->delete();
        });

        return redirect()->route('debts.show', $debt->id)
            ->with('success', 'Payment deleted successfully.');
    }
}
