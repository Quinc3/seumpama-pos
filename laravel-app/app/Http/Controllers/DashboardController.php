<?php

namespace App\Http\Controllers;

use App\Models\BusinessSession;
use App\Models\Ingredient;
use App\Models\Order;
use App\Models\StockForecast;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function __invoke()
    {
        $today = Carbon::today();
        $todayStr = $today->toDateString();
        $weekStart = $today->copy()->subDays(6)->toDateString();

        $todaySession = BusinessSession::where('business_date', $todayStr)->first();

        // ============ KPI ============
        $todaySales = 0.0;
        $todayTxCount = 0;
        $todayItemCount = 0;

        if ($todaySession) {
            $todayOrders = Order::where('business_session_id', $todaySession->id)
                ->where('status', 'paid');

            $todaySales = (float) (clone $todayOrders)->sum('total_amount');
            $todayTxCount = (clone $todayOrders)->count();
            $todayItemCount = (int) (clone $todayOrders)
                ->join('order_items', 'order_items.order_id', '=', 'orders.id')
                ->sum('order_items.quantity');
        }

        $avgTx = $todayTxCount > 0 ? $todaySales / $todayTxCount : 0;

        // ============ Stock Alerts ============
        $lowStockCount = Ingredient::where('is_active', true)
            ->whereNotNull('minimum_stock')
            ->where('minimum_stock', '>', 0)
            ->whereColumn('current_stock', '<=', 'minimum_stock')
            ->count();

        $outOfStockCount = Ingredient::where('is_active', true)
            ->where('current_stock', '<=', 0)
            ->count();

        // ============ 7-day Sales Trend ============
        $trendRaw = DB::table('orders')
            ->join('business_sessions', 'business_sessions.id', '=', 'orders.business_session_id')
            ->where('orders.status', 'paid')
            ->whereBetween('business_sessions.business_date', [$weekStart, $todayStr])
            ->groupBy('business_sessions.business_date')
            ->orderBy('business_sessions.business_date')
            ->select('business_sessions.business_date as date', DB::raw('SUM(orders.total_amount) as total'))
            ->pluck('total', 'date')
            ->all();

        $salesTrend = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = $today->copy()->subDays($i);
            $key = $date->toDateString();
            $salesTrend[] = [
                'date' => $key,
                'label' => $date->format('d M'),
                'total' => (float) ($trendRaw[$key] ?? 0),
            ];
        }

        // ============ Top 5 Products (7 days) ============
        $topProducts = DB::table('order_items')
            ->join('orders', 'orders.id', '=', 'order_items.order_id')
            ->join('business_sessions', 'business_sessions.id', '=', 'orders.business_session_id')
            ->where('orders.status', 'paid')
            ->whereBetween('business_sessions.business_date', [$weekStart, $todayStr])
            ->groupBy('order_items.product_name')
            ->orderByDesc(DB::raw('SUM(order_items.quantity)'))
            ->limit(5)
            ->select(
                'order_items.product_name',
                DB::raw('SUM(order_items.quantity) as qty'),
                DB::raw('SUM(order_items.subtotal) as revenue')
            )
            ->get();

        // ============ Recent Orders (5) ============
        $recentOrders = Order::with(['user', 'payments.paymentMethod'])
            ->where('status', 'paid')
            ->orderByDesc('id')
            ->limit(5)
            ->get();

        // ============ Low Stock Ingredients (top 5) ============
        $lowStockIngredients = Ingredient::with('unit')
            ->where('is_active', true)
            ->whereNotNull('minimum_stock')
            ->where('minimum_stock', '>', 0)
            ->whereColumn('current_stock', '<=', 'minimum_stock')
            ->orderBy('current_stock')
            ->limit(5)
            ->get();

        // ============ Purchase Recommendations (top 5 from latest forecast) ============
        $recommendations = StockForecast::with('ingredient.unit')
            ->where('recommended_quantity', '>', 0)
            ->orderByDesc('id')
            ->limit(20)
            ->get()
            ->unique('ingredient_id')
            ->take(5)
            ->values();

        $latestForecastDate = StockForecast::max('forecast_date');
        $latestPredictionDate = \App\Models\AnnPrediction::where('status', 'generated')->max('prediction_date');

        return view('dashboard', compact(
            'todaySales', 'todayTxCount', 'todayItemCount', 'avgTx',
            'lowStockCount', 'outOfStockCount',
            'salesTrend', 'topProducts', 'recentOrders',
            'lowStockIngredients', 'recommendations',
            'latestForecastDate', 'latestPredictionDate'
        ));
    }
}
