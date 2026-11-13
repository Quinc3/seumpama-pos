<x-app-layout title="Purchasing" subtitle="Purchase order bahan baku">
    <x-slot name="actions">
        @permission('inventory.manage')
        <x-button :href="route('purchase-orders.create')" tag="a" variant="primary" icon="shopping-bag">
            Buat PO
        </x-button>
        @endpermission
    </x-slot>

    @if (session('success'))
    <div class="mb-4"><x-alert variant="success" icon="clipboard-check" dismissible>{{ session('success') }}</x-alert></div>
    @endif
    @if (session('error'))
    <div class="mb-4"><x-alert variant="error" icon="shield" dismissible>{{ session('error') }}</x-alert></div>
    @endif

    <x-card padding="none" class="mb-4">
        <form method="GET" class="p-4 flex flex-wrap gap-3 items-end">
            <div class="w-40">
                <label class="block text-xs font-medium text-slate-600 mb-1">Status</label>
                <select name="status" class="w-full px-3 py-2 rounded-lg border border-slate-300 text-sm bg-white">
                    <option value="">Semua</option>
                    @foreach (['draft'=>'Draft','approved'=>'Approved','partially_received'=>'Partially Received','received'=>'Received','cancelled'=>'Cancelled'] as $v=>$l)
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
            <button type="submit" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-sm font-medium rounded-lg">Filter</button>
            @if (request()->hasAny(['status','supplier_id']))
            <a href="{{ route('purchase-orders.index') }}" class="text-sm text-slate-500 hover:text-slate-700 py-2">Reset</a>
            @endif
        </form>
    </x-card>

    <x-card padding="none">
        @if ($orders->isEmpty())
        <div class="p-12 text-center">
            <x-icon name="shopping-bag" :size="48" class="mx-auto text-slate-300 mb-3" />
            <p class="text-slate-600 font-medium">Belum ada purchase order</p>
        </div>
        @else
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-slate-50 border-b border-slate-200">
                    <tr>
                        <th class="text-left px-4 py-3 font-medium text-slate-600">PO#</th>
                        <th class="text-left px-4 py-3 font-medium text-slate-600">Supplier</th>
                        <th class="text-left px-4 py-3 font-medium text-slate-600">Tanggal</th>
                        <th class="text-center px-4 py-3 font-medium text-slate-600">Items</th>
                        <th class="text-right px-4 py-3 font-medium text-slate-600">Total</th>
                        <th class="text-left px-4 py-3 font-medium text-slate-600">Status</th>
                        <th class="text-right px-4 py-3 font-medium text-slate-600">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach ($orders as $po)
                    <tr class="hover:bg-slate-50">
                        <td class="px-4 py-3 font-mono text-xs text-slate-900 font-medium">{{ $po->purchase_number }}</td>
                        <td class="px-4 py-3 text-slate-700">{{ $po->supplier?->name }}</td>
                        <td class="px-4 py-3 text-slate-600 text-xs">{{ $po->purchase_date->format('d M Y') }}</td>
                        <td class="px-4 py-3 text-center font-mono text-slate-700">{{ $po->items_count }}</td>
                        <td class="px-4 py-3 text-right font-mono text-slate-900">Rp {{ number_format((float) $po->total_amount, 0, ',', '.') }}</td>
                        <td class="px-4 py-3">
                            @php
                            $v = match($po->status) {
                            'draft' => 'neutral',
                            'approved' => 'info',
                            'partially_received' => 'warning',
                            'received' => 'success',
                            'cancelled' => 'danger',
                            default => 'neutral',
                            };
                            @endphp
                            <x-badge :variant="$v">{{ ucfirst(str_replace('_',' ',$po->status)) }}</x-badge>
                        </td>
                        <td class="px-4 py-3 text-right">
                            <a href="{{ route('purchase-orders.show', $po) }}"
                                class="text-sm text-blue-600 hover:text-blue-800 font-medium">Detail</a>
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
