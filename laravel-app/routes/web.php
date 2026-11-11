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
