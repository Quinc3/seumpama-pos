<x-app-layout :title="$recipe->name" :subtitle="'Resep untuk ' . $recipe->product->name">
    <x-slot name="actions">
        @permission('products.manage')
        @if ($recipe->is_active)
        <x-button :href="route('recipes.edit', $recipe)" tag="a" variant="primary" icon="settings">
            Edit (Versi Baru)
        </x-button>
        @endif
        <form method="POST" action="{{ route('recipes.destroy', $recipe) }}"
            onsubmit="return confirm('Hapus resep v{{ $recipe->version }} ini? Tindakan tidak dapat dibatalkan.');"
            class="inline">
            @csrf @method('DELETE')
            <x-button type="submit" variant="danger" icon="x">Hapus</x-button>
        </form>
        @endpermission
        <x-button :href="route('recipes.index')" tag="a" variant="outline">Kembali</x-button>
    </x-slot>

    @if (session('success'))
    <div class="mb-4"><x-alert variant="success" icon="clipboard-check" dismissible>{{ session('success') }}</x-alert></div>
    @endif
    @if (session('error'))
    <div class="mb-4"><x-alert variant="error" icon="shield" dismissible>{{ session('error') }}</x-alert></div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-4 mb-4">
        <x-card title="Informasi Resep" class="lg:col-span-1">
            <dl class="space-y-3 text-sm">
                <div>
                    <dt class="text-slate-500 text-xs mb-1">Produk</dt>
                    <dd class="font-medium text-slate-900">{{ $recipe->product->name }}</dd>
                    <dd class="text-xs text-slate-400 font-mono">{{ $recipe->product->sku }}</dd>
                </div>
                <div>
                    <dt class="text-slate-500 text-xs mb-1">Versi</dt>
                    <dd><x-badge variant="info">v{{ $recipe->version }}</x-badge></dd>
                </div>
                <div>
                    <dt class="text-slate-500 text-xs mb-1">Berlaku Sejak</dt>
                    <dd class="text-slate-900">{{ $recipe->effective_from->format('d M Y H:i') }}</dd>
                </div>
                @if ($recipe->effective_until)
                <div>
                    <dt class="text-slate-500 text-xs mb-1">Diarsipkan Pada</dt>
                    <dd class="text-slate-900">{{ $recipe->effective_until->format('d M Y H:i') }}</dd>
                </div>
                @endif
                <div>
                    <dt class="text-slate-500 text-xs mb-1">Status</dt>
                    <dd>
                        @if ($recipe->is_active)
                        <x-badge variant="success">Aktif</x-badge>
                        @else
                        <x-badge variant="neutral">Arsip</x-badge>
                        @endif
                    </dd>
                </div>
                <div>
                    <dt class="text-slate-500 text-xs mb-1">Dibuat Oleh</dt>
                    <dd class="text-slate-900">{{ $recipe->createdBy?->name ?? '—' }}</dd>
                </div>
            </dl>
        </x-card>

        <x-card title="Komposisi Bahan" :subtitle="$recipe->items->count() . ' bahan'" class="lg:col-span-2">
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-slate-50 border-b border-slate-200">
                        <tr>
                            <th class="text-left px-4 py-2 font-medium text-slate-600">Bahan</th>
                            <th class="text-left px-4 py-2 font-medium text-slate-600">Kategori</th>
                            <th class="text-right px-4 py-2 font-medium text-slate-600">Jumlah</th>
                            <th class="text-left px-4 py-2 font-medium text-slate-600">Satuan</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach ($recipe->items as $item)
                        <tr>
                            <td class="px-4 py-3">
                                <div class="font-medium text-slate-900">{{ $item->ingredient->name }}</div>
                                <div class="text-xs text-slate-400 font-mono">{{ $item->ingredient->sku }}</div>
                            </td>
                            <td class="px-4 py-3 text-slate-600 text-sm">{{ $item->ingredient->category ?: '—' }}</td>
                            <td class="px-4 py-3 text-right font-mono font-medium text-slate-900">
                                {{ number_format((float) $item->quantity, 4, '.', ',') }}
                            </td>
                            <td class="px-4 py-3 text-slate-600 text-sm">{{ $item->unit?->symbol }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </x-card>
    </div>

    @if ($versions->count() > 1)
    <x-card title="Riwayat Versi" :subtitle="$versions->count() . ' versi'">
        <div class="space-y-2">
            @foreach ($versions as $v)
            <div class="flex items-center gap-3 p-3 rounded-lg border
                                {{ $v->id === $recipe->id ? 'bg-blue-50 border-blue-200' : 'bg-slate-50 border-slate-200' }}">
                <div class="flex items-center justify-center w-12 h-12 rounded-lg font-mono font-bold text-sm shrink-0
                                    {{ $v->is_active ? 'bg-blue-600 text-white' : 'bg-slate-300 text-slate-600' }}">
                    v{{ $v->version }}
                </div>
                <div class="flex-1 min-w-0">
                    <div class="font-medium text-slate-900">{{ $v->name }}</div>
                    <div class="text-xs text-slate-500">
                        {{ $v->effective_from->format('d M Y H:i') }}
                        @if ($v->effective_until)
                        — {{ $v->effective_until->format('d M Y H:i') }}
                        @endif
                    </div>
                </div>
                <div class="shrink-0">
                    @if ($v->is_active)
                    <x-badge variant="success">Aktif</x-badge>
                    @else
                    <x-badge variant="neutral">Arsip</x-badge>
                    @endif
                </div>
                <div class="shrink-0 w-24 text-right">
                    @if ($v->id === $recipe->id)
                    <span class="text-xs text-slate-400">Sedang dilihat</span>
                    @else
                    <a href="{{ route('recipes.show', $v) }}" class="text-sm text-blue-600 hover:text-blue-800 font-medium">Lihat</a>
                    @endif
                </div>
            </div>
            @endforeach
        </div>
    </x-card>
    @endif
</x-app-layout>
