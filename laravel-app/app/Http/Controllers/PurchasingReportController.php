<?php

namespace App\Http\Controllers;

use App\Models\Supplier;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class PurchasingReportController extends Controller
{
    public function orders(Request $request)
    {
        [$from, $to, $period] = $this->resolvePeriod($request);

        $query = DB::table('purchase_orders as po')
            ->join('suppliers', 'suppliers.id', '=', 'po.supplier_id')
            ->join('users', 'users.id', '=', 'po.created_by')
            ->whereBetween(DB::raw('DATE(po.purchase_date)'), [$from->format('Y-m-d'), $to->format('Y-m-d')])
            ->select(
                'po.id',
                'po.purchase_number',
                'po.status',
                'po.purchase_date',
                'po.expected_date',
                'po.total_amount',
                'suppliers.name as supplier_name',
                'users.name as user_name',
                DB::raw('(SELECT COUNT(*) FROM purchase_order_items WHERE purchase_order_id = po.id) as item_count')
            );

        if ($request->status) {
            $query->where('po.status', $request->status);
        }
        if ($request->supplier_id) {
            $query->where('po.supplier_id', $request->supplier_id);
        }

        $orders = $query->orderByDesc('po.id')->paginate(25)->withQueryString();

        // Summary
        $summaryQuery = DB::table('purchase_orders')
            ->whereBetween(DB::raw('DATE(purchase_date)'), [$from->format('Y-m-d'), $to->format('Y-m-d')]);

        if ($request->supplier_id) {
            $summaryQuery->where('supplier_id', $request->supplier_id);
        }

        $summary = $summaryQuery->select(
            DB::raw('COUNT(*) as total_po'),
            DB::raw('COALESCE(SUM(CASE WHEN status IN ("approved","partially_received","received") THEN total_amount ELSE 0 END), 0) as total_approved_value'),
            DB::raw('SUM(CASE WHEN status = "received" THEN 1 ELSE 0 END) as total_received'),
            DB::raw('SUM(CASE WHEN status IN ("approved","partially_received") THEN 1 ELSE 0 END) as total_pending')
        )->first();

        $suppliers = Supplier::orderBy('name')->get();

        $statuses = [
            'draft' => 'Draft',
            'approved' => 'Approved',
            'partially_received' => 'Partially Received',
            'received' => 'Received',
            'cancelled' => 'Cancelled',
        ];

        return view('reports.purchasing.orders', compact(
            'orders',
            'summary',
            'suppliers',
            'statuses',
            'from',
            'to',
            'period'
        ));
    }

    public function suppliers(Request $request)
    {
        [$from, $to] = $this->resolveRange($request);

        // Per supplier: total PO, value, avg lead time (purchase_date → first GR received_at)
        $rows = DB::table('suppliers')
            ->leftJoin('purchase_orders as po', function ($join) use ($from, $to) {
                $join->on('po.supplier_id', '=', 'suppliers.id')
                    ->whereBetween(DB::raw('DATE(po.purchase_date)'), [$from->format('Y-m-d'), $to->format('Y-m-d')])
                    ->whereIn('po.status', ['approved', 'partially_received', 'received']);
            })
            ->select(
                'suppliers.id',
                'suppliers.name',
                'suppliers.is_active',
                DB::raw('COUNT(po.id) as total_po'),
                DB::raw('COALESCE(SUM(po.total_amount), 0) as total_value')
            )
            ->groupBy('suppliers.id', 'suppliers.name', 'suppliers.is_active')
            ->havingRaw('COUNT(po.id) > 0 OR suppliers.is_active = 1')
            ->orderByDesc('total_value')
            ->get();

        // Lead time per supplier (dari PO dengan GR)
        $leadTimeRows = DB::table('purchase_orders as po')
            ->join('goods_receipts as gr', 'gr.purchase_order_id', '=', 'po.id')
            ->whereIn('po.status', ['partially_received', 'received'])
            ->whereBetween(DB::raw('DATE(po.purchase_date)'), [$from->format('Y-m-d'), $to->format('Y-m-d')])
            ->select(
                'po.supplier_id',
                DB::raw('AVG(DATEDIFF(gr.received_at, po.purchase_date)) as avg_lead_days'),
                DB::raw('MIN(DATEDIFF(gr.received_at, po.purchase_date)) as min_lead_days'),
                DB::raw('MAX(DATEDIFF(gr.received_at, po.purchase_date)) as max_lead_days'),
                DB::raw('COUNT(DISTINCT po.id) as po_with_gr_count')
            )
            ->groupBy('po.supplier_id')
            ->get()
            ->keyBy('supplier_id');

        // Attach lead time + on-time %
        $rows = $rows->map(function ($row) use ($leadTimeRows) {
            $lt = $leadTimeRows[$row->id] ?? null;
            $row->avg_lead_days = $lt ? round((float) $lt->avg_lead_days, 1) : null;
            $row->min_lead_days = $lt ? (int) $lt->min_lead_days : null;
            $row->max_lead_days = $lt ? (int) $lt->max_lead_days : null;
            $row->po_with_gr_count = $lt ? (int) $lt->po_with_gr_count : 0;
            return $row;
        });

        return view('reports.purchasing.suppliers', compact('rows', 'from', 'to'));
    }

    public function receipts(Request $request)
    {
        [$from, $to] = $this->resolveRange($request);

        $query = DB::table('goods_receipts as gr')
            ->join('purchase_orders as po', 'po.id', '=', 'gr.purchase_order_id')
            ->join('suppliers', 'suppliers.id', '=', 'po.supplier_id')
            ->join('users', 'users.id', '=', 'gr.received_by')
            ->whereBetween(DB::raw('DATE(gr.received_at)'), [$from->format('Y-m-d'), $to->format('Y-m-d')])
            ->select(
                'gr.id',
                'gr.receipt_number',
                'gr.received_at',
                'gr.notes',
                'po.purchase_number',
                'po.id as po_id',
                'suppliers.name as supplier_name',
                'users.name as receiver_name',
                DB::raw('(SELECT COUNT(*) FROM goods_receipt_items WHERE goods_receipt_id = gr.id) as item_count'),
                DB::raw('(SELECT COALESCE(SUM(received_quantity),0) FROM goods_receipt_items WHERE goods_receipt_id = gr.id) as total_qty')
            );

        if ($request->supplier_id) {
            $query->where('po.supplier_id', $request->supplier_id);
        }

        $receipts = $query->orderByDesc('gr.id')->paginate(25)->withQueryString();

        $suppliers = Supplier::orderBy('name')->get();

        return view('reports.purchasing.receipts', compact('receipts', 'suppliers', 'from', 'to'));
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

    protected function resolveRange(Request $request): array
    {
        [$from, $to] = $this->resolvePeriod($request);
        return [$from, $to];
    }
}
