<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration 
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->enum('type', ['purchase', 'sale']); // purchase = masuk, sale = keluar
            $table->foreignId('customer_id')->nullable()->constrained()->nullOnDelete(); // Can be null for generic sales
            $table->decimal('total_amount', 15, 2);
            $table->date('transaction_date');
            $table->string('status')->default('completed'); // completed, pending, cancelled
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};
