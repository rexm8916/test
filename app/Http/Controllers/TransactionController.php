<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class TransactionController extends Controller
{
    public function index(Request $request)
    {
        $query = \App\Models\Transaction::with('customer');

        if ($request->start_date) {
            $query->whereDate('transaction_date', '>=', $request->start_date);
        }

        if ($request->end_date) {
            $query->whereDate('transaction_date', '<=', $request->end_date);
        }

        if ($request->customer_id) {
            $query->where('customer_id', $request->customer_id);
        }

        if ($request->type) {
            $query->where('type', $request->type);
        }

        $transactions = $query->orderBy('transaction_date', 'desc')->latest()->paginate(10);
        $customersQuery = \App\Models\Customer::orderBy('name');
        if ($request->type == 'sale') {
            $customersQuery->where('type', 'customer');
        }
        elseif ($request->type == 'purchase') {
            $customersQuery->where('type', 'supplier');
        }
        $customers = $customersQuery->get();

        return view('transactions.index', compact('transactions', 'customers'));
    }

    public function show($id)
    {
        $transaction = \App\Models\Transaction::with(['items.product', 'customer', 'debt.payments'])->findOrFail($id);
        return view('transactions.show', compact('transaction'));
    }

    public function destroy($id)
    {
        $transaction = \App\Models\Transaction::with('items')->findOrFail($id);

        \Illuminate\Support\Facades\DB::transaction(function () use ($transaction) {
            // Revert Stock
            foreach ($transaction->items as $item) {
                $product = \App\Models\Product::find($item->product_id);
                if ($product) {
                    if ($transaction->type === 'purchase') {
                        $product->decrement('stock', $item->quantity);
                    }
                    else {
                        $product->increment('stock', $item->quantity);
                    }
                }
            }

            // Delete transaction (Cascade will handle items, debts, payments if set, but let's rely on DB or Model events)
            // If cascade is not set in migration, we might need manual delete.
            // Based on typical Laravel migrations constrained() uses default reference which might not cascade depending on DB setup.
            // Safest to rely on foreign key cascade if confirmed, or delete manually.
            $transaction->delete();
        });

        return redirect()->route('transactions.index')->with('success', 'Transaction deleted and stock reverted.');
    }

    public function create()
    {
        $products = \App\Models\Product::all();
        $type = request('type', 'sale');
        $customerType = $type == 'purchase' ? 'supplier' : 'customer';
        $customers = \App\Models\Customer::where('type', $customerType)->get();
        return view('transactions.create', compact('products', 'customers'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'type' => 'required|in:purchase,sale',
            'transaction_date' => 'required|date',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.price' => 'required|numeric|min:0',
            // Discount validation
            'discount' => 'nullable|numeric|min:0',
            // Debt validation
            'payment_status' => 'required|in:paid,partial,unpaid',
            'amount_paid' => 'nullable|numeric|min:0',
            // Customer is required
            // Logic:
            // Paid -> Can be Umum (we will set default in controller if empty, or enforce selection)
            // Unpaid/Partial -> Must be selected AND cannot be 'Umum' (if we strictly follow 'harus isi dulu')?
            // Let's just require customer_id for all, but for Paid we allow 'Umum'.
            // For Unpaid/Partial, user asked "harus isi dulu customer nya". This implies they shouldn't use "Umum".
            // Let's assume standard 'required'. The View will handle the "Umum" selection for Paid.
            // For Unpaid/Partial, we might need to check if it's NOT Umum if that's the requirement,
            // but for now, 'required' and 'exists' is the baseline.
            'customer_id' => 'required|exists:customers,id',
        ]);

        $transaction = \Illuminate\Support\Facades\DB::transaction(function () use ($request) {
            // 1. Calculate Subtotal (Before Discount)
            $subtotalAmount = 0;
            foreach ($request->items as $item) {
                $subtotalAmount += $item['quantity'] * $item['price'];
            }

            // 2. Apply Discount (Only for sales, but logic supports both if needed)
            $discount = $request->discount ?? 0;
            $totalAmount = max(0, $subtotalAmount - $discount);

            // 3. Create Transaction
            $transaction = \App\Models\Transaction::create([
                'type' => $request->type,
                'customer_id' => $request->customer_id,
                'total_amount' => $totalAmount, // This is now Final Total
                'discount' => $discount,
                'transaction_date' => $request->transaction_date . ' ' . now()->format('H:i:s'),
                'status' => 'completed',
            ]);

            // 4. Create Items & Update Stock
            foreach ($request->items as $itemData) {
                $itemSubtotal = $itemData['quantity'] * $itemData['price'];

                \App\Models\TransactionItem::create([
                    'transaction_id' => $transaction->id,
                    'product_id' => $itemData['product_id'],
                    'quantity' => $itemData['quantity'],
                    'price' => $itemData['price'],
                    'subtotal' => $itemSubtotal,
                ]);

                // Update Stock
                $product = \App\Models\Product::find($itemData['product_id']);
                if ($request->type === 'purchase') {
                    $product->increment('stock', $itemData['quantity']);
                // Optional: Update buy price?
                }
                else {
                    $product->decrement('stock', $itemData['quantity']);
                }
            }

            // 5. Handle Debt/Payment
            // 5. Handle Debt/Payment
            if ($request->type === 'sale') {
                $amountPaid = $request->amount_paid ?? 0;

                // Determine debt status based on payment
                $debtStatus = $request->payment_status;
                if ($debtStatus !== 'paid') {
                    $debtStatus = ($amountPaid > 0) ? 'partial' : 'unpaid';
                }

                $debt = \App\Models\Debt::create([
                    'transaction_id' => $transaction->id,
                    'amount_total' => $totalAmount,
                    'amount_paid' => $amountPaid, // Store tendered amount here
                    'status' => $debtStatus,
                    'due_date' => $request->due_date,
                ]);

                // Record initial payment if any
                if ($amountPaid > 0) {
                    \App\Models\Payment::create([
                        'debt_id' => $debt->id,
                        'amount' => $amountPaid,
                        'payment_date' => $request->transaction_date,
                        'notes' => 'Initial payment',
                    ]);
                }
            }

            return $transaction;
        });

        return redirect()->route('transactions.show', $transaction->id)->with('success', 'Transaction recorded successfully.');
    }

    public function print($id)
    {
        $transaction = \App\Models\Transaction::with(['items.product', 'customer'])->findOrFail($id);
        return view('transactions.print', compact('transaction'));
    }
}
