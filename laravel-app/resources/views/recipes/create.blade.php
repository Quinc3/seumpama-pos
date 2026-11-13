<x-app-layout title="Buat Resep" :subtitle="'Resep untuk ' . $product->name">
    <x-slot name="actions">
        <x-button :href="route('recipes.index')" tag="a" variant="outline">Kembali</x-button>
    </x-slot>

    <x-card class="max-w-3xl">
        <div class="mb-6 p-4 bg-blue-50 border border-blue-200 rounded-lg text-sm text-blue-800">
            <strong>{{ $product->name }}</strong>
            <span class="font-mono text-xs ml-2">({{ $product->sku }})</span>
        </div>

        @include('recipes._form', ['product' => $product, 'ingredients' => $ingredients])
    </x-card>
</x-app-layout>
