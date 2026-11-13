@php
$isEdit = isset($product);
$p = $product ?? null;
$categoryOptions = $categories->mapWithKeys(fn ($c) => [$c->id => $c->name])->toArray();
@endphp

<div class="space-y-6">
    {{-- Basic Info --}}
    <div>
        <h3 class="text-sm font-semibold text-slate-900 mb-3 pb-2 border-b border-slate-100">Informasi Dasar</h3>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <x-input name="sku" label="SKU" required
                :value="old('sku', $p?->sku ?? '')"
                placeholder="Contoh: PRD-001" />

            <x-input name="name" label="Nama Produk" required
                :value="old('name', $p?->name ?? '')"
                placeholder="Contoh: Cappuccino" />

            <x-select name="category_id" label="Kategori" required
                :options="$categoryOptions" placeholder="— Pilih Kategori —"
                :value="old('category_id', $p?->category_id ?? '')" />

            <x-input name="selling_price" type="number" step="any" min="0" required
                label="Harga Jual (Rp)"
                :value="old('selling_price', $p?->selling_price ?? '')"
                placeholder="Contoh: 28000" />

            <div class="md:col-span-2">
                <label for="description" class="block text-sm font-medium text-slate-700 mb-1">Deskripsi</label>
                <textarea id="description" name="description" rows="3"
                    placeholder="Deskripsi produk (opsional)"
                    class="w-full px-3 py-2 rounded-lg border border-slate-300 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">{{ old('description', $p?->description ?? '') }}</textarea>
                @error('description')
                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                @enderror
            </div>
        </div>
    </div>

    {{-- Image --}}
    <div>
        <h3 class="text-sm font-semibold text-slate-900 mb-3 pb-2 border-b border-slate-100">Gambar Produk</h3>

        <x-file-input
            name="image"
            label="{{ $isEdit ? 'Ganti Gambar Produk' : 'Upload Gambar Produk' }}"
            :existing-path="$isEdit ? $p->image_path : null"
            :remove-name="$isEdit ? 'remove_image' : null"
            hint="Format: JPG, PNG, WebP — Maksimal 2MB. Opsional." />
    </div>

    {{-- Status --}}
    <div>
        <h3 class="text-sm font-semibold text-slate-900 mb-3 pb-2 border-b border-slate-100">Status</h3>
        <div class="space-y-3">
            <div class="flex items-center gap-2">
                <input type="checkbox" id="is_available" name="is_available" value="1"
                    @checked(old('is_available', $p?->is_available ?? true))
                class="h-4 w-4 text-blue-600 border-slate-300 rounded focus:ring-blue-500">
                <label for="is_available" class="text-sm text-slate-700">Tersedia untuk dijual</label>
            </div>
            <div class="flex items-center gap-2">
                <input type="checkbox" id="is_active" name="is_active" value="1"
                    @checked(old('is_active', $p?->is_active ?? true))
                class="h-4 w-4 text-blue-600 border-slate-300 rounded focus:ring-blue-500">
                <label for="is_active" class="text-sm text-slate-700">Aktif (tampil di daftar)</label>
            </div>
        </div>
    </div>
</div>
