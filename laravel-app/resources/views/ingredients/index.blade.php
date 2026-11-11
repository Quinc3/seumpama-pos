<x-app-layout title="Ingredients" subtitle="Bahan baku dan perlengkapan inventaris">
    <x-slot name="actions">
        @permission('inventory.manage')
        <x-button :href="route('ingredients.create')" icon="home" tag="a">
            Tambah Bahan
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

    <x-card padding="none" class="mb-4">
        <form method="GET" class="p-4 flex flex-wrap gap-3 items-end">
            <div class="flex-1 min-w-[200px]">
                <label class="block text-xs font-medium text-slate-600 mb-1">Cari</label>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Nama atau SKU..."
                    class="w-full px-3 py-2 rounded-lg border border-slate-300 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
            </div>
            <div class="w-40">
                <label class="block text-xs font-medium text-slate-600 mb-1">Tipe</label>
                <select name="type" class="w-full px-3 py-2 rounded-lg border border-slate-300 text-sm bg-white">
                    <option value="">Semua</option>
                    <option value="core" @selected(request('type')==='core' )>Core</option>
                    <option value="non_core" @selected(request('type')==='non_core' )>Non-Core</option>
                </select>
            </div>
            <button type="submit" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-sm font-medium rounded-lg">
                Filter
            </button>
            @if (request()->hasAny(['search', 'type', 'active']))
            <a href="{{ route('ingredients.index') }}" class="text-sm text-slate-500 hover:text-slate-700 py-2">Reset</a>
            @endif
        </form>
    </x-card>

    <x-card padding="none">
        @if ($ingredients->isEmpty())
        <div class="p-12 text-center">
            <x-icon name="package" :size="48" class="mx-auto text-slate-300 mb-3" />
            <p class="text-slate-600 font-medium">Belum ada bahan baku</p>
            <p class="text-sm text-slate-400 mt-1">Tambahkan bahan baku untuk memulai.</p>
            @permission('inventory.manage')
            <div class="mt-4">
                <x-button :href="route('ingredients.create')" tag="a" icon="home">
                    Tambah Bahan
                </x-button>
            </div>
            @endpermission
        </div>
        @else
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-slate-50 border-b border-slate-200">
                    <tr>
                        <th class="text-left px-4 py-3 font-medium text-slate-600">SKU</th>
                        <th class="text-left px-4 py-3 font-medium text-slate-600">Nama</th>
                        <th class="text-left px-4 py-3 font-medium text-slate-600">Tipe</th>
                        <th class="text-right px-4 py-3 font-medium text-slate-600">Stok</th>
                        <th class="text-left px-4 py-3 font-medium text-slate-600">Satuan</th>
                        <th class="text-left px-4 py-3 font-medium text-slate-600">Status</th>
                        <th class="text-center px-4 py-3 font-medium text-slate-600">ANN</th>
                        <th class="text-right px-4 py-3 font-medium text-slate-600">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach ($ingredients as $ing)
                    <tr class="hover:bg-slate-50">
                        <td class="px-4 py-3 font-mono text-xs text-slate-600">{{ $ing->sku }}</td>
                        <td class="px-4 py-3">
                            <div class="text-slate-900 font-medium">{{ $ing->name }}</div>
                            @if ($ing->category)
                            <div class="text-xs text-slate-400">{{ $ing->category }}</div>
                            @endif
                        </td>
                        <td class="px-4 py-3">
                            @if ($ing->ingredient_type === 'core')
                            <x-badge variant="info">Core</x-badge>
                            @else
                            <x-badge variant="neutral">Non-Core</x-badge>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-right font-mono text-slate-900">
                            {{ number_format((float) $ing->current_stock, 4, '.', ',') }}
                        </td>
                        <td class="px-4 py-3 text-slate-600 text-xs">{{ $ing->unit?->symbol }}</td>
                        <td class="px-4 py-3">
                            @php
                            $status = $ing->stockStatus();
                            $badgeVariant = match ($status) {
                            'out' => 'danger',
                            'critical' => 'danger',
                            'warning' => 'warning',
                            default => 'success',
                            };
                            $badgeLabel = match ($status) {
                            'out' => 'Habis',
                            'critical' => 'Kritis',
                            'warning' => 'Menipis',
                            default => 'Aman',
                            };
                            @endphp
                            <x-badge :variant="$badgeVariant">{{ $badgeLabel }}</x-badge>
                        </td>
                        <td class="px-4 py-3 text-center">
                            @if ($ing->is_prediction_target)
                            <x-badge variant="info">✓</x-badge>
                            @else
                            <span class="text-slate-300">—</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-right">
                            <div class="inline-flex gap-2">
                                <a href="{{ route('ingredients.show', $ing) }}"
                                    class="text-sm text-slate-600 hover:text-slate-800 font-medium">Detail</a>
                                @permission('inventory.manage')
                                <a href="{{ route('ingredients.edit', $ing) }}"
                                    class="text-sm text-blue-600 hover:text-blue-800 font-medium">Edit</a>
                                <form method="POST" action="{{ route('ingredients.destroy', $ing) }}"
                                    onsubmit="return confirm('Hapus bahan ini? Tindakan tidak dapat dibatalkan.');">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="text-sm text-red-600 hover:text-red-800 font-medium">Hapus</button>
                                </form>
                                @endpermission
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        @if ($ingredients->hasPages())
        <div class="px-4 py-4 border-t border-slate-200">{{ $ingredients->links() }}</div>
        @endif
        @endif
    </x-card>
</x-app-layout>
