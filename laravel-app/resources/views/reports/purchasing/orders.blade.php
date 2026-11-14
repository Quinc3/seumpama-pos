<x-app-layout title="Purchase Order Report" subtitle="Riwayat PO dengan filter">
    <x-slot name="actions">
        <x-button :href="route('reports.purchasing.suppliers')" tag="a" variant="outline" icon="truck">
            Supplier Performance
        </x-button>
        <x-button :href="route('reports.purchasing.receipts')" tag="a" variant="outline" icon="arrow-right-left">
            Receipts History
        </x-button>
    </x-slot>

    {{-- Summary --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 mb-4">
        <x-card padding="sm">
            <p class="text-xs text-slate-500">Total PO</p>
            <p class="text-xl font-bold mt-1 font-mono text-slate-900">{{ number_format((int) $summary->total_po) }}</p>
        </x-card>
        <x-card padding="sm">
            <p class="text-xs text-slate-500">Total Nilai (Approved+)</p>
            <p class="text-xl font-bold mt-1 font-mono text-blue-600">Rp {{ number_format((float) $summary->total_approved_value, 0, ',', '.') }}</p>
        </x-card>
        <x-card padding="sm">
            <p class="text-xs text-slate-500">Received</p>
            <p class="text-xl font-bold mt-1 font-mono text-green-600">{{ number_format((int) $summary->total_received) }}</p>
        </x-card>
        <x-card padding="sm">
            <p class="text-xs text-slate-500">Pending</p>
            <p class="text-xl font-bold mt-1 font-mono text-amber-600">{{ number_format((int) $summary->total_pending) }}</p>
        </x-card>
    </div>

    {{-- Filters --}}
    <x-card padding="none" class="mb-4">
        <form method="GET" class="p-4 space-y-3">
            <div class="flex flex-wrap gap-2">
                <a href="{{ route('reports.purchasing.orders', ['period' => 'today']) }}"
                    class="px-3 py-1.5 rounded-lg text-sm font-medium {{ $period === 'today' ? 'bg-blue-600 text-white' : 'bg-slate-100 text-slate-700 hover:bg-slate-200' }}">Hari Ini</a>
                <a href="{{ route('reports.purchasing.orders', ['period' => 'week']) }}"
                    class="px-3 py-1.5 rounded-lg text-sm font-medium {{ $period === 'week' ? 'bg-blue-600 text-white' : 'bg-slate-100 text-slate-700 hover:bg-slate-200' }}">Minggu Ini</a>
                <a href="{{ route('reports.purchasing.orders', ['period' => 'month']) }}"
                    class="px-3 py-1.5 rounded-lg text-sm font-medium {{ $period === 'month' ? 'bg-blue-600 text-white' : 'bg-slate-100 text-slate-700 hover:bg-slate-200' }}">Bulan Ini</a>
            </div>

            <div class="flex flex-wrap gap-3 items-end pt-3 border-t border-slate-100">
                <div class="w-36">
                    <label class="block text-xs font-medium text-slate-600 mb-1">Dari</label>
                    <input type="date" name="from" value="{{ $from->format('Y-m-d') }}" class="w-full px-3 py-2 rounded-lg border border-slate-300 text-sm">
                </div>
                <div class="w-36">
                    <label class="block text-xs font-medium text-slate-600 mb-1">Sampai</label>
                    <input type="date" name="to" value="{{ $to->format('Y-m-d') }}" class="w-full px-3 py-2 rounded-lg border border-slate-300 text-sm">
                </div>
                <div class="w-44">
                    <label class="block text-xs font-medium text-slate-600 mb-1">Status</label>
                    <select name="status" class="w-full px-3 py-2 rounded-lg border border-slate-300 text-sm bg-white">
                        <option value="">Semua</option>
                        @foreach ($statuses as $v => $l)
                        <option value="{{ $v }}" @selected(request('status')===$v)>{{ $l }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="w-52">
                    <label class="block text-xs font-medium text-slate-600 mb-1">Supplier</label>
                    <select name="supplier_id" class="w-full px-3 py-2 rounded-lg border border-slate-300 text-sm bg-white">
                        <option value="">Semua</option>
                        @foreach ($suppliers as $s)
                        <option value="{{ $s->id }}" @selected(request('supplier_id')==$s->id)>{{ $s->name }}</option>
                        @endforeach
                    </select>
                </div>
                <input type="hidden" name="period" value="custom">
                <button type="submit" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-sm font-medium rounded-lg">Terapkan</button>
                @if (request()->hasAny(['status','supplier_id','from','to']))
                <a href="{{ route('reports.purchasing.orders') }}" class="text-sm text-slate-500 hover:text-slate-700 py-2">Reset</a>
                @endif
            </div>

            <div class="text-xs text-slate-500">Periode: <strong>{{ $from->format('d M Y') }} — {{ $to->format('d M Y') }}</strong></div>
        </form>
    </x-card>

    {{-- Table --}}
    <x-card padding="none">
        @if ($orders->isEmpty())
        <div class="p-12 text-center">
            <x-icon name="shopping-bag" :size="48" class="mx-auto text-slate-300 mb-3" />
            <p class="text-slate-600 font-medium">Tidak ada PO pada periode ini</p>
        </div>
        @else
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-slate-50 border-b border-slate-200">
                    <tr>
                        <th class="text-left px-3 py-3 font-medium text-slate-600">PO#</th>
                        <th class="text-left px-3 py-3 font-medium text-slate-600">Supplier</th>
                        <th class="text-left px-3 py-3 font-medium text-slate-600">Tanggal</th>
                        <th class="text-center px-3 py-3 font-medium text-slate-600">Items</th>
                        <th class="text-right px-3 py-3 font-medium text-slate-600">Total</th>
                        <th class="text-left px-3 py-3 font-medium text-slate-600">Status</th>
                        <th class="text-left px-3 py-3 font-medium text-slate-600">Dibuat</th>
                        <th class="text-right px-3 py-3 font-medium text-slate-600"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach ($orders as $po)
                    <tr class="hover:bg-slate-50">
                        <td class="px-3 py-2 font-mono text-xs text-slate-900 font-medium">{{ $po->purchase_number }}</td>
                        <td class="px-3 py-2 text-slate-700 text-sm">{{ $po->supplier_name }}</td>
                        <td class="px-3 py-2 text-slate-600 text-xs">{{ \Carbon\Carbon::parse($po->purchase_date)->format('d M Y') }}</td>
                        <td class="px-3 py-2 text-center font-mono text-slate-700">{{ $po->item_count }}</td>
                        <td class="px-3 py-2 text-right font-mono text-slate-900">Rp {{ number_format((float) $po->total_amount, 0, ',', '.') }}</td>
                        <td class="px-3 py-2">
                            @php
                            $variant = match($po->status) {
                            'draft' => 'neutral',
                            'approved' => 'info',
                            'partially_received' => 'warning',
                            'received' => 'success',
                            'cancelled' => 'danger',
                            default => 'neutral',
                            };
                            @endphp
                            <x-badge :variant="$variant">{{ $statuses[$po->status] ?? $po->status }}</x-badge>
                        </td>
                        <td class="px-3 py-2 text-slate-600 text-xs">{{ $po->user_name }}</td>
                        <td class="px-3 py-2 text-right">
                            <a href="{{ route('purchase-orders.show', $po->id) }}" class="text-sm text-blue-600 hover:text-blue-800 font-medium">Detail</a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @if ($orders->hasPages())
        <div class="px-4 py-3 border-t border-slate-200">{{ $orders->links() }}</div>
        @endif
        @endif
    </x-card>
</x-app-layout>
