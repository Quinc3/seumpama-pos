<x-app-layout title="Expense Report" subtitle="Laporan pengeluaran">
    <x-slot name="actions">
        <x-button :href="route('reports.expenses.pdf', request()->query())" tag="a" variant="outline" icon="receipt">
            Export PDF
        </x-button>
    </x-slot>

    {{-- Filter --}}
    <x-card padding="none" class="mb-4">
        <form method="GET" class="p-4 space-y-3">
            <div class="flex flex-wrap gap-2">
                <a href="{{ route('reports.expenses', ['period' => 'today']) }}"
                    class="px-3 py-1.5 rounded-lg text-sm font-medium {{ $period === 'today' ? 'bg-blue-600 text-white' : 'bg-slate-100 text-slate-700 hover:bg-slate-200' }}">Hari Ini</a>
                <a href="{{ route('reports.expenses', ['period' => 'week']) }}"
                    class="px-3 py-1.5 rounded-lg text-sm font-medium {{ $period === 'week' ? 'bg-blue-600 text-white' : 'bg-slate-100 text-slate-700 hover:bg-slate-200' }}">Minggu Ini</a>
                <a href="{{ route('reports.expenses', ['period' => 'month']) }}"
                    class="px-3 py-1.5 rounded-lg text-sm font-medium {{ $period === 'month' ? 'bg-blue-600 text-white' : 'bg-slate-100 text-slate-700 hover:bg-slate-200' }}">Bulan Ini</a>
            </div>
            <div class="flex flex-wrap gap-3 items-end pt-3 border-t border-slate-100">
                <div class="w-36">
                    <label class="block text-xs font-medium text-slate-600 mb-1">Dari</label>
                    <input type="date" name="from" value="{{ $from->format('Y-m-d') }}" class="w-full px-3 py-2 rounded-lg border border-slate-300 text-sm">
                </div>
                <div class="w-36">
                    <label class="block text-xs font-medium text-slate-600 mb-1">Sampai</label>
                    <input type="date" name="to" value="{{ $to->format('Y-m-d') }}" class="w-full px-3 py-2 rounded-lg border border-slate-300 text-sm">
                </div>
                <input type="hidden" name="period" value="custom">
                <button type="submit" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-sm font-medium rounded-lg">Terapkan</button>
            </div>
            <div class="text-xs text-slate-500">Periode: <strong>{{ $from->format('d M Y') }} — {{ $to->format('d M Y') }}</strong></div>
        </form>
    </x-card>

    {{-- KPI --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-3 mb-4">
        <x-card padding="sm">
            <p class="text-xs text-slate-500">Total Pengeluaran</p>
            <p class="text-xl font-bold mt-1 font-mono text-red-600">Rp {{ number_format($grandTotal, 0, ',', '.') }}</p>
        </x-card>
        <x-card padding="sm">
            <p class="text-xs text-slate-500">Kategori Terlibat</p>
            <p class="text-xl font-bold mt-1 font-mono text-slate-900">{{ $byCategory->count() }}</p>
        </x-card>
        <x-card padding="sm">
            <p class="text-xs text-slate-500">Transaksi</p>
            <p class="text-xl font-bold mt-1 font-mono text-slate-900">{{ $byCategory->sum('tx_count') }}</p>
        </x-card>
    </div>

    {{-- By Category --}}
    <x-card title="Per Kategori" class="mb-4">
        @if ($byCategory->isEmpty())
        <p class="text-sm text-slate-500 text-center py-6">Tidak ada pengeluaran pada periode ini.</p>
        @else
        @php $maxCat = $byCategory->max('total_amount'); @endphp
        <div class="space-y-3">
            @foreach ($byCategory as $cat)
            <div>
                <div class="flex justify-between text-sm mb-1">
                    <span class="font-medium text-slate-900">{{ $cat->name }}</span>
                    <span class="font-mono text-slate-900">Rp {{ number_format((float) $cat->total_amount, 0, ',', '.') }} ({{ $cat->tx_count }}x)</span>
                </div>
                <div class="h-2 bg-slate-100 rounded overflow-hidden">
                    <div class="h-full bg-red-500"
                        style="width: {{ $maxCat > 0 ? ($cat->total_amount / $maxCat) * 100 : 0 }}%"></div>
                </div>
            </div>
            @endforeach
        </div>
        @endif
    </x-card>

    {{-- Recent --}}
    <x-card title="Pengeluaran Terbaru" subtitle="30 transaksi terakhir">
        @if ($recentExpenses->isEmpty())
        <p class="text-sm text-slate-500 text-center py-6">Tidak ada data.</p>
        @else
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="text-xs text-slate-500 border-b border-slate-200">
                    <tr>
                        <th class="text-left py-2 pr-3">Tanggal</th>
                        <th class="text-left py-2 pr-3">Kategori</th>
                        <th class="text-left py-2 pr-3">Deskripsi</th>
                        <th class="text-right py-2 px-4">Nominal</th>
                        <th class="text-left py-2 px-2">Dicatat</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach ($recentExpenses as $e)
                    <tr>
                        <td class="py-2 pr-3 text-xs text-slate-600">{{ \Carbon\Carbon::parse($e->expense_date)->format('d/m/Y') }}</td>
                        <td class="py-2 pr-3"><x-badge variant="info">{{ $e->category_name }}</x-badge></td>
                        <td class="py-2 pr-3 text-slate-900">{{ $e->description }}</td>
                        <td class="py-2 px-4 text-right font-mono font-medium text-slate-900 whitespace-nowrap">Rp {{ number_format((float) $e->amount, 0, ',', '.') }}</td>
                        <td class="py-2 px-2 text-xs text-slate-600">{{ $e->user_name }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @endif
    </x-card>
</x-app-layout>
