<?php

namespace App\Http\Controllers;

use App\Models\Ingredient;
use App\Models\StockForecast;
use App\Services\Forecast\StockForecastService;
use Illuminate\Http\Request;

class StockForecastController extends Controller
{
    public function index()
    {
        $ingredients = Ingredient::where('is_active', true)
            ->where('is_prediction_target', true)
            ->with('unit')
            ->orderBy('name')
            ->get();

        // Ambil forecast terbaru per ingredient
        $forecasts = StockForecast::with(['ingredient.unit', 'annPrediction'])
            ->orderByDesc('id')
            ->get()
            ->unique('ingredient_id')
            ->keyBy('ingredient_id');

        $summary = [
            'normal' => $forecasts->where('status', 'normal')->count(),
            'warning' => $forecasts->where('status', 'warning')->count(),
            'critical' => $forecasts->where('status', 'critical')->count(),
            'missing' => $ingredients->filter(fn($i) => ! isset($forecasts[$i->id]))->count(),
        ];

        $latestGenerated = StockForecast::max('created_at');

        return view('reports.forecasts.index', compact('ingredients', 'forecasts', 'summary', 'latestGenerated'));
    }

    public function generate(StockForecastService $service)
    {
        set_time_limit(120);

        try {
            $result = $service->generateAll(serviceLevel: 95.0);
            return redirect()
                ->route('stock-forecasts.index')
                ->with('success', "Forecast berhasil dibuat untuk {$result['count']} ingredient.");
        } catch (\Throwable $e) {
            return redirect()
                ->route('stock-forecasts.index')
                ->with('error', 'Gagal membuat forecast: ' . $e->getMessage());
        }
    }

    public function show(StockForecast $stockForecast)
    {
        $stockForecast->load(['ingredient.unit', 'annPrediction']);

        // Riwayat forecast ingredient ini (5 terakhir)
        $history = StockForecast::where('ingredient_id', $stockForecast->ingredient_id)
            ->orderByDesc('id')
            ->limit(10)
            ->get();

        return view('reports.forecasts.show', [
            'forecast' => $stockForecast,
            'history' => $history,
        ]);
    }
}
