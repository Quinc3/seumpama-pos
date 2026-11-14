<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\IngredientController;
use App\Http\Controllers\UnitController;
use App\Services\AnnServiceClient;
use Illuminate\Support\Facades\Route;

Route::get('/', fn() => redirect('/login'));

Route::get('/login', [LoginController::class, 'showLogin'])->name('login');
Route::post('/login', [LoginController::class, 'login'])->name('login.attempt');
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

Route::middleware(['auth'])->group(function () {
    Route::get('/dashboard', fn() => view('dashboard'))->name('dashboard');

    // ============ UNITS ============
    Route::middleware('permission:inventory.manage')->group(function () {
        Route::resource('units', UnitController::class)->except(['show']);
    });

    // ============ CATEGORIES ============
    Route::middleware('permission:products.view')->group(function () {
        Route::get('categories', [CategoryController::class, 'index'])
            ->name('categories.index');
    });

    Route::middleware('permission:products.manage')->group(function () {
        Route::get('categories/create', [CategoryController::class, 'create'])
            ->name('categories.create');
        Route::post('categories', [CategoryController::class, 'store'])
            ->name('categories.store');
        Route::get('categories/{category}/edit', [CategoryController::class, 'edit'])
            ->whereNumber('category')
            ->name('categories.edit');
        Route::put('categories/{category}', [CategoryController::class, 'update'])
            ->whereNumber('category')
            ->name('categories.update');
        Route::delete('categories/{category}', [CategoryController::class, 'destroy'])
            ->whereNumber('category')
            ->name('categories.destroy');
    });

    // ============ INGREDIENTS ============
    Route::middleware('permission:inventory.view')->group(function () {
        Route::get('ingredients', [IngredientController::class, 'index'])
            ->name('ingredients.index');
        Route::get('ingredients/{ingredient}', [IngredientController::class, 'show'])
            ->whereNumber('ingredient')
            ->name('ingredients.show');
    });

    Route::middleware('permission:inventory.manage')->group(function () {
        Route::get('ingredients/create', [IngredientController::class, 'create'])
            ->name('ingredients.create');
        Route::post('ingredients', [IngredientController::class, 'store'])
            ->name('ingredients.store');
        Route::get('ingredients/{ingredient}/edit', [IngredientController::class, 'edit'])
            ->whereNumber('ingredient')
            ->name('ingredients.edit');
        Route::put('ingredients/{ingredient}', [IngredientController::class, 'update'])
            ->whereNumber('ingredient')
            ->name('ingredients.update');
        Route::delete('ingredients/{ingredient}', [IngredientController::class, 'destroy'])
            ->whereNumber('ingredient')
            ->name('ingredients.destroy');
    });

    // ============ PRODUCTS ============
    Route::middleware('permission:products.view')->group(function () {
        Route::get('products', [\App\Http\Controllers\ProductController::class, 'index'])
            ->name('products.index');
    });

    Route::middleware('permission:products.manage')->group(function () {
        Route::get('products/create', [\App\Http\Controllers\ProductController::class, 'create'])
            ->name('products.create');
        Route::post('products', [\App\Http\Controllers\ProductController::class, 'store'])
            ->name('products.store');
        Route::get('products/{product}/edit', [\App\Http\Controllers\ProductController::class, 'edit'])
            ->whereNumber('product')
            ->name('products.edit');
        Route::put('products/{product}', [\App\Http\Controllers\ProductController::class, 'update'])
            ->whereNumber('product')
            ->name('products.update');
        Route::delete('products/{product}', [\App\Http\Controllers\ProductController::class, 'destroy'])
            ->whereNumber('product')
            ->name('products.destroy');
    });

    // ============ SUPPLIERS ============
    Route::middleware('permission:inventory.view')->group(function () {
        Route::get('suppliers', [\App\Http\Controllers\SupplierController::class, 'index'])
            ->name('suppliers.index');
    });

    Route::middleware('permission:inventory.manage')->group(function () {
        Route::get('suppliers/create', [\App\Http\Controllers\SupplierController::class, 'create'])
            ->name('suppliers.create');
        Route::post('suppliers', [\App\Http\Controllers\SupplierController::class, 'store'])
            ->name('suppliers.store');
        Route::get('suppliers/{supplier}/edit', [\App\Http\Controllers\SupplierController::class, 'edit'])
            ->whereNumber('supplier')->name('suppliers.edit');
        Route::put('suppliers/{supplier}', [\App\Http\Controllers\SupplierController::class, 'update'])
            ->whereNumber('supplier')->name('suppliers.update');
        Route::delete('suppliers/{supplier}', [\App\Http\Controllers\SupplierController::class, 'destroy'])
            ->whereNumber('supplier')->name('suppliers.destroy');
    });

    // ============ CUSTOMERS ============
    Route::middleware('permission:reports.view')->group(function () {
        Route::get('customers', [\App\Http\Controllers\CustomerController::class, 'index'])
            ->name('customers.index');
    });

    Route::middleware('permission:reports.view')->group(function () {
        Route::get('customers/create', [\App\Http\Controllers\CustomerController::class, 'create'])
            ->name('customers.create');
        Route::post('customers', [\App\Http\Controllers\CustomerController::class, 'store'])
            ->name('customers.store');
        Route::get('customers/{customer}/edit', [\App\Http\Controllers\CustomerController::class, 'edit'])
            ->whereNumber('customer')->name('customers.edit');
        Route::put('customers/{customer}', [\App\Http\Controllers\CustomerController::class, 'update'])
            ->whereNumber('customer')->name('customers.update');
        Route::delete('customers/{customer}', [\App\Http\Controllers\CustomerController::class, 'destroy'])
            ->whereNumber('customer')->name('customers.destroy');
    });

    // ============ USERS ============
    Route::middleware('permission:users.manage')->group(function () {
        Route::get('users', [\App\Http\Controllers\UserController::class, 'index'])->name('users.index');
        Route::get('users/create', [\App\Http\Controllers\UserController::class, 'create'])->name('users.create');
        Route::post('users', [\App\Http\Controllers\UserController::class, 'store'])->name('users.store');
        Route::get('users/{user}/edit', [\App\Http\Controllers\UserController::class, 'edit'])
            ->whereNumber('user')->name('users.edit');
        Route::put('users/{user}', [\App\Http\Controllers\UserController::class, 'update'])
            ->whereNumber('user')->name('users.update');
        Route::delete('users/{user}', [\App\Http\Controllers\UserController::class, 'destroy'])
            ->whereNumber('user')->name('users.destroy');
    });

    // ============ RECIPES ============
    Route::middleware('permission:products.view')->group(function () {
        Route::get('recipes', [\App\Http\Controllers\RecipeController::class, 'index'])
            ->name('recipes.index');
        Route::get('recipes/{recipe}', [\App\Http\Controllers\RecipeController::class, 'show'])
            ->whereNumber('recipe')
            ->name('recipes.show');
    });

    Route::middleware('permission:products.manage')->group(function () {
        Route::get('products/{product}/recipe/create', [\App\Http\Controllers\RecipeController::class, 'create'])
            ->whereNumber('product')
            ->name('recipes.create');
        Route::post('products/{product}/recipe', [\App\Http\Controllers\RecipeController::class, 'store'])
            ->whereNumber('product')
            ->name('recipes.store');
        Route::get('recipes/{recipe}/edit', [\App\Http\Controllers\RecipeController::class, 'edit'])
            ->whereNumber('recipe')
            ->name('recipes.edit');
        Route::put('recipes/{recipe}', [\App\Http\Controllers\RecipeController::class, 'update'])
            ->whereNumber('recipe')
            ->name('recipes.update');
        Route::delete('recipes/{recipe}', [\App\Http\Controllers\RecipeController::class, 'destroy'])
            ->whereNumber('recipe')
            ->name('recipes.destroy');
    });

    // ============ INVENTORY MOVEMENTS ============
    Route::middleware('permission:inventory.view')->group(function () {
        Route::get('inventory/movements', [\App\Http\Controllers\InventoryMovementController::class, 'index'])
            ->name('inventory.movements.index');
    });

    Route::middleware('permission:inventory.manage')->group(function () {
        Route::get('inventory/movements/stock-in', [\App\Http\Controllers\InventoryMovementController::class, 'createIn'])
            ->name('inventory.movements.create-in');
        Route::get('inventory/movements/stock-out', [\App\Http\Controllers\InventoryMovementController::class, 'createOut'])
            ->name('inventory.movements.create-out');
        Route::post('inventory/movements', [\App\Http\Controllers\InventoryMovementController::class, 'store'])
            ->name('inventory.movements.store');
    });

    // ============ STOCK OPNAMES ============
    Route::middleware('permission:inventory.view')->group(function () {
        Route::get('stock-opnames', [\App\Http\Controllers\StockOpnameController::class, 'index'])
            ->name('stock-opnames.index');
        Route::get('stock-opnames/{stockOpname}', [\App\Http\Controllers\StockOpnameController::class, 'show'])
            ->whereNumber('stockOpname')
            ->name('stock-opnames.show');
    });

    Route::middleware('permission:inventory.manage')->group(function () {
        Route::get('stock-opnames/create', [\App\Http\Controllers\StockOpnameController::class, 'create'])
            ->name('stock-opnames.create');
        Route::post('stock-opnames', [\App\Http\Controllers\StockOpnameController::class, 'store'])
            ->name('stock-opnames.store');
        Route::post('stock-opnames/{stockOpname}/approve', [\App\Http\Controllers\StockOpnameController::class, 'approve'])
            ->whereNumber('stockOpname')
            ->name('stock-opnames.approve');
        Route::post('stock-opnames/{stockOpname}/cancel', [\App\Http\Controllers\StockOpnameController::class, 'cancel'])
            ->whereNumber('stockOpname')
            ->name('stock-opnames.cancel');
    });

    // ============ PURCHASE ORDERS ============
    Route::middleware('permission:inventory.view')->group(function () {
        Route::get('purchase-orders', [\App\Http\Controllers\PurchaseOrderController::class, 'index'])
            ->name('purchase-orders.index');
        Route::get('purchase-orders/{purchaseOrder}', [\App\Http\Controllers\PurchaseOrderController::class, 'show'])
            ->whereNumber('purchaseOrder')->name('purchase-orders.show');
    });

    Route::middleware('permission:inventory.manage')->group(function () {
        Route::get('purchase-orders/create', [\App\Http\Controllers\PurchaseOrderController::class, 'create'])
            ->name('purchase-orders.create');
        Route::post('purchase-orders', [\App\Http\Controllers\PurchaseOrderController::class, 'store'])
            ->name('purchase-orders.store');
        Route::post('purchase-orders/{purchaseOrder}/approve', [\App\Http\Controllers\PurchaseOrderController::class, 'approve'])
            ->whereNumber('purchaseOrder')->name('purchase-orders.approve');
        Route::post('purchase-orders/{purchaseOrder}/cancel', [\App\Http\Controllers\PurchaseOrderController::class, 'cancel'])
            ->whereNumber('purchaseOrder')->name('purchase-orders.cancel');
    });

    // ============ GOODS RECEIPTS ============
    Route::middleware('permission:inventory.manage')->group(function () {
        Route::get('purchase-orders/{purchaseOrder}/receive', [\App\Http\Controllers\GoodsReceiptController::class, 'create'])
            ->whereNumber('purchaseOrder')->name('goods-receipts.create');
        Route::post('purchase-orders/{purchaseOrder}/receive', [\App\Http\Controllers\GoodsReceiptController::class, 'store'])
            ->whereNumber('purchaseOrder')->name('goods-receipts.store');
    });

    Route::middleware('permission:inventory.view')->group(function () {
        Route::get('goods-receipts/{goodsReceipt}', [\App\Http\Controllers\GoodsReceiptController::class, 'show'])
            ->whereNumber('goodsReceipt')->name('goods-receipts.show');
    });

    // ============ POS ============
    Route::middleware('permission:pos.access')->group(function () {
        Route::get('pos', [\App\Http\Controllers\PosController::class, 'index'])
            ->name('pos.index');
        Route::post('pos/cart/items', [\App\Http\Controllers\PosController::class, 'addItem'])
            ->name('pos.cart.items.store');
        Route::patch('pos/cart/items/{cartItem}', [\App\Http\Controllers\PosController::class, 'updateItem'])
            ->whereNumber('cartItem')
            ->name('pos.cart.items.update');
        Route::delete('pos/cart/items/{cartItem}', [\App\Http\Controllers\PosController::class, 'removeItem'])
            ->whereNumber('cartItem')
            ->name('pos.cart.items.destroy');
        Route::post('pos/cart/clear', [\App\Http\Controllers\PosController::class, 'clear'])
            ->name('pos.cart.clear');

        // ============ POS (tambahan checkout) ============
        Route::middleware('permission:pos.access')->group(function () {
            // ... route POS yang sudah ada

            Route::post('pos/checkout', [\App\Http\Controllers\PosController::class, 'checkout'])
                ->name('pos.checkout');
        });

        // ============ ORDERS ============
        Route::middleware('permission:pos.access')->group(function () {
            Route::get('orders', [\App\Http\Controllers\OrderController::class, 'index'])
                ->name('orders.index');
            Route::get('orders/{order}', [\App\Http\Controllers\OrderController::class, 'show'])
                ->whereNumber('order')
                ->name('orders.show');
        });

        Route::middleware('permission:pos.access')->group(function () {
            Route::get('orders', [\App\Http\Controllers\OrderController::class, 'index'])
                ->name('orders.index');
            Route::get('orders/{order}', [\App\Http\Controllers\OrderController::class, 'show'])
                ->whereNumber('order')
                ->name('orders.show');
            Route::get('orders/{order}/receipt', [\App\Http\Controllers\OrderController::class, 'receipt'])
                ->whereNumber('order')
                ->name('orders.receipt');
            Route::get('orders/{order}/pdf', [\App\Http\Controllers\OrderController::class, 'pdf'])
                ->whereNumber('order')
                ->name('orders.pdf');
        });

        Route::middleware('permission:pos.refund')->group(function () {
            Route::post('orders/{order}/refund', [\App\Http\Controllers\OrderController::class, 'refund'])
                ->whereNumber('order')
                ->name('orders.refund');
        });
    });

    // ============ REPORTS ============
    Route::middleware('permission:reports.view')->group(function () {
        Route::get('reports/sales', [\App\Http\Controllers\SalesReportController::class, 'index'])
            ->name('reports.sales');
        Route::get('reports/sales/pdf', [\App\Http\Controllers\SalesReportController::class, 'pdf'])
            ->name('reports.sales.pdf');
    });

    Route::middleware('permission:inventory.view')->group(function () {
        Route::get('reports/stock', [\App\Http\Controllers\InventoryReportController::class, 'stock'])
            ->name('reports.stock');
        Route::get('reports/stock/pdf', [\App\Http\Controllers\InventoryReportController::class, 'stockPdf'])
            ->name('reports.stock.pdf');
        Route::get('reports/movements', [\App\Http\Controllers\InventoryReportController::class, 'movements'])
            ->name('reports.movements');
        Route::get('reports/usage', [\App\Http\Controllers\InventoryReportController::class, 'usage'])
            ->name('reports.usage');
        Route::get('reports/usage/pdf', [\App\Http\Controllers\InventoryReportController::class, 'usagePdf'])
            ->name('reports.usage.pdf');
        Route::get('reports/low-stock', [\App\Http\Controllers\InventoryReportController::class, 'lowStock'])
            ->name('reports.low-stock');

        // Purchasing reports
        Route::get('reports/purchasing/orders', [\App\Http\Controllers\PurchasingReportController::class, 'orders'])
            ->name('reports.purchasing.orders');
        Route::get('reports/purchasing/suppliers', [\App\Http\Controllers\PurchasingReportController::class, 'suppliers'])
            ->name('reports.purchasing.suppliers');
        Route::get('reports/purchasing/receipts', [\App\Http\Controllers\PurchasingReportController::class, 'receipts'])
            ->name('reports.purchasing.receipts');
    });

    // ============ EXPENSES ============
    Route::middleware('permission:reports.view')->group(function () {
        // CRUD
        Route::get('expenses', [\App\Http\Controllers\ExpenseController::class, 'index'])
            ->name('expenses.index');
        Route::get('expenses/create', [\App\Http\Controllers\ExpenseController::class, 'create'])
            ->name('expenses.create');
        Route::post('expenses', [\App\Http\Controllers\ExpenseController::class, 'store'])
            ->name('expenses.store');
        Route::get('expenses/{expense}/edit', [\App\Http\Controllers\ExpenseController::class, 'edit'])
            ->whereNumber('expense')->name('expenses.edit');
        Route::put('expenses/{expense}', [\App\Http\Controllers\ExpenseController::class, 'update'])
            ->whereNumber('expense')->name('expenses.update');
        Route::delete('expenses/{expense}', [\App\Http\Controllers\ExpenseController::class, 'destroy'])
            ->whereNumber('expense')->name('expenses.destroy');

        // Report
        Route::get('reports/expenses', [\App\Http\Controllers\ExpenseReportController::class, 'index'])
            ->name('reports.expenses');
        Route::get('reports/expenses/pdf', [\App\Http\Controllers\ExpenseReportController::class, 'pdf'])
            ->name('reports.expenses.pdf');
    });

    // ============ WEATHER ============
    Route::middleware('permission:reports.view')->group(function () {
        Route::get('weather', [\App\Http\Controllers\WeatherController::class, 'index'])
            ->name('weather.index');
        Route::post('weather/sync', [\App\Http\Controllers\WeatherController::class, 'sync'])
            ->name('weather.sync');
    });

    // ============ DATASETS ============
    Route::middleware('permission:ann.view')->group(function () {
        Route::get('datasets', [\App\Http\Controllers\DatasetController::class, 'index'])
            ->name('datasets.index');
        Route::get('datasets/create', [\App\Http\Controllers\DatasetController::class, 'create'])
            ->name('datasets.create');
        Route::post('datasets', [\App\Http\Controllers\DatasetController::class, 'store'])
            ->name('datasets.store');
        Route::get('datasets/{dataset}', [\App\Http\Controllers\DatasetController::class, 'show'])
            ->whereNumber('dataset')->name('datasets.show');
        Route::post('datasets/{dataset}/archive', [\App\Http\Controllers\DatasetController::class, 'archive'])
            ->whereNumber('dataset')->name('datasets.archive');
        Route::delete('datasets/{dataset}', [\App\Http\Controllers\DatasetController::class, 'destroy'])
            ->whereNumber('dataset')->name('datasets.destroy');
    });

    // ============ HEALTH CHECK ============
    Route::get('/health/ann', function (AnnServiceClient $client) {
        try {
            return response()->json(['laravel' => 'ok', 'ann' => $client->health()]);
        } catch (\Throwable $e) {
            return response()->json([
                'laravel' => 'ok',
                'ann' => 'unavailable',
                'error' => $e->getMessage(),
            ], 503);
        }
    });
});
