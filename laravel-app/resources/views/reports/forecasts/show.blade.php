<x-app-layout :title="'Forecast — ' . $forecast->ingredient->name" :subtitle="$forecast->forecast_date->format('d M Y')">
    <x-slot name="actions">
        <x-button :href="route('stock-forecasts.index')" tag="a" variant="outline">Kembali</x-button>
    </x-slot>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-4 mb-4">
        <x-card title="Parameter" class="lg:col-span-1">
            <dl class="space-y-3 text-sm">
                <div>
                    <dt class="text-slate-500 text-xs">Ingredient</dt>
                    <dd class="font-medium text-slate-900">{{ $forecast->ingredient->name }}</dd>
                    <dd class="text-xs text-slate-400 font-mono">{{ $forecast->ingredient->sku }}</dd>
                </div>
                <div>
                    <dt class="text-slate-500 text-xs">Status</dt>
                    <dd>
                        @php
                        $v = match($forecast->status) {
                        'normal' => 'success',
                        'warning' => 'warning',
                        'critical' => 'danger',
                        default => 'neutral',
                        };
                        @endphp
                        <x-badge :variant="$v">{{ ucfirst($forecast->status) }}</x-badge>
                    </dd>
                </div>
                <div>
                    <dt class="text-slate-500 text-xs">Service Level</dt>
                    <dd class="text-slate-900">{{ number_format((float) $forecast->service_level, 0) }}% (Z = {{ number_format((float) $forecast->z_score, 4) }})</dd>
                </div>
                <div>
                    <dt class="text-slate-500 text-xs">Lead Time</dt>
                    <dd class="text-slate-900">{{ $forecast->lead_time_days }} hari</dd>
                </div>
                <div>
                    <dt class="text-slate-500 text-xs">Forecast Date</dt>
                    <dd class="text-slate-900">{{ $forecast->forecast_date->format('d M Y') }}</dd>
                </div>
            </dl>
        </x-card>

        <x-card title="Perhitungan" class="lg:col-span-2">
            <div class="grid grid-cols-2 gap-4 text-sm">
                <div>
                    <p class="text-slate-500 text-xs">Current Stock</p>
                    <p class="text-xl font-mono font-bold text-slate-900">{{ number_format((float) $forecast->current_stock, 2) }}</p>
                </div>
                <div>
                    <p class="text-slate-500 text-xs">Average Daily Demand</p>
                    <p class="text-xl font-mono text-slate-900">{{ number_format((float) $forecast->average_daily_demand, 4) }}</p>
                </div>
                <div>
                    <p class="text-slate-500 text-xs">Demand σ (Std Dev)</p>
                    <p class="text-xl font-mono text-slate-900">{{ number_format((float) $forecast->demand_std_dev, 4) }}</p>
                </div>
                <div>
                    <p class="text-slate-500 text-xs">ANN Predicted Demand</p>
                    <p class="text-xl font-mono text-slate-900">{{ $forecast->predicted_demand !== null ? number_format((float) $forecast->predicted_demand, 4) : '—' }}</p>
                </div>
                <div class="pt-2 border-t border-slate-100">
                    <p class="text-slate-500 text-xs">Safety Stock</p>
                    <p class="text-xl font-mono font-bold text-amber-700">{{ number_format((float) $forecast->safety_stock, 4) }}</p>
                    <p class="text-xs text-slate-500">Z × σd × √L</p>
                </div>
                <div class="pt-2 border-t border-slate-100">
                    <p class="text-slate-500 text-xs">Reorder Point</p>
                    <p class="text-xl font-mono font-bold text-blue-700">{{ number_format((float) $forecast->reorder_point, 4) }}</p>
                    <p class="text-xs text-slate-500">Avg × L + SS</p>
                </div>
                <div class="pt-2 border-t border-slate-100">
                    <p class="text-slate-500 text-xs">Estimated Coverage</p>
                    <p class="text-xl font-mono text-slate-900">{{ $forecast->estimated_coverage_days !== null ? $forecast->estimated_coverage_days . ' hari' : '—' }}</p>
                </div>
                <div class="pt-2 border-t border-slate-100">
                    <p class="text-slate-500 text-xs">Recommended Purchase</p>
                    <p class="text-xl font-mono font-bold text-green-700">{{ number_format((float) $forecast->recommended_quantity, 2) }}</p>
                    <p class="text-xs text-slate-500">max(0, ROP − Stock)</p>
                </div>
            </div>
        </x-card>
    </div>

    <x-card title="Riwayat Forecast" :subtitle="'10 snapshot terakhir'">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-slate-50 border-b border-slate-200">
                    <tr>
                        <th class="text-left px-3 py-2 font-medium text-slate-600">Tanggal</th>
                        <th class="text-right px-3 py-2 font-medium text-slate-600">Stok</th>
                        <th class="text-right px-3 py-2 font-medium text-slate-600">SS</th>
                        <th class="text-right px-3 py-2 font-medium text-slate-600">ROP</th>
                        <th class="text-right px-3 py-2 font-medium text-slate-600">Rekomendasi</th>
                        <th class="text-left px-3 py-2 font-medium text-slate-600">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach ($history as $h)
                    <tr class="{{ $h->id === $forecast->id ? 'bg-blue-50' : '' }}">
                        <td class="px-3 py-2 font-mono text-xs">{{ $h->forecast_date->format('d M Y') }}</td>
                        <td class="px-3 py-2 text-right font-mono">{{ number_format((float) $h->current_stock, 2) }}</td>
                        <td class="px-3 py-2 text-right font-mono text-slate-600">{{ number_format((float) $h->safety_stock, 2) }}</td>
                        <td class="px-3 py-2 text-right font-mono text-slate-600">{{ number_format((float) $h->reorder_point, 2) }}</td>
                        <td class="px-3 py-2 text-right font-mono font-medium">{{ number_format((float) $h->recommended_quantity, 2) }}</td>
                        <td class="px-3 py-2">
                            @php
                            $v = match($h->status) {
                            'normal' => 'success',
                            'warning' => 'warning',
                            'critical' => 'danger',
                            default => 'neutral',
                            };
                            @endphp
                            <x-badge :variant="$v">{{ ucfirst($h->status) }}</x-badge>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </x-card>
</x-app-layout>
