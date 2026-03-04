<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\InventoryLedger;
use App\Models\Branch;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use OpenApi\Attributes as OA;

class InventoryLedgerApiController extends Controller
{
    #[OA\Get(
        path: "/api/inventory",
        summary: "Get list of inventory ledgers",
        description: "Retrieve inventory ledgers with optional date and branch filters",
        operationId: "getInventoryLedgers",
        tags: ["Inventory Base"],
        security: [["bearerAuth" => []]]
    )]
    #[OA\Parameter(
        name: "start_date",
        in: "query",
        description: "Start date for filter (YYYY-MM-DD)",
        required: false,
        schema: new OA\Schema(type: "string", format: "date")
    )]
    #[OA\Parameter(
        name: "end_date",
        in: "query",
        description: "End date for filter (YYYY-MM-DD)",
        required: false,
        schema: new OA\Schema(type: "string", format: "date")
    )]
    #[OA\Parameter(
        name: "branch_id",
        in: "query",
        description: "Branch ID (SuperAdmin only)",
        required: false,
        schema: new OA\Schema(type: "integer")
    )]
    #[OA\Response(
        response: 200,
        description: "Successful operation",
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: "success", type: "boolean", example: true),
                new OA\Property(property: "message", type: "string", example: "Data inventory berhasil ditarik."),
                new OA\Property(
                    property: "data",
                    type: "object",
                    properties: [
                        new OA\Property(property: "total_saldo", type: "number", format: "float", example: 1000000),
                        new OA\Property(property: "total_stock", type: "integer", example: 50)
                    ]
                )
            ]
        )
    )]
    #[OA\Response(
        response: 401,
        description: "Unauthenticated"
    )]
    public function index(Request $request)
    {
        $startDate = $request->input('start_date', Carbon::now()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->input('end_date', Carbon::now()->endOfMonth()->format('Y-m-d'));

        // Validation: If Sampai Tanggal < Mulai Tanggal, sync Mulai Tanggal to Sampai Tanggal
        if ($startDate && $endDate && $endDate < $startDate) {
            $startDate = $endDate;
        }

        $user = auth('sanctum')->user();
        if (!$user) {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }

        $selectedBranch = null;
        $branches = [];

        $query = InventoryLedger::orderBy('date')->orderBy('id');

        if ($user->isSuperAdmin()) {
            $branches = Branch::orderBy('name')->get();
            if ($request->filled('branch_id')) {
                $selectedBranch = $request->branch_id;
                $query->where('branch_id', $selectedBranch);
            }
        } elseif ($user->isAdmin()) {
            $selectedBranch = $user->branch_id;
            $query->where('branch_id', $selectedBranch);
        }

        $ledgers = $query->get();

        // Calculate Running Balance
        $balance = 0;
        $ledgers->transform(function ($item) use (&$balance) {
            if ($item->type == 'initial' || $item->type == 'purchase') {
                $balance += $item->amount;
            } else {
                $balance -= $item->amount;
            }
            $item->balance = $balance;
            return $item;
        });

        // Filter ledgers down to the selected date range (if provided)
        $filteredLedgers = $ledgers->filter(function ($item) use ($startDate, $endDate) {
            $itemDate = Carbon::parse($item->date)->format('Y-m-d');

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

        return response()->json([
            'success' => true,
            'message' => 'Data inventory berhasil ditarik.',
            'data' => [
                'ledgers' => $filteredLedgers,
                'total_saldo' => $totalSaldo,
                'total_stock' => $totalStock,
                'total_masuk' => $totalMasuk,
                'total_keluar' => $totalKeluar,
                'stock_per_item' => $stockPerItem,
                'start_date' => $startDate,
                'end_date' => $endDate,
                'branches' => $branches,
                'selected_branch' => $selectedBranch,
            ]
        ], 200);
    }

    #[OA\Post(
        path: "/api/inventory",
        summary: "Create new inventory record",
        description: "Add a new inventory ledger entry (initial, purchase, sale, sale_item). Supports single item or array of items for purchases.",
        operationId: "storeInventoryLedger",
        tags: ["Inventory Base"],
        security: [["bearerAuth" => []]]
    )]
    #[OA\RequestBody(
        required: true,
        content: new OA\JsonContent(
            required: ["date", "type"],
            properties: [
                new OA\Property(property: "date", type: "string", format: "date", example: "2023-10-25"),
                new OA\Property(property: "type", type: "string", enum: ["initial", "purchase", "sale", "sale_item"], example: "purchase"),
                new OA\Property(property: "branch_id", type: "integer", nullable: true, example: null),
                new OA\Property(property: "item_name", type: "string", nullable: true, example: "Item A"),
                new OA\Property(property: "quantity", type: "integer", nullable: true, example: 10),
                new OA\Property(property: "unit_price", type: "number", format: "float", nullable: true, example: 15000),
                new OA\Property(property: "amount", type: "number", format: "float", nullable: true, example: 150000),
                new OA\Property(
                    property: "items",
                    type: "array",
                    nullable: true,
                    items: new OA\Items(
                        type: "object",
                        properties: [
                            new OA\Property(property: "item_name", type: "string", example: "Item B"),
                            new OA\Property(property: "quantity", type: "integer", example: 5),
                            new OA\Property(property: "unit_price", type: "number", format: "float", example: 20000)
                        ]
                    )
                )
            ]
        )
    )]
    #[OA\Response(
        response: 201,
        description: "Entry added successfully",
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: "success", type: "boolean", example: true),
                new OA\Property(property: "message", type: "string", example: "Entry added successfully.")
            ]
        )
    )]
    #[OA\Response(
        response: 400,
        description: "Bad Request"
    )]
    #[OA\Response(
        response: 422,
        description: "Validation Error"
    )]
    public function store(Request $request)
    {
        try {
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
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal.',
                'errors' => $e->errors()
            ], 422);
        }

        $type = $request->type;
        $user = auth('sanctum')->user();
        if (!$user) {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }

        $branchId = null;

        if ($user->isSuperAdmin()) {
            $branchId = $request->branch_id;
        } elseif ($user->isAdmin()) {
            $branchId = $user->branch_id;
        }

        try {
            DB::transaction(function () use ($request, $type, $branchId) {
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

                        InventoryLedger::create([
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
                        $totalIn = InventoryLedger::where('item_name', $itemName)
                            ->where('branch_id', $branchId)
                            ->whereIn('type', ['initial', 'purchase'])
                            ->sum('quantity');

                        $totalOut = InventoryLedger::where('item_name', $itemName)
                            ->where('branch_id', $branchId)
                            ->where('type', 'sale')
                            ->sum('quantity');

                        $maxStock = max(0, $totalIn - $totalOut);

                        $minPrice = InventoryLedger::where('item_name', $itemName)
                            ->where('branch_id', $branchId)
                            ->where('type', 'purchase')
                            ->max('unit_price') ?? 0;

                        if ($request->quantity > $maxStock) {
                            throw ValidationException::withMessages([
                                'quantity' => 'Stok tidak mencukupi. Maks: ' . $maxStock
                            ]);
                        }

                        if ($request->unit_price > 0 && $request->unit_price < $minPrice) {
                            throw ValidationException::withMessages([
                                'unit_price' => 'Harga tidak boleh kurang dari harga beli: Rp ' . number_format($minPrice, 0, ',', '.')
                            ]);
                        }
                    }

                    // Normalize sale sub-types to 'sale' for database
                    if (in_array($type, ['sale_item'])) {
                        $type = 'sale';
                    }

                    InventoryLedger::create([
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

            return response()->json([
                'success' => true,
                'message' => 'Entry added successfully.',
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    #[OA\Put(
        path: "/api/inventory/{id}",
        summary: "Update an existing inventory record",
        description: "Update the details of an existing inventory ledger entry",
        operationId: "updateInventoryLedger",
        tags: ["Inventory Base"],
        security: [["bearerAuth" => []]]
    )]
    #[OA\Parameter(
        name: "id",
        in: "path",
        description: "ID of the inventory ledger to update",
        required: true,
        schema: new OA\Schema(type: "integer")
    )]
    #[OA\RequestBody(
        required: true,
        content: new OA\JsonContent(
            required: ["date", "type"],
            properties: [
                new OA\Property(property: "date", type: "string", format: "date", example: "2023-10-25"),
                new OA\Property(property: "type", type: "string", enum: ["initial", "purchase", "sale", "sale_item"], example: "sale"),
                new OA\Property(property: "branch_id", type: "integer", nullable: true, example: null),
                new OA\Property(property: "item_name", type: "string", nullable: true, example: "Item A"),
                new OA\Property(property: "quantity", type: "integer", nullable: true, example: 5),
                new OA\Property(property: "unit_price", type: "number", format: "float", nullable: true, example: 20000),
                new OA\Property(property: "amount", type: "number", format: "float", nullable: true, example: 100000)
            ]
        )
    )]
    #[OA\Response(
        response: 200,
        description: "Entry updated successfully",
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: "success", type: "boolean", example: true),
                new OA\Property(property: "message", type: "string", example: "Entry updated successfully.")
            ]
        )
    )]
    #[OA\Response(
        response: 403,
        description: "Unauthorized action"
    )]
    #[OA\Response(
        response: 404,
        description: "Data not found"
    )]
    #[OA\Response(
        response: 422,
        description: "Validation Error"
    )]
    public function update(Request $request, $id)
    {
        try {
            $request->validate([
                'date' => 'required|date',
                'type' => 'required|in:initial,purchase,sale,sale_item',
                'item_name' => 'nullable|string',
                'quantity' => 'nullable|integer|min:1',
                'unit_price' => 'nullable|numeric|min:0',
                'amount' => 'nullable|numeric|min:0',
                'branch_id' => 'nullable|exists:branches,id',
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal.',
                'errors' => $e->errors()
            ], 422);
        }

        $ledger = InventoryLedger::find($id);

        if (!$ledger) {
            return response()->json([
                'success' => false,
                'message' => 'Data tidak ditemukan.',
            ], 404);
        }

        $user = auth('sanctum')->user();
        if (!$user || ($user->isAdmin() && $ledger->branch_id !== $user->branch_id)) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized action.',
            ], 403);
        }

        $type = $request->type;
        $amount = $request->amount;
        $itemName = $request->item_name ? strtoupper($request->item_name) : null;

        if ($type == 'purchase' || $type == 'sale_item') {
            $amount = $request->quantity * $request->unit_price;
        }

        try {
            // Add backend validation for sale_item on update
            if ($type == 'sale_item' && $itemName) {
                $totalIn = InventoryLedger::where('item_name', $itemName)
                    ->where('branch_id', $ledger->branch_id)
                    ->whereIn('type', ['initial', 'purchase'])
                    ->sum('quantity');

                $totalOut = InventoryLedger::where('item_name', $itemName)
                    ->where('branch_id', $ledger->branch_id)
                    ->where('type', 'sale')
                    ->where('id', '!=', $id) // Exclude current transaction
                    ->sum('quantity');

                $maxStock = max(0, $totalIn - $totalOut);

                $minPrice = InventoryLedger::where('item_name', $itemName)
                    ->where('branch_id', $ledger->branch_id)
                    ->where('type', 'purchase')
                    ->max('unit_price') ?? 0;

                if ($request->quantity > $maxStock) {
                    throw ValidationException::withMessages([
                        'quantity' => 'Stok tidak mencukupi. Maks: ' . $maxStock
                    ]);
                }

                if ($request->unit_price > 0 && $request->unit_price < $minPrice) {
                    throw ValidationException::withMessages([
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

            if ($user->isSuperAdmin() && $request->filled('branch_id')) {
                $updateData['branch_id'] = $request->branch_id;
            }

            $ledger->update($updateData);

            return response()->json([
                'success' => true,
                'message' => 'Entry updated successfully.',
                'data' => $ledger
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    #[OA\Delete(
        path: "/api/inventory/{id}",
        summary: "Delete an existing inventory record",
        description: "Remove the specified inventory ledger entry from database",
        operationId: "destroyInventoryLedger",
        tags: ["Inventory Base"],
        security: [["bearerAuth" => []]]
    )]
    #[OA\Parameter(
        name: "id",
        description: "ID of the inventory ledger to delete",
        in: "path",
        required: true,
        schema: new OA\Schema(type: "integer")
    )]
    #[OA\Response(
        response: 200,
        description: "Entry deleted successfully",
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: "success", type: "boolean", example: true),
                new OA\Property(property: "message", type: "string", example: "Entry deleted successfully.")
            ]
        )
    )]
    #[OA\Response(
        response: 403,
        description: "Unauthorized action"
    )]
    #[OA\Response(
        response: 404,
        description: "Data not found"
    )]
    public function destroy(string $id)
    {
        $ledger = InventoryLedger::find($id);

        if (!$ledger) {
            return response()->json([
                'success' => false,
                'message' => 'Data tidak ditemukan.',
            ], 404);
        }

        $user = auth('sanctum')->user();
        if (!$user || ($user->isAdmin() && $ledger->branch_id !== $user->branch_id)) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized action.',
            ], 403);
        }

        $ledger->delete();

        return response()->json([
            'success' => true,
            'message' => 'Entry deleted successfully.',
        ], 200);
    }
}
