<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class InventoryAdjustment extends Model
{
    use HasFactory;

    protected $fillable = [
        'adjustment_number',
        'user_id',
        'adjustment_date',
        'type',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'adjustment_date' => 'date',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(InventoryAdjustmentItem::class);
    }

    public function getTypeLabelAttribute(): string
    {
        return match ($this->type) {
            'damage' => 'Barang Rusak',
            'lost' => 'Barang Hilang',
            'internal_use' => 'Penggunaan Internal',
            'correction' => 'Koreksi Stok / Opname',
            default => $this->type,
        };
    }
}
