<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\InventoryLedgerApiController;
use App\Http\Controllers\Api\AuthApiController;

// Public Auth routes
Route::post('/login', [AuthApiController::class, 'login']);

// Protected routes
Route::middleware('auth:sanctum')->group(function () {
    // Auth
    Route::post('/logout', [AuthApiController::class, 'logout']);
    Route::get('/me', [AuthApiController::class, 'me']);

    // Inventory
    Route::get('/inventory', [InventoryLedgerApiController::class, 'index']);
    Route::post('/inventory', [InventoryLedgerApiController::class, 'store']);
    Route::put('/inventory/{id}', [InventoryLedgerApiController::class, 'update']);
    Route::delete('/inventory/{id}', [InventoryLedgerApiController::class, 'destroy']);
});
