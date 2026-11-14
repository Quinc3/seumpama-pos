<x-app-layout title="Weather Data" subtitle="Data cuaca per business date (agregasi dari Open-Meteo)">
    @if (session('success'))
    <div class="mb-4"><x-alert variant="success" icon="clipboard-check" dismissible>{{ session('success') }}</x-alert></div>
    @endif
    @if (session('error'))
    <div class="mb-4"><x-alert variant="error" icon="shield" dismissible>{{ session('error') }}</x-alert></div>
    @endif

    {{-- Sync + Stats --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-3 mb-4">
        <x-card title="Sync Data" class="lg:col-span-1">
            <form method="POST" action="{{ route('weather.sync') }}" class="space-y-3">
                @csrf
                <div>
                    <label class="block text-xs font-medium text-slate-600 mb-1">
                        Ambil berapa hari terakhir?
                    </label>
                    <input type="number" name="days" value="30" min="1" max="92"
                        class="w-full px-3 py-2 rounded-lg border border-slate-300 text-sm">
                    <p class="text-xs text-slate-500 mt-1">Maks 92 hari (limit Open-Meteo). Butuh internet.</p>
                </div>
                <x-button type="submit" variant="primary" icon="cloud" class="w-full">
                    Sync Sekarang
                </x-button>
                <p class="text-xs text-slate-400 leading-relaxed pt-2 border-t border-slate-100">
                    Sync berulang tidak menggandakan data — tanggal & jam yang sama akan di-<em>update</em>.
                </p>
            </form>

            @if ($lastSync)
            <div class="mt-3 pt-3 border-t border-slate-100 text-xs text-slate-500">
                Last sync: {{ \Carbon\Carbon::parse($lastSync)->format('d M Y H:i') }}
            </div>
            @else
            <div class="mt-3 pt-3 border-t border-slate-100 text-xs text-slate-400">
                Belum pernah sync
            </div>
            @endif
        </x-card>

        <x-card title="Ringkasan" class="lg:col-span-2">
            <div class="grid grid-cols-3 gap-3">
                <div class="text-center p-3 bg-slate-50 rounded-lg">
                    <p class="text-xs text-slate-500">Total Hari</p>
                    <p class="text-2xl font-bold font-mono text-slate-900 mt-1">{{ $stats['total'] }}</p>
                </div>
                <div class="text-center p-3 bg-green-50 rounded-lg">
                    <p class="text-xs text-green-700">Complete</p>
                    <p class="text-2xl font-bold font-mono text-green-700 mt-1">{{ $stats['complete'] }}</p>
                </div>
                <div class="text-center p-3 bg-amber-50 rounded-lg">
                    <p class="text-xs text-amber-700">Partial</p>
                    <p class="text-2xl font-bold font-mono text-amber-700 mt-1">{{ $stats['partial'] }}</p>
                </div>
            </div>
            <p class="text-xs text-slate-500 mt-3">
                Missing = hari tanpa data (tidak muncul di tabel). Agregasi mengikuti <strong>business date</strong> (15:00–23:00 / 15:00–02:00 untuk Jumat-Sabtu).
            </p>
        </x-card>
    </div>

    {{-- Filter tanggal --}}
    <x-card padding="none" class="mb-4">
        <form method="GET" class="p-4 flex flex-wrap gap-3 items-end">
            <div class="w-40">
                <label class="block text-xs font-medium text-slate-600 mb-1">Dari</label>
                <input type="date" name="from" value="{{ $from->format('Y-m-d') }}" class="w-full px-3 py-2 rounded-lg border border-slate-300 text-sm">
            </div>
            <div class="w-40">
                <label class="block text-xs font-medium text-slate-600 mb-1">Sampai</label>
                <input type="date" name="to" value="{{ $to->format('Y-m-d') }}" class="w-full px-3 py-2 rounded-lg border border-slate-300 text-sm">
            </div>
            <button type="submit" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-sm font-medium rounded-lg">Filter</button>
            @if (request()->hasAny(['from', 'to']))
            <a href="{{ route('weather.index') }}" class="text-sm text-slate-500 hover:text-slate-700 py-2">Reset</a>
            @endif
        </form>
    </x-card>

    {{-- Table --}}
    <x-card padding="none">
        @if ($summaries->isEmpty())
        <div class="p-12 text-center">
            <x-icon name="cloud" :size="48" class="mx-auto text-slate-300 mb-3" />
            <p class="text-slate-600 font-medium">Belum ada data cuaca</p>
            <p class="text-sm text-slate-400 mt-1">Klik "Sync Sekarang" untuk mengambil data dari Open-Meteo.</p>
        </div>
        @else
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-slate-50 border-b border-slate-200">
                    <tr>
                        <th class="text-left px-3 py-3 font-medium text-slate-600">Business Date</th>
                        <th class="text-right px-3 py-3 font-medium text-slate-600">Temp Avg</th>
                        <th class="text-right px-3 py-3 font-medium text-slate-600">Min</th>
                        <th class="text-right px-3 py-3 font-medium text-slate-600">Max</th>
                        <th class="text-right px-3 py-3 font-medium text-slate-600">Rainfall</th>
                        <th class="text-left px-3 py-3 font-medium text-slate-600">Kondisi</th>
                        <th class="text-right px-3 py-3 font-medium text-slate-600">Observasi</th>
                        <th class="text-center px-3 py-3 font-medium text-slate-600">Completeness</th>
                        <th class="text-left px-3 py-3 font-medium text-slate-600">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach ($summaries as $s)
                    <tr class="hover:bg-slate-50">
                        <td class="px-3 py-2 font-mono text-xs text-slate-900 font-medium">
                            {{ $s->business_date->format('d M Y') }}
                        </td>
                        <td class="px-3 py-2 text-right font-mono text-slate-900">
                            {{ $s->average_temperature !== null ? number_format((float) $s->average_temperature, 1) . '°C' : '—' }}
                        </td>
                        <td class="px-3 py-2 text-right font-mono text-slate-600 text-xs">
                            {{ $s->minimum_temperature !== null ? number_format((float) $s->minimum_temperature, 1) : '—' }}
                        </td>
                        <td class="px-3 py-2 text-right font-mono text-slate-600 text-xs">
                            {{ $s->maximum_temperature !== null ? number_format((float) $s->maximum_temperature, 1) : '—' }}
                        </td>
                        <td class="px-3 py-2 text-right font-mono text-slate-900">
                            {{ $s->total_rainfall !== null ? number_format((float) $s->total_rainfall, 2) . ' mm' : '—' }}
                        </td>
                        <td class="px-3 py-2 text-slate-700 text-xs">{{ $s->dominant_condition ?: '—' }}</td>
                        <td class="px-3 py-2 text-right font-mono text-slate-600 text-xs">{{ $s->observation_count }}</td>
                        <td class="px-3 py-2 text-center font-mono text-slate-700 text-xs">{{ number_format((float) $s->data_completeness, 0) }}%</td>
                        <td class="px-3 py-2">
                            @if ($s->source_status === 'complete')
                            <x-badge variant="success">Complete</x-badge>
                            @elseif ($s->source_status === 'partial')
                            <x-badge variant="warning">Partial</x-badge>
                            @else
                            <x-badge variant="neutral">Missing</x-badge>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @if ($summaries->hasPages())
        <div class="px-4 py-3 border-t border-slate-200">{{ $summaries->links() }}</div>
        @endif
        @endif
    </x-card>
</x-app-layout>
