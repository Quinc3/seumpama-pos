<x-app-layout title="Edit Customer" :subtitle="'Perbarui data ' . $customer->name">
    <x-card class="max-w-2xl">
        <form method="POST" action="{{ route('customers.update', $customer) }}" class="space-y-6">
            @csrf @method('PUT')
            @include('customers._form')
            <div class="flex gap-2 pt-2 border-t border-slate-100">
                <x-button type="submit" icon="clipboard-check">Simpan Perubahan</x-button>
                <x-button :href="route('customers.index')" tag="a" variant="outline">Batal</x-button>
            </div>
        </form>
    </x-card>
</x-app-layout>
