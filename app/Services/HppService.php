<?php

namespace App\Services;

use App\Models\Product;
use App\Models\ProductVariant;

class HppService
{
    /**
     * Calculate and update Moving Weighted Average HPP when stock is received.
     *
     * Formula:
     * New HPP = ((Current Stock * Current HPP) + (Qty In * Unit Net Cost)) / (Current Stock + Qty In)
     * If Current Stock <= 0, New HPP = Unit Net Cost.
     */
    public function updateHppOnPurchase(ProductVariant $variant, int $quantityIn, float|string $unitNetCost): float
    {
        $currentStock = (int) $variant->stock;
        $currentHpp = (float) $variant->current_hpp;
        $unitNetCost = (float) $unitNetCost;

        if ($currentStock <= 0) {
            $newHpp = $unitNetCost;
        } else {
            $currentTotalCost = $currentStock * $currentHpp;
            $incomingTotalCost = $quantityIn * $unitNetCost;
            $newTotalStock = $currentStock + $quantityIn;

            if ($newTotalStock > 0) {
                $newHpp = ($currentTotalCost + $incomingTotalCost) / $newTotalStock;
            } else {
                $newHpp = $unitNetCost;
            }
        }

        $newHpp = round($newHpp, 2);

        $variant->current_hpp = $newHpp;
        $variant->save();

        // Also update parent product HPP (average of its variants or sync if single variant)
        $this->syncProductHpp($variant->product);

        return $newHpp;
    }

    /**
     * Synchronize the product-level HPP based on its variants.
     */
    public function syncProductHpp(Product $product): void
    {
        $variants = $product->variants()->get();
        if ($variants->isEmpty()) {
            return;
        }

        $totalStock = $variants->sum('stock');
        if ($totalStock > 0) {
            $totalValuation = $variants->reduce(function ($carry, $v) {
                return $carry + ($v->stock * (float) $v->current_hpp);
            }, 0.0);

            $product->current_hpp = round($totalValuation / $totalStock, 2);
        } else {
            // Average of variants' HPP
            $avgHpp = $variants->avg('current_hpp') ?? 0;
            $product->current_hpp = round((float) $avgHpp, 2);
        }

        $product->save();
    }
}
