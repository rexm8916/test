<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Debt extends Model
{
    protected $fillable = ['transaction_id', 'amount_total', 'amount_paid', 'status', 'due_date'];

    protected $casts = [
        'due_date' => 'date',
    ];

    public function transaction()
    {
        return $this->belongsTo(Transaction::class);
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }
}
