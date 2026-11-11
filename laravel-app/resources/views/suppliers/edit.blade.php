<x-app-layout title="Edit Supplier" :subtitle="'Perbarui data ' . $supplier->name">
    <x-card class="max-w-2xl">
        <form method="POST" action="{{ route('suppliers.update', $supplier) }}" class="space-y-6">
            @csrf @method('PUT')
            @include('suppliers._form')
            <div class="flex gap-2 pt-2 border-t border-slate-100">
                <x-button type="submit" icon="clipboard-check">Simpan Perubahan</x-button>
                <x-button :href="route('suppliers.index')" tag="a" variant="outline">Batal</x-button>
            </div>
        </form>
    </x-card>
</x-app-layout>
