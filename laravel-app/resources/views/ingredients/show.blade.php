<x-app-layout :title="$ingredient->name" :subtitle="'SKU: ' . $ingredient->sku">
    <x-slot name="actions">
        @permission('inventory.manage')
        <x-button :href="route('ingredients.edit', $ingredient)" tag="a" variant="primary" icon="settings">
            Edit
        </x-button>
        @endpermission
        <x-button :href="route('ingredients.index')" tag="a" variant="outline">Kembali</x-button>
    </x-slot>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
        <x-card title="Informasi" class="lg:col-span-2">
            <dl class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-sm">
                <div>
                    <dt class="text-slate-500 mb-1">SKU</dt>
                    <dd class="font-mono text-slate-900">{{ $ingredient->sku }}</dd>
                </div>
                <div>
                    <dt class="text-slate-500 mb-1">Kategori</dt>
                    <dd class="text-slate-900">{{ $ingredient->category ?: '—' }}</dd>
                </div>
                <div>
                    <dt class="text-slate-500 mb-1">Tipe</dt>
                    <dd>
                        @if ($ingredient->ingredient_type === 'core')
                        <x-badge variant="info">Core</x-badge>
                        @else
                        <x-badge variant="neutral">Non-Core</x-badge>
                        @endif
                    </dd>
                </div>
                <div>
                    <dt class="text-slate-500 mb-1">Satuan</dt>
                    <dd class="text-slate-900">{{ $ingredient->unit?->name }} ({{ $ingredient->unit?->symbol }})</dd>
                </div>
                <div>
                    <dt class="text-slate-500 mb-1">Target ANN</dt>
                    <dd>{{ $ingredient->is_prediction_target ? 'Ya' : 'Tidak' }}</dd>
                </div>
                <div>
                    <dt class="text-slate-500 mb-1">Status</dt>
                    <dd>
                        @if ($ingredient->is_active)
                        <x-badge variant="success">Aktif</x-badge>
                        @else
                        <x-badge variant="neutral">Nonaktif</x-badge>
                        @endif
                    </dd>
                </div>
            </dl>

            @if ($ingredient->notes)
            <div class="mt-4 pt-4 border-t border-slate-100">
                <dt class="text-slate-500 text-sm mb-1">Catatan</dt>
                <dd class="text-slate-900 text-sm">{{ $ingredient->notes }}</dd>
            </div>
            @endif
        </x-card>

        <x-card title="Stok">
            <div class="space-y-3 text-sm">
                <div class="flex justify-between">
                    <span class="text-slate-500">Stok Saat Ini</span>
                    <span class="font-mono font-semibold text-slate-900">
                        {{ number_format((float) $ingredient->current_stock, 4) }} {{ $ingredient->unit?->symbol }}
                    </span>
                </div>
                <div class="flex justify-between">
                    <span class="text-slate-500">Minimum</span>
                    <span class="font-mono text-slate-700">
                        {{ $ingredient->minimum_stock ? number_format((float) $ingredient->minimum_stock, 4) : '—' }}
                    </span>
                </div>
                <div class="flex justify-between">
                    <span class="text-slate-500">Safety Stock</span>
                    <span class="font-mono text-slate-700">
                        {{ $ingredient->safety_stock ? number_format((float) $ingredient->safety_stock, 4) : '—' }}
                    </span>
                </div>
                <div class="flex justify-between">
                    <span class="text-slate-500">Lead Time</span>
                    <span class="font-mono text-slate-700">
                        {{ $ingredient->lead_time_days ? $ingredient->lead_time_days . ' hari' : '—' }}
                    </span>
                </div>
            </div>

            <div class="mt-4 pt-4 border-t border-slate-100">
                <p class="text-xs text-slate-400">
                    Perubahan stok akan dilakukan melalui Stock Movement (Phase 3).
                </p>
            </div>
        </x-card>
    </div>
</x-app-layout>
