<?php

namespace App\Services\Dataset;

use App\Models\BusinessSchedule;
use App\Models\DatasetRecord;
use App\Models\DatasetVersion;
use App\Models\Ingredient;
use App\Models\WeatherDailySummary;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class DatasetGeneratorService
{
    /**
     * Generate dataset version baru (immutable snapshot).
     *
     * @throws \RuntimeException
     */
    public function generate(string $datasetName, Carbon $from, Carbon $to): DatasetVersion
    {
        if ($from->gt($to)) {
            throw new \InvalidArgumentException('Start date harus sebelum end date.');
        }

        $ingredients = Ingredient::where('is_active', true)
            ->where('is_prediction_target', true)
            ->orderBy('name')
            ->get();

        if ($ingredients->isEmpty()) {
            throw new \RuntimeException('Tidak ada ingredient dengan is_prediction_target = true.');
        }

        // Aggregate daily consumption per ingredient from paid orders (exclude refunded)
        $consumptionByDateIngredient = $this->fetchConsumption($from, $to);

        // Weather summaries per business_date
        $weatherByDate = WeatherDailySummary::whereBetween('business_date', [
                $from->format('Y-m-d'), $to->format('Y-m-d')
            ])
            ->get()
            ->keyBy(fn ($w) => $w->business_date->format('Y-m-d'));

        // Build all business dates in range
        $businessDates = [];
        for ($d = $from->copy(); $d->lte($to); $d->addDay()) {
            $businessDates[] = $d->copy();
        }

        return DB::transaction(function () use (
            $datasetName, $from, $to, $ingredients,
            $consumptionByDateIngredient, $weatherByDate, $businessDates
        ) {
            $version = DatasetVersion::create([
                'version' => DatasetVersion::generateVersionNumber(),
                'dataset_name' => $datasetName,
                'start_date' => $from->format('Y-m-d'),
                'end_date' => $to->format('Y-m-d'),
                'total_records' => 0,
                'status' => 'draft',
                'feature_schema' => $this->featureSchema(),
                'validation_summary' => null,
                'created_by' => auth()->id(),
            ]);

            $records = [];
            $validCount = 0;
            $partialCount = 0;

            foreach ($ingredients as $ingredient) {
                // Pre-compute daily consumption for this ingredient
                $dailyConsumption = [];
                foreach ($businessDates as $date) {
                    $key = $date->format('Y-m-d');
                    $dailyConsumption[$key] = (float) ($consumptionByDateIngredient[$ingredient->id][$key] ?? 0);
                }

                foreach ($businessDates as $date) {
                    $key = $date->format('Y-m-d');
                    $actual = $dailyConsumption[$key];

                    // Lags: nilai null kalau tanggal target di luar range data historis
                    $lag = function (int $days) use ($date, $dailyConsumption, $from) {
                        $target = $date->copy()->subDays($days);
                        if ($target->lt($from)) return null;
                        return $dailyConsumption[$target->format('Y-m-d')] ?? null;
                    };

                    $lag1 = $lag(1);
                    $lag2 = $lag(2);
                    $lag3 = $lag(3);
                    $lag7 = $lag(7);

                    $rollingMean3 = ($lag1 !== null && $lag2 !== null && $lag3 !== null)
                        ? round(($lag1 + $lag2 + $lag3) / 3, 4)
                        : null;

                    $rollingMean7 = null;
                    if ($lag7 !== null) {
                        $vals = [];
                        for ($d = 1; $d <= 7; $d++) {
                            $v = $lag($d);
                            if ($v === null) { $vals = []; break; }
                            $vals[] = $v;
                        }
                        if (count($vals) === 7) {
                            $rollingMean7 = round(array_sum($vals) / 7, 4);
                        }
                    }

                    // Weather
                    $weather = $weatherByDate[$key] ?? null;
                    $temp = $weather?->average_temperature;
                    $rain = $weather?->total_rainfall;

                    // Day of week + is_weekend (Fri = 5, Sat = 6 per Carbon dayOfWeek)
                    $dow = (int) $date->dayOfWeek;
                    $isWeekend = in_array($dow, [5, 6]);

                    // Quality assessment
                    $missing = [];
                    if ($lag1 === null) $missing[] = 'lag_d1';
                    if ($lag7 === null) $missing[] = 'lag_d7';
                    if ($temp === null) $missing[] = 'temperature';
                    if ($rain === null) $missing[] = 'rainfall';

                    if (empty($missing)) {
                        $quality = 'valid';
                        $validCount++;
                    } else {
                        $quality = 'partial';
                        $partialCount++;
                    }

                    $records[] = [
                        'dataset_version_id' => $version->id,
                        'ingredient_id' => $ingredient->id,
                        'business_date' => $key,
                        'actual_demand' => $actual,
                        'lag_d1' => $lag1,
                        'lag_d2' => $lag2,
                        'lag_d3' => $lag3,
                        'lag_d7' => $lag7,
                        'rolling_mean_3d' => $rollingMean3,
                        'rolling_mean_7d' => $rollingMean7,
                        'temperature' => $temp,
                        'rainfall' => $rain,
                        'day_of_week' => $dow,
                        'is_weekend' => $isWeekend,
                        'data_quality' => $quality,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                }
            }

            // Bulk insert untuk performa
            foreach (array_chunk($records, 500) as $chunk) {
                DatasetRecord::insert($chunk);
            }

            $version->update([
                'total_records' => count($records),
                'validation_summary' => [
                    'total_records' => count($records),
                    'valid_records' => $validCount,
                    'partial_records' => $partialCount,
                    'total_ingredients' => $ingredients->count(),
                    'total_business_days' => count($businessDates),
                ],
                'status' => 'validated',
            ]);

            return $version;
        });
    }

    /**
     * @return array<int, array<string, float>> [ingredient_id => [Y-m-d => qty]]
     */
    protected function fetchConsumption(Carbon $from, Carbon $to): array
    {
        $rows = DB::table('sale_ingredient_usages as siu')
            ->join('order_items as oi', 'oi.id', '=', 'siu.order_item_id')
            ->join('orders as o', 'o.id', '=', 'oi.order_id')
            ->join('business_sessions as bs', 'bs.id', '=', 'o.business_session_id')
            ->where('o.status', 'paid')
            ->whereBetween('bs.business_date', [$from->format('Y-m-d'), $to->format('Y-m-d')])
            ->select(
                'siu.ingredient_id',
                'bs.business_date',
                DB::raw('SUM(siu.total_quantity) as qty')
            )
            ->groupBy('siu.ingredient_id', 'bs.business_date')
            ->get();

        $result = [];
        foreach ($rows as $r) {
            $dateKey = Carbon::parse($r->business_date)->format('Y-m-d');
            $result[$r->ingredient_id][$dateKey] = (float) $r->qty;
        }

        return $result;
    }

    protected function featureSchema(): array
    {
        return [
            'version' => '1.0',
            'target' => 'actual_demand',
            'features' => [
                'lag_d1', 'lag_d2', 'lag_d3', 'lag_d7',
                'rolling_mean_3d', 'rolling_mean_7d',
                'temperature', 'rainfall',
                'day_of_week', 'is_weekend',
            ],
            'notes' => [
                'Lag dan rolling null jika data historis sebelum dataset tidak mencukupi',
                'Temperature & rainfall null jika weather summary tidak tersedia',
                'actual_demand = 0 jika tidak ada transaksi paid pada business_date',
                'Hanya ingredient dengan is_prediction_target=true yang masuk dataset',
            ],
        ];
    }
}
