<x-app-layout title="Edit Promosi" :subtitle="'Edit ' . $promotion->name">
    <x-card class="max-w-3xl">
        <form method="POST" action="{{ route('promotions.update', $promotion) }}" class="space-y-6">
            @csrf @method('PUT')
            @include('promotions._form')
            <div class="flex gap-2 pt-4 border-t border-slate-100">
                <x-button type="submit" icon="clipboard-check">Simpan Perubahan</x-button>
                <x-button :href="route('promotions.index')" tag="a" variant="outline">Batal</x-button>
            </div>
        </form>
    </x-card>
</x-app-layout>
