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
