<?php

namespace App\Http\Controllers;

use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class SalesReportController extends Controller
{
    public function index(Request $request)
    {
        $data = $this->buildReport($request);

        return view('reports.sales.index', $data);
    }

    public function pdf(Request $request)
    {
        $data = $this->buildReport($request);
        $data['generatedAt'] = now();

        $pdf = Pdf::loadView('reports.sales.pdf', $data)->setPaper('a4', 'portrait');

        return $pdf->download('sales-report-' . $data['from']->format('Ymd') . '-' . $data['to']->format('Ymd') . '.pdf');
    }

    protected function buildReport(Request $request): array
    {
        [$from, $to, $period] = $this->resolvePeriod($request);
        $fromDate = $from->format('Y-m-d');
        $toDate = $to->format('Y-m-d');

        // Base query — Query Builder langsung
        $base = DB::table('orders')
            ->join('business_sessions', 'business_sessions.id', '=', 'orders.business_session_id')
            ->where('orders.status', 'paid')
            ->whereBetween('business_sessions.business_date', [$fromDate, $toDate]);

        // KPI
        $aggregates = (clone $base)
            ->select(
                DB::raw('COALESCE(SUM(orders.total_amount), 0) as total_sales'),
                DB::raw('COUNT(orders.id) as total_transactions')
            )
            ->first();

        $totalSales = (float) $aggregates->total_sales;
        $totalTransactions = (int) $aggregates->total_transactions;
        $avgTransaction = $totalTransactions > 0 ? $totalSales / $totalTransactions : 0;

        // Total items sold
        $totalItemsSold = (float) DB::table('order_items')
            ->join('orders', 'orders.id', '=', 'order_items.order_id')
            ->join('business_sessions', 'business_sessions.id', '=', 'orders.business_session_id')
            ->where('orders.status', 'paid')
            ->whereBetween('business_sessions.business_date', [$fromDate, $toDate])
            ->sum('order_items.quantity');

        // Daily breakdown
        $dailyData = (clone $base)
            ->select(
                'business_sessions.business_date as date',
                DB::raw('SUM(orders.total_amount) as revenue'),
                DB::raw('COUNT(orders.id) as tx_count')
            )
            ->groupBy('business_sessions.business_date')
            ->orderBy('business_sessions.business_date')
            ->get();

        // Top products
        $topProducts = DB::table('order_items')
            ->join('orders', 'orders.id', '=', 'order_items.order_id')
            ->join('business_sessions', 'business_sessions.id', '=', 'orders.business_session_id')
            ->where('orders.status', 'paid')
            ->whereBetween('business_sessions.business_date', [$fromDate, $toDate])
            ->select(
                'order_items.product_id',
                DB::raw('MAX(order_items.product_name) as product_name'),
                DB::raw('SUM(order_items.quantity) as total_qty'),
                DB::raw('SUM(order_items.subtotal) as total_revenue')
            )
            ->groupBy('order_items.product_id')
            ->orderByDesc('total_revenue')
            ->limit(10)
            ->get();

        // By category
        $byCategory = DB::table('order_items')
            ->join('orders', 'orders.id', '=', 'order_items.order_id')
            ->join('business_sessions', 'business_sessions.id', '=', 'orders.business_session_id')
            ->join('products', 'products.id', '=', 'order_items.product_id')
            ->leftJoin('categories', 'categories.id', '=', 'products.category_id')
            ->where('orders.status', 'paid')
            ->whereBetween('business_sessions.business_date', [$fromDate, $toDate])
            ->select(
                'products.category_id',
                DB::raw('MAX(categories.name) as category_name'),
                DB::raw('SUM(order_items.quantity) as total_qty'),
                DB::raw('SUM(order_items.subtotal) as total_revenue')
            )
            ->groupBy('products.category_id')
            ->orderByDesc('total_revenue')
            ->get();

        // By cashier
        $byCashier = (clone $base)
            ->join('users', 'users.id', '=', 'orders.user_id')
            ->select(
                'users.id as user_id',
                'users.name as user_name',
                DB::raw('COUNT(orders.id) as tx_count'),
                DB::raw('SUM(orders.total_amount) as total_revenue')
            )
            ->groupBy('users.id', 'users.name')
            ->orderByDesc('total_revenue')
            ->get();

        // Recent orders (Query Builder)
        $recentOrders = (clone $base)
            ->select('orders.*')
            ->orderByDesc('orders.id')
            ->limit(20)
            ->get();

        // Attach user + payment method
        $userIds = $recentOrders->pluck('user_id')->unique();
        $userNames = DB::table('users')->whereIn('id', $userIds)->pluck('name', 'id');

        $orderIds = $recentOrders->pluck('id')->all();
        $payments = DB::table('order_payments')
            ->join('payment_methods', 'payment_methods.id', '=', 'order_payments.payment_method_id')
            ->whereIn('order_payments.order_id', $orderIds)
            ->select('order_payments.order_id', 'payment_methods.name as method_name')
            ->get()
            ->keyBy('order_id');

        $recentOrders = $recentOrders->map(function ($row) use ($userNames, $payments) {
            $row->user_name = $userNames[$row->user_id] ?? '—';
            $row->payment_method = $payments[$row->id]?->method_name ?? '—';
            return $row;
        });

        return [
            'from' => $from,
            'to' => $to,
            'period' => $period,
            'totalSales' => $totalSales,
            'totalTransactions' => $totalTransactions,
            'avgTransaction' => $avgTransaction,
            'totalItemsSold' => $totalItemsSold,
            'dailyData' => $dailyData,
            'topProducts' => $topProducts,
            'byCategory' => $byCategory,
            'byCashier' => $byCashier,
            'recentOrders' => $recentOrders,
        ];
    }

    protected function resolvePeriod(Request $request): array
    {
        $period = $request->input('period', 'month');

        $from = $request->input('from');
        $to = $request->input('to');

        if ($period === 'today') {
            $from = $to = Carbon::today();
        } elseif ($period === 'week') {
            $from = Carbon::today()->startOfWeek();
            $to = Carbon::today()->endOfWeek();
        } elseif ($period === 'month') {
            $from = Carbon::today()->startOfMonth();
            $to = Carbon::today()->endOfMonth();
        } else {
            $from = $from ? Carbon::parse($from)->startOfDay() : Carbon::today()->startOfMonth();
            $to = $to ? Carbon::parse($to)->endOfDay() : Carbon::today();
            $period = 'custom';
        }

        return [$from, $to, $period];
    }
}
