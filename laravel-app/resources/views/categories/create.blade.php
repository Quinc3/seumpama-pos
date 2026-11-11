<x-app-layout title="Tambah Kategori" subtitle="Buat kategori produk baru">
    <x-card class="max-w-2xl">
        <form method="POST" action="{{ route('categories.store') }}" class="space-y-6">
            @csrf
            @include('categories._form')

            <div class="flex gap-2 pt-2 border-t border-slate-100">
                <x-button type="submit" icon="clipboard-check">Simpan</x-button>
                <x-button :href="route('categories.index')" tag="a" variant="outline">Batal</x-button>
            </div>
        </form>
    </x-card>
</x-app-layout>
