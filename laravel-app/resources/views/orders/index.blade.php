<x-app-layout title="Transactions" subtitle="Riwayat transaksi penjualan">
    <x-slot name="actions">
        <x-button :href="route('pos.index')" tag="a" variant="primary" icon="shopping-cart">
            Buka POS
        </x-button>
    </x-slot>

    @if (session('success'))
    <div class="mb-4"><x-alert variant="success" icon="clipboard-check" dismissible>{{ session('success') }}</x-alert></div>
    @endif

    <x-card padding="none" class="mb-4">
        <form method="GET" class="p-4 flex flex-wrap gap-3 items-end">
            <div class="flex-1 min-w-[200px]">
                <label class="block text-xs font-medium text-slate-600 mb-1">Cari Order#</label>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="ORD-..."
                    class="w-full px-3 py-2 rounded-lg border border-slate-300 text-sm">
            </div>
            <div class="w-40">
                <label class="block text-xs font-medium text-slate-600 mb-1">Status</label>
                <select name="status" class="w-full px-3 py-2 rounded-lg border border-slate-300 text-sm bg-white">
                    <option value="">Semua</option>
                    @foreach (['paid'=>'Paid','cancelled'=>'Cancelled','refunded'=>'Refunded'] as $v=>$l)
                    <option value="{{ $v }}" @selected(request('status')===$v)>{{ $l }}</option>
                    @endforeach
                </select>
            </div>
            <div class="w-36">
                <label class="block text-xs font-medium text-slate-600 mb-1">Dari</label>
                <input type="date" name="from" value="{{ request('from') }}" class="w-full px-3 py-2 rounded-lg border border-slate-300 text-sm">
            </div>
            <div class="w-36">
                <label class="block text-xs font-medium text-slate-600 mb-1">Sampai</label>
                <input type="date" name="to" value="{{ request('to') }}" class="w-full px-3 py-2 rounded-lg border border-slate-300 text-sm">
            </div>
            <button type="submit" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-sm font-medium rounded-lg">Filter</button>
            @if (request()->hasAny(['search','status','from','to']))
            <a href="{{ route('orders.index') }}" class="text-sm text-slate-500 hover:text-slate-700 py-2">Reset</a>
            @endif
        </form>
    </x-card>

    <x-card padding="none">
        @if ($orders->isEmpty())
        <div class="p-12 text-center">
            <x-icon name="receipt" :size="48" class="mx-auto text-slate-300 mb-3" />
            <p class="text-slate-600 font-medium">Belum ada transaksi</p>
            <p class="text-sm text-slate-400 mt-1">Transaksi pertama akan muncul di sini.</p>
        </div>
        @else
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-slate-50 border-b border-slate-200">
                    <tr>
                        <th class="text-left px-4 py-3 font-medium text-slate-600">Order#</th>
                        <th class="text-left px-4 py-3 font-medium text-slate-600">Waktu</th>
                        <th class="text-left px-4 py-3 font-medium text-slate-600">Kasir</th>
                        <th class="text-center px-4 py-3 font-medium text-slate-600">Items</th>
                        <th class="text-right px-4 py-3 font-medium text-slate-600">Total</th>
                        <th class="text-left px-4 py-3 font-medium text-slate-600">Metode</th>
                        <th class="text-left px-4 py-3 font-medium text-slate-600">Status</th>
                        <th class="text-right px-4 py-3 font-medium text-slate-600">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach ($orders as $o)
                    <tr class="hover:bg-slate-50">
                        <td class="px-4 py-3 font-mono text-xs font-medium text-slate-900">{{ $o->order_number }}</td>
                        <td class="px-4 py-3 text-xs text-slate-600">{{ $o->created_at->format('d M Y H:i') }}</td>
                        <td class="px-4 py-3 text-xs text-slate-600">{{ $o->user?->name }}</td>
                        <td class="px-4 py-3 text-center font-mono text-slate-700">{{ $o->items->count() }}</td>
                        <td class="px-4 py-3 text-right font-mono font-medium text-slate-900">Rp {{ number_format((float) $o->total_amount, 0, ',', '.') }}</td>
                        <td class="px-4 py-3 text-xs text-slate-600">{{ $o->payments->first()?->paymentMethod?->name ?? '—' }}</td>
                        <td class="px-4 py-3">
                            @php
                            $v = match($o->status) {
                            'paid' => 'success',
                            'refunded' => 'warning',
                            'cancelled' => 'danger',
                            default => 'neutral',
                            };
                            @endphp
                            <x-badge :variant="$v">{{ ucfirst($o->status) }}</x-badge>
                        </td>
                        <td class="px-4 py-3 text-right">
                            <a href="{{ route('orders.show', $o) }}" class="text-sm text-blue-600 hover:text-blue-800 font-medium">Detail</a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @if ($orders->hasPages())
        <div class="px-4 py-4 border-t border-slate-200">{{ $orders->links() }}</div>
        @endif
        @endif
    </x-card>
</x-app-layout>
