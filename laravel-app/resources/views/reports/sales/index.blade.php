<x-app-layout title="Sales Reports" subtitle="Laporan penjualan berdasarkan business date">
    <x-slot name="actions">
        <x-button :href="route('reports.sales.pdf', request()->query())" tag="a" variant="outline" icon="receipt">
            Export PDF
        </x-button>
    </x-slot>

    {{-- Filters --}}
    <x-card padding="none" class="mb-4">
        <form method="GET" class="p-4 space-y-3">
            <div class="flex flex-wrap gap-2">
                <a href="{{ route('reports.sales', ['period' => 'today']) }}"
                    class="px-3 py-1.5 rounded-lg text-sm font-medium transition-colors {{ $period === 'today' ? 'bg-blue-600 text-white' : 'bg-slate-100 text-slate-700 hover:bg-slate-200' }}">
                    Hari Ini
                </a>
                <a href="{{ route('reports.sales', ['period' => 'week']) }}"
                    class="px-3 py-1.5 rounded-lg text-sm font-medium transition-colors {{ $period === 'week' ? 'bg-blue-600 text-white' : 'bg-slate-100 text-slate-700 hover:bg-slate-200' }}">
                    Minggu Ini
                </a>
                <a href="{{ route('reports.sales', ['period' => 'month']) }}"
                    class="px-3 py-1.5 rounded-lg text-sm font-medium transition-colors {{ $period === 'month' ? 'bg-blue-600 text-white' : 'bg-slate-100 text-slate-700 hover:bg-slate-200' }}">
                    Bulan Ini
                </a>
            </div>

            <div class="flex flex-wrap gap-3 items-end pt-3 border-t border-slate-100">
                <div class="w-40">
                    <label class="block text-xs font-medium text-slate-600 mb-1">Dari Tanggal</label>
                    <input type="date" name="from" value="{{ $from->format('Y-m-d') }}"
                        class="w-full px-3 py-2 rounded-lg border border-slate-300 text-sm">
                </div>
                <div class="w-40">
                    <label class="block text-xs font-medium text-slate-600 mb-1">Sampai</label>
                    <input type="date" name="to" value="{{ $to->format('Y-m-d') }}"
                        class="w-full px-3 py-2 rounded-lg border border-slate-300 text-sm">
                </div>
                <input type="hidden" name="period" value="custom">
                <button type="submit" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-lg">
                    Terapkan
                </button>
            </div>

            <div class="text-xs text-slate-500">
                Periode: <strong>{{ $from->format('d M Y') }} — {{ $to->format('d M Y') }}</strong>
            </div>
        </form>
    </x-card>

    {{-- KPI Cards --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 mb-4">
        <x-card padding="sm">
            <div class="flex items-start justify-between">
                <div>
                    <p class="text-xs text-slate-500">Total Penjualan</p>
                    <p class="text-xl font-bold text-slate-900 font-mono mt-1">
                        Rp {{ number_format($totalSales, 0, ',', '.') }}
                    </p>
                </div>
                <x-icon name="trending-up" :size="20" class="text-green-500" />
            </div>
        </x-card>
        <x-card padding="sm">
            <div class="flex items-start justify-between">
                <div>
                    <p class="text-xs text-slate-500">Transaksi</p>
                    <p class="text-xl font-bold text-slate-900 font-mono mt-1">{{ number_format($totalTransactions) }}</p>
                </div>
                <x-icon name="receipt" :size="20" class="text-blue-500" />
            </div>
        </x-card>
        <x-card padding="sm">
            <div class="flex items-start justify-between">
                <div>
                    <p class="text-xs text-slate-500">Rata-rata Transaksi</p>
                    <p class="text-xl font-bold text-slate-900 font-mono mt-1">
                        Rp {{ number_format($avgTransaction, 0, ',', '.') }}
                    </p>
                </div>
                <x-icon name="bar-chart" :size="20" class="text-amber-500" />
            </div>
        </x-card>
        <x-card padding="sm">
            <div class="flex items-start justify-between">
                <div>
                    <p class="text-xs text-slate-500">Item Terjual</p>
                    <p class="text-xl font-bold text-slate-900 font-mono mt-1">
                        {{ (float) $totalItemsSold == floor((float) $totalItemsSold) ? number_format((float) $totalItemsSold, 0, ',', '.') : number_format((float) $totalItemsSold, 2, ',', '.') }}
                    </p>
                </div>
                <x-icon name="shopping-bag" :size="20" class="text-purple-500" />
            </div>
        </x-card>
    </div>

    {{-- Daily Trend --}}
    <x-card title="Tren Penjualan Harian" class="mb-4">
        @if ($dailyData->isEmpty())
        <p class="text-sm text-slate-500 text-center py-6">Tidak ada data pada periode ini.</p>
        @else
        @php
        $maxRevenue = $dailyData->max('revenue');
        @endphp
        <div class="space-y-2">
            @foreach ($dailyData as $row)
            <div class="flex items-center gap-3 text-sm">
                <div class="w-24 text-slate-600 text-xs font-mono shrink-0">
                    {{ \Carbon\Carbon::parse($row->date)->format('d M') }}
                </div>
                <div class="flex-1 bg-slate-100 rounded h-5 relative overflow-hidden">
                    <div class="h-full bg-blue-500 rounded transition-all"
                        style="width: {{ $maxRevenue > 0 ? ($row->revenue / $maxRevenue) * 100 : 0 }}%"></div>
                </div>
                <div class="w-32 text-right font-mono text-xs text-slate-900 font-medium shrink-0">
                    Rp {{ number_format($row->revenue, 0, ',', '.') }}
                </div>
                <div class="w-12 text-right text-xs text-slate-500 shrink-0">
                    {{ $row->tx_count }}x
                </div>
            </div>
            @endforeach
        </div>
        @endif
    </x-card>

    {{-- 2 col: Top Products + By Category --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 mb-4">
        <x-card title="Top 10 Produk" subtitle="Berdasarkan revenue">
            @if ($topProducts->isEmpty())
            <p class="text-sm text-slate-500 text-center py-6">Tidak ada data.</p>
            @else
            @php $maxRev = $topProducts->max('total_revenue'); @endphp
            <div class="space-y-2">
                @foreach ($topProducts as $p)
                <div>
                    <div class="flex justify-between text-sm mb-1">
                        <span class="font-medium text-slate-900 truncate">{{ $p->product_name }}</span>
                        <span class="font-mono text-xs text-slate-600 shrink-0 ml-2">
                            {{ number_format($p->total_qty, 0) }}× • Rp {{ number_format($p->total_revenue, 0, ',', '.') }}
                        </span>
                    </div>
                    <div class="h-2 bg-slate-100 rounded overflow-hidden">
                        <div class="h-full bg-green-500"
                            style="width: {{ $maxRev > 0 ? ($p->total_revenue / $maxRev) * 100 : 0 }}%"></div>
                    </div>
                </div>
                @endforeach
            </div>
            @endif
        </x-card>

        <x-card title="Penjualan per Kategori">
            @if ($byCategory->isEmpty())
            <p class="text-sm text-slate-500 text-center py-6">Tidak ada data.</p>
            @else
            <table class="w-full text-sm">
                <thead class="text-xs text-slate-500 border-b border-slate-200">
                    <tr>
                        <th class="text-left py-2">Kategori</th>
                        <th class="text-right py-2">Qty</th>
                        <th class="text-right py-2">Revenue</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach ($byCategory as $cat)
                    <tr>
                        <td class="py-2 text-slate-900">{{ $cat->category_name ?? 'Tanpa Kategori' }}</td>
                        <td class="py-2 text-right font-mono text-slate-600">{{ number_format($cat->total_qty, 0) }}</td>
                        <td class="py-2 text-right font-mono text-slate-900">Rp {{ number_format($cat->total_revenue, 0, ',', '.') }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
            @endif
        </x-card>
    </div>

    {{-- By Cashier --}}
    <x-card title="Performa Kasir" class="mb-4">
        @if ($byCashier->isEmpty())
        <p class="text-sm text-slate-500 text-center py-6">Tidak ada data.</p>
        @else
        <table class="w-full text-sm">
            <thead class="text-xs text-slate-500 border-b border-slate-200">
                <tr>
                    <th class="text-left py-2">Kasir</th>
                    <th class="text-right py-2">Transaksi</th>
                    <th class="text-right py-2">Revenue</th>
                    <th class="text-right py-2">Rata-rata/Transaksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @foreach ($byCashier as $c)
                <tr>
                    <td class="py-2 text-slate-900 font-medium">{{ $c->user_name }}</td>
                    <td class="py-2 text-right font-mono text-slate-600">{{ number_format($c->tx_count) }}</td>
                    <td class="py-2 text-right font-mono text-slate-900">Rp {{ number_format($c->total_revenue, 0, ',', '.') }}</td>
                    <td class="py-2 text-right font-mono text-slate-600">
                        Rp {{ $c->tx_count > 0 ? number_format($c->total_revenue / $c->tx_count, 0, ',', '.') : 0 }}
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
        @endif
    </x-card>

    {{-- Recent Transactions --}}
    <x-card title="Transaksi Terbaru" subtitle="20 transaksi terakhir pada periode ini">
        @if ($recentOrders->isEmpty())
        <p class="text-sm text-slate-500 text-center py-6">Tidak ada transaksi.</p>
        @else
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="text-xs text-slate-500 border-b border-slate-200">
                    <tr>
                        <th class="text-left py-2">Order#</th>
                        <th class="text-left py-2">Waktu</th>
                        <th class="text-left py-2">Kasir</th>
                        <th class="text-left py-2">Metode</th>
                        <th class="text-right py-2">Total</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach ($recentOrders as $o)
                    <tr class="hover:bg-slate-50">
                        <td class="py-2">
                            <a href="{{ route('orders.show', $o->id) }}" class="font-mono text-xs text-blue-600 hover:text-blue-800">
                                {{ $o->order_number }}
                            </a>
                        </td>
                        <td class="py-2 text-xs text-slate-600">{{ \Carbon\Carbon::parse($o->created_at)->format('d/m H:i') }}</td>
                        <td class="py-2 text-xs text-slate-600">{{ $o->user_name }}</td>
                        <td class="py-2 text-xs text-slate-600">{{ $o->payment_method }}</td>
                        <td class="py-2 text-right font-mono text-slate-900 font-medium">
                            Rp {{ number_format((float) $o->total_amount, 0, ',', '.') }}
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @endif
    </x-card>
</x-app-layout>
