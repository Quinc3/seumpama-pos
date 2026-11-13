<x-app-layout title="Buat Stock Opname" subtitle="Hitung stok fisik dan bandingkan dengan sistem">
    <x-slot name="actions">
        <x-button :href="route('stock-opnames.index')" tag="a" variant="outline">Kembali</x-button>
    </x-slot>

    <x-card class="max-w-4xl">
        <form method="POST" action="{{ route('stock-opnames.store') }}" class="space-y-6"
            x-data="{
                  items: [{ ingredient_id: '', physical_quantity: '', notes: '' }],
                  ingredients: {{ $ingredients->map(fn($i) => [
                      'id' => $i->id,
                      'name' => $i->name,
                      'sku' => $i->sku,
                      'unit_symbol' => $i->unit?->symbol ?? '',
                      'system_stock' => (float) $i->current_stock,
                  ])->toJson() }},
                  addRow() { this.items.push({ ingredient_id: '', physical_quantity: '', notes: '' }); },
                  removeRow(i) { this.items.splice(i, 1); if (this.items.length === 0) this.addRow(); },
                  getIngredient(i) {
                      const id = parseInt(this.items[i].ingredient_id);
                      return this.ingredients.find(x => x.id === id) || null;
                  },
                  getDiff(i) {
                      const ing = this.getIngredient(i);
                      if (!ing) return null;
                      const phys = parseFloat(this.items[i].physical_quantity);
                      if (isNaN(phys)) return null;
                      return phys - ing.system_stock;
                  }
              }">
            @csrf

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <x-input name="opname_date" type="date" label="Tanggal Opname" required
                    :value="old('opname_date', now()->format('Y-m-d'))" />
                <div></div>
            </div>

            <div>
                <label for="notes" class="block text-sm font-medium text-slate-700 mb-1">Catatan Umum</label>
                <textarea id="notes" name="notes" rows="2" placeholder="Catatan opname (opsional)"
                    class="w-full px-3 py-2 rounded-lg border border-slate-300 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500">{{ old('notes') }}</textarea>
            </div>

            <div>
                <div class="flex items-center justify-between mb-3">
                    <h3 class="text-sm font-semibold text-slate-900">Item yang Dihitung</h3>
                    <button type="button" @click="addRow()"
                        class="inline-flex items-center gap-1 px-3 py-1.5 text-xs font-medium text-blue-700 bg-blue-50 hover:bg-blue-100 rounded-lg">
                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M5 12h14" />
                            <path d="M12 5v14" />
                        </svg>
                        Tambah Bahan
                    </button>
                </div>

                @error('items')
                <div class="mb-3 p-3 bg-red-50 border border-red-200 rounded text-sm text-red-700">{{ $message }}</div>
                @enderror

                <div class="space-y-3">
                    <template x-for="(item, index) in items" :key="index">
                        <div class="p-3 bg-slate-50 rounded-lg border border-slate-200">
                            <div class="grid grid-cols-1 md:grid-cols-12 gap-3 items-end">
                                <div class="md:col-span-5">
                                    <label class="block text-xs font-medium text-slate-600 mb-1">Bahan</label>
                                    <select :name="`items[${index}][ingredient_id]`" x-model="item.ingredient_id" required
                                        class="w-full px-3 py-2 rounded-lg border border-slate-300 text-sm bg-white focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                        <option value="">— Pilih —</option>
                                        <template x-for="ing in ingredients" :key="ing.id">
                                            <option :value="ing.id" x-text="`${ing.name} (${ing.sku})`"></option>
                                        </template>
                                    </select>
                                </div>
                                <div class="md:col-span-2">
                                    <label class="block text-xs font-medium text-slate-600 mb-1">Stok Sistem</label>
                                    <div class="px-3 py-2 bg-slate-100 rounded text-sm font-mono text-right text-slate-700"
                                        x-text="getIngredient(index) ? new Intl.NumberFormat('id-ID', {minimumFractionDigits: 2, maximumFractionDigits: 4}).format(getIngredient(index).system_stock) : '—'"></div>
                                </div>
                                <div class="md:col-span-2">
                                    <label class="block text-xs font-medium text-slate-600 mb-1">Fisik <span class="text-red-500">*</span></label>
                                    <input type="number" step="0.0001" min="0" required
                                        :name="`items[${index}][physical_quantity]`"
                                        x-model="item.physical_quantity"
                                        placeholder="0"
                                        class="w-full px-3 py-2 rounded-lg border border-slate-300 text-sm text-right focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                </div>
                                <div class="md:col-span-2">
                                    <label class="block text-xs font-medium text-slate-600 mb-1">Selisih</label>
                                    <div class="px-3 py-2 rounded text-sm font-mono text-right font-medium"
                                        :class="getDiff(index) === null ? 'bg-slate-100 text-slate-400' : (getDiff(index) === 0 ? 'bg-slate-100 text-slate-600' : (getDiff(index) > 0 ? 'bg-green-50 text-green-700' : 'bg-red-50 text-red-700'))"
                                        x-text="getDiff(index) === null ? '—' : (getDiff(index) > 0 ? '+' : '') + new Intl.NumberFormat('id-ID', {minimumFractionDigits: 2, maximumFractionDigits: 4}).format(getDiff(index))"></div>
                                </div>
                                <div class="md:col-span-1 flex justify-end">
                                    <button type="button" @click="removeRow(index)" x-show="items.length > 1"
                                        class="p-2 rounded text-red-500 hover:bg-red-50">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                            <path d="M3 6h18" />
                                            <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6" />
                                            <path d="M8 6V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2" />
                                        </svg>
                                    </button>
                                </div>
                            </div>
                            <div class="mt-2">
                                <input type="text" :name="`items[${index}][notes]`" x-model="item.notes"
                                    placeholder="Catatan per item (opsional, misal: tumpah, expired)"
                                    class="w-full px-3 py-1.5 rounded border border-slate-300 text-xs">
                            </div>
                        </div>
                    </template>
                </div>

                <p class="mt-2 text-xs text-slate-500">
                    Selisih = Fisik − Sistem. Positif = stok fisik lebih banyak. Negatif = kurang.
                </p>
            </div>

            <div class="flex gap-2 pt-4 border-t border-slate-100">
                <x-button type="submit" icon="clipboard-check">Simpan sebagai Draft</x-button>
                <x-button :href="route('stock-opnames.index')" tag="a" variant="outline">Batal</x-button>
            </div>
        </form>
    </x-card>
</x-app-layout>
