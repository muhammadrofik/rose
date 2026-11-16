<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StockMovement extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'product_variant_id',
        'movement_type',
        'quantity_before',
        'quantity_change',
        'quantity_after',
        'reference_type',
        'reference_id',
        'user_id',
        'unit_cost',
        'notes',
        'created_at',
    ];

    protected function casts(): array
    {
        return [
            'quantity_before' => 'integer',
            'quantity_change' => 'integer',
            'quantity_after' => 'integer',
            'unit_cost' => 'decimal:2',
            'created_at' => 'datetime',
        ];
    }

    public function variant(): BelongsTo
    {
        return $this->belongsTo(ProductVariant::class, 'product_variant_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function getMovementTypeLabelAttribute(): string
    {
        return match ($this->movement_type) {
            'PURCHASE_RECEIPT' => 'Barang Masuk (Pembelian)',
            'SALE' => 'Penjualan (Kasir)',
            'ADJUSTMENT_DAMAGE' => 'Penyesuaian (Rusak)',
            'ADJUSTMENT_LOST' => 'Penyesuaian (Hilang)',
            'ADJUSTMENT_INTERNAL' => 'Penggunaan Internal',
            'ADJUSTMENT_CORRECTION' => 'Koreksi Stok / Opname',
            'INITIAL' => 'Stok Awal',
            default => $this->movement_type,
        };
    }
}
