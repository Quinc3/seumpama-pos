<x-app-layout title="Recipes / BOM" subtitle="Resep produk — komposisi bahan baku per porsi">
    @if (session('success'))
    <div class="mb-4"><x-alert variant="success" icon="clipboard-check" dismissible>{{ session('success') }}</x-alert></div>
    @endif
    @if (session('error'))
    <div class="mb-4"><x-alert variant="error" icon="shield" dismissible>{{ session('error') }}</x-alert></div>
    @endif

    <x-card padding="none" class="mb-4">
        <form method="GET" class="p-4 flex flex-wrap gap-3 items-end">
            <div class="flex-1 min-w-[200px]">
                <label class="block text-xs font-medium text-slate-600 mb-1">Cari</label>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Nama atau SKU produk..."
                    class="w-full px-3 py-2 rounded-lg border border-slate-300 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
            </div>
            <div class="w-44">
                <label class="block text-xs font-medium text-slate-600 mb-1">Status Resep</label>
                <select name="status" class="w-full px-3 py-2 rounded-lg border border-slate-300 text-sm bg-white">
                    <option value="">Semua</option>
                    <option value="with" @selected(request('status')==='with' )>Sudah ada resep</option>
                    <option value="without" @selected(request('status')==='without' )>Belum ada resep</option>
                </select>
            </div>
            <button type="submit" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-sm font-medium rounded-lg">Filter</button>
            @if (request()->hasAny(['search', 'status']))
            <a href="{{ route('recipes.index') }}" class="text-sm text-slate-500 hover:text-slate-700 py-2">Reset</a>
            @endif
        </form>
    </x-card>

    <x-card padding="none">
        @if ($products->isEmpty())
        <div class="p-12 text-center">
            <x-icon name="layers" :size="48" class="mx-auto text-slate-300 mb-3" />
            <p class="text-slate-600 font-medium">Belum ada produk</p>
            <p class="text-sm text-slate-400 mt-1">Tambahkan produk dulu di halaman Products.</p>
        </div>
        @else
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-slate-50 border-b border-slate-200">
                    <tr>
                        <th class="text-left px-4 py-3 font-medium text-slate-600">Produk</th>
                        <th class="text-left px-4 py-3 font-medium text-slate-600">Kategori</th>
                        <th class="text-center px-4 py-3 font-medium text-slate-600">Jumlah Bahan</th>
                        <th class="text-center px-4 py-3 font-medium text-slate-600">Versi</th>
                        <th class="text-left px-4 py-3 font-medium text-slate-600">Status</th>
                        <th class="text-right px-4 py-3 font-medium text-slate-600">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach ($products as $product)
                    <tr class="hover:bg-slate-50">
                        <td class="px-4 py-3">
                            <div class="font-medium text-slate-900">{{ $product->name }}</div>
                            <div class="text-xs text-slate-400 font-mono">{{ $product->sku }}</div>
                        </td>
                        <td class="px-4 py-3 text-slate-600">{{ $product->category?->name }}</td>
                        <td class="px-4 py-3 text-center">
                            @if ($product->recipe)
                            <span class="font-mono font-medium text-slate-900">{{ $product->recipe->items->count() }}</span>
                            @else
                            <span class="text-slate-300">—</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-center text-slate-600">
                            @if ($product->recipe)
                            v{{ $product->recipe->version }}
                            @else
                            <span class="text-slate-300">—</span>
                            @endif
                        </td>
                        <td class="px-4 py-3">
                            @if ($product->recipe)
                            <x-badge variant="success">Aktif</x-badge>
                            @else
                            <x-badge variant="warning">Belum ada resep</x-badge>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-right">
                            <div class="inline-flex gap-2">
                                @if ($product->recipe)
                                <a href="{{ route('recipes.show', $product->recipe) }}"
                                    class="text-sm text-slate-600 hover:text-slate-800 font-medium">Detail</a>
                                @permission('products.manage')
                                <a href="{{ route('recipes.edit', $product->recipe) }}"
                                    class="text-sm text-blue-600 hover:text-blue-800 font-medium">Edit</a>
                                @endpermission
                                @else
                                @permission('products.manage')
                                <a href="{{ route('recipes.create', $product) }}"
                                    class="text-sm text-blue-600 hover:text-blue-800 font-medium">Buat Resep</a>
                                @endpermission
                                @endif
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @if ($products->hasPages())
        <div class="px-4 py-4 border-t border-slate-200">{{ $products->links() }}</div>
        @endif
        @endif
    </x-card>
</x-app-layout>
