<x-app-layout title="Stock Forecast" subtitle="Rekomendasi persediaan berbasis safety stock + reorder point">
    <x-slot name="actions">
        <form method="POST" action="{{ route('stock-forecasts.generate') }}" class="inline"
            onsubmit="return confirm('Generate forecast untuk semua ingredient? Snapshot stok & perhitungan akan diambil saat ini.');">
            @csrf
            <x-button type="submit" variant="primary" icon="trending-up">
                Generate Forecast
            </x-button>
        </form>
    </x-slot>

    @if (session('success'))
    <div class="mb-4"><x-alert variant="success" icon="clipboard-check" dismissible>{{ session('success') }}</x-alert></div>
    @endif
    @if (session('error'))
    <div class="mb-4"><x-alert variant="error" icon="shield" dismissible>{{ session('error') }}</x-alert></div>
    @endif

    {{-- Summary --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 mb-4">
        <x-card padding="sm">
            <p class="text-xs text-slate-500">Normal</p>
            <p class="text-xl font-bold mt-1 font-mono text-green-600">{{ $summary['normal'] }}</p>
        </x-card>
        <x-card padding="sm">
            <p class="text-xs text-slate-500">Warning</p>
            <p class="text-xl font-bold mt-1 font-mono text-amber-600">{{ $summary['warning'] }}</p>
        </x-card>
        <x-card padding="sm">
            <p class="text-xs text-slate-500">Critical</p>
            <p class="text-xl font-bold mt-1 font-mono text-red-600">{{ $summary['critical'] }}</p>
        </x-card>
        <x-card padding="sm">
            <p class="text-xs text-slate-500">Belum Forecast</p>
            <p class="text-xl font-bold mt-1 font-mono text-slate-400">{{ $summary['missing'] }}</p>
        </x-card>
    </div>

    @if ($latestGenerated)
    <p class="text-xs text-slate-500 mb-4">Last generated: {{ \Carbon\Carbon::parse($latestGenerated)->format('d M Y H:i') }}</p>
    @endif

    <x-card padding="none">
        @if ($ingredients->isEmpty())
        <div class="p-12 text-center">
            <x-icon name="trending-up" :size="48" class="mx-auto text-slate-300 mb-3" />
            <p class="text-slate-600 font-medium">Tidak ada ingredient dengan is_prediction_target</p>
        </div>
        @else
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-slate-50 border-b border-slate-200">
                    <tr>
                        <th class="text-left px-3 py-3 font-medium text-slate-600">Ingredient</th>
                        <th class="text-right px-3 py-3 font-medium text-slate-600">Stok</th>
                        <th class="text-right px-3 py-3 font-medium text-slate-600">Avg/day</th>
                        <th class="text-right px-3 py-3 font-medium text-slate-600">σd</th>
                        <th class="text-right px-3 py-3 font-medium text-slate-600">Lead</th>
                        <th class="text-right px-3 py-3 font-medium text-slate-600">SS</th>
                        <th class="text-right px-3 py-3 font-medium text-slate-600">ROP</th>
                        <th class="text-right px-3 py-3 font-medium text-slate-600">Rekomendasi</th>
                        <th class="text-center px-3 py-3 font-medium text-slate-600">Coverage</th>
                        <th class="text-left px-3 py-3 font-medium text-slate-600">Status</th>
                        <th class="text-right px-3 py-3 font-medium text-slate-600"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach ($ingredients as $ing)
                    @php $f = $forecasts[$ing->id] ?? null; @endphp
                    <tr class="hover:bg-slate-50">
                        <td class="px-3 py-2">
                            <div class="font-medium text-slate-900">{{ $ing->name }}</div>
                            <div class="text-xs text-slate-400 font-mono">{{ $ing->sku }}</div>
                        </td>
                        <td class="px-3 py-2 text-right font-mono text-slate-900">{{ number_format((float) $ing->current_stock, 2) }} {{ $ing->unit?->symbol }}</td>
                        <td class="px-3 py-2 text-right font-mono text-slate-700">{{ $f ? number_format((float) $f->average_daily_demand, 4) : '—' }}</td>
                        <td class="px-3 py-2 text-right font-mono text-slate-500">{{ $f ? number_format((float) $f->demand_std_dev, 4) : '—' }}</td>
                        <td class="px-3 py-2 text-right font-mono text-slate-600">{{ $f ? $f->lead_time_days . 'h' : ($ing->lead_time_days . 'h') }}</td>
                        <td class="px-3 py-2 text-right font-mono text-slate-700">{{ $f ? number_format((float) $f->safety_stock, 4) : '—' }}</td>
                        <td class="px-3 py-2 text-right font-mono text-slate-700">{{ $f ? number_format((float) $f->reorder_point, 4) : '—' }}</td>
                        <td class="px-3 py-2 text-right font-mono font-medium {{ $f && (float) $f->recommended_quantity > 0 ? 'text-blue-700' : 'text-slate-400' }}">
                            {{ $f ? number_format((float) $f->recommended_quantity, 2) : '—' }}
                        </td>
                        <td class="px-3 py-2 text-center text-xs text-slate-600">
                            {{ $f?->estimated_coverage_days !== null ? $f->estimated_coverage_days . ' hari' : '—' }}
                        </td>
                        <td class="px-3 py-2">
                            @if ($f)
                            @php
                            $v = match($f->status) {
                            'normal' => 'success',
                            'warning' => 'warning',
                            'critical' => 'danger',
                            default => 'neutral',
                            };
                            @endphp
                            <x-badge :variant="$v">{{ ucfirst($f->status) }}</x-badge>
                            @else
                            <x-badge variant="neutral">Belum ada</x-badge>
                            @endif
                        </td>
                        <td class="px-3 py-2 text-right">
                            @if ($f)
                            <a href="{{ route('stock-forecasts.show', $f) }}" class="text-sm text-blue-600 hover:text-blue-800 font-medium">Detail</a>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @endif
    </x-card>

    <x-alert variant="info" icon="clipboard-check" class="mt-4">
        <strong>Formula:</strong>
        Safety Stock = Z × σd × √L dengan Z (95%) = 1.6449.
        Reorder Point = Average Daily Demand × Lead Time + Safety Stock.
        Rekomendasi = max(0, ROP − Current Stock). Historical dihitung dari
        <code>sale_ingredient_usages</code> (paid orders) dengan 0-fill untuk hari tanpa penjualan.
    </x-alert>
</x-app-layout>
