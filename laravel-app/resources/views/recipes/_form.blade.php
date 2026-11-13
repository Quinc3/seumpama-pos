@php
$isEdit = isset($recipe);
$r = $recipe ?? null;
$formName = old('name', $isEdit ? $r->name : 'Resep ' . $product->name);
$actionUrl = $isEdit ? route('recipes.update', $r) : route('recipes.store', $product);
$cancelUrl = $isEdit ? route('recipes.show', $r) : route('recipes.index');
$submitLabel = $isEdit ? 'Simpan sebagai Versi Baru' : 'Simpan Resep';

$initialItems = old('items', $isEdit
? $r->items->map(fn($i) => [
'ingredient_id' => (int) $i->ingredient_id,
'quantity' => (float) $i->quantity,
])->values()->toArray()
: [['ingredient_id' => '', 'quantity' => '']]
);

$ingredientsArray = $ingredients->map(fn($i) => [
'id' => $i->id,
'name' => $i->name,
'sku' => $i->sku,
'unit_symbol' => $i->unit?->symbol ?? '',
])->values()->toArray();
@endphp

<form method="POST" action="{{ $actionUrl }}" class="space-y-6"
    x-data="{
          items: @js($initialItems),
          ingredients: @js($ingredientsArray),
          addRow() { this.items.push({ ingredient_id: '', quantity: '' }); },
          removeRow(i) { this.items.splice(i, 1); if (this.items.length === 0) this.addRow(); },
          getUnit(i) {
              const id = parseInt(this.items[i].ingredient_id);
              const ing = this.ingredients.find(x => x.id === id);
              return ing ? ing.unit_symbol : '';
          }
      }">
    @csrf
    @if ($isEdit) @method('PUT') @endif

    <div>
        <x-input name="name" label="Nama Resep" required
            :value="$formName"
            placeholder="Contoh: Resep Standard" />
    </div>

    <div>
        <div class="flex items-center justify-between mb-3">
            <h3 class="text-sm font-semibold text-slate-900">Komposisi Bahan</h3>
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
                <div class="flex items-start gap-3 p-3 bg-slate-50 rounded-lg border border-slate-200">
                    <div class="flex-1">
                        <label class="block text-xs font-medium text-slate-600 mb-1">Bahan</label>
                        <select :name="`items[${index}][ingredient_id]`"
                            x-model="item.ingredient_id"
                            required
                            class="w-full px-3 py-2 rounded-lg border border-slate-300 text-sm bg-white focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            <option value="">— Pilih Bahan —</option>
                            <template x-for="ing in ingredients" :key="ing.id">
                                <option :value="ing.id" x-text="`${ing.name} (${ing.sku})`"></option>
                            </template>
                        </select>
                    </div>
                    <div class="w-32">
                        <label class="block text-xs font-medium text-slate-600 mb-1">Jumlah</label>
                        <input type="number" step="0.0001" min="0.0001" required
                            :name="`items[${index}][quantity]`"
                            x-model="item.quantity"
                            placeholder="0"
                            class="w-full px-3 py-2 rounded-lg border border-slate-300 text-sm text-right focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    </div>
                    <div class="w-16 pt-6">
                        <span class="block text-sm font-mono text-slate-700 text-center py-2" x-text="getUnit(index) || '—'"></span>
                    </div>
                    <div class="pt-6">
                        <button type="button" @click="removeRow(index)"
                            x-show="items.length > 1"
                            class="p-1.5 rounded text-red-500 hover:bg-red-50">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M3 6h18" />
                                <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6" />
                                <path d="M8 6V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2" />
                            </svg>
                        </button>
                    </div>
                </div>
            </template>
        </div>

        <p class="mt-2 text-xs text-slate-500">
            Satuan otomatis mengikuti satuan dasar bahan baku. Quantity dalam satuan tersebut.
        </p>
    </div>

    <div class="flex gap-2 pt-4 border-t border-slate-100">
        <x-button type="submit" icon="clipboard-check">{{ $submitLabel }}</x-button>
        <x-button :href="$cancelUrl" tag="a" variant="outline">Batal</x-button>
    </div>
</form>
