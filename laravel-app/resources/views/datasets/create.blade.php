<x-app-layout title="Buat Dataset" subtitle="Generate dataset dari data transaksi + cuaca">
    <x-card class="max-w-2xl">
        <x-alert variant="info" icon="database" class="mb-4">
            Dataset akan mengagregasi <strong>pemakaian bahan</strong> (dari paid orders) + <strong>data cuaca</strong> per <strong>business date</strong>.
            Data bersifat <strong>immutable</strong> — setelah dibuat, tidak dapat diubah.
        </x-alert>

        <form method="POST" action="{{ route('datasets.store') }}" class="space-y-4">
            @csrf

            <x-input name="dataset_name" label="Nama Dataset" required
                :value="old('dataset_name', 'Dataset ' . now()->format('d M Y'))"
                placeholder="Contoh: Training Q4 2026" />

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <x-input name="start_date" type="date" label="Start Date" required
                    :value="old('start_date', now()->subDays(30)->format('Y-m-d'))" />
                <x-input name="end_date" type="date" label="End Date" required
                    :value="old('end_date', now()->format('Y-m-d'))" />
            </div>

            <div class="p-3 bg-amber-50 border border-amber-200 rounded-lg text-xs text-amber-800">
                <strong>Catatan:</strong> Dataset mencakup <strong>semua</strong> business_date di rentang ini.
                Hari tanpa transaksi → <code>actual_demand = 0</code>. Baris pertama akan memiliki <code>lag_dX = null</code> (butuh histori minimal 7 hari ke belakang).
            </div>

            <div class="flex gap-2 pt-4 border-t border-slate-100">
                <x-button type="submit" variant="primary" icon="database">Generate Dataset</x-button>
                <x-button :href="route('datasets.index')" tag="a" variant="outline">Batal</x-button>
            </div>
        </form>
    </x-card>
</x-app-layout>
