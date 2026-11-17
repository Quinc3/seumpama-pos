<?php

namespace App\Services\Forecast;

use App\Models\AnnPrediction;
use App\Models\Ingredient;
use App\Models\StockForecast;
use Illuminate\Support\Facades\DB;

class StockForecastService
{
    /**
     * Hitung rata-rata & standar deviasi demand harian historis.
     * Hari tanpa pemakaian dihitung 0 (0-fill semua business dates).
     */
    public function getHistoricalStats(Ingredient $ingredient, int $windowDays = 30): array
    {
        $endDate = now()->startOfDay();
        $startDate = $endDate->copy()->subDays($windowDays - 1);

        // Usage per business_date (paid only)
        $usageByDate = DB::table('sale_ingredient_usages as siu')
            ->join('order_items as oi', 'oi.id', '=', 'siu.order_item_id')
            ->join('orders as o', 'o.id', '=', 'oi.order_id')
            ->join('business_sessions as bs', 'bs.id', '=', 'o.business_session_id')
            ->where('o.status', 'paid')
            ->where('siu.ingredient_id', $ingredient->id)
            ->whereBetween('bs.business_date', [$startDate->toDateString(), $endDate->toDateString()])
            ->select('bs.business_date', DB::raw('SUM(siu.total_quantity) as qty'))
            ->groupBy('bs.business_date')
            ->pluck('qty', 'business_date')
            ->map(fn($v) => (float) $v)
            ->all();

        // 0-fill semua tanggal dalam window
        $demands = [];
        $cursor = $startDate->copy();
        while ($cursor->lte($endDate)) {
            $key = $cursor->toDateString();
            $demands[] = (float) ($usageByDate[$key] ?? 0.0);
            $cursor->addDay();
        }

        $avg = array_sum($demands) / $windowDays;

        $varianceSum = 0.0;
        foreach ($demands as $d) {
            $varianceSum += ($d - $avg) ** 2;
        }
        $stdDev = sqrt($varianceSum / $windowDays);

        return [
            'avg' => round($avg, 4),
            'std_dev' => round($stdDev, 4),
            'days' => $windowDays,
        ];
    }

    public function zScoreForServiceLevel(float $serviceLevel): float
    {
        return match (true) {
            $serviceLevel >= 99.0 => 2.3263,
            $serviceLevel >= 97.5 => 1.9600,
            $serviceLevel >= 95.0 => 1.6449,
            $serviceLevel >= 90.0 => 1.2816,
            default => 1.2816,
        };
    }

    /**
     * Hitung forecast (belum disimpan). Return array siap insert.
     */
    public function calculateForecast(Ingredient $ingredient, float $serviceLevel = 95.0): array
    {
        $stats = $this->getHistoricalStats($ingredient);
        $avg = $stats['avg'];
        $stdDev = $stats['std_dev'];
        $leadTime = (float) ($ingredient->lead_time_days ?? 0);
        $currentStock = (float) $ingredient->current_stock;

        $z = $this->zScoreForServiceLevel($serviceLevel);

        // Safety Stock = Z × σd × √L
        $safetyStock = $z * $stdDev * sqrt(max(0, $leadTime));

        // Prediction (nullable)
        $prediction = AnnPrediction::where('ingredient_id', $ingredient->id)
            ->where('status', 'generated')
            ->orderByDesc('prediction_date')
            ->orderByDesc('id')
            ->first();

        $predictedDemand = $prediction ? (float) $prediction->predicted_demand : null;

        // ROP = (predicted ?? avg) × L + SS
        $demandForROP = $predictedDemand ?? $avg;
        $reorderPoint = $demandForROP * $leadTime + $safetyStock;

        // Recommended: order sampai ROP terpenuhi
        $recommendedQty = max(0.0, $reorderPoint - $currentStock);

        // Coverage days (pakai historical avg)
        $coverageDays = $avg > 0 ? round($currentStock / $avg, 2) : null;

        // Status
        if ($currentStock < $safetyStock) {
            $status = 'critical';
        } elseif ($currentStock < $reorderPoint) {
            $status = 'warning';
        } else {
            $status = 'normal';
        }

        return [
            'ingredient_id' => $ingredient->id,
            'ann_prediction_id' => $prediction?->id,
            'forecast_date' => now()->toDateString(),
            'current_stock' => $currentStock,
            'predicted_demand' => $predictedDemand,
            'average_daily_demand' => $avg,
            'demand_std_dev' => $stdDev,
            'lead_time_days' => $leadTime,
            'service_level' => $serviceLevel,
            'z_score' => $z,
            'safety_stock' => round($safetyStock, 4),
            'reorder_point' => round($reorderPoint, 4),
            'estimated_coverage_days' => $coverageDays,
            'recommended_quantity' => round($recommendedQty, 4),
            'status' => $status,
        ];
    }

    public function generateForecast(Ingredient $ingredient, float $serviceLevel = 95.0): StockForecast
    {
        $data = $this->calculateForecast($ingredient, $serviceLevel);
        return StockForecast::create($data);
    }

    public function generateAll(float $serviceLevel = 95.0): array
    {
        $ingredients = Ingredient::where('is_active', true)
            ->where('is_prediction_target', true)
            ->orderBy('name')
            ->get();

        $count = 0;
        $results = [];
        foreach ($ingredients as $ing) {
            $forecast = $this->generateForecast($ing, $serviceLevel);
            $results[] = $forecast;
            $count++;
        }

        return ['count' => $count, 'forecasts' => $results];
    }
}
