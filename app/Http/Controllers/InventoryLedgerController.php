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

        // Calculate Total Stock (summen quantity)
        $totalStockIn = $ledgers->whereIn('type', ['initial', 'purchase'])->sum('quantity');
        $totalStockOut = $ledgers->where('type', 'sale')->sum('quantity');
        $totalStock = $totalStockIn - $totalStockOut;

        $totalMasuk = $ledgers->whereIn('type', ['initial', 'purchase'])->sum('amount');
        $totalKeluar = $ledgers->where('type', 'sale')->sum('amount');

        // Calculate Stock Per Item
        $stockPerItem = [];
        $items = $ledgers->whereNotNull('item_name')->groupBy('item_name');
        foreach ($items as $name => $transactions) {
            $in = $transactions->whereIn('type', ['initial', 'purchase'])->sum('quantity');
            $out = $transactions->where('type', 'sale')->sum('quantity');
            $stockPerItem[$name] = $in - $out;
        }

        // Reverse collection to display in descending order, newest first
        $ledgers = $ledgers->reverse()->values();

        // The final balance will be the balance of the first item in the reversed collection (newest entry)
        // If there are no entries, balance is 0.
        $totalSaldo = $ledgers->first() ? $ledgers->first()->balance : 0;

        return view('inventory.index', compact('ledgers', 'totalSaldo', 'totalStock', 'totalMasuk', 'totalKeluar', 'stockPerItem'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $existingItems = \App\Models\InventoryLedger::whereNotNull('item_name')
            ->select('item_name')
            ->distinct()
            ->orderBy('item_name')
            ->pluck('item_name');
            
        return view('inventory.create', compact('existingItems'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(\Illuminate\Http\Request $request)
    {
        $request->validate([
            'date' => 'required|date',
            'type' => 'required|in:initial,purchase,sale,sale_item',
            'item_name' => 'nullable|string',
            'quantity' => 'nullable|integer|min:1',
            'unit_price' => 'nullable|numeric|min:0',
            'amount' => 'nullable|numeric|min:0',
        ]);

        $type = $request->type;
        $amount = $request->amount;
        $itemName = $request->item_name;

        if ($type == 'purchase' || $type == 'sale_item') {
            $amount = $request->quantity * $request->unit_price;
        }

        // Add backend validation for sale_item
        if ($type == 'sale_item' && $itemName) {
            $totalIn = \App\Models\InventoryLedger::where('item_name', $itemName)
                ->whereIn('type', ['initial', 'purchase'])
                ->sum('quantity');
                
            $totalOut = \App\Models\InventoryLedger::where('item_name', $itemName)
                ->where('type', 'sale')
                ->sum('quantity');
                
            $maxStock = max(0, $totalIn - $totalOut);
            
            $minPrice = \App\Models\InventoryLedger::where('item_name', $itemName)
                ->where('type', 'purchase')
                ->max('unit_price') ?? 0;

            if ($request->quantity > $maxStock) {
                throw \Illuminate\Validation\ValidationException::withMessages([
                    'quantity' => 'Stok tidak mencukupi. Maks: ' . $maxStock
                ]);
            }

            if ($request->unit_price > 0 && $request->unit_price < $minPrice) {
                throw \Illuminate\Validation\ValidationException::withMessages([
                    'unit_price' => 'Harga tidak boleh kurang dari harga beli: Rp ' . number_format($minPrice, 0, ',', '.')
                ]);
            }
        }

        // Normalize sale sub-types to 'sale' for database
        if (in_array($type, ['sale_item'])) {
            $type = 'sale';
        }

        \App\Models\InventoryLedger::create([
            'date' => $request->date,
            'type' => $type,
            'item_name' => $itemName,
            'quantity' => $request->quantity,
            'unit_price' => $request->unit_price,
            'amount' => $amount,
        ]);

        return redirect()->route('inventory.index')->with('success', 'Entry added successfully.');
    }

    public function edit($id)
    {
        $ledger = \App\Models\InventoryLedger::findOrFail($id);
        
        $existingItems = \App\Models\InventoryLedger::whereNotNull('item_name')
            ->select('item_name')
            ->distinct()
            ->orderBy('item_name')
            ->pluck('item_name');
            
        return view('inventory.edit', compact('ledger', 'existingItems'));
    }

    public function update(\Illuminate\Http\Request $request, $id)
    {
        $request->validate([
            'date' => 'required|date',
            'type' => 'required|in:initial,purchase,sale,sale_item',
            'item_name' => 'nullable|string',
            'quantity' => 'nullable|integer|min:1',
            'unit_price' => 'nullable|numeric|min:0',
            'amount' => 'nullable|numeric|min:0',
        ]);

        $ledger = \App\Models\InventoryLedger::findOrFail($id);

        $type = $request->type;
        $amount = $request->amount;
        $itemName = $request->item_name;

        if ($type == 'purchase' || $type == 'sale_item') {
            $amount = $request->quantity * $request->unit_price;
        }

        // Add backend validation for sale_item on update
        if ($type == 'sale_item' && $itemName) {
            $totalIn = \App\Models\InventoryLedger::where('item_name', $itemName)
                ->whereIn('type', ['initial', 'purchase'])
                ->sum('quantity');
                
            $totalOut = \App\Models\InventoryLedger::where('item_name', $itemName)
                ->where('type', 'sale')
                ->where('id', '!=', $id) // Exclude current transaction
                ->sum('quantity');
                
            // If the original type was a sale for the SAME item, we also need to account for logic
            // actually excluding the current transaction from totalOut works beautifully here.
            $maxStock = max(0, $totalIn - $totalOut);
            
            $minPrice = \App\Models\InventoryLedger::where('item_name', $itemName)
                ->where('type', 'purchase')
                ->max('unit_price') ?? 0;

            if ($request->quantity > $maxStock) {
                throw \Illuminate\Validation\ValidationException::withMessages([
                    'quantity' => 'Stok tidak mencukupi. Maks: ' . $maxStock
                ]);
            }

            if ($request->unit_price > 0 && $request->unit_price < $minPrice) {
                throw \Illuminate\Validation\ValidationException::withMessages([
                    'unit_price' => 'Harga tidak boleh kurang dari harga beli: Rp ' . number_format($minPrice, 0, ',', '.')
                ]);
            }
        }

        // Normalize sale sub-types to 'sale' for database
        if (in_array($type, ['sale_item'])) {
            $type = 'sale';
        }

        $ledger->update([
            'date' => $request->date,
            'type' => $type,
            'item_name' => $itemName,
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
