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
