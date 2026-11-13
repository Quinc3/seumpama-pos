<x-app-layout :title="'Order ' . $order->order_number" :subtitle="$order->created_at->format('d M Y H:i')">
    <x-slot name="actions">
        <x-button :href="route('orders.index')" tag="a" variant="outline">Kembali</x-button>
        <x-button :href="route('pos.index')" tag="a" variant="primary" icon="shopping-cart">
            Order Baru
        </x-button>
    </x-slot>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
        {{-- Order info --}}
        <x-card title="Info" class="lg:col-span-1">
            <dl class="space-y-3 text-sm">
                <div>
                    <dt class="text-slate-500 text-xs">Order#</dt>
                    <dd class="font-mono font-medium text-slate-900">{{ $order->order_number }}</dd>
                </div>
                <div>
                    <dt class="text-slate-500 text-xs">Status</dt>
                    <dd><x-badge :variant="$order->status === 'paid' ? 'success' : 'neutral'">{{ ucfirst($order->status) }}</x-badge></dd>
                </div>
                <div>
                    <dt class="text-slate-500 text-xs">Kasir</dt>
                    <dd class="text-slate-900">{{ $order->user?->name }}</dd>
                </div>
                <div>
                    <dt class="text-slate-500 text-xs">Business Date</dt>
                    <dd class="text-slate-900">{{ $order->businessSession?->business_date->format('d M Y') }}</dd>
                </div>
                <div>
                    <dt class="text-slate-500 text-xs">Waktu</dt>
                    <dd class="text-slate-900">{{ $order->created_at->format('d M Y H:i') }}</dd>
                </div>
                @if ($order->notes)
                <div class="pt-3 border-t border-slate-100">
                    <dt class="text-slate-500 text-xs">Catatan</dt>
                    <dd class="text-slate-700">{{ $order->notes }}</dd>
                </div>
                @endif
            </dl>
        </x-card>

        {{-- Items --}}
        <x-card title="Item" :subtitle="$order->items->count() . ' jenis'" class="lg:col-span-2">
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-slate-50 border-b border-slate-200">
                        <tr>
                            <th class="text-left px-3 py-2 font-medium text-slate-600">Produk</th>
                            <th class="text-right px-3 py-2 font-medium text-slate-600">Qty</th>
                            <th class="text-right px-3 py-2 font-medium text-slate-600">Harga</th>
                            <th class="text-right px-3 py-2 font-medium text-slate-600">Subtotal</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach ($order->items as $item)
                        <tr>
                            <td class="px-3 py-2">
                                <div class="font-medium text-slate-900">{{ $item->product_name }}</div>
                                <div class="text-xs text-slate-400 font-mono">{{ $item->product?->sku }}</div>
                            </td>
                            <td class="px-3 py-2 text-right font-mono text-slate-900">{{ number_format((float) $item->quantity, 2) }}</td>
                            <td class="px-3 py-2 text-right font-mono text-slate-700">Rp {{ number_format((float) $item->unit_price, 0, ',', '.') }}</td>
                            <td class="px-3 py-2 text-right font-mono font-medium text-slate-900">Rp {{ number_format((float) $item->subtotal, 0, ',', '.') }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </x-card>
    </div>

    {{-- Payments --}}
    <div class="mt-4">
        <x-card title="Pembayaran">
            @foreach ($order->payments as $p)
            <div class="flex justify-between items-center py-2 {{ !$loop->last ? 'border-b border-slate-100' : '' }}">
                <div>
                    <div class="text-sm font-medium text-slate-900">{{ $p->paymentMethod?->name }}</div>
                    @if ($p->reference_number)
                    <div class="text-xs text-slate-500">Ref: {{ $p->reference_number }}</div>
                    @endif
                </div>
                <div class="text-right">
                    <div class="font-mono font-bold text-slate-900">Rp {{ number_format((float) $p->amount, 0, ',', '.') }}</div>
                    @if ($p->paymentMethod?->code === 'cash' && (float) $p->amount > (float) $order->total_amount)
                    <div class="text-xs text-green-700">Kembali: Rp {{ number_format((float) $p->amount - (float) $order->total_amount, 0, ',', '.') }}</div>
                    @endif
                </div>
            </div>
            @endforeach

            <div class="mt-3 pt-3 border-t-2 border-slate-200 flex justify-between items-center">
                <span class="text-base font-semibold text-slate-900">Total</span>
                <span class="text-xl font-bold text-slate-900 font-mono">Rp {{ number_format((float) $order->total_amount, 0, ',', '.') }}</span>
            </div>
        </x-card>
    </div>
</x-app-layout>
