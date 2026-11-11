<?php

use App\Http\Controllers\Auth\LoginController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect('/login');
});

Route::get('/login', [LoginController::class, 'showLogin'])->name('login');
Route::post('/login', [LoginController::class, 'login'])->name('login.attempt');
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

Route::middleware(['auth'])->group(function () {
    Route::get('/dashboard', function () {
        return view('dashboard');
    })->name('dashboard');

    Route::middleware('permission:inventory.manage')->group(function () {
        Route::resource('units', \App\Http\Controllers\UnitController::class)
            ->except(['show']);
    });

    Route::middleware('permission:products.view')->group(function () {
        Route::get('categories', [\App\Http\Controllers\CategoryController::class, 'index'])
            ->name('categories.index');
    });

    Route::middleware('permission:products.manage')->group(function () {
        Route::get('categories/create', [\App\Http\Controllers\CategoryController::class, 'create'])
            ->name('categories.create');
        Route::post('categories', [\App\Http\Controllers\CategoryController::class, 'store'])
            ->name('categories.store');
        Route::get('categories/{category}/edit', [\App\Http\Controllers\CategoryController::class, 'edit'])
            ->name('categories.edit');
        Route::put('categories/{category}', [\App\Http\Controllers\CategoryController::class, 'update'])
            ->name('categories.update');
        Route::delete('categories/{category}', [\App\Http\Controllers\CategoryController::class, 'destroy'])
            ->name('categories.destroy');
    });

    Route::get('/health/ann', function (\App\Services\AnnServiceClient $client) {
        try {
            $data = $client->health();
            return response()->json(['laravel' => 'ok', 'ann' => $data]);
        } catch (\Throwable $e) {
            return response()->json([
                'laravel' => 'ok',
                'ann' => 'unavailable',
                'error' => $e->getMessage(),
            ], 503);
        }
    });
});
