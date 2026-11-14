<x-app-layout title="Supplier Performance" subtitle="Performa supplier berdasarkan PO dan penerimaan">
    <x-slot name="actions">
        <x-button :href="route('reports.purchasing.orders')" tag="a" variant="outline">PO History</x-button>
        <x-button :href="route('reports.purchasing.receipts')" tag="a" variant="outline">Receipts</x-button>
    </x-slot>

    <x-card padding="none" class="mb-4">
        <form method="GET" class="p-4 flex flex-wrap gap-3 items-end">
            <div class="w-36">
                <label class="block text-xs font-medium text-slate-600 mb-1">Dari</label>
                <input type="date" name="from" value="{{ $from->format('Y-m-d') }}" class="w-full px-3 py-2 rounded-lg border border-slate-300 text-sm">
            </div>
            <div class="w-36">
                <label class="block text-xs font-medium text-slate-600 mb-1">Sampai</label>
                <input type="date" name="to" value="{{ $to->format('Y-m-d') }}" class="w-full px-3 py-2 rounded-lg border border-slate-300 text-sm">
            </div>
            <button type="submit" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-sm font-medium rounded-lg">Terapkan</button>
        </form>
    </x-card>

    <x-card padding="none">
        @if ($rows->isEmpty())
        <div class="p-12 text-center">
            <x-icon name="truck" :size="48" class="mx-auto text-slate-300 mb-3" />
            <p class="text-slate-600 font-medium">Belum ada data supplier</p>
        </div>
        @else
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-slate-50 border-b border-slate-200">
                    <tr>
                        <th class="text-left px-3 py-3 font-medium text-slate-600">Supplier</th>
                        <th class="text-center px-3 py-3 font-medium text-slate-600">PO Count</th>
                        <th class="text-right px-3 py-3 font-medium text-slate-600">Total Value</th>
                        <th class="text-right px-3 py-3 font-medium text-slate-600">Avg Lead Time</th>
                        <th class="text-center px-3 py-3 font-medium text-slate-600">Min-Max (hari)</th>
                        <th class="text-left px-3 py-3 font-medium text-slate-600">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach ($rows as $row)
                    <tr class="hover:bg-slate-50">
                        <td class="px-3 py-2 font-medium text-slate-900">{{ $row->name }}</td>
                        <td class="px-3 py-2 text-center font-mono text-slate-700">{{ number_format((int) $row->total_po) }}</td>
                        <td class="px-3 py-2 text-right font-mono text-slate-900">Rp {{ number_format((float) $row->total_value, 0, ',', '.') }}</td>
                        <td class="px-3 py-2 text-right font-mono text-slate-700">
                            @if ($row->avg_lead_days !== null)
                            {{ number_format((float) $row->avg_lead_days, 1) }} hari
                            @else
                            <span class="text-slate-400">—</span>
                            @endif
                        </td>
                        <td class="px-3 py-2 text-center font-mono text-slate-600 text-xs">
                            @if ($row->min_lead_days !== null)
                            {{ $row->min_lead_days }} - {{ $row->max_lead_days }}
                            @else
                            —
                            @endif
                        </td>
                        <td class="px-3 py-2">
                            @if ($row->is_active)
                            <x-badge variant="success">Aktif</x-badge>
                            @else
                            <x-badge variant="neutral">Nonaktif</x-badge>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @endif
    </x-card>
</x-app-layout>
