<x-app-layout title="Products" subtitle="Produk menu yang dijual">
    <x-slot name="actions">
        @permission('products.manage')
        <x-button :href="route('products.create')" icon="home" tag="a">
            Tambah Produk
        </x-button>
        @endpermission
    </x-slot>

    @if (session('success'))
    <div class="mb-4">
        <x-alert variant="success" icon="clipboard-check" dismissible>{{ session('success') }}</x-alert>
    </div>
    @endif

    <x-card padding="none" class="mb-4">
        <form method="GET" class="p-4 flex flex-wrap gap-3 items-end">
            <div class="flex-1 min-w-[200px]">
                <label class="block text-xs font-medium text-slate-600 mb-1">Cari</label>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Nama atau SKU..."
                    class="w-full px-3 py-2 rounded-lg border border-slate-300 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
            </div>
            <div class="w-44">
                <label class="block text-xs font-medium text-slate-600 mb-1">Kategori</label>
                <select name="category_id" class="w-full px-3 py-2 rounded-lg border border-slate-300 text-sm bg-white">
                    <option value="">Semua</option>
                    @foreach ($categories as $cat)
                    <option value="{{ $cat->id }}" @selected(request('category_id')==$cat->id)>{{ $cat->name }}</option>
                    @endforeach
                </select>
            </div>
            <button type="submit" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-sm font-medium rounded-lg">Filter</button>
            @if (request()->hasAny(['search', 'category_id']))
            <a href="{{ route('products.index') }}" class="text-sm text-slate-500 hover:text-slate-700 py-2">Reset</a>
            @endif
        </form>
    </x-card>

    <x-card padding="none">
        @if ($products->isEmpty())
        <div class="p-12 text-center">
            <x-icon name="coffee" :size="48" class="mx-auto text-slate-300 mb-3" />
            <p class="text-slate-600 font-medium">Belum ada produk</p>
            <p class="text-sm text-slate-400 mt-1">Tambahkan produk untuk memulai.</p>
            @permission('products.manage')
            <div class="mt-4">
                <x-button :href="route('products.create')" tag="a" icon="home">Tambah Produk</x-button>
            </div>
            @endpermission
        </div>
        @else
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 p-4">
            @foreach ($products as $product)
            <div class="bg-white border border-slate-200 rounded-lg overflow-hidden hover:shadow-md transition-shadow">
                {{-- Image --}}
                <div class="aspect-video bg-slate-100 flex items-center justify-center overflow-hidden">
                    @if ($product->image_path)
                    <img src="{{ Storage::url($product->image_path) }}"
                        alt="{{ $product->name }}"
                        class="w-full h-full object-cover">
                    @else
                    <x-icon name="coffee" :size="48" class="text-slate-300" />
                    @endif
                </div>

                {{-- Body --}}
                <div class="p-4">
                    <div class="flex items-start justify-between gap-2 mb-1">
                        <h3 class="font-semibold text-slate-900 leading-tight">{{ $product->name }}</h3>
                        @if ($product->is_available)
                        <x-badge variant="success">Tersedia</x-badge>
                        @else
                        <x-badge variant="neutral">Kosong</x-badge>
                        @endif
                    </div>
                    <p class="text-xs text-slate-500">{{ $product->category?->name }}</p>
                    <p class="text-xs text-slate-400 font-mono mt-1">{{ $product->sku }}</p>

                    <div class="mt-3 flex items-baseline justify-between">
                        <div class="text-lg font-bold text-slate-900">
                            Rp {{ number_format((float) $product->selling_price, 0, ',', '.') }}
                        </div>
                        @if (! $product->is_active)
                        <x-badge variant="warning">Nonaktif</x-badge>
                        @endif
                    </div>

                    @permission('products.manage')
                    <div class="mt-3 pt-3 border-t border-slate-100 flex gap-3 text-sm">
                        <a href="{{ route('products.edit', $product) }}"
                            class="text-blue-600 hover:text-blue-800 font-medium">Edit</a>
                        <form method="POST" action="{{ route('products.destroy', $product) }}"
                            onsubmit="return confirm('Hapus produk ini? Tindakan tidak dapat dibatalkan.');"
                            class="ml-auto">
                            @csrf @method('DELETE')
                            <button type="submit" class="text-red-600 hover:text-red-800 font-medium">Hapus</button>
                        </form>
                    </div>
                    @endpermission
                </div>
            </div>
            @endforeach
        </div>

        @if ($products->hasPages())
        <div class="px-4 py-4 border-t border-slate-200">{{ $products->links() }}</div>
        @endif
        @endif
    </x-card>
</x-app-layout>
