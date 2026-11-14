<x-app-layout title="Low Stock Alert" subtitle="Bahan kritis (≤ min) atau mendekati minimum (≤ 1.5× min)">
    <x-card padding="none">
        @if ($ingredients->isEmpty())
        <div class="p-12 text-center">
            <x-icon name="clipboard-check" :size="48" class="mx-auto text-green-300 mb-3" />
            <p class="text-slate-600 font-medium">Semua stok dalam kondisi aman</p>
            <p class="text-sm text-slate-400 mt-1">Tidak ada bahan yang perlu segera dipesan.</p>
        </div>
        @else
        @php
        $criticalCount = $ingredients->where('status', 'critical')->count();
        $outCount = $ingredients->where('status', 'out')->count();
        $warningCount = $ingredients->where('status', 'warning')->count();
        @endphp
        <div class="p-4 bg-amber-50 border-b border-amber-200 text-sm text-amber-800 flex flex-wrap gap-3">
            <strong>{{ $ingredients->count() }} bahan</strong> perlu perhatian:
            @if ($outCount > 0) <span class="px-2 py-0.5 bg-red-100 text-red-800 rounded text-xs font-medium">{{ $outCount }} habis</span> @endif
            @if ($criticalCount > 0) <span class="px-2 py-0.5 bg-red-100 text-red-800 rounded text-xs font-medium">{{ $criticalCount }} kritis</span> @endif
            @if ($warningCount > 0) <span class="px-2 py-0.5 bg-amber-100 text-amber-800 rounded text-xs font-medium">{{ $warningCount }} menipis</span> @endif
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-slate-50 border-b border-slate-200">
                    <tr>
                        <th class="text-left px-3 py-3 font-medium text-slate-600">Bahan</th>
                        <th class="text-right px-3 py-3 font-medium text-slate-600">Stok</th>
                        <th class="text-right px-3 py-3 font-medium text-slate-600">Minimum</th>
                        <th class="text-right px-3 py-3 font-medium text-slate-600">Safety</th>
                        <th class="text-right px-3 py-3 font-medium text-slate-600">Lead Time</th>
                        <th class="text-left px-3 py-3 font-medium text-slate-600">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach ($ingredients as $item)
                    <tr class="hover:bg-slate-50">
                        <td class="px-3 py-2">
                            <div class="font-medium text-slate-900">{{ $item->name }}</div>
                            <div class="text-xs text-slate-400 font-mono">{{ $item->sku }}</div>
                        </td>
                        <td class="px-3 py-2 text-right font-mono {{ $item->status === 'out' || $item->status === 'critical' ? 'text-red-700 font-bold' : 'text-amber-700 font-medium' }}">
                            {{ number_format((float) $item->current_stock, 2, '.', ',') }} {{ $item->unit_symbol }}
                        </td>
                        <td class="px-3 py-2 text-right font-mono text-slate-600 text-xs">{{ number_format((float) $item->minimum_stock, 2) }}</td>
                        <td class="px-3 py-2 text-right font-mono text-slate-600 text-xs">{{ $item->safety_stock ? number_format((float) $item->safety_stock, 2) : '—' }}</td>
                        <td class="px-3 py-2 text-right font-mono text-slate-600 text-xs">{{ $item->lead_time_days ? $item->lead_time_days . ' hr' : '—' }}</td>
                        <td class="px-3 py-2">
                            @if ($item->status === 'out')
                            <x-badge variant="danger">Habis</x-badge>
                            @elseif ($item->status === 'critical')
                            <x-badge variant="danger">Kritis</x-badge>
                            @else
                            <x-badge variant="warning">Menipis</x-badge>
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
