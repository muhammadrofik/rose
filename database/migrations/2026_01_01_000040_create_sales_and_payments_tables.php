<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sales', function (Blueprint $table) {
            $table->id();
            $table->string('invoice_number')->unique();
            $table->foreignId('user_id')->constrained()->restrictOnDelete();
            $table->date('sale_date')->index();
            $table->decimal('total_amount', 14, 2)->default(0);
            $table->decimal('discount_amount', 14, 2)->default(0);
            $table->decimal('grand_total', 14, 2)->default(0);
            $table->decimal('total_hpp', 14, 2)->default(0);
            $table->decimal('gross_profit', 14, 2)->default(0);
            $table->string('payment_status')->default('paid')->index(); // 'paid', 'pending', 'cancelled'
            $table->string('payment_method')->default('cash')->index(); // 'cash', 'transfer', 'qris'
            $table->string('customer_name')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('sale_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sale_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->constrained()->restrictOnDelete();
            $table->foreignId('product_variant_id')->constrained()->restrictOnDelete();
            $table->integer('quantity');
            $table->decimal('unit_price', 14, 2);
            $table->decimal('unit_discount', 14, 2)->default(0);
            $table->decimal('allocated_transaction_discount', 14, 2)->default(0);
            $table->decimal('subtotal', 14, 2);
            $table->decimal('hpp_snapshot', 14, 2);
            $table->decimal('item_gross_profit', 14, 2);
            $table->timestamps();
        });

        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sale_id')->constrained()->cascadeOnDelete();
            $table->string('payment_method')->index();
            $table->decimal('amount_paid', 14, 2);
            $table->decimal('change_amount', 14, 2)->default(0);
            $table->string('payment_reference')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payments');
        Schema::dropIfExists('sale_items');
        Schema::dropIfExists('sales');
    }
};
