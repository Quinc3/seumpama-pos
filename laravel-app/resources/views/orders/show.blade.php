<x-app-layout :title="'Order ' . $order->order_number" :subtitle="$order->created_at->format('d M Y H:i')">
    <x-slot name="actions">
        <x-button :href="route('orders.index')" tag="a" variant="outline">Kembali</x-button>
        <x-button :href="route('orders.receipt', $order)" tag="a" variant="outline" icon="receipt" target="_blank">
            Print Struk
        </x-button>
        <x-button :href="route('orders.pdf', $order)" tag="a" variant="outline" icon="clipboard-check">
            Download PDF
        </x-button>
        <x-button :href="route('pos.index')" tag="a" variant="primary" icon="shopping-cart">
            Order Baru
        </x-button>
        @permission('pos.refund')
        @if ($order->isPaid())
        <button type="button"
            onclick="if (confirm('Refund order ini? Stok akan dikembalikan dan tidak bisa dibatalkan.')) { document.getElementById('refund-modal').classList.remove('hidden'); }"
            class="inline-flex items-center gap-2 px-4 py-2 rounded-lg bg-red-600 text-white text-sm font-medium hover:bg-red-700 transition-colors">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M3 12a9 9 0 1 0 9-9 9.75 9.75 0 0 0-6.74 2.74L3 8" />
                <path d="M3 3v5h5" />
            </svg>
            Refund
        </button>
        @endif
        @endpermission

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
                    <dd>
                        @php
                        $statusVariant = match($order->status) {
                        'paid' => 'success',
                        'refunded' => 'warning',
                        'cancelled' => 'danger',
                        default => 'neutral',
                        };
                        @endphp
                        <x-badge :variant="$statusVariant">{{ ucfirst($order->status) }}</x-badge>
                    </dd>
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

    {{-- Refund Modal --}}
    <div id="refund-modal" class="hidden fixed inset-0 z-50 flex items-center justify-center p-4">
        <div class="absolute inset-0 bg-slate-900/50" onclick="document.getElementById('refund-modal').classList.add('hidden')"></div>

        <div class="relative bg-white rounded-xl shadow-2xl w-full max-w-md overflow-hidden">
            <form method="POST" action="{{ route('orders.refund', $order) }}">
                @csrf
                <div class="px-5 py-4 border-b border-slate-200 flex items-center justify-between">
                    <h3 class="text-base font-bold text-slate-900">Refund Order {{ $order->order_number }}</h3>
                    <button type="button" onclick="document.getElementById('refund-modal').classList.add('hidden')"
                        class="text-slate-400 hover:text-slate-600">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M18 6 6 18" />
                            <path d="m6 6 12 12" />
                        </svg>
                    </button>
                </div>

                <div class="p-5 space-y-4">
                    <p class="text-sm text-slate-600">
                        Stock bahan akan dikembalikan via movement <strong>return_in</strong>.
                        Status order berubah menjadi <strong>Refunded</strong>. Tindakan tidak dapat dibatalkan.
                    </p>

                    <div>
                        <label for="reason" class="block text-sm font-medium text-slate-700 mb-1">
                            Alasan Refund <span class="text-red-500">*</span>
                        </label>
                        <textarea id="reason" name="reason" rows="3" required minlength="5" maxlength="500"
                            placeholder="Contoh: Customer komplain, salah pesanan"
                            class="w-full px-3 py-2 rounded-lg border border-slate-300 text-sm focus:ring-2 focus:ring-red-500 focus:border-red-500"></textarea>
                    </div>

                    <div class="p-3 bg-amber-50 border border-amber-200 rounded-lg text-xs text-amber-800">
                        <strong>Total refund:</strong> Rp {{ number_format((float) $order->total_amount, 0, ',', '.') }} (kembalikan dana secara manual ke customer)
                    </div>
                </div>

                <div class="px-5 py-3 bg-slate-50 border-t border-slate-200 flex justify-end gap-2">
                    <button type="button" onclick="document.getElementById('refund-modal').classList.add('hidden')"
                        class="px-4 py-2 text-sm font-medium text-slate-700 bg-white border border-slate-300 rounded-lg hover:bg-slate-50">
                        Batal
                    </button>
                    <button type="submit"
                        class="px-4 py-2 text-sm font-medium text-white bg-red-600 rounded-lg hover:bg-red-700">
                        Proses Refund
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
