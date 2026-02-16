<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $fillable = ['name', 'stock', 'buy_price', 'sell_price'];

    public function transactionItems()
    {
        return $this->hasMany(TransactionItem::class);
    }
}
