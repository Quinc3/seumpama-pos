@php
$isEdit = isset($ingredient);
$ing = $ingredient ?? null;
$unitOptions = $units->mapWithKeys(fn ($u) => [$u->id => "{$u->name} ({$u->symbol})"])->toArray();
$initialType = old('ingredient_type', $ing?->ingredient_type ?? 'core');
@endphp

<div class="space-y-6" x-data="{ type: '{{ $initialType }}' }">
    {{-- Basic Info --}}
    <div>
        <h3 class="text-sm font-semibold text-slate-900 mb-3 pb-2 border-b border-slate-100">Informasi Dasar</h3>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <x-input name="sku" label="SKU" required
                :value="old('sku', $ing?->sku ?? '')"
                placeholder="Contoh: ING-001" />

            <x-input name="name" label="Nama Bahan" required
                :value="old('name', $ing?->name ?? '')"
                placeholder="Contoh: Biji Kopi Arabica" />

            <x-input name="category" label="Kategori (opsional)"
                :value="old('category', $ing?->category ?? '')"
                placeholder="Contoh: Coffee, Dairy, Syrup" />

            <x-select name="unit_id" label="Satuan Dasar" required
                :options="$unitOptions" placeholder="— Pilih Satuan —"
                :value="old('unit_id', $ing?->unit_id ?? '')" />

            <x-select name="ingredient_type" label="Tipe Bahan" required
                :options="['core' => 'Core (bahan inti)', 'non_core' => 'Non-Core (perlengkapan)']"
                :value="$initialType"
                @change="type = $event.target.value" />
        </div>
    </div>

    {{-- Stock Settings --}}
    <div>
        <h3 class="text-sm font-semibold text-slate-900 mb-3 pb-2 border-b border-slate-100">Pengaturan Stok</h3>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            @if (! $isEdit)
            <x-input name="current_stock" type="number" step="0.0001" min="0"
                label="Stok Awal" :value="old('current_stock', 0)" />
            <div></div>
            @else
            <div class="md:col-span-2 p-3 bg-blue-50 border border-blue-200 rounded-lg text-xs text-blue-800">
                <strong>Stok saat ini:</strong> {{ number_format((float) $ing->current_stock, 4) }} {{ $ing->unit?->symbol }}.
                Perubahan stok akan dilakukan melalui <em>Stock Movement</em> (Phase 3).
            </div>
            @endif

            <x-input name="minimum_stock" type="number" step="0.0001" min="0"
                label="Minimum Stock"
                :value="old('minimum_stock', $ing?->minimum_stock ?? '')"
                placeholder="Stok minimum untuk warning" />

            <x-input name="safety_stock" type="number" step="0.0001" min="0"
                label="Safety Stock"
                :value="old('safety_stock', $ing?->safety_stock ?? '')"
                placeholder="Buffer persediaan" />

            <x-input name="lead_time_days" type="number" step="0.01" min="0"
                label="Lead Time (hari)"
                :value="old('lead_time_days', $ing?->lead_time_days ?? '')"
                placeholder="Contoh: 2.5" />
        </div>
    </div>

    {{-- ANN Settings --}}
    <div>
        <h3 class="text-sm font-semibold text-slate-900 mb-3 pb-2 border-b border-slate-100">Pengaturan ANN</h3>

        <div x-show="type === 'core'" class="flex items-start gap-2">
            <input type="checkbox" id="is_prediction_target" name="is_prediction_target" value="1"
                @checked(old('is_prediction_target', $ing?->is_prediction_target ?? false))
            class="mt-0.5 h-4 w-4 text-blue-600 border-slate-300 rounded focus:ring-blue-500">
            <div>
                <label for="is_prediction_target" class="text-sm font-medium text-slate-900">Target Prediksi ANN</label>
                <p class="text-xs text-slate-500">Aktifkan jika bahan ini menjadi target prediksi ANN.</p>
            </div>
        </div>

        <p x-show="type === 'non_core'" style="display: none;" class="text-xs text-slate-500 italic">
            Bahan tipe Non-Core tidak dapat menjadi target prediksi ANN.
        </p>
    </div>

    {{-- Status + Notes --}}
    <div>
        <h3 class="text-sm font-semibold text-slate-900 mb-3 pb-2 border-b border-slate-100">Status & Catatan</h3>
        <div class="space-y-4">
            <div class="flex items-center gap-2">
                <input type="checkbox" id="is_active" name="is_active" value="1"
                    @checked(old('is_active', $ing?->is_active ?? true))
                class="h-4 w-4 text-blue-600 border-slate-300 rounded focus:ring-blue-500">
                <label for="is_active" class="text-sm text-slate-700">Aktif</label>
            </div>

            <div>
                <label for="notes" class="block text-sm font-medium text-slate-700 mb-1">Catatan</label>
                <textarea id="notes" name="notes" rows="2"
                    placeholder="Catatan tambahan (opsional)"
                    class="w-full px-3 py-2 rounded-lg border border-slate-300 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">{{ old('notes', $ing?->notes ?? '') }}</textarea>
                @error('notes')
                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                @enderror
            </div>
        </div>
    </div>
</div>
