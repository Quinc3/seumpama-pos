<div class="space-y-4">
    <x-input
        name="name"
        label="Nama Kategori"
        required
        :value="old('name', $category->name ?? '')"
        placeholder="Contoh: Coffee, Non-Coffee" />

    <div>
        <label for="description" class="block text-sm font-medium text-slate-700 mb-1">Deskripsi</label>
        <textarea id="description" name="description" rows="3"
            placeholder="Deskripsi singkat (opsional)"
            class="w-full px-3 py-2 rounded-lg border border-slate-300 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">{{ old('description', $category->description ?? '') }}</textarea>
        @error('description')
        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
        @enderror
    </div>

    <x-input
        name="sort_order"
        type="number"
        label="Urutan Tampilan"
        :value="old('sort_order', $category->sort_order ?? 0)"
        placeholder="0" />

    <div class="flex items-center gap-2">
        <input type="checkbox" id="is_active" name="is_active" value="1"
            @checked(old('is_active', $category->is_active ?? true))
        class="h-4 w-4 text-blue-600 border-slate-300 rounded focus:ring-blue-500">
        <label for="is_active" class="text-sm text-slate-700">Aktif</label>
    </div>
</div>
