<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class InventoryLedgerController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $ledgers = \App\Models\InventoryLedger::orderBy('date')->orderBy('id')->get();

        // Calculate Running Balance
        $balance = 0;
        $ledgers->transform(function ($item) use (&$balance) {
            if ($item->type == 'initial' || $item->type == 'purchase') {
                $balance += $item->amount;
            }
            else {
                $balance -= $item->amount;
            }
            $item->balance = $balance;
            return $item;
        });

        return view('inventory.index', compact('ledgers'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('inventory.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(\Illuminate\Http\Request $request)
    {
        $request->validate([
            'date' => 'required|date',
            'type' => 'required|in:initial,purchase,sale',
            'item_name' => 'nullable|string|required_if:type,purchase',
            'quantity' => 'nullable|integer|min:1|required_if:type,purchase',
            'unit_price' => 'nullable|numeric|min:0|required_if:type,purchase',
            'amount' => 'nullable|numeric|min:0|required_if:type,initial,sale',
        ]);

        $amount = $request->amount;
        if ($request->type == 'purchase') {
            $amount = $request->quantity * $request->unit_price;
        }

        \App\Models\InventoryLedger::create([
            'date' => $request->date,
            'type' => $request->type,
            'item_name' => $request->item_name,
            'quantity' => $request->quantity,
            'unit_price' => $request->unit_price,
            'amount' => $amount,
        ]);

        return redirect()->route('inventory.index')->with('success', 'Entry added successfully.');
    }

    public function edit($id)
    {
        $ledger = \App\Models\InventoryLedger::findOrFail($id);
        return view('inventory.edit', compact('ledger'));
    }

    public function update(\Illuminate\Http\Request $request, $id)
    {
        $request->validate([
            'date' => 'required|date',
            'type' => 'required|in:initial,purchase,sale',
            'item_name' => 'nullable|string|required_if:type,purchase',
            'quantity' => 'nullable|integer|min:1|required_if:type,purchase',
            'unit_price' => 'nullable|numeric|min:0|required_if:type,purchase',
            'amount' => 'nullable|numeric|min:0|required_if:type,initial,sale',
        ]);

        $ledger = \App\Models\InventoryLedger::findOrFail($id);

        $amount = $request->amount;
        if ($request->type == 'purchase') {
            $amount = $request->quantity * $request->unit_price;
        }

        $ledger->update([
            'date' => $request->date,
            'type' => $request->type,
            'item_name' => $request->item_name,
            'quantity' => $request->quantity,
            'unit_price' => $request->unit_price,
            'amount' => $amount,
        ]);

        return redirect()->route('inventory.index')->with('success', 'Entry updated successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        // View details if needed, or redirect
        return redirect()->route('inventory.index');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $ledger = \App\Models\InventoryLedger::findOrFail($id);
        $ledger->delete();
        return redirect()->route('inventory.index')->with('success', 'Entry deleted successfully.');
    }
}
