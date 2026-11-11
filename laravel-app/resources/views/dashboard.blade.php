<x-app-layout
    title="Dashboard"
    subtitle="Ringkasan operasional Coffee Shop Seumpama Bunga">
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        @foreach (['Penjualan Hari Ini', 'Transaksi', 'Stok Kritis', 'Prediksi Besok'] as $label)
        <x-card padding="sm">
            <p class="text-sm text-slate-500">{{ $label }}</p>
            <p class="text-2xl font-bold text-slate-900 mt-1">—</p>
            <p class="text-xs text-slate-400 mt-1">Belum ada data</p>
        </x-card>
        @endforeach
    </div>

    <x-card title="Selamat datang, {{ auth()->user()->name }}">
        <p class="text-slate-600">
            Layout aplikasi sudah berfungsi. Modul POS, Inventory, dan lainnya akan ditambahkan bertahap.
        </p>

        <div class="mt-4 flex flex-wrap items-center gap-2">
            <x-badge variant="info">Role: {{ auth()->user()?->role()?->name ?? '—' }}</x-badge>
            <x-badge variant="success">Authenticated</x-badge>
            <x-badge variant="warning">Phase 1</x-badge>
            <x-badge>Draft</x-badge>
        </div>

        <div class="mt-6 flex flex-wrap gap-2">
            <x-button icon="coffee">Primary</x-button>
            <x-button variant="secondary">Secondary</x-button>
            <x-button variant="outline">Outline</x-button>
            <x-button variant="ghost">Ghost</x-button>
            <x-button variant="danger" icon="x">Danger</x-button>
            <x-button variant="primary" size="sm">Small</x-button>
            <x-button variant="primary" size="lg">Large</x-button>
        </div>

        <div class="mt-6">
            <x-alert variant="info" icon="clipboard-check" dismissible>
                Ini contoh alert info dengan tombol dismiss (Alpine). Klik X di kanan untuk menutup.
            </x-alert>

        </div>
    </x-card>
</x-app-layout>
