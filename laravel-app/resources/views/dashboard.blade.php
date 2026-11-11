<x-app-layout
    title="Dashboard"
    subtitle="Ringkasan operasional Coffee Shop Seumpama Bunga">
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        {{-- KPI placeholder cards --}}
        @foreach (['Penjualan Hari Ini', 'Transaksi', 'Stok Kritis', 'Prediksi Besok'] as $label)
        <div class="bg-white rounded-lg border border-slate-200 p-4">
            <p class="text-sm text-slate-500">{{ $label }}</p>
            <p class="text-2xl font-bold text-slate-900 mt-1">—</p>
            <p class="text-xs text-slate-400 mt-1">Belum ada data</p>
        </div>
        @endforeach
    </div>

    <div class="bg-white rounded-lg border border-slate-200 p-6">
        <h2 class="text-lg font-semibold text-slate-900 mb-2">Selamat datang, {{ auth()->user()->name }}</h2>
        <p class="text-slate-600">
            Layout aplikasi sudah berfungsi. Modul POS, Inventory, dan lainnya akan ditambahkan bertahap.
        </p>
        <div class="mt-4 flex flex-wrap gap-2 text-xs">
            <span class="px-2 py-1 rounded bg-blue-50 text-blue-700">Role: {{ auth()->user()?->role()?->name ?? '—' }}</span>
            <span class="px-2 py-1 rounded bg-green-50 text-green-700">Authenticated</span>
        </div>
    </div>
</x-app-layout>
