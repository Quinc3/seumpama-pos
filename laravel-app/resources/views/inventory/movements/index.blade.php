<x-app-layout title="Stock Movements" subtitle="Riwayat pergerakan stok bahan baku">
    <x-slot name="actions">
        @permission('inventory.manage')
        <x-button :href="route('inventory.movements.create-in')" tag="a" variant="primary" icon="arrow-right-left">
            Stock In
        </x-button>
        <x-button :href="route('inventory.movements.create-out')" tag="a" variant="outline" icon="arrow-right-left">
            Stock Out
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
            <div class="flex-1 min-w-[200px]">
                <label class="block text-xs font-medium text-slate-600 mb-1">Bahan</label>
                <select name="ingredient_id" class="w-full px-3 py-2 rounded-lg border border-slate-300 text-sm bg-white">
                    <option value="">Semua Bahan</option>
                    @foreach ($ingredients as $ing)
                    <option value="{{ $ing->id }}" @selected(request('ingredient_id')==$ing->id)>{{ $ing->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="w-48">
                <label class="block text-xs font-medium text-slate-600 mb-1">Tipe</label>
                <select name="movement_type" class="w-full px-3 py-2 rounded-lg border border-slate-300 text-sm bg-white">
                    <option value="">Semua Tipe</option>
                    @foreach ($allTypes as $type => $label)
                    <option value="{{ $type }}" @selected(request('movement_type')===$type)>{{ $label }}</option>
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
            @if (request()->hasAny(['ingredient_id', 'movement_type', 'from', 'to']))
            <a href="{{ route('inventory.movements.index') }}" class="text-sm text-slate-500 hover:text-slate-700 py-2">Reset</a>
            @endif
        </form>
    </x-card>

    <x-card padding="none">
        @if ($movements->isEmpty())
        <div class="p-12 text-center">
            <x-icon name="arrow-right-left" :size="48" class="mx-auto text-slate-300 mb-3" />
            <p class="text-slate-600 font-medium">Belum ada movement</p>
            <p class="text-sm text-slate-400 mt-1">Belum ada pergerakan stok yang tercatat.</p>
        </div>
        @else
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-slate-50 border-b border-slate-200">
                    <tr>
                        <th class="text-left px-4 py-3 font-medium text-slate-600">Waktu</th>
                        <th class="text-left px-4 py-3 font-medium text-slate-600">Bahan</th>
                        <th class="text-left px-4 py-3 font-medium text-slate-600">Tipe</th>
                        <th class="text-right px-4 py-3 font-medium text-slate-600">Qty</th>
                        <th class="text-right px-4 py-3 font-medium text-slate-600">Before</th>
                        <th class="text-right px-4 py-3 font-medium text-slate-600">After</th>
                        <th class="text-left px-4 py-3 font-medium text-slate-600">Catatan</th>
                        <th class="text-left px-4 py-3 font-medium text-slate-600">Oleh</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach ($movements as $m)
                    <tr class="hover:bg-slate-50">
                        <td class="px-4 py-3 text-slate-600 text-xs whitespace-nowrap">
                            {{ $m->created_at->format('d M Y H:i') }}
                        </td>
                        <td class="px-4 py-3">
                            <div class="font-medium text-slate-900">{{ $m->ingredient->name }}</div>
                            <div class="text-xs text-slate-400 font-mono">{{ $m->ingredient->sku }}</div>
                        </td>
                        <td class="px-4 py-3">
                            @php
                            $badgeVariant = match (true) {
                            in_array($m->movement_type, ['purchase_in', 'adjustment_in', 'return_in']) => 'success',
                            in_array($m->movement_type, ['waste', 'return_out']) => 'danger',
                            $m->movement_type === 'sale_out' => 'info',
                            default => 'warning',
                            };
                            @endphp
                            <x-badge :variant="$badgeVariant">{{ $m->movement_label }}</x-badge>
                        </td>
                        <td class="px-4 py-3 text-right font-mono font-medium {{ $m->isIncoming() ? 'text-green-700' : 'text-red-700' }}">
                            {{ $m->isIncoming() ? '+' : '−' }}{{ number_format((float) $m->quantity, 4, '.', ',') }}
                        </td>
                        <td class="px-4 py-3 text-right font-mono text-slate-600">{{ number_format((float) $m->stock_before, 4, '.', ',') }}</td>
                        <td class="px-4 py-3 text-right font-mono text-slate-900 font-medium">{{ number_format((float) $m->stock_after, 4, '.', ',') }}</td>
                        <td class="px-4 py-3 text-slate-500 text-xs max-w-xs truncate">{{ $m->notes ?: '—' }}</td>
                        <td class="px-4 py-3 text-slate-600 text-xs">{{ $m->createdBy?->name }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @if ($movements->hasPages())
        <div class="px-4 py-4 border-t border-slate-200">{{ $movements->links() }}</div>
        @endif
        @endif
    </x-card>
</x-app-layout>
