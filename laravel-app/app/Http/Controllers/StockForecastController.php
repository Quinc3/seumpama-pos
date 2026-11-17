<?php

namespace App\Http\Controllers;

use App\Models\Ingredient;
use App\Models\StockForecast;
use App\Services\Forecast\StockForecastService;
use App\Services\Forecast\PredictionService;
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

        $latestPredictions = \App\Models\AnnPrediction::where('status', 'generated')
            ->orderByDesc('prediction_generated_at')
            ->get()
            ->unique('ingredient_id')
            ->keyBy('ingredient_id');

        $latestPredictionDate = \App\Models\AnnPrediction::where('status', 'generated')
            ->max('prediction_date');

        return view('reports.forecasts.index', compact(
            'ingredients',
            'forecasts',
            'summary',
            'latestGenerated',
            'latestPredictions',
            'latestPredictionDate'
        ));
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

    public function generatePredictions(PredictionService $service)
    {
        set_time_limit(180);

        try {
            $result = $service->predictAll();

            $success = $result['success'];
            $failed = $result['failed'];
            $total = $result['total'];

            // Semua gagal → cek kalau itu karena ANN service down
            if ($success === 0 && $failed > 0) {
                $firstError = null;
                foreach ($result['results'] as $r) {
                    if (! empty($r['message'])) {
                        $firstError = $r['message'];
                        break;
                    }
                }

                $isConnectionError = $firstError && (
                    str_contains($firstError, 'Connection refused') ||
                    str_contains($firstError, 'cURL error') ||
                    str_contains($firstError, 'Failed to connect') ||
                    str_contains($firstError, 'timed out')
                );

                $msg = $isConnectionError
                    ? "Semua prediction gagal: ANN Service tidak dapat dihubungi. Pastikan uvicorn running di port 8001."
                    : "Semua prediction gagal. Detail: " . substr($firstError ?? 'unknown', 0, 200);

                return redirect()
                    ->route('stock-forecasts.index')
                    ->with('error', $msg);
            }

            // Partial atau sukses
            $msg = "Prediction selesai ({$success}/{$total} sukses) untuk tanggal "
                . $result['target_date']->format('d M Y') . ".";
            if ($failed > 0) {
                $msg .= " {$failed} ingredient gagal — lihat log.";
            }

            return redirect()
                ->route('stock-forecasts.index')
                ->with($failed > 0 ? 'warning' : 'success', $msg);
        } catch (\Throwable $e) {
            return redirect()
                ->route('stock-forecasts.index')
                ->with('error', 'Gagal membuat prediction: ' . $e->getMessage());
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
