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
        Schema::create('sales', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('user_id')->constrained()->restrictOnDelete();
            $table->date('sale_date');
            $table->string('invoice_number')->unique();
            $table->decimal('total_amount', 10, 2);
            $table->enum('payment_method', [
                'mpesa', 
                'cash',
                'card'
            ])->default('cash');
            $table->enum('payment_status', [
                'paid',
                'pending',
                'partial'
            ])->default('paid');
            $table->text('remarks')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sales');
    }
};
