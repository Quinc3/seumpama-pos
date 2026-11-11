<x-app-layout title="Tambah Bahan Baku" subtitle="Daftarkan bahan baku baru">
    <x-card class="max-w-3xl">
        <form method="POST" action="{{ route('ingredients.store') }}" class="space-y-6">
            @csrf
            @include('ingredients._form')

            <div class="flex gap-2 pt-2 border-t border-slate-100">
                <x-button type="submit" icon="clipboard-check">Simpan</x-button>
                <x-button :href="route('ingredients.index')" tag="a" variant="outline">Batal</x-button>
            </div>
        </form>
    </x-card>
</x-app-layout>
