<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SaleItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'sale_id',
        'product_id',
        'product_variant_id',
        'quantity',
        'unit_price',
        'unit_discount',
        'allocated_transaction_discount',
        'subtotal',
        'hpp_snapshot',
        'item_gross_profit',
    ];

    protected function casts(): array
    {
        return [
            'quantity' => 'integer',
            'unit_price' => 'decimal:2',
            'unit_discount' => 'decimal:2',
            'allocated_transaction_discount' => 'decimal:2',
            'subtotal' => 'decimal:2',
            'hpp_snapshot' => 'decimal:2',
            'item_gross_profit' => 'decimal:2',
        ];
    }

    public function sale(): BelongsTo
    {
        return $this->belongsTo(Sale::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function variant(): BelongsTo
    {
        return $this->belongsTo(ProductVariant::class, 'product_variant_id');
    }
}
