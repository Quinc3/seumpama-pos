<x-app-layout :title="$dataset->version" :subtitle="$dataset->dataset_name">
    <x-slot name="actions">
        @if ($dataset->status !== 'archived')
        <form method="POST" action="{{ route('datasets.archive', $dataset) }}" class="inline"
            onsubmit="return confirm('Arsipkan dataset ini? Data akan tetap ada, tapi tidak bisa dipakai untuk training baru.');">
            @csrf
            <x-button type="submit" variant="outline" icon="package">Arsipkan</x-button>
        </form>
        @endif
        <x-button :href="route('datasets.index')" tag="a" variant="outline">Kembali</x-button>
    </x-slot>

    @if (session('success'))
    <div class="mb-4"><x-alert variant="success" icon="clipboard-check" dismissible>{{ session('success') }}</x-alert></div>
    @endif

    {{-- Info --}}
    <div class="grid grid-cols-1 lg:grid-cols-4 gap-3 mb-4">
        <x-card padding="sm">
            <p class="text-xs text-slate-500">Status</p>
            <p class="mt-1">
                @php
                $variant = match($dataset->status) {
                'validated' => 'info',
                'approved' => 'success',
                'archived' => 'warning',
                default => 'neutral',
                };
                @endphp
                <x-badge :variant="$variant">{{ ucfirst($dataset->status) }}</x-badge>
            </p>
        </x-card>
        <x-card padding="sm">
            <p class="text-xs text-slate-500">Total Records</p>
            <p class="text-xl font-bold font-mono text-slate-900 mt-1">{{ number_format($dataset->total_records) }}</p>
        </x-card>
        <x-card padding="sm">
            <p class="text-xs text-slate-500">Period</p>
            <p class="text-sm font-mono text-slate-900 mt-1">
                {{ $dataset->start_date->format('d/m/Y') }} — {{ $dataset->end_date->format('d/m/Y') }}
            </p>
        </x-card>
        <x-card padding="sm">
            <p class="text-xs text-slate-500">Quality</p>
            <p class="text-xs text-slate-700 mt-1">
                @if ($dataset->validation_summary)
                {{ $dataset->validation_summary['valid_records'] ?? 0 }} valid,
                {{ $dataset->validation_summary['partial_records'] ?? 0 }} partial
                @else
                —
                @endif
            </p>
        </x-card>
    </div>

    {{-- Filter --}}
    <x-card padding="none" class="mb-4">
        <form method="GET" class="p-4 flex flex-wrap gap-3 items-end">
            <div class="flex-1 min-w-[200px]">
                <label class="block text-xs font-medium text-slate-600 mb-1">Filter Bahan</label>
                <select name="ingredient_id" class="w-full px-3 py-2 rounded-lg border border-slate-300 text-sm bg-white">
                    <option value="">Semua Bahan</option>
                    @foreach ($ingredients as $ing)
                    <option value="{{ $ing->id }}" @selected(request('ingredient_id')==$ing->id)>{{ $ing->name }} ({{ $ing->sku }})</option>
                    @endforeach
                </select>
            </div>
            <button type="submit" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-sm font-medium rounded-lg">Filter</button>
            @if (request()->has('ingredient_id'))
            <a href="{{ route('datasets.show', $dataset) }}" class="text-sm text-slate-500 hover:text-slate-700 py-2">Reset</a>
            @endif
        </form>
    </x-card>

    {{-- Records Table --}}
    <x-card padding="none">
        @if ($records->isEmpty())
        <div class="p-12 text-center">
            <x-icon name="database" :size="48" class="mx-auto text-slate-300 mb-3" />
            <p class="text-slate-600 font-medium">Tidak ada records</p>
        </div>
        @else
        <div class="overflow-x-auto">
            <table class="w-full text-xs">
                <thead class="bg-slate-50 border-b border-slate-200">
                    <tr>
                        <th class="text-left px-2 py-2 font-medium text-slate-600">Tanggal</th>
                        <th class="text-left px-2 py-2 font-medium text-slate-600">Bahan</th>
                        <th class="text-right px-2 py-2 font-medium text-slate-600">Actual</th>
                        <th class="text-right px-2 py-2 font-medium text-slate-600">Lag d1</th>
                        <th class="text-right px-2 py-2 font-medium text-slate-600">Lag d7</th>
                        <th class="text-right px-2 py-2 font-medium text-slate-600">Roll 3d</th>
                        <th class="text-right px-2 py-2 font-medium text-slate-600">Roll 7d</th>
                        <th class="text-right px-2 py-2 font-medium text-slate-600">Temp</th>
                        <th class="text-right px-2 py-2 font-medium text-slate-600">Rain</th>
                        <th class="text-center px-2 py-2 font-medium text-slate-600">DoW</th>
                        <th class="text-left px-2 py-2 font-medium text-slate-600">Quality</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach ($records as $r)
                    <tr class="hover:bg-slate-50">
                        <td class="px-2 py-1.5 font-mono text-slate-900">{{ $r->business_date->format('d/m/y') }}</td>
                        <td class="px-2 py-1.5 text-slate-700">
                            {{ $r->ingredient?->name }}
                            <div class="text-slate-400 font-mono">{{ $r->ingredient?->sku }}</div>
                        </td>
                        <td class="px-2 py-1.5 text-right font-mono text-slate-900 font-medium">
                            {{ number_format((float) $r->actual_demand, 2) }}
                        </td>
                        <td class="px-2 py-1.5 text-right font-mono text-slate-600">
                            {{ $r->lag_d1 !== null ? number_format((float) $r->lag_d1, 2) : '—' }}
                        </td>
                        <td class="px-2 py-1.5 text-right font-mono text-slate-600">
                            {{ $r->lag_d7 !== null ? number_format((float) $r->lag_d7, 2) : '—' }}
                        </td>
                        <td class="px-2 py-1.5 text-right font-mono text-slate-600">
                            {{ $r->rolling_mean_3d !== null ? number_format((float) $r->rolling_mean_3d, 2) : '—' }}
                        </td>
                        <td class="px-2 py-1.5 text-right font-mono text-slate-600">
                            {{ $r->rolling_mean_7d !== null ? number_format((float) $r->rolling_mean_7d, 2) : '—' }}
                        </td>
                        <td class="px-2 py-1.5 text-right font-mono text-slate-600">
                            {{ $r->temperature !== null ? number_format((float) $r->temperature, 1) : '—' }}
                        </td>
                        <td class="px-2 py-1.5 text-right font-mono text-slate-600">
                            {{ $r->rainfall !== null ? number_format((float) $r->rainfall, 1) : '—' }}
                        </td>
                        <td class="px-2 py-1.5 text-center text-slate-600">
                            {{ $r->day_of_week }}{{ $r->is_weekend ? ' ★' : '' }}
                        </td>
                        <td class="px-2 py-1.5">
                            @if ($r->data_quality === 'valid')
                            <x-badge variant="success">Valid</x-badge>
                            @elseif ($r->data_quality === 'partial')
                            <x-badge variant="warning">Partial</x-badge>
                            @else
                            <x-badge variant="danger">Invalid</x-badge>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @if ($records->hasPages())
        <div class="px-4 py-3 border-t border-slate-200">{{ $records->links() }}</div>
        @endif
        @endif
    </x-card>
</x-app-layout>
