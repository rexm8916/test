<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class InventoryApiController extends Controller
{
    public function getItemInfo(Request $request)
    {
        $itemName = $request->query('item_name');
        
        if (!$itemName) {
            return response()->json(['error' => 'Item name is required'], 400);
        }

        // Calculate total stock remaining
        $totalIn = \App\Models\InventoryLedger::where('item_name', $itemName)
            ->whereIn('type', ['initial', 'purchase'])
            ->sum('quantity');
            
        $totalOut = \App\Models\InventoryLedger::where('item_name', $itemName)
            ->where('type', 'sale')
            ->sum('quantity');
            
        $stock = $totalIn - $totalOut;

        // Get minimum purchase price ever for this item (or average, per user's request they just said 'jangan kurang dari unit_price').
        // We will assume the minimum valid unit_price they can sell at is the highest purchase price (HPP max) or moving average. 
        // A simple approach is the latest purchase price or highest purchase price. Let's provide highest purchase price as default 'modal'.
        $basePrice = \App\Models\InventoryLedger::where('item_name', $itemName)
            ->where('type', 'purchase')
            ->max('unit_price') ?? 0;

        return response()->json([
            'stock' => max(0, $stock),
            'base_price' => $basePrice
        ]);
    }
}
