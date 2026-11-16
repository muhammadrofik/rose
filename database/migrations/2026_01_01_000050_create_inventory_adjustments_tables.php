<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('inventory_adjustments', function (Blueprint $table) {
            $table->id();
            $table->string('adjustment_number')->unique();
            $table->foreignId('user_id')->constrained()->restrictOnDelete();
            $table->date('adjustment_date')->index();
            $table->string('type')->default('correction')->index(); // 'damage', 'lost', 'internal_use', 'correction'
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('inventory_adjustment_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('inventory_adjustment_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_variant_id')->constrained()->restrictOnDelete();
            $table->integer('system_stock');
            $table->integer('physical_stock');
            $table->integer('difference'); // physical - system
            $table->decimal('unit_cost', 14, 2)->default(0);
            $table->string('reason')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inventory_adjustment_items');
        Schema::dropIfExists('inventory_adjustments');
    }
};
