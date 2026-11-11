<x-app-layout title="Tambah Produk" subtitle="Daftarkan produk menu baru">
    <x-card class="max-w-3xl">
        <form method="POST" action="{{ route('products.store') }}" enctype="multipart/form-data" class="space-y-6">
            @csrf
            @include('products._form')

            <div class="flex gap-2 pt-2 border-t border-slate-100">
                <x-button type="submit" icon="clipboard-check">Simpan</x-button>
                <x-button :href="route('products.index')" tag="a" variant="outline">Batal</x-button>
            </div>
        </form>
    </x-card>
</x-app-layout>
