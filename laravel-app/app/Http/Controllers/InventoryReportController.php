<?php

namespace App\Http\Controllers;

use App\Models\Ingredient;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class InventoryReportController extends Controller
{
    public function stock(Request $request)
    {
        $data = $this->buildStockReport($request);

        return view('reports.inventory.stock', $data);
    }

    public function stockPdf(Request $request)
    {
        $data = $this->buildStockReport($request);
        $data['generatedAt'] = now();

        $pdf = Pdf::loadView('reports.inventory.stock-pdf', $data)->setPaper('a4', 'portrait');

        return $pdf->download('stock-report-' . now()->format('Ymd') . '.pdf');
    }

    public function movements(Request $request)
    {
        $data = $this->buildMovementReport($request);

        return view('reports.inventory.movements', $data);
    }

    public function usage(Request $request)
    {
        $data = $this->buildUsageReport($request);

        return view('reports.inventory.usage', $data);
    }

    public function usagePdf(Request $request)
    {
        $data = $this->buildUsageReport($request);
        $data['generatedAt'] = now();

        $pdf = Pdf::loadView('reports.inventory.usage-pdf', $data)->setPaper('a4', 'portrait');

        return $pdf->download('usage-report-' . $data['from']->format('Ymd') . '-' . $data['to']->format('Ymd') . '.pdf');
    }

    public function lowStock()
    {
        $ingredients = DB::table('ingredients')
            ->join('units', 'units.id', '=', 'ingredients.unit_id')
            ->whereNull('ingredients.deleted_at')
            ->where('ingredients.is_active', true)
            ->whereNotNull('ingredients.minimum_stock')
            ->where('ingredients.minimum_stock', '>', 0)
            ->whereRaw('ingredients.current_stock <= (ingredients.minimum_stock * 1.5)')
            ->select(
                'ingredients.id',
                'ingredients.sku',
                'ingredients.name',
                'ingredients.current_stock',
                'ingredients.minimum_stock',
                'ingredients.safety_stock',
                'ingredients.lead_time_days',
                'units.symbol as unit_symbol'
            )
            ->orderBy('ingredients.current_stock')
            ->get()
            ->map(function ($row) {
                $cur = (float) $row->current_stock;
                $min = (float) $row->minimum_stock;
                if ($cur <= 0) {
                    $row->status = 'out';
                } elseif ($cur <= $min) {
                    $row->status = 'critical';
                } else {
                    $row->status = 'warning';
                }
                return $row;
            });

        return view('reports.inventory.low-stock', compact('ingredients'));
    }

    protected function buildStockReport(Request $request): array
    {
        $type = $request->input('type'); // core, non_core, or null
        $status = $request->input('status'); // out, critical, warning, normal

        $query = DB::table('ingredients')
            ->join('units', 'units.id', '=', 'ingredients.unit_id')
            ->whereNull('ingredients.deleted_at')
            ->where('ingredients.is_active', true)
            ->select(
                'ingredients.id',
                'ingredients.sku',
                'ingredients.name',
                'ingredients.category',
                'ingredients.ingredient_type',
                'ingredients.current_stock',
                'ingredients.minimum_stock',
                'ingredients.safety_stock',
                'ingredients.lead_time_days',
                'ingredients.is_prediction_target',
                'units.symbol as unit_symbol'
            );

        if ($type) {
            $query->where('ingredients.ingredient_type', $type);
        }

        $items = $query->orderBy('ingredients.name')->get()->map(function ($row) {
            $cur = (float) $row->current_stock;
            $min = (float) $row->minimum_stock;
            if ($cur <= 0) {
                $row->stock_status = 'out';
            } elseif ($min > 0 && $cur <= $min) {
                $row->stock_status = 'critical';
            } elseif ($min > 0 && $cur <= $min * 1.5) {
                $row->stock_status = 'warning';
            } else {
                $row->stock_status = 'normal';
            }
            return $row;
        });

        if ($status) {
            $items = $items->where('stock_status', $status)->values();
        }

        $summary = [
            'total' => $items->count(),
            'normal' => $items->where('stock_status', 'normal')->count(),
            'warning' => $items->where('stock_status', 'warning')->count(),
            'critical' => $items->where('stock_status', 'critical')->count(),
            'out' => $items->where('stock_status', 'out')->count(),
        ];

        return [
            'items' => $items,
            'summary' => $summary,
            'filterType' => $type,
            'filterStatus' => $status,
        ];
    }

    protected function buildMovementReport(Request $request): array
    {
        [$from, $to] = $this->resolveRange($request);

        $query = DB::table('inventory_movements')
            ->join('ingredients', 'ingredients.id', '=', 'inventory_movements.ingredient_id')
            ->join('units', 'units.id', '=', 'ingredients.unit_id')
            ->join('users', 'users.id', '=', 'inventory_movements.created_by')
            ->whereBetween(DB::raw('DATE(inventory_movements.created_at)'), [$from->format('Y-m-d'), $to->format('Y-m-d')])
            ->select(
                'inventory_movements.*',
                'ingredients.name as ingredient_name',
                'ingredients.sku as ingredient_sku',
                'units.symbol as unit_symbol',
                'users.name as user_name'
            );

        if ($request->ingredient_id) {
            $query->where('inventory_movements.ingredient_id', $request->ingredient_id);
        }
        if ($request->movement_type) {
            $query->where('inventory_movements.movement_type', $request->movement_type);
        }

        $movements = $query->orderByDesc('inventory_movements.id')->paginate(50)->withQueryString();

        // Summary
        $summary = DB::table('inventory_movements')
            ->whereBetween(DB::raw('DATE(created_at)'), [$from->format('Y-m-d'), $to->format('Y-m-d')])
            ->select(
                DB::raw('SUM(CASE WHEN movement_type IN ("purchase_in","adjustment_in","return_in") THEN quantity ELSE 0 END) as total_in'),
                DB::raw('SUM(CASE WHEN movement_type IN ("sale_out","adjustment_out","waste","return_out") THEN quantity ELSE 0 END) as total_out'),
                DB::raw('COUNT(*) as total_movements')
            )
            ->first();

        $ingredients = Ingredient::where('is_active', true)->orderBy('name')->get();

        $movementTypes = [
            'purchase_in' => 'Purchase In',
            'sale_out' => 'Sale Out',
            'adjustment_in' => 'Adjustment In',
            'adjustment_out' => 'Adjustment Out',
            'waste' => 'Waste',
            'return_in' => 'Return In',
            'return_out' => 'Return Out',
        ];

        return [
            'movements' => $movements,
            'summary' => $summary,
            'from' => $from,
            'to' => $to,
            'ingredients' => $ingredients,
            'movementTypes' => $movementTypes,
        ];
    }

    protected function buildUsageReport(Request $request): array
    {
        [$from, $to] = $this->resolveRange($request);

        // Usage per ingredient — dari sale_ingredient_usages, exclude refunded orders
        $usage = DB::table('sale_ingredient_usages as siu')
            ->join('order_items', 'order_items.id', '=', 'siu.order_item_id')
            ->join('orders', 'orders.id', '=', 'order_items.order_id')
            ->join('business_sessions', 'business_sessions.id', '=', 'orders.business_session_id')
            ->join('ingredients', 'ingredients.id', '=', 'siu.ingredient_id')
            ->join('units', 'units.id', '=', 'ingredients.unit_id')
            ->where('orders.status', 'paid')  // exclude refunded/cancelled
            ->whereBetween('business_sessions.business_date', [$from->format('Y-m-d'), $to->format('Y-m-d')])
            ->select(
                'ingredients.id',
                'ingredients.sku',
                'ingredients.name',
                'ingredients.category',
                'units.symbol as unit_symbol',
                DB::raw('SUM(siu.total_quantity) as total_used'),
                DB::raw('COUNT(DISTINCT siu.order_item_id) as order_count')
            )
            ->groupBy('ingredients.id', 'ingredients.sku', 'ingredients.name', 'ingredients.category', 'units.symbol')
            ->orderByDesc('total_used')
            ->get();

        // Daily usage (optional, ambil top 5 ingredient saja)
        $topIngredientIds = $usage->take(5)->pluck('id')->all();
        $dailyUsage = collect();
        if (! empty($topIngredientIds)) {
            $dailyUsage = DB::table('sale_ingredient_usages as siu')
                ->join('order_items', 'order_items.id', '=', 'siu.order_item_id')
                ->join('orders', 'orders.id', '=', 'order_items.order_id')
                ->join('business_sessions', 'business_sessions.id', '=', 'orders.business_session_id')
                ->join('ingredients', 'ingredients.id', '=', 'siu.ingredient_id')
                ->where('orders.status', 'paid')
                ->whereBetween('business_sessions.business_date', [$from->format('Y-m-d'), $to->format('Y-m-d')])
                ->whereIn('siu.ingredient_id', $topIngredientIds)
                ->select(
                    'business_sessions.business_date as date',
                    'ingredients.name as ingredient_name',
                    'ingredients.id as ingredient_id',
                    DB::raw('SUM(siu.total_quantity) as qty')
                )
                ->groupBy('business_sessions.business_date', 'ingredients.id', 'ingredients.name')
                ->orderBy('business_sessions.business_date')
                ->get()
                ->groupBy('ingredient_id');
        }

        return [
            'usage' => $usage,
            'dailyUsage' => $dailyUsage,
            'from' => $from,
            'to' => $to,
        ];
    }

    protected function resolveRange(Request $request): array
    {
        $period = $request->input('period', 'month');

        if ($period === 'today') {
            return [Carbon::today(), Carbon::today()];
        }
        if ($period === 'week') {
            return [Carbon::today()->startOfWeek(), Carbon::today()->endOfWeek()];
        }
        if ($period === 'month') {
            return [Carbon::today()->startOfMonth(), Carbon::today()->endOfMonth()];
        }

        $from = $request->input('from') ? Carbon::parse($request->input('from'))->startOfDay() : Carbon::today()->startOfMonth();
        $to = $request->input('to') ? Carbon::parse($request->input('to'))->endOfDay() : Carbon::today();

        return [$from, $to];
    }
}
