<x-app-layout title="Tambah Promosi" subtitle="Buat promosi baru">
    <x-card class="max-w-3xl">
        <form method="POST" action="{{ route('promotions.store') }}" class="space-y-6">
            @csrf
            @include('promotions._form')
            <div class="flex gap-2 pt-4 border-t border-slate-100">
                <x-button type="submit" icon="clipboard-check">Simpan</x-button>
                <x-button :href="route('promotions.index')" tag="a" variant="outline">Batal</x-button>
            </div>
        </form>
    </x-card>
</x-app-layout>
