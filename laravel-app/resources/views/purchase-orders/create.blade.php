<x-app-layout title="Buat Purchase Order" subtitle="PO bahan baku baru">
    <x-slot name="actions">
        <x-button :href="route('purchase-orders.index')" tag="a" variant="outline">Kembali</x-button>
    </x-slot>

    <x-card class="max-w-4xl">
        <form method="POST" action="{{ route('purchase-orders.store') }}" class="space-y-6"
            x-data="{
                  items: [{ ingredient_id: '', quantity: '', unit_price: '' }],
                  ingredients: {{ $ingredients->map(fn($i) => [
                      'id' => $i->id,
                      'name' => $i->name,
                      'sku' => $i->sku,
                      'unit_symbol' => $i->unit?->symbol ?? '',
                  ])->toJson() }},
                  addRow() { this.items.push({ ingredient_id: '', quantity: '', unit_price: '' }); },
                  removeRow(i) { this.items.splice(i, 1); if (this.items.length === 0) this.addRow(); },
                  getUnit(i) { const id = parseInt(this.items[i].ingredient_id); const ing = this.ingredients.find(x=>x.id===id); return ing ? ing.unit_symbol : ''; },
                  rowTotal(i) { const q = parseFloat(this.items[i].quantity); const p = parseFloat(this.items[i].unit_price); return (isNaN(q)||isNaN(p)) ? 0 : q*p; },
                  get grandTotal() { return this.items.reduce((sum,_,i) => sum + this.rowTotal(i), 0); }
              }">
            @csrf

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div class="md:col-span-2">
                    <x-select name="supplier_id" label="Supplier" required
                        :options="$suppliers->mapWithKeys(fn($s) => [$s->id => $s->name])->toArray()"
                        placeholder="— Pilih Supplier —" :value="old('supplier_id')" />
                </div>
                <div></div>
                <x-input name="purchase_date" type="date" label="Tanggal PO" required :value="old('purchase_date', now()->format('Y-m-d'))" />
                <x-input name="expected_date" type="date" label="Estimasi Kedatangan" :value="old('expected_date')" />
            </div>

            <div>
                <label for="notes" class="block text-sm font-medium text-slate-700 mb-1">Catatan</label>
                <textarea id="notes" name="notes" rows="2" placeholder="Catatan PO (opsional)"
                    class="w-full px-3 py-2 rounded-lg border border-slate-300 text-sm">{{ old('notes') }}</textarea>
            </div>

            <div>
                <div class="flex items-center justify-between mb-3">
                    <h3 class="text-sm font-semibold text-slate-900">Item PO</h3>
                    <button type="button" @click="addRow()"
                        class="inline-flex items-center gap-1 px-3 py-1.5 text-xs font-medium text-blue-700 bg-blue-50 hover:bg-blue-100 rounded-lg">
                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M5 12h14" />
                            <path d="M12 5v14" />
                        </svg>
                        Tambah Item
                    </button>
                </div>

                @error('items')
                <div class="mb-3 p-3 bg-red-50 border border-red-200 rounded text-sm text-red-700">{{ $message }}</div>
                @enderror

                <div class="space-y-3">
                    <template x-for="(item, index) in items" :key="index">
                        <div class="flex items-end gap-3 p-3 bg-slate-50 rounded-lg border border-slate-200">
                            <div class="flex-1">
                                <label class="block text-xs font-medium text-slate-600 mb-1">Bahan</label>
                                <select :name="`items[${index}][ingredient_id]`" x-model="item.ingredient_id" required
                                    class="w-full px-3 py-2 rounded-lg border border-slate-300 text-sm bg-white">
                                    <option value="">— Pilih —</option>
                                    <template x-for="ing in ingredients" :key="ing.id">
                                        <option :value="ing.id" x-text="`${ing.name} (${ing.sku})`"></option>
                                    </template>
                                </select>
                            </div>
                            <div class="w-28">
                                <label class="block text-xs font-medium text-slate-600 mb-1">Qty</label>
                                <input type="number" step="0.0001" min="0.0001" required
                                    :name="`items[${index}][quantity]`" x-model="item.quantity" placeholder="0"
                                    class="w-full px-3 py-2 rounded-lg border border-slate-300 text-sm text-right">
                            </div>
                            <div class="w-16 pt-6">
                                <span class="block text-sm font-mono text-slate-700 text-center py-2" x-text="getUnit(index) || '—'"></span>
                            </div>
                            <div class="w-32">
                                <label class="block text-xs font-medium text-slate-600 mb-1">Harga (Rp)</label>
                                <input type="number" step="any" min="0" required
                                    :name="`items[${index}][unit_price]`"
                                    x-model="item.unit_price"
                                    placeholder="0"
                                    class="w-full px-3 py-2 rounded-lg border border-slate-300 text-sm text-right">
                            </div>
                            <div class="w-32 text-right pt-6">
                                <span class="block text-sm font-mono font-medium text-slate-900 py-2" x-text="'Rp ' + new Intl.NumberFormat('id-ID').format(rowTotal(index))"></span>
                            </div>
                            <div class="pt-6">
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
                    </template>
                </div>

                <div class="mt-4 p-3 bg-blue-50 border border-blue-200 rounded-lg flex justify-between items-center">
                    <span class="text-sm font-medium text-blue-900">Total PO</span>
                    <span class="font-mono font-bold text-blue-900 text-lg" x-text="'Rp ' + new Intl.NumberFormat('id-ID').format(grandTotal)"></span>
                </div>
            </div>

            <div class="flex gap-2 pt-4 border-t border-slate-100">
                <x-button type="submit" icon="clipboard-check">Simpan sebagai Draft</x-button>
                <x-button :href="route('purchase-orders.index')" tag="a" variant="outline">Batal</x-button>
            </div>
        </form>
    </x-card>
</x-app-layout>
