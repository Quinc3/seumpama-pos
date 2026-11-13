<x-app-layout :title="'Stock Opname ' . $opname->reference_number" :subtitle="$opname->opname_date->format('d M Y')">
    <x-slot name="actions">
        @permission('inventory.manage')
        @if ($opname->isDraft())
        <form method="POST" action="{{ route('stock-opnames.approve', $opname) }}" class="inline"
            onsubmit="return confirm('Approve opname ini? Adjustment movement akan dibuat dan tidak bisa diubah.');">
            @csrf
            <x-button type="submit" variant="primary" icon="clipboard-check">Approve</x-button>
        </form>
        <form method="POST" action="{{ route('stock-opnames.cancel', $opname) }}" class="inline"
            onsubmit="return confirm('Batalkan opname ini? Draft akan dibatalkan tanpa adjustment.');">
            @csrf
            <x-button type="submit" variant="danger" icon="x">Batalkan</x-button>
        </form>
        @endif
        @endpermission
        <x-button :href="route('stock-opnames.index')" tag="a" variant="outline">Kembali</x-button>
    </x-slot>

    @if (session('success'))
    <div class="mb-4"><x-alert variant="success" icon="clipboard-check" dismissible>{{ session('success') }}</x-alert></div>
    @endif
    @if (session('error'))
    <div class="mb-4"><x-alert variant="error" icon="shield" dismissible>{{ session('error') }}</x-alert></div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-4 gap-4 mb-4">
        <x-card title="Info" class="lg:col-span-1">
            <dl class="space-y-3 text-sm">
                <div>
                    <dt class="text-slate-500 text-xs mb-1">Reference</dt>
                    <dd class="font-mono font-medium text-slate-900">{{ $opname->reference_number }}</dd>
                </div>
                <div>
                    <dt class="text-slate-500 text-xs mb-1">Tanggal</dt>
                    <dd class="text-slate-900">{{ $opname->opname_date->format('d M Y') }}</dd>
                </div>
                <div>
                    <dt class="text-slate-500 text-xs mb-1">Status</dt>
                    <dd>
                        @if ($opname->status === 'draft')
                        <x-badge variant="warning">Draft</x-badge>
                        @elseif ($opname->status === 'approved')
                        <x-badge variant="success">Approved</x-badge>
                        @else
                        <x-badge variant="neutral">Cancelled</x-badge>
                        @endif
                    </dd>
                </div>
                <div>
                    <dt class="text-slate-500 text-xs mb-1">Dibuat Oleh</dt>
                    <dd class="text-slate-900">{{ $opname->createdBy?->name }}</dd>
                </div>
                @if ($opname->approved_at)
                <div>
                    <dt class="text-slate-500 text-xs mb-1">Di-approve Oleh</dt>
                    <dd class="text-slate-900">{{ $opname->approvedBy?->name }}</dd>
                    <dd class="text-xs text-slate-500">{{ $opname->approved_at->format('d M Y H:i') }}</dd>
                </div>
                @endif
            </dl>
            @if ($opname->notes)
            <div class="mt-3 pt-3 border-t border-slate-100">
                <dt class="text-slate-500 text-xs mb-1">Catatan</dt>
                <dd class="text-sm text-slate-700">{{ $opname->notes }}</dd>
            </div>
            @endif
        </x-card>

        <x-card title="Item yang Dihitung" :subtitle="$opname->items->count() . ' item'" class="lg:col-span-3">
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-slate-50 border-b border-slate-200">
                        <tr>
                            <th class="text-left px-3 py-2 font-medium text-slate-600">Bahan</th>
                            <th class="text-right px-3 py-2 font-medium text-slate-600">Sistem</th>
                            <th class="text-right px-3 py-2 font-medium text-slate-600">Fisik</th>
                            <th class="text-right px-3 py-2 font-medium text-slate-600">Selisih</th>
                            <th class="text-left px-3 py-2 font-medium text-slate-600">Catatan</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach ($opname->items as $item)
                        <tr>
                            <td class="px-3 py-2">
                                <div class="font-medium text-slate-900">{{ $item->ingredient->name }}</div>
                                <div class="text-xs text-slate-400 font-mono">{{ $item->ingredient->sku }}</div>
                            </td>
                            <td class="px-3 py-2 text-right font-mono text-slate-600">{{ number_format((float) $item->system_quantity, 4, '.', ',') }}</td>
                            <td class="px-3 py-2 text-right font-mono text-slate-900 font-medium">{{ number_format((float) $item->physical_quantity, 4, '.', ',') }}</td>
                            <td class="px-3 py-2 text-right font-mono font-medium
                                    {{ (float) $item->difference > 0 ? 'text-green-700' : ((float) $item->difference < 0 ? 'text-red-700' : 'text-slate-500') }}">
                                {{ (float) $item->difference > 0 ? '+' : '' }}{{ number_format((float) $item->difference, 4, '.', ',') }}
                            </td>
                            <td class="px-3 py-2 text-slate-500 text-xs">{{ $item->notes ?: '—' }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </x-card>
    </div>
</x-app-layout>
