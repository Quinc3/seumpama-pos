<x-app-layout title="Tambah Supplier" subtitle="Daftarkan pemasok baru">
    <x-card class="max-w-2xl">
        <form method="POST" action="{{ route('suppliers.store') }}" class="space-y-6">
            @csrf
            @include('suppliers._form')
            <div class="flex gap-2 pt-2 border-t border-slate-100">
                <x-button type="submit" icon="clipboard-check">Simpan</x-button>
                <x-button :href="route('suppliers.index')" tag="a" variant="outline">Batal</x-button>
            </div>
        </form>
    </x-card>
</x-app-layout>
