<x-app-layout title="Tambah User" subtitle="Buat akun pengguna baru">
    <x-card class="max-w-2xl">
        <form method="POST" action="{{ route('users.store') }}" class="space-y-6">
            @csrf
            @include('users._form')
            <div class="flex gap-2 pt-2 border-t border-slate-100">
                <x-button type="submit" icon="clipboard-check">Simpan</x-button>
                <x-button :href="route('users.index')" tag="a" variant="outline">Batal</x-button>
            </div>
        </form>
    </x-card>
</x-app-layout>
