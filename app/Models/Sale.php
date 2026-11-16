<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Sale extends Model
{
    use HasFactory;

    protected $fillable = [
        'invoice_number',
        'user_id',
        'sale_date',
        'total_amount',
        'discount_amount',
        'grand_total',
        'total_hpp',
        'gross_profit',
        'payment_status',
        'payment_method',
        'customer_name',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'sale_date' => 'date',
            'total_amount' => 'decimal:2',
            'discount_amount' => 'decimal:2',
            'grand_total' => 'decimal:2',
            'total_hpp' => 'decimal:2',
            'gross_profit' => 'decimal:2',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(SaleItem::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function getGrossMarginPercentageAttribute(): float
    {
        if ($this->grand_total <= 0) {
            return 0.0;
        }

        return round(($this->gross_profit / $this->grand_total) * 100, 2);
    }
}
