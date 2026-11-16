<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->constrained()->cascadeOnDelete();
            $table->foreignId('supplier_id')->nullable()->constrained()->nullOnDelete();
            $table->string('sku')->unique();
            $table->string('barcode')->nullable()->index();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('brand')->nullable();
            $table->text('description')->nullable();
            $table->string('image_path')->nullable();
            $table->string('unit')->default('pcs');
            $table->decimal('selling_price', 14, 2)->default(0);
            $table->decimal('current_hpp', 14, 2)->default(0);
            $table->integer('min_stock')->default(5);
            $table->boolean('is_active')->default(true)->index();
            $table->timestamps();
        });

        Schema::create('product_variants', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->string('sku')->unique();
            $table->string('barcode')->nullable()->index();
            $table->string('variant_name');
            $table->string('size')->nullable()->index();
            $table->string('color')->nullable()->index();
            $table->integer('stock')->default(0);
            $table->decimal('selling_price', 14, 2)->default(0);
            $table->decimal('current_hpp', 14, 2)->default(0);
            $table->timestamps();
        });

        // Add check constraint for stock >= 0 on PostgreSQL
        if (DB::getDriverName() === 'pgsql') {
            DB::statement('ALTER TABLE product_variants ADD CONSTRAINT check_variant_stock_non_negative CHECK (stock >= 0);');
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('product_variants');
        Schema::dropIfExists('products');
    }
};
