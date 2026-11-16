<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\CatalogController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\InventoryAdjustmentController;
use App\Http\Controllers\InventoryController;
use App\Http\Controllers\PosController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ProfitController;
use App\Http\Controllers\PurchaseController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\SaleController;
use App\Http\Controllers\SettingController;
use App\Http\Controllers\SupplierController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

// Authentication Routes
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.post');
});

Route::post('/logout', [AuthController::class, 'logout'])->name('logout')->middleware('auth');

// Protected Routes (Admin & Kasir)
Route::middleware(['auth'])->group(function () {
    Route::get('/', fn () => redirect()->route('dashboard'));

    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Catalog (Public/Staff browsing)
    Route::get('/catalog', [CatalogController::class, 'index'])->name('catalog.index');
    Route::get('/catalog/{product}', [CatalogController::class, 'show'])->name('catalog.show');

    // POS Cashier
    Route::get('/pos', [PosController::class, 'index'])->name('pos.index');

    // Sales History
    Route::get('/sales', [SaleController::class, 'index'])->name('sales.index');
    Route::get('/sales/{sale}', [SaleController::class, 'show'])->name('sales.show');
    Route::get('/sales/{sale}/receipt', [SaleController::class, 'printReceipt'])->name('sales.receipt');

    // Admin-Only Routes
    Route::middleware(['can:admin'])->group(function () {
        // Master Data
        Route::resource('products', ProductController::class);
        Route::resource('categories', CategoryController::class)->except(['create', 'show', 'edit']);
        Route::resource('suppliers', SupplierController::class)->except(['create', 'show', 'edit']);

        // Inventory Management
        Route::get('/inventory', [InventoryController::class, 'index'])->name('inventory.index');
        Route::get('/inventory/movements', [InventoryController::class, 'movements'])->name('inventory.movements');

        // Purchases (Barang Masuk)
        Route::resource('purchases', PurchaseController::class)->only(['index', 'create', 'store', 'show']);

        // Stock Adjustments / Opname
        Route::resource('inventory/adjustments', InventoryAdjustmentController::class)
            ->names('inventory.adjustments')
            ->only(['index', 'create', 'store', 'show']);

        // Profit & Moving Average HPP
        Route::get('/profit', [ProfitController::class, 'index'])->name('profit.index');

        // Reports & CSV Export
        Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');
        Route::get('/reports/export', [ReportController::class, 'export'])->name('reports.export');

        // User Management
        Route::resource('users', UserController::class)->except(['create', 'show', 'edit']);

        // Store Settings
        Route::get('/settings', [SettingController::class, 'index'])->name('settings.index');
        Route::post('/settings', [SettingController::class, 'update'])->name('settings.update');
    });
});
