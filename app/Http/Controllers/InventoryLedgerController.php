<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class InventoryLedgerController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');
        
        $user = auth()->user();
        $selectedBranch = null;
        $branches = [];

        $query = \App\Models\InventoryLedger::orderBy('date')->orderBy('id');

        if ($user && $user->isSuperAdmin()) {
            $branches = \App\Models\Branch::orderBy('name')->get();
            if ($request->filled('branch_id')) {
                $selectedBranch = $request->branch_id;
                $query->where('branch_id', $selectedBranch);
            }
        } elseif ($user && $user->isAdmin()) {
            $selectedBranch = $user->branch_id;
            $query->where('branch_id', $selectedBranch);
        }

        $ledgers = $query->get();

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

        // Filter ledgers down to the selected date range (if provided)
        $filteredLedgers = $ledgers->filter(function ($item) use ($startDate, $endDate) {
            $itemDate = \Carbon\Carbon::parse($item->date)->format('Y-m-d');
            
            if ($startDate && $endDate) {
                return $itemDate >= $startDate && $itemDate <= $endDate;
            } elseif ($startDate) {
                return $itemDate >= $startDate;
            } elseif ($endDate) {
                return $itemDate <= $endDate;
            }
            
            return true; // No filter if both empty
        })->values();

        // Calculate period totals for the dashboard cards
        $totalMasuk = $filteredLedgers->whereIn('type', ['initial', 'purchase'])->sum('amount');
        $totalKeluar = $filteredLedgers->where('type', 'sale')->sum('amount');
        
        // Use net change for the filtered period as Total Saldo and Total Stock
        $totalSaldo = $totalMasuk - $totalKeluar;

        $totalStockIn = $filteredLedgers->whereIn('type', ['initial', 'purchase'])->sum('quantity');
        $totalStockOut = $filteredLedgers->where('type', 'sale')->sum('quantity');
        $totalStock = $totalStockIn - $totalStockOut;

        // Calculate Stock Per Item (period-specific)
        $stockPerItem = [];
        $items = $filteredLedgers->whereNotNull('item_name')->groupBy('item_name');
        foreach ($items as $name => $transactions) {
            $in = $transactions->whereIn('type', ['initial', 'purchase'])->sum('quantity');
            $out = $transactions->where('type', 'sale')->sum('quantity');
            $stockPerItem[$name] = $in - $out;
        }

        // Reverse collection to display in descending order, newest first
        $filteredLedgers = $filteredLedgers->reverse()->values();

        // Determine total saldo (ending balance of the period)
        if ($filteredLedgers->isNotEmpty()) {
            $totalSaldo = $filteredLedgers->first()->balance;
        } else {
            // Find last transaction before startDate
            $lastBefore = $ledgers->filter(function($i) use ($startDate) {
                return \Carbon\Carbon::parse($i->date)->format('Y-m-d') < $startDate;
            })->last();
            $totalSaldo = $lastBefore ? $lastBefore->balance : 0;
        }

        return view('inventory.index', [
            'ledgers' => $filteredLedgers,
            'totalSaldo' => $totalSaldo,
            'totalStock' => $totalStock,
            'totalMasuk' => $totalMasuk,
            'totalKeluar' => $totalKeluar,
            'stockPerItem' => $stockPerItem,
            'startDate' => $startDate,
            'endDate' => $endDate,
            'branches' => $branches,
            'selectedBranch' => $selectedBranch,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $user = auth()->user();
        $query = \App\Models\InventoryLedger::whereNotNull('item_name');

        if ($user && $user->isAdmin()) {
            $query->where('branch_id', $user->branch_id);
        }

        $existingItems = $query->select('item_name')
            ->distinct()
            ->orderBy('item_name')
            ->pluck('item_name');
            
        $branches = [];
        if ($user && $user->isSuperAdmin()) {
            $branches = \App\Models\Branch::orderBy('name')->get();
        }

        return view('inventory.create', compact('existingItems', 'branches'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(\Illuminate\Http\Request $request)
    {
        $request->validate([
            'date' => 'required|date',
            'type' => 'required|in:initial,purchase,sale,sale_item',
            'branch_id' => 'nullable|exists:branches,id',
            // Validation for single item inputs
            'item_name' => 'nullable|string',
            'quantity' => 'nullable|integer|min:1',
            'unit_price' => 'nullable|numeric|min:0',
            'amount' => 'nullable|numeric|min:0',
            // Validation for array inputs (purchases)
            'items' => 'nullable|array',
            'items.*.item_name' => 'required_with:items|string',
            'items.*.quantity' => 'required_with:items|integer|min:1',
            'items.*.unit_price' => 'required_with:items|numeric|min:0',
        ]);

        $type = $request->type;
        $user = auth()->user();
        $branchId = null;
        
        if ($user && $user->isSuperAdmin()) {
            $branchId = $request->branch_id;
        } elseif ($user && $user->isAdmin()) {
            $branchId = $user->branch_id;
        }

        \Illuminate\Support\Facades\DB::transaction(function () use ($request, $type, $branchId) {
            if ($type === 'purchase' && $request->has('items') && is_array($request->items)) {
                // Loop through array of items for multiple purchases
                foreach ($request->items as $item) {
                    // Skip if the row is completely empty or missing required fields
                    if (empty($item['item_name']) || empty($item['quantity']) || empty($item['unit_price'])) {
                        continue;
                    }

                    $itemName = strtoupper($item['item_name']);
                    $quantity = $item['quantity'];
                    $unitPrice = $item['unit_price'];
                    $amount = $quantity * $unitPrice;

                    \App\Models\InventoryLedger::create([
                        'date' => $request->date,
                        'type' => $type,
                        'item_name' => $itemName,
                        'quantity' => $quantity,
                        'unit_price' => $unitPrice,
                        'amount' => $amount,
                        'branch_id' => $branchId,
                    ]);
                }
            } else {
                // Single item logic (sale, sale_item, initial)
                $amount = $request->amount;
                $itemName = $request->item_name ? strtoupper($request->item_name) : null;

                if ($type == 'sale_item') {
                    $amount = $request->quantity * $request->unit_price;
                }

                // Add backend validation for sale_item
                if ($type == 'sale_item' && $itemName) {
                    $totalIn = \App\Models\InventoryLedger::where('item_name', $itemName)
                        ->where('branch_id', $branchId)
                        ->whereIn('type', ['initial', 'purchase'])
                        ->sum('quantity');
                        
                    $totalOut = \App\Models\InventoryLedger::where('item_name', $itemName)
                        ->where('branch_id', $branchId)
                        ->where('type', 'sale')
                        ->sum('quantity');
                        
                    $maxStock = max(0, $totalIn - $totalOut);
                    
                    $minPrice = \App\Models\InventoryLedger::where('item_name', $itemName)
                        ->where('branch_id', $branchId)
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
                    'branch_id' => $branchId,
                ]);
            }
        });

        return redirect()->route('inventory.index')->with('success', 'Entry added successfully.');
    }

    public function edit($id)
    {
        $ledger = \App\Models\InventoryLedger::findOrFail($id);
        
        $user = auth()->user();
        if ($user && $user->isAdmin() && $ledger->branch_id !== $user->branch_id) {
            abort(403, 'Unauthorized action.');
        }

        $query = \App\Models\InventoryLedger::whereNotNull('item_name');
        
        if ($user && $user->isAdmin()) {
            $query->where('branch_id', $user->branch_id);
        }

        $existingItems = $query->select('item_name')
            ->distinct()
            ->orderBy('item_name')
            ->pluck('item_name');
            
        $branches = [];
        if ($user && $user->isSuperAdmin()) {
            $branches = \App\Models\Branch::orderBy('name')->get();
        }

        return view('inventory.edit', compact('ledger', 'existingItems', 'branches'));
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
            'branch_id' => 'nullable|exists:branches,id',
        ]);

        $ledger = \App\Models\InventoryLedger::findOrFail($id);
        
        $user = auth()->user();
        if ($user && $user->isAdmin() && $ledger->branch_id !== $user->branch_id) {
            abort(403, 'Unauthorized action.');
        }

        $type = $request->type;
        $amount = $request->amount;
        $itemName = $request->item_name ? strtoupper($request->item_name) : null;

        if ($type == 'purchase' || $type == 'sale_item') {
            $amount = $request->quantity * $request->unit_price;
        }

        // Add backend validation for sale_item on update
        if ($type == 'sale_item' && $itemName) {
            $totalIn = \App\Models\InventoryLedger::where('item_name', $itemName)
                ->where('branch_id', $ledger->branch_id)
                ->whereIn('type', ['initial', 'purchase'])
                ->sum('quantity');
                
            $totalOut = \App\Models\InventoryLedger::where('item_name', $itemName)
                ->where('branch_id', $ledger->branch_id)
                ->where('type', 'sale')
                ->where('id', '!=', $id) // Exclude current transaction
                ->sum('quantity');
                
            // If the original type was a sale for the SAME item, we also need to account for logic
            // actually excluding the current transaction from totalOut works beautifully here.
            $maxStock = max(0, $totalIn - $totalOut);
            
            $minPrice = \App\Models\InventoryLedger::where('item_name', $itemName)
                ->where('branch_id', $ledger->branch_id)
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
        
        $updateData = [
            'date' => $request->date,
            'type' => $type,
            'item_name' => $itemName,
            'quantity' => $request->quantity,
            'unit_price' => $request->unit_price,
            'amount' => $amount,
        ];
        
        if ($user && $user->isSuperAdmin() && $request->filled('branch_id')) {
            $updateData['branch_id'] = $request->branch_id;
        }

        $ledger->update($updateData);

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
