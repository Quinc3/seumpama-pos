<x-app-layout title="Edit User" :subtitle="'Perbarui data ' . $user->name">
    <x-card class="max-w-2xl">
        <form method="POST" action="{{ route('users.update', $user) }}" class="space-y-6">
            @csrf @method('PUT')
            @include('users._form')
            <div class="flex gap-2 pt-2 border-t border-slate-100">
                <x-button type="submit" icon="clipboard-check">Simpan Perubahan</x-button>
                <x-button :href="route('users.index')" tag="a" variant="outline">Batal</x-button>
            </div>
        </form>
    </x-card>
</x-app-layout>
