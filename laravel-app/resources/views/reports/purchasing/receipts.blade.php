<x-app-layout title="Receipts History" subtitle="Riwayat penerimaan barang dari supplier">
    <x-slot name="actions">
        <x-button :href="route('reports.purchasing.orders')" tag="a" variant="outline">PO History</x-button>
        <x-button :href="route('reports.purchasing.suppliers')" tag="a" variant="outline">Supplier Performance</x-button>
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
            <div class="w-52">
                <label class="block text-xs font-medium text-slate-600 mb-1">Supplier</label>
                <select name="supplier_id" class="w-full px-3 py-2 rounded-lg border border-slate-300 text-sm bg-white">
                    <option value="">Semua</option>
                    @foreach ($suppliers as $s)
                    <option value="{{ $s->id }}" @selected(request('supplier_id')==$s->id)>{{ $s->name }}</option>
                    @endforeach
                </select>
            </div>
            <button type="submit" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-sm font-medium rounded-lg">Terapkan</button>
            @if (request()->hasAny(['supplier_id','from','to']))
            <a href="{{ route('reports.purchasing.receipts') }}" class="text-sm text-slate-500 hover:text-slate-700 py-2">Reset</a>
            @endif
        </form>
    </x-card>

    <x-card padding="none">
        @if ($receipts->isEmpty())
        <div class="p-12 text-center">
            <x-icon name="arrow-right-left" :size="48" class="mx-auto text-slate-300 mb-3" />
            <p class="text-slate-600 font-medium">Tidak ada penerimaan pada periode ini</p>
        </div>
        @else
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-slate-50 border-b border-slate-200">
                    <tr>
                        <th class="text-left px-3 py-3 font-medium text-slate-600">GR#</th>
                        <th class="text-left px-3 py-3 font-medium text-slate-600">PO Ref</th>
                        <th class="text-left px-3 py-3 font-medium text-slate-600">Supplier</th>
                        <th class="text-left px-3 py-3 font-medium text-slate-600">Diterima</th>
                        <th class="text-center px-3 py-3 font-medium text-slate-600">Items</th>
                        <th class="text-right px-3 py-3 font-medium text-slate-600">Total Qty</th>
                        <th class="text-left px-3 py-3 font-medium text-slate-600">Penerima</th>
                        <th class="text-right px-3 py-3 font-medium text-slate-600"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach ($receipts as $gr)
                    <tr class="hover:bg-slate-50">
                        <td class="px-3 py-2 font-mono text-xs font-medium text-slate-900">{{ $gr->receipt_number }}</td>
                        <td class="px-3 py-2 font-mono text-xs text-slate-600">{{ $gr->purchase_number }}</td>
                        <td class="px-3 py-2 text-slate-700 text-sm">{{ $gr->supplier_name }}</td>
                        <td class="px-3 py-2 text-slate-600 text-xs">{{ \Carbon\Carbon::parse($gr->received_at)->format('d M Y H:i') }}</td>
                        <td class="px-3 py-2 text-center font-mono text-slate-700">{{ $gr->item_count }}</td>
                        <td class="px-3 py-2 text-right font-mono text-slate-900 font-medium">{{ number_format((float) $gr->total_qty, 2) }}</td>
                        <td class="px-3 py-2 text-slate-600 text-xs">{{ $gr->receiver_name }}</td>
                        <td class="px-3 py-2 text-right">
                            <a href="{{ route('goods-receipts.show', $gr->id) }}" class="text-sm text-blue-600 hover:text-blue-800 font-medium">Detail</a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @if ($receipts->hasPages())
        <div class="px-4 py-3 border-t border-slate-200">{{ $receipts->links() }}</div>
        @endif
        @endif
    </x-card>
</x-app-layout>
