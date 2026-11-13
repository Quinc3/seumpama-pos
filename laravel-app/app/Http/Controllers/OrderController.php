<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\Request;

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
}
