<x-app-layout title="Stock Movement Report" subtitle="Riwayat pergerakan stok dengan filter">
    {{-- Summary --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-3 mb-4">
        <x-card padding="sm">
            <p class="text-xs text-slate-500">Total Movement</p>
            <p class="text-xl font-bold mt-1 font-mono text-slate-900">{{ number_format($summary->total_movements ?? 0) }}</p>
        </x-card>
        <x-card padding="sm">
            <p class="text-xs text-slate-500">Total Masuk</p>
            <p class="text-xl font-bold mt-1 font-mono text-green-600">{{ number_format((float) ($summary->total_in ?? 0), 2) }}</p>
        </x-card>
        <x-card padding="sm">
            <p class="text-xs text-slate-500">Total Keluar</p>
            <p class="text-xl font-bold mt-1 font-mono text-red-600">{{ number_format((float) ($summary->total_out ?? 0), 2) }}</p>
        </x-card>
    </div>

    {{-- Filter --}}
    <x-card padding="none" class="mb-4">
        <form method="GET" class="p-4 flex flex-wrap gap-3 items-end">
            <div class="w-52">
                <label class="block text-xs font-medium text-slate-600 mb-1">Bahan</label>
                <select name="ingredient_id" class="w-full px-3 py-2 rounded-lg border border-slate-300 text-sm bg-white">
                    <option value="">Semua</option>
                    @foreach ($ingredients as $ing)
                    <option value="{{ $ing->id }}" @selected(request('ingredient_id')==$ing->id)>{{ $ing->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="w-44">
                <label class="block text-xs font-medium text-slate-600 mb-1">Tipe</label>
                <select name="movement_type" class="w-full px-3 py-2 rounded-lg border border-slate-300 text-sm bg-white">
                    <option value="">Semua</option>
                    @foreach ($movementTypes as $type => $label)
                    <option value="{{ $type }}" @selected(request('movement_type')===$type)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="w-36">
                <label class="block text-xs font-medium text-slate-600 mb-1">Dari</label>
                <input type="date" name="from" value="{{ $from->format('Y-m-d') }}" class="w-full px-3 py-2 rounded-lg border border-slate-300 text-sm">
            </div>
            <div class="w-36">
                <label class="block text-xs font-medium text-slate-600 mb-1">Sampai</label>
                <input type="date" name="to" value="{{ $to->format('Y-m-d') }}" class="w-full px-3 py-2 rounded-lg border border-slate-300 text-sm">
            </div>
            <button type="submit" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-sm font-medium rounded-lg">Filter</button>
            @if (request()->hasAny(['ingredient_id','movement_type','from','to']))
            <a href="{{ route('reports.movements') }}" class="text-sm text-slate-500 hover:text-slate-700 py-2">Reset</a>
            @endif
        </form>
    </x-card>

    {{-- Table --}}
    <x-card padding="none">
        @if ($movements->isEmpty())
        <div class="p-12 text-center">
            <x-icon name="arrow-right-left" :size="48" class="mx-auto text-slate-300 mb-3" />
            <p class="text-slate-600 font-medium">Tidak ada movement</p>
        </div>
        @else
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-slate-50 border-b border-slate-200">
                    <tr>
                        <th class="text-left px-3 py-2 font-medium text-slate-600">Waktu</th>
                        <th class="text-left px-3 py-2 font-medium text-slate-600">Bahan</th>
                        <th class="text-left px-3 py-2 font-medium text-slate-600">Tipe</th>
                        <th class="text-right px-3 py-2 font-medium text-slate-600">Qty</th>
                        <th class="text-right px-3 py-2 font-medium text-slate-600">Before</th>
                        <th class="text-right px-3 py-2 font-medium text-slate-600">After</th>
                        <th class="text-left px-3 py-2 font-medium text-slate-600">Oleh</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach ($movements as $m)
                    <tr class="hover:bg-slate-50">
                        <td class="px-3 py-2 text-slate-600 text-xs">{{ \Carbon\Carbon::parse($m->created_at)->format('d M Y H:i') }}</td>
                        <td class="px-3 py-2">
                            <div class="font-medium text-slate-900">{{ $m->ingredient_name }}</div>
                            <div class="text-xs text-slate-400 font-mono">{{ $m->ingredient_sku }}</div>
                        </td>
                        <td class="px-3 py-2">
                            @php
                            $isIn = in_array($m->movement_type, ['purchase_in','adjustment_in','return_in']);
                            $variant = $isIn ? 'success' : ($m->movement_type === 'sale_out' ? 'info' : 'warning');
                            @endphp
                            <x-badge :variant="$variant">{{ $movementTypes[$m->movement_type] ?? $m->movement_type }}</x-badge>
                        </td>
                        <td class="px-3 py-2 text-right font-mono font-medium {{ $isIn ? 'text-green-700' : 'text-red-700' }}">
                            {{ $isIn ? '+' : '−' }}{{ number_format((float) $m->quantity, 2, '.', ',') }}
                        </td>
                        <td class="px-3 py-2 text-right font-mono text-slate-600 text-xs">{{ number_format((float) $m->stock_before, 2) }}</td>
                        <td class="px-3 py-2 text-right font-mono text-slate-900 font-medium">{{ number_format((float) $m->stock_after, 2) }}</td>
                        <td class="px-3 py-2 text-slate-600 text-xs">{{ $m->user_name }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @if ($movements->hasPages())
        <div class="px-4 py-3 border-t border-slate-200">{{ $movements->links() }}</div>
        @endif
        @endif
    </x-card>
</x-app-layout>
