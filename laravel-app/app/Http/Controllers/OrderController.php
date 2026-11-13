<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\Request;
use App\Models\BusinessSetting;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\DB;

class OrderController extends Controller
{
    public function index(Request $request)
    {
        $orders = Order::with(['user', 'items', 'payments.paymentMethod'])
            ->when($request->status, fn($q, $s) => $q->where('status', $s))
            ->when($request->from, fn($q, $d) => $q->whereDate('created_at', '>=', $d))
            ->when($request->to, fn($q, $d) => $q->whereDate('created_at', '<=', $d))
            ->when($request->search, fn($q, $s) => $q->where('order_number', 'like', "%{$s}%"))
            ->orderByDesc('id')
            ->paginate(25)
            ->withQueryString();

        return view('orders.index', compact('orders'));
    }

    public function show(Order $order)
    {
        $order->load([
            'items.product',
            'items.recipe',
            'payments.paymentMethod',
            'user',
            'businessSession',
        ]);

        return view('orders.show', compact('order'));
    }

    public function receipt(Order $order)
    {
        $order->load(['items', 'payments.paymentMethod', 'user']);

        $settings = [
            'shop_name' => BusinessSetting::get('shop_name', 'Seumpama Bunga'),
            'shop_address' => BusinessSetting::get('shop_address', ''),
            'shop_phone' => BusinessSetting::get('shop_phone', ''),
            'receipt_footer' => BusinessSetting::get('receipt_footer', ''),
        ];

        return view('orders.receipt', compact('order', 'settings'));
    }

    public function pdf(Order $order)
    {
        $order->load(['items', 'payments.paymentMethod', 'user']);

        $settings = [
            'shop_name' => BusinessSetting::get('shop_name', 'Seumpama Bunga'),
            'shop_address' => BusinessSetting::get('shop_address', ''),
            'shop_phone' => BusinessSetting::get('shop_phone', ''),
            'receipt_footer' => BusinessSetting::get('receipt_footer', ''),
        ];

        $pdf = Pdf::loadView('orders.receipt', compact('order', 'settings'))
            ->setPaper([0, 0, 226.77, 850], 'portrait');

        return $pdf->download("struk-{$order->order_number}.pdf");
    }

    public function refund(Request $request, Order $order, \App\Services\OrderService $orderService)
    {
        $data = $request->validate([
            'reason' => ['required', 'string', 'min:5', 'max:500'],
        ]);

        try {
            $orderService->refund($order, $data['reason']);
        } catch (\Throwable $e) {
            return back()->with('error', 'Refund gagal: ' . $e->getMessage());
        }

        return redirect()
            ->route('orders.show', $order)
            ->with('success', "Order {$order->order_number} berhasil di-refund. Stock telah dikembalikan.");
    }
}
