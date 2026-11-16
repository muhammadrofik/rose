<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'category_id',
        'supplier_id',
        'sku',
        'barcode',
        'name',
        'slug',
        'brand',
        'description',
        'image_path',
        'unit',
        'selling_price',
        'current_hpp',
        'min_stock',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'selling_price' => 'decimal:2',
            'current_hpp' => 'decimal:2',
            'min_stock' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (Product $product) {
            if (empty($product->slug)) {
                $product->slug = Str::slug($product->name.'-'.($product->sku ?: uniqid()));
            }
        });
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    public function variants(): HasMany
    {
        return $this->hasMany(ProductVariant::class);
    }

    public function purchaseItems(): HasMany
    {
        return $this->hasMany(PurchaseItem::class);
    }

    public function saleItems(): HasMany
    {
        return $this->hasMany(SaleItem::class);
    }

    public function getTotalStockAttribute(): int
    {
        return (int) $this->variants->sum('stock');
    }

    public function getIsLowStockAttribute(): bool
    {
        return $this->total_stock <= $this->min_stock;
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function scopeSearch(Builder $query, ?string $term): Builder
    {
        if (! $term) {
            return $query;
        }

        return $query->where(function ($q) use ($term) {
            $q->where('name', 'ilike', "%{$term}%")
                ->orWhere('sku', 'ilike', "%{$term}%")
                ->orWhere('barcode', 'ilike', "%{$term}%")
                ->orWhere('brand', 'ilike', "%{$term}%")
                ->orWhereHas('variants', function ($vq) use ($term) {
                    $vq->where('sku', 'ilike', "%{$term}%")
                        ->orWhere('barcode', 'ilike', "%{$term}%")
                        ->orWhere('variant_name', 'ilike', "%{$term}%");
                });
        });
    }
}
