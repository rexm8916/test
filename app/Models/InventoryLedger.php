<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InventoryLedger extends Model
{
    protected $fillable = [
        'date',
        'type',
        'item_name',
        'quantity',
        'unit_price',
        'amount',
    ];

    protected $casts = [
        'date' => 'date',
    ];
//
}
