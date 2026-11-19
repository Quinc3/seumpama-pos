@php
$p = $promotion ?? null;
$selectedProductIds = old('product_ids', $selectedProducts ?? []);
$initialType = old('discount_type', $p?->discount_type ?? 'percentage');
@endphp

<div class="space-y-4" x-data="{ dtype: '{{ $initialType }}' }">
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <x-input name="name" label="Nama Promosi" required
            :value="old('name', $p?->name ?? '')"
            placeholder="Contoh: Promo Jumat Berkah" />

        <x-input name="code" label="Kode (opsional)"
            :value="old('code', $p?->code ?? '')"
            placeholder="Contoh: JUMAT10" />

        <x-select name="discount_type" label="Tipe Diskon" required
            :options="['percentage' => 'Persentase (%)', 'fixed' => 'Nominal (Rp)']"
            :value="$initialType"
            @change="dtype = $event.target.value" />

        <x-input name="discount_value" type="number" step="any" min="0.01" required
            :label="'Nilai Diskon'"
            :value="old('discount_value', $p?->discount_value ?? '')"
            placeholder="10 untuk 10% atau 5000 untuk Rp 5000" />

        <x-input name="start_at" type="datetime-local" label="Mulai" required
            :value="old('start_at', isset($p) ? $p->start_at->format('Y-m-d\TH:i') : now()->format('Y-m-d\TH:i'))" />

        <x-input name="end_at" type="datetime-local" label="Berakhir" required
            :value="old('end_at', isset($p) ? $p->end_at->format('Y-m-d\TH:i') : now()->addDays(7)->format('Y-m-d\TH:i'))" />

        <x-input name="minimum_purchase" type="number" step="any" min="0"
            label="Minimum Belanja (opsional)"
            :value="old('minimum_purchase', $p?->minimum_purchase ?? '')"
            placeholder="Kosongkan = tanpa minimum" />

        <div x-show="dtype === 'percentage'">
            <x-input name="maximum_discount" type="number" step="any" min="0"
                label="Maksimal Diskon (opsional)"
                :value="old('maximum_discount', $p?->maximum_discount ?? '')"
                placeholder="Batas atas diskon Rp" />
        </div>
    </div>

    <div>
        <label class="block text-sm font-medium text-slate-700 mb-2">
            Produk yang Berlaku
            <span class="text-xs text-slate-400">(kosongkan = semua produk)</span>
        </label>
        <div class="border border-slate-200 rounded-lg max-h-64 overflow-y-auto p-2 space-y-1">
            @foreach ($products as $prod)
            <label class="flex items-center gap-2 p-1.5 rounded hover:bg-slate-50 cursor-pointer">
                <input type="checkbox" name="product_ids[]" value="{{ $prod->id }}"
                    @checked(in_array($prod->id, $selectedProductIds))
                class="h-4 w-4 text-blue-600 border-slate-300 rounded focus:ring-blue-500">
                <span class="text-sm text-slate-900">{{ $prod->name }}</span>
                <span class="text-xs text-slate-400 font-mono">{{ $prod->sku }}</span>
            </label>
            @endforeach
        </div>
    </div>

    <div class="flex items-center gap-2">
        <input type="checkbox" id="is_active" name="is_active" value="1"
            @checked(old('is_active', $p?->is_active ?? true))
        class="h-4 w-4 text-blue-600 border-slate-300 rounded focus:ring-blue-500">
        <label for="is_active" class="text-sm text-slate-700">Aktif</label>
    </div>
</div>
