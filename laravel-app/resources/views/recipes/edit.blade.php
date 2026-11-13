<x-app-layout :title="'Edit Resep v' . $recipe->version" :subtitle="'Resep untuk ' . $recipe->product->name">
    <x-slot name="actions">
        <x-button :href="route('recipes.show', $recipe)" tag="a" variant="outline">Kembali</x-button>
    </x-slot>

    <x-card class="max-w-3xl">
        <x-alert variant="info" icon="layers" class="mb-6">
            Anda sedang mengedit resep <strong>v{{ $recipe->version }}</strong> (aktif).
            Setelah disimpan, sistem akan membuat <strong>versi baru v{{ $recipe->version + 1 }}</strong>
            dan versi ini akan <strong>diarsipkan</strong> sebagai histori.
        </x-alert>

        <div class="mb-6 p-4 bg-slate-50 border border-slate-200 rounded-lg text-sm text-slate-700">
            <strong>{{ $recipe->product->name }}</strong>
            <span class="font-mono text-xs ml-2">({{ $recipe->product->sku }})</span>
        </div>

        @include('recipes._form', ['recipe' => $recipe, 'ingredients' => $ingredients])
    </x-card>
</x-app-layout>
