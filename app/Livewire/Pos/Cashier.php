<?php

namespace App\Livewire\Pos;

use App\Models\Category;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Sale;
use App\Services\SalesService;
use Exception;
use Livewire\Component;

class Cashier extends Component
{
    public string $search = '';

    public ?int $selectedCategory = null;

    // Cart: array of items keyed by variant_id
    public array $cart = [];

    // Checkout properties
    public string $paymentMethod = 'cash'; // 'cash', 'transfer', 'qris'

    public float $discountAmount = 0;

    public float $amountPaid = 0;

    public string $customerName = '';

    public string $notes = '';

    // Modal states
    public bool $variantModalOpen = false;

    public ?int $activeProductId = null;

    public ?Product $activeProduct = null;

    public bool $receiptModalOpen = false;

    public ?Sale $lastSale = null;

    public string $errorMessage = '';

    public string $receiptPaperSize = '58mm'; // '58mm' or '80mm'

    public function mount(): void
    {
        $this->cart = [];
        $this->paymentMethod = 'cash';
        $this->discountAmount = 0;
        $this->amountPaid = 0;
    }

    public function selectCategory(?int $categoryId = null): void
    {
        $this->selectedCategory = $categoryId;
    }

    public function openVariantModal(int $productId): void
    {
        $this->activeProductId = $productId;
        $this->activeProduct = Product::with('variants')->find($productId);
        $this->variantModalOpen = true;
    }

    public function closeVariantModal(): void
    {
        $this->variantModalOpen = false;
        $this->activeProduct = null;
        $this->activeProductId = null;
    }

    public function addProductToCart(int $productId): void
    {
        $product = Product::with('variants')->findOrFail($productId);

        if ($product->variants->count() === 1) {
            $variant = $product->variants->first();
            $this->addVariantToCart($variant->id);
        } else {
            // Open modal to select variant
            $this->openVariantModal($productId);
        }
    }

    public function addVariantToCart(int $variantId): void
    {
        $variant = ProductVariant::with('product')->findOrFail($variantId);

        if ($variant->stock <= 0) {
            $this->errorMessage = "Stok untuk {$variant->full_name} habis.";

            return;
        }

        $currentInCart = $this->cart[$variantId]['quantity'] ?? 0;
        if ($currentInCart + 1 > $variant->stock) {
            $this->errorMessage = "Jumlah melebihi stok tersedia ({$variant->stock} pcs).";

            return;
        }

        if (isset($this->cart[$variantId])) {
            $this->cart[$variantId]['quantity'] += 1;
        } else {
            $this->cart[$variantId] = [
                'variant_id' => $variant->id,
                'product_id' => $variant->product_id,
                'name' => $variant->product->name,
                'variant_name' => $variant->variant_name,
                'full_name' => $variant->full_name,
                'price' => (float) $variant->selling_price,
                'quantity' => 1,
                'max_stock' => $variant->stock,
                'sku' => $variant->sku,
            ];
        }

        $this->errorMessage = '';
        $this->closeVariantModal();
        $this->recalculatePayment();
    }

    public function updateQuantity(int $variantId, int $delta): void
    {
        if (! isset($this->cart[$variantId])) {
            return;
        }

        $newQty = $this->cart[$variantId]['quantity'] + $delta;
        $maxStock = $this->cart[$variantId]['max_stock'];

        if ($newQty <= 0) {
            $this->removeFromCart($variantId);

            return;
        }

        if ($newQty > $maxStock) {
            $this->errorMessage = "Stok tidak mencukupi. Tersedia: {$maxStock} pcs.";

            return;
        }

        $this->cart[$variantId]['quantity'] = $newQty;
        $this->errorMessage = '';
        $this->recalculatePayment();
    }

    public function removeFromCart(int $variantId): void
    {
        unset($this->cart[$variantId]);
        $this->recalculatePayment();
    }

    public function clearCart(): void
    {
        $this->cart = [];
        $this->discountAmount = 0;
        $this->amountPaid = 0;
        $this->errorMessage = '';
    }

    public function setCashTendered(float|int $amount): void
    {
        $this->amountPaid = (float) $amount;
    }

    public function setExactPayment(): void
    {
        $this->amountPaid = $this->grandTotal;
    }

    public function recalculatePayment(): void
    {
        if ($this->paymentMethod !== 'cash') {
            $this->amountPaid = $this->grandTotal;
        } elseif ($this->amountPaid < $this->grandTotal && $this->amountPaid > 0) {
            // Keep if user typed, otherwise suggest exact
        }
    }

    public function updatedPaymentMethod(): void
    {
        if ($this->paymentMethod !== 'cash') {
            $this->amountPaid = $this->grandTotal;
        }
    }

    public function updatedDiscountAmount(): void
    {
        if ($this->paymentMethod !== 'cash') {
            $this->amountPaid = $this->grandTotal;
        }
    }

    public function getSubtotalProperty(): float
    {
        $sub = 0;
        foreach ($this->cart as $item) {
            $sub += $item['price'] * $item['quantity'];
        }

        return $sub;
    }

    public function getGrandTotalProperty(): float
    {
        return max(0, $this->subtotal - (float) $this->discountAmount);
    }

    public function getChangeAmountProperty(): float
    {
        if ($this->amountPaid >= $this->grandTotal) {
            return $this->amountPaid - $this->grandTotal;
        }

        return 0;
    }

    public function checkout(SalesService $salesService): void
    {
        $this->errorMessage = '';

        if (empty($this->cart)) {
            $this->errorMessage = 'Keranjang belanja masih kosong.';

            return;
        }

        if ($this->amountPaid < $this->grandTotal) {
            $this->errorMessage = 'Nominal pembayaran kurang dari total belanja.';

            return;
        }

        try {
            $items = [];
            foreach ($this->cart as $item) {
                $items[] = [
                    'product_variant_id' => $item['variant_id'],
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['price'],
                    'unit_discount' => 0,
                ];
            }

            $sale = $salesService->processSale(
                data: [
                    'sale_date' => today()->toDateString(),
                    'discount_amount' => $this->discountAmount,
                    'customer_name' => $this->customerName ?: 'Pelanggan Umum',
                    'notes' => $this->notes,
                    'payment_method' => $this->paymentMethod,
                    'amount_paid' => $this->amountPaid,
                ],
                items: $items,
                cashier: auth()->user()
            );

            $this->lastSale = $sale;
            $this->receiptModalOpen = true;

            // Reset cart
            $this->cart = [];
            $this->discountAmount = 0;
            $this->amountPaid = 0;
            $this->customerName = '';
            $this->notes = '';
            $this->errorMessage = '';

        } catch (Exception $e) {
            $this->errorMessage = $e->getMessage();
        }
    }

    public function closeReceiptModal(): void
    {
        $this->receiptModalOpen = false;
        $this->lastSale = null;
    }

    public function render()
    {
        $categories = Category::withCount('products')->orderBy('name')->get();

        $query = Product::with(['category', 'variants'])
            ->active();

        if ($this->search) {
            $query->search($this->search);
        }

        if ($this->selectedCategory) {
            $query->where('category_id', $this->selectedCategory);
        }

        $products = $query->orderBy('name')->paginate(12);

        return view('livewire.pos.cashier', [
            'products' => $products,
            'categories' => $categories,
        ]);
    }
}
