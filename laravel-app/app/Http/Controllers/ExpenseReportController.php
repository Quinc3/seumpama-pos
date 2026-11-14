<?php

namespace App\Http\Controllers;

use App\Models\ExpenseCategory;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class ExpenseReportController extends Controller
{
    public function index(Request $request)
    {
        $data = $this->buildReport($request);

        return view('reports.expenses.index', $data);
    }

    public function pdf(Request $request)
    {
        $data = $this->buildReport($request);
        $data['generatedAt'] = now();

        $pdf = Pdf::loadView('reports.expenses.pdf', $data)->setPaper('a4', 'portrait');

        return $pdf->download('expense-report-' . $data['from']->format('Ymd') . '-' . $data['to']->format('Ymd') . '.pdf');
    }

    protected function buildReport(Request $request): array
    {
        [$from, $to, $period] = $this->resolvePeriod($request);
        $fromDate = $from->format('Y-m-d');
        $toDate = $to->format('Y-m-d');

        // By category
        $byCategory = DB::table('expenses')
            ->join('expense_categories', 'expense_categories.id', '=', 'expenses.expense_category_id')
            ->whereBetween('expenses.expense_date', [$fromDate, $toDate])
            ->select(
                'expense_categories.id',
                'expense_categories.name',
                DB::raw('COUNT(expenses.id) as tx_count'),
                DB::raw('SUM(expenses.amount) as total_amount')
            )
            ->groupBy('expense_categories.id', 'expense_categories.name')
            ->orderByDesc('total_amount')
            ->get();

        $grandTotal = $byCategory->sum('total_amount');

        // Daily trend
        $dailyData = DB::table('expenses')
            ->whereBetween('expense_date', [$fromDate, $toDate])
            ->select(
                'expense_date as date',
                DB::raw('SUM(amount) as total'),
                DB::raw('COUNT(*) as tx_count')
            )
            ->groupBy('expense_date')
            ->orderBy('expense_date')
            ->get();

        // Recent expenses
        $recentExpenses = DB::table('expenses')
            ->join('expense_categories', 'expense_categories.id', '=', 'expenses.expense_category_id')
            ->join('users', 'users.id', '=', 'expenses.created_by')
            ->leftJoin('payment_methods', 'payment_methods.id', '=', 'expenses.payment_method_id')
            ->whereBetween('expenses.expense_date', [$fromDate, $toDate])
            ->select(
                'expenses.id',
                'expenses.description',
                'expenses.amount',
                'expenses.expense_date',
                'expense_categories.name as category_name',
                'users.name as user_name',
                'payment_methods.name as payment_method_name'
            )
            ->orderByDesc('expenses.expense_date')
            ->orderByDesc('expenses.id')
            ->limit(30)
            ->get();

        return [
            'from' => $from,
            'to' => $to,
            'period' => $period,
            'byCategory' => $byCategory,
            'grandTotal' => (float) $grandTotal,
            'dailyData' => $dailyData,
            'recentExpenses' => $recentExpenses,
        ];
    }

    protected function resolvePeriod(Request $request): array
    {
        $period = $request->input('period', 'month');

        if ($period === 'today') return [Carbon::today(), Carbon::today(), 'today'];
        if ($period === 'week') return [Carbon::today()->startOfWeek(), Carbon::today()->endOfWeek(), 'week'];
        if ($period === 'month') return [Carbon::today()->startOfMonth(), Carbon::today()->endOfMonth(), 'month'];

        $from = $request->input('from') ? Carbon::parse($request->input('from'))->startOfDay() : Carbon::today()->startOfMonth();
        $to = $request->input('to') ? Carbon::parse($request->input('to'))->endOfDay() : Carbon::today();

        return [$from, $to, 'custom'];
    }
}
