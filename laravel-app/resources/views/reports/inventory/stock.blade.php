<x-app-layout title="Stock Overview" subtitle="Ringkasan stok seluruh bahan baku">
    <x-slot name="actions">
        <x-button :href="route('reports.stock.pdf', request()->query())" tag="a" variant="outline" icon="receipt">
            Export PDF
        </x-button>
    </x-slot>

    {{-- Summary Cards --}}
    <div class="grid grid-cols-2 lg:grid-cols-5 gap-3 mb-4">
        @php
        $cards = [
        ['label' => 'Total Bahan', 'value' => $summary['total'], 'color' => 'slate'],
        ['label' => 'Normal', 'value' => $summary['normal'], 'color' => 'green'],
        ['label' => 'Menipis', 'value' => $summary['warning'], 'color' => 'amber'],
        ['label' => 'Kritis', 'value' => $summary['critical'], 'color' => 'red'],
        ['label' => 'Habis', 'value' => $summary['out'], 'color' => 'red'],
        ];
        @endphp
        @foreach ($cards as $card)
        <x-card padding="sm">
            <p class="text-xs text-slate-500">{{ $card['label'] }}</p>
            <p class="text-xl font-bold mt-1 font-mono text-{{ $card['color'] }}-600">{{ $card['value'] }}</p>
        </x-card>
        @endforeach
    </div>

    {{-- Filter --}}
    <x-card padding="none" class="mb-4">
        <form method="GET" class="p-4 flex flex-wrap gap-3 items-end">
            <div class="w-44">
                <label class="block text-xs font-medium text-slate-600 mb-1">Tipe Bahan</label>
                <select name="type" class="w-full px-3 py-2 rounded-lg border border-slate-300 text-sm bg-white">
                    <option value="">Semua</option>
                    <option value="core" @selected($filterType==='core' )>Core</option>
                    <option value="non_core" @selected($filterType==='non_core' )>Non-Core</option>
                </select>
            </div>
            <div class="w-44">
                <label class="block text-xs font-medium text-slate-600 mb-1">Status Stok</label>
                <select name="status" class="w-full px-3 py-2 rounded-lg border border-slate-300 text-sm bg-white">
                    <option value="">Semua</option>
                    <option value="normal" @selected($filterStatus==='normal' )>Normal</option>
                    <option value="warning" @selected($filterStatus==='warning' )>Menipis</option>
                    <option value="critical" @selected($filterStatus==='critical' )>Kritis</option>
                    <option value="out" @selected($filterStatus==='out' )>Habis</option>
                </select>
            </div>
            <button type="submit" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-sm font-medium rounded-lg">Filter</button>
            @if ($filterType || $filterStatus)
            <a href="{{ route('reports.stock') }}" class="text-sm text-slate-500 hover:text-slate-700 py-2">Reset</a>
            @endif
        </form>
    </x-card>

    {{-- Table --}}
    <x-card padding="none">
        @if ($items->isEmpty())
        <div class="p-12 text-center">
            <x-icon name="package" :size="48" class="mx-auto text-slate-300 mb-3" />
            <p class="text-slate-600 font-medium">Tidak ada bahan</p>
        </div>
        @else
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-slate-50 border-b border-slate-200">
                    <tr>
                        <th class="text-left px-3 py-3 font-medium text-slate-600">SKU</th>
                        <th class="text-left px-3 py-3 font-medium text-slate-600">Nama</th>
                        <th class="text-left px-3 py-3 font-medium text-slate-600">Kategori</th>
                        <th class="text-left px-3 py-3 font-medium text-slate-600">Tipe</th>
                        <th class="text-right px-3 py-3 font-medium text-slate-600">Stok</th>
                        <th class="text-right px-3 py-3 font-medium text-slate-600">Min</th>
                        <th class="text-left px-3 py-3 font-medium text-slate-600">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach ($items as $item)
                    <tr class="hover:bg-slate-50">
                        <td class="px-3 py-2 font-mono text-xs text-slate-600">{{ $item->sku }}</td>
                        <td class="px-3 py-2">
                            <div class="font-medium text-slate-900">{{ $item->name }}</div>
                            @if ($item->is_prediction_target)
                            <span class="text-xs text-blue-600">★ ANN target</span>
                            @endif
                        </td>
                        <td class="px-3 py-2 text-slate-600 text-xs">{{ $item->category ?: '—' }}</td>
                        <td class="px-3 py-2">
                            @if ($item->ingredient_type === 'core')
                            <x-badge variant="info">Core</x-badge>
                            @else
                            <x-badge variant="neutral">Non-Core</x-badge>
                            @endif
                        </td>
                        <td class="px-3 py-2 text-right font-mono text-slate-900 font-medium">
                            {{ number_format((float) $item->current_stock, 2, '.', ',') }} {{ $item->unit_symbol }}
                        </td>
                        <td class="px-3 py-2 text-right font-mono text-slate-500 text-xs">
                            {{ $item->minimum_stock ? number_format((float) $item->minimum_stock, 2) : '—' }}
                        </td>
                        <td class="px-3 py-2">
                            @php
                            $badge = match($item->stock_status) {
                            'out' => ['danger', 'Habis'],
                            'critical' => ['danger', 'Kritis'],
                            'warning' => ['warning', 'Menipis'],
                            default => ['success', 'Normal'],
                            };
                            @endphp
                            <x-badge :variant="$badge[0]">{{ $badge[1] }}</x-badge>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @endif
    </x-card>
</x-app-layout>
