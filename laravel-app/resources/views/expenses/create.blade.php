<x-app-layout title="Tambah Pengeluaran" subtitle="Catat pengeluaran operasional">
    <x-card class="max-w-2xl">
        <form method="POST" action="{{ route('expenses.store') }}" class="space-y-6">
            @csrf
            @include('expenses._form')
            <div class="flex gap-2 pt-4 border-t border-slate-100">
                <x-button type="submit" icon="clipboard-check">Simpan</x-button>
                <x-button :href="route('expenses.index')" tag="a" variant="outline">Batal</x-button>
            </div>
        </form>
    </x-card>
</x-app-layout>
