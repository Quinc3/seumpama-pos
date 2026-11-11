<x-app-layout
    title="Edit Unit"
    subtitle="Perbarui data {{ $unit->name }}">
    <x-card class="max-w-xl">
        <form method="POST" action="{{ route('units.update', $unit) }}" class="space-y-6">
            @csrf @method('PUT')
            @include('units._form')

            <div class="flex gap-2 pt-2 border-t border-slate-100">
                <x-button type="submit" variant="primary" icon="clipboard-check">Simpan Perubahan</x-button>
                <x-button :href="route('units.index')" tag="a" variant="outline">Batal</x-button>
            </div>
        </form>
    </x-card>
</x-app-layout>
