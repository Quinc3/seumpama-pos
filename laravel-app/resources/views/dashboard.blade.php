<x-app-layout title="Dashboard" subtitle="Ringkasan operasional Coffee Shop Seumpama Bunga">
    {{-- Header info --}}
    <div class="mb-4 flex flex-wrap gap-3 text-xs text-slate-500">
        <span>Hari ini: <strong>{{ now()->format('d M Y') }}</strong></span>
        @if ($latestPredictionDate)
        <span>Prediction: <strong>{{ \Carbon\Carbon::parse($latestPredictionDate)->format('d M Y') }}</strong></span>
        @endif
        @if ($latestForecastDate)
        <span>Forecast: <strong>{{ \Carbon\Carbon::parse($latestForecastDate)->format('d M Y') }}</strong></span>
        @endif
    </div>

    {{-- KPI Cards --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 mb-4">
        <x-card padding="sm">
            <div class="flex items-start justify-between">
                <div>
                    <p class="text-xs text-slate-500">Penjualan Hari Ini</p>
                    <p class="text-xl font-bold text-slate-900 font-mono mt-1">
                        Rp {{ number_format($todaySales, 0, ',', '.') }}
                    </p>
                    <p class="text-xs text-slate-400 mt-1">{{ $todayTxCount }} transaksi</p>
                </div>
                <div class="w-10 h-10 rounded-lg bg-green-50 flex items-center justify-center">
                    <x-icon name="trending-up" :size="20" class="text-green-600" />
                </div>
            </div>
        </x-card>

        <x-card padding="sm">
            <div class="flex items-start justify-between">
                <div>
                    <p class="text-xs text-slate-500">Item Terjual</p>
                    <p class="text-xl font-bold text-slate-900 font-mono mt-1">{{ number_format($todayItemCount) }}</p>
                    <p class="text-xs text-slate-400 mt-1">hari ini</p>
                </div>
                <div class="w-10 h-10 rounded-lg bg-blue-50 flex items-center justify-center">
                    <x-icon name="shopping-cart" :size="20" class="text-blue-600" />
                </div>
            </div>
        </x-card>

        <x-card padding="sm">
            <div class="flex items-start justify-between">
                <div>
                    <p class="text-xs text-slate-500">Rata-rata Transaksi</p>
                    <p class="text-xl font-bold text-slate-900 font-mono mt-1">
                        Rp {{ number_format($avgTx, 0, ',', '.') }}
                    </p>
                    <p class="text-xs text-slate-400 mt-1">per transaksi</p>
                </div>
                <div class="w-10 h-10 rounded-lg bg-amber-50 flex items-center justify-center">
                    <x-icon name="bar-chart" :size="20" class="text-amber-600" />
                </div>
            </div>
        </x-card>

        <x-card padding="sm">
            <div class="flex items-start justify-between">
                <div>
                    <p class="text-xs text-slate-500">Stok Kritis</p>
                    <p class="text-xl font-bold font-mono mt-1 {{ $lowStockCount > 0 ? 'text-red-600' : 'text-slate-900' }}">
                        {{ $lowStockCount }}
                    </p>
                    <p class="text-xs text-slate-400 mt-1">
                        @if ($outOfStockCount > 0)
                        {{ $outOfStockCount }} habis
                        @else
                        bahan di bawah minimum
                        @endif
                    </p>
                </div>
                <div class="w-10 h-10 rounded-lg bg-red-50 flex items-center justify-center">
                    <x-icon name="shield" :size="20" class="text-red-600" />
                </div>
            </div>
        </x-card>
    </div>

    {{-- Sales Trend Chart (7 days) --}}
    <x-card title="Tren Penjualan 7 Hari" class="mb-4">
        @php $maxTrend = collect($salesTrend)->max('total') ?: 0; @endphp
        @if ($maxTrend == 0)
        <p class="text-sm text-slate-500 text-center py-8">Belum ada penjualan dalam 7 hari terakhir.</p>
        @else
        <div class="flex items-end gap-2 h-40 pt-4">
            @foreach ($salesTrend as $day)
            @php $height = $maxTrend > 0 ? ($day['total'] / $maxTrend) * 100 : 0; @endphp
            <div class="flex-1 flex flex-col items-center gap-1">
                <div class="text-xs font-mono text-slate-600 mb-1 whitespace-nowrap">
                    {{ $day['total'] > 0 ? 'Rp ' . number_format($day['total'] / 1000, 0) . 'k' : '—' }}
                </div>
                <div class="w-full bg-slate-100 rounded-t relative" style="height: 100px;">
                    <div class="absolute bottom-0 left-0 right-0 bg-blue-500 hover:bg-blue-600 transition-colors rounded-t"
                        style="height: {{ $height }}%;"
                        title="{{ $day['label'] }}: Rp {{ number_format($day['total'], 0, ',', '.') }}"></div>
                </div>
                <div class="text-xs text-slate-500">{{ $day['label'] }}</div>
            </div>
            @endforeach
        </div>
        @endif
    </x-card>

    {{-- 2 Column: Top Products + Recent Orders --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 mb-4">
        {{-- Top Products --}}
        <x-card title="Top 5 Produk" subtitle="7 hari terakhir">
            @if ($topProducts->isEmpty())
            <p class="text-sm text-slate-500 text-center py-6">Belum ada produk terjual.</p>
            @else
            @php $maxQty = $topProducts->max('qty') ?: 1; @endphp
            <div class="space-y-3">
                @foreach ($topProducts as $i => $p)
                <div>
                    <div class="flex justify-between items-center text-sm mb-1">
                        <div class="flex items-center gap-2">
                            <span class="w-5 h-5 rounded bg-blue-100 text-blue-700 text-xs font-bold flex items-center justify-center">{{ $i + 1 }}</span>
                            <span class="font-medium text-slate-900">{{ $p->product_name }}</span>
                        </div>
                        <span class="font-mono text-xs text-slate-600">{{ number_format($p->qty, 0) }}×</span>
                    </div>
                    <div class="h-1.5 bg-slate-100 rounded overflow-hidden">
                        <div class="h-full bg-blue-500" style="width: {{ ($p->qty / $maxQty) * 100 }}%"></div>
                    </div>
                </div>
                @endforeach
            </div>
            @endif
        </x-card>

        {{-- Recent Orders --}}
        <x-card title="Transaksi Terbaru" subtitle="5 terakhir">
            @if ($recentOrders->isEmpty())
            <p class="text-sm text-slate-500 text-center py-6">Belum ada transaksi.</p>
            @else
            <div class="divide-y divide-slate-100 -mx-6 -mb-6">
                @foreach ($recentOrders as $o)
                <a href="{{ route('orders.show', $o) }}" class="flex items-center justify-between px-6 py-3 hover:bg-slate-50 transition-colors">
                    <div class="min-w-0">
                        <div class="font-mono text-xs font-medium text-slate-900">{{ $o->order_number }}</div>
                        <div class="text-xs text-slate-500 mt-0.5">
                            {{ $o->created_at->format('d M H:i') }} • {{ $o->user?->name }}
                        </div>
                    </div>
                    <div class="text-right shrink-0 ml-3">
                        <div class="font-mono text-sm font-medium text-slate-900">
                            Rp {{ number_format((float) $o->total_amount, 0, ',', '.') }}
                        </div>
                        <div class="text-xs text-slate-400">{{ $o->payments->first()?->paymentMethod?->name }}</div>
                    </div>
                </a>
                @endforeach
            </div>
            @endif
        </x-card>
    </div>

    {{-- 2 Column: Low Stock + Recommendations --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
        {{-- Low Stock Alerts --}}
        <x-card title="Stok Menipis" subtitle="Perlu perhatian">
            <x-slot name="actions">
                @if ($lowStockCount > 0)
                <a href="{{ route('reports.low-stock') }}" class="text-xs text-blue-600 hover:text-blue-800 font-medium">
                    Lihat semua →
                </a>
                @endif
            </x-slot>

            @if ($lowStockIngredients->isEmpty())
            <div class="text-center py-6">
                <x-icon name="clipboard-check" :size="36" class="mx-auto text-green-400 mb-2" />
                <p class="text-sm text-slate-500">Semua stok aman.</p>
            </div>
            @else
            <div class="space-y-2">
                @foreach ($lowStockIngredients as $ing)
                <div class="flex items-center justify-between p-2 rounded-lg bg-amber-50 border border-amber-100">
                    <div class="min-w-0">
                        <div class="text-sm font-medium text-slate-900 truncate">{{ $ing->name }}</div>
                        <div class="text-xs text-slate-500 font-mono">{{ $ing->sku }}</div>
                    </div>
                    <div class="text-right shrink-0 ml-2">
                        <div class="font-mono text-sm font-bold text-red-700">
                            {{ number_format((float) $ing->current_stock, 2) }}
                        </div>
                        <div class="text-xs text-slate-500">min {{ number_format((float) $ing->minimum_stock, 0) }}</div>
                    </div>
                </div>
                @endforeach
            </div>
            @endif
        </x-card>

        {{-- Purchase Recommendations --}}
        <x-card title="Rekomendasi Pembelian" subtitle="Dari forecast terbaru">
            <x-slot name="actions">
                @if ($recommendations->isNotEmpty())
                <a href="{{ route('stock-forecasts.index') }}" class="text-xs text-blue-600 hover:text-blue-800 font-medium">
                    Lihat forecast →
                </a>
                @endif
            </x-slot>

            @if ($recommendations->isEmpty())
            <div class="text-center py-6">
                <x-icon name="trending-up" :size="36" class="mx-auto text-slate-300 mb-2" />
                <p class="text-sm text-slate-500">Belum ada rekomendasi.</p>
                <p class="text-xs text-slate-400 mt-1">Generate forecast dari menu Stock Forecasting.</p>
            </div>
            @else
            <div class="space-y-2">
                @foreach ($recommendations as $rec)
                <div class="flex items-center justify-between p-2 rounded-lg bg-blue-50 border border-blue-100">
                    <div class="min-w-0">
                        <div class="text-sm font-medium text-slate-900 truncate">{{ $rec->ingredient?->name }}</div>
                        <div class="text-xs text-slate-500 font-mono">{{ $rec->ingredient?->sku }}</div>
                    </div>
                    <div class="text-right shrink-0 ml-2">
                        <div class="font-mono text-sm font-bold text-blue-700">
                            +{{ number_format((float) $rec->recommended_quantity, 2) }} {{ $rec->ingredient?->unit?->symbol }}
                        </div>
                        <div class="text-xs text-slate-500">ROP {{ number_format((float) $rec->reorder_point, 0) }}</div>
                    </div>
                </div>
                @endforeach
                <a href="{{ route('stock-forecasts.index') }}"
                    class="block mt-2 text-center text-xs font-medium text-white bg-blue-600 hover:bg-blue-700 py-2 rounded-lg">
                    Buat PO dari Rekomendasi →
                </a>
            </div>
            @endif
        </x-card>
    </div>
</x-app-layout>
