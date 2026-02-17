<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class DebtController extends Controller
{
    public function index()
    {
        $debts = \App\Models\Debt::with('transaction.customer')->orderBy('created_at', 'desc')->get();
        return view('debts.index', compact('debts'));
    }

    public function show($id)
    {
        $debt = \App\Models\Debt::with(['transaction.customer', 'transaction.items.product', 'payments'])->findOrFail($id);
        return view('debts.show', compact('debt'));
    }

    public function storePayment(Request $request, $id)
    {
        $debt = \App\Models\Debt::findOrFail($id);

        $request->validate([
            'amount' => 'required|numeric|min:1|max:' . ($debt->amount_total - $debt->amount_paid),
            'payment_date' => 'required|date',
            'notes' => 'nullable|string',
        ]);

        \Illuminate\Support\Facades\DB::transaction(function () use ($request, $debt) {
            // Create Payment
            \App\Models\Payment::create([
                'debt_id' => $debt->id,
                'amount' => $request->amount,
                'payment_date' => $request->payment_date,
                'notes' => $request->notes,
            ]);

            // Update Debt
            $debt->increment('amount_paid', $request->amount);

            if ($debt->amount_paid >= $debt->amount_total) {
                $debt->update(['status' => 'paid']);
            }
            else {
                $debt->update(['status' => 'partial']);
            }
        });

        return redirect()->route('debts.show', $debt->id)
            ->with('success', 'Payment recorded successfully.');
    }
    public function destroy($id)
    {
        $debt = \App\Models\Debt::findOrFail($id);
        $debt->delete();

        return redirect()->route('debts.index')->with('success', 'Debt record deleted successfully.');
    }
}
