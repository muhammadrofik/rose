<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ProductVariant extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id',
        'sku',
        'barcode',
        'variant_name',
        'size',
        'color',
        'stock',
        'selling_price',
        'current_hpp',
    ];

    protected function casts(): array
    {
        return [
            'stock' => 'integer',
            'selling_price' => 'decimal:2',
            'current_hpp' => 'decimal:2',
        ];
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function stockMovements(): HasMany
    {
        return $this->hasMany(StockMovement::class);
    }

    public function purchaseItems(): HasMany
    {
        return $this->hasMany(PurchaseItem::class);
    }

    public function saleItems(): HasMany
    {
        return $this->hasMany(SaleItem::class);
    }

    public function inventoryAdjustmentItems(): HasMany
    {
        return $this->hasMany(InventoryAdjustmentItem::class);
    }

    public function getFullNameAttribute(): string
    {
        if ($this->variant_name === 'Standard' || empty($this->variant_name)) {
            return $this->product->name ?? '';
        }

        return ($this->product->name ?? '').' ('.$this->variant_name.')';
    }
}
