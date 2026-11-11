<x-app-layout
    title="Categories"
    subtitle="Kategori produk menu">
    <x-slot name="actions">
        @permission('products.manage')
        <x-button :href="route('categories.create')" icon="home" tag="a">
            Tambah Kategori
        </x-button>
        @endpermission
    </x-slot>

    @if (session('success'))
    <div class="mb-4">
        <x-alert variant="success" icon="clipboard-check" dismissible>{{ session('success') }}</x-alert>
    </div>
    @endif

    @if (session('error'))
    <div class="mb-4">
        <x-alert variant="error" icon="shield" dismissible>{{ session('error') }}</x-alert>
    </div>
    @endif

    <x-card padding="none">
        @if ($categories->isEmpty())
        <div class="p-12 text-center">
            <x-icon name="package" :size="48" class="mx-auto text-slate-300 mb-3" />
            <p class="text-slate-600 font-medium">Belum ada kategori</p>
            <p class="text-sm text-slate-400 mt-1">Tambahkan kategori pertama untuk memulai.</p>
            @permission('products.manage')
            <div class="mt-4">
                <x-button :href="route('categories.create')" tag="a" icon="home">
                    Tambah Kategori
                </x-button>
            </div>
            @endpermission
        </div>
        @else
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-slate-50 border-b border-slate-200">
                    <tr>
                        <th class="text-left px-6 py-3 font-medium text-slate-600 w-16">Urut</th>
                        <th class="text-left px-6 py-3 font-medium text-slate-600">Nama</th>
                        <th class="text-left px-6 py-3 font-medium text-slate-600">Slug</th>
                        <th class="text-left px-6 py-3 font-medium text-slate-600">Deskripsi</th>
                        <th class="text-left px-6 py-3 font-medium text-slate-600">Status</th>
                        <th class="text-right px-6 py-3 font-medium text-slate-600">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach ($categories as $category)
                    <tr class="hover:bg-slate-50">
                        <td class="px-6 py-3 text-slate-500 font-mono text-xs">{{ $category->sort_order }}</td>
                        <td class="px-6 py-3 text-slate-900 font-medium">{{ $category->name }}</td>
                        <td class="px-6 py-3">
                            <span class="inline-flex px-2 py-0.5 rounded bg-slate-100 text-slate-700 font-mono text-xs">
                                {{ $category->slug }}
                            </span>
                        </td>
                        <td class="px-6 py-3 text-slate-600 max-w-xs truncate">
                            {{ $category->description ?: '—' }}
                        </td>
                        <td class="px-6 py-3">
                            @if ($category->is_active)
                            <x-badge variant="success">Aktif</x-badge>
                            @else
                            <x-badge variant="neutral">Nonaktif</x-badge>
                            @endif
                        </td>
                        <td class="px-6 py-3 text-right">
                            <div class="inline-flex gap-2">
                                @permission('products.manage')
                                <a href="{{ route('categories.edit', $category) }}"
                                    class="text-sm text-blue-600 hover:text-blue-800 font-medium">Edit</a>
                                <form method="POST" action="{{ route('categories.destroy', $category) }}"
                                    onsubmit="return confirm('Hapus kategori {{ $category->name }}?');">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="text-sm text-red-600 hover:text-red-800 font-medium">
                                        Hapus
                                    </button>
                                </form>
                                @else
                                <span class="text-sm text-slate-400">—</span>
                                @endpermission
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        @if ($categories->hasPages())
        <div class="px-6 py-4 border-t border-slate-200">
            {{ $categories->links() }}
        </div>
        @endif
        @endif
    </x-card>
</x-app-layout>
