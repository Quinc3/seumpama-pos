@php
$isOut = $mode === 'out';
$title = $isOut ? 'Stock Out' : 'Stock In';
$subtitle = $isOut ? 'Catat pengurangan stok (waste, adjustment minus, retur keluar)' : 'Catat penambahan stok (pembelian, adjustment plus, retur masuk)';
@endphp

<x-app-layout :title="$title" :subtitle="$subtitle">
    <x-slot name="actions">
        <x-button :href="route('inventory.movements.index')" tag="a" variant="outline">Kembali</x-button>
    </x-slot>

    <x-card class="max-w-2xl">
        <form method="POST" action="{{ route('inventory.movements.store') }}" class="space-y-5"
            x-data="{
                  ingredientId: '',
                  ingredients: {{ $ingredients->map(fn($i) => [
                      'id' => $i->id,
                      'name' => $i->name,
                      'sku' => $i->sku,
                      'unit_symbol' => $i->unit?->symbol ?? '',
                      'current_stock' => (float) $i->current_stock,
                  ])->toJson() }},
                  quantity: '',
                  get selectedIngredient() { return this.ingredients.find(i => i.id == this.ingredientId) || null; },
                  get previewAfter() {
                      if (!this.selectedIngredient || !this.quantity) return null;
                      const q = parseFloat(this.quantity);
                      if (isNaN(q) || q <= 0) return null;
                      return this.selectedIngredient.current_stock + ({{ $isOut ? '-1' : '1' }} * q);
                  }
              }">
            @csrf
            <input type="hidden" name="mode" value="{{ $mode }}">

            <div>
                <label for="ingredient_id" class="block text-sm font-medium text-slate-700 mb-1">
                    Bahan <span class="text-red-500">*</span>
                </label>
                <select id="ingredient_id" name="ingredient_id" required x-model="ingredientId"
                    class="w-full px-3 py-2 rounded-lg border border-slate-300 text-sm bg-white focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    <option value="">— Pilih Bahan —</option>
                    @foreach ($ingredients as $ing)
                    <option value="{{ $ing->id }}">{{ $ing->name }} ({{ $ing->sku }})</option>
                    @endforeach
                </select>
                @error('ingredient_id')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
            </div>

            <div x-show="selectedIngredient" x-cloak class="p-3 bg-slate-50 border border-slate-200 rounded-lg text-sm">
                <span class="text-slate-600">Stok saat ini:</span>
                <strong class="text-slate-900 font-mono" x-text="selectedIngredient ? new Intl.NumberFormat('id-ID', {minimumFractionDigits: 4}).format(selectedIngredient.current_stock) : ''"></strong>
                <span class="text-slate-600" x-text="selectedIngredient ? selectedIngredient.unit_symbol : ''"></span>
            </div>

            <div>
                <label for="movement_type" class="block text-sm font-medium text-slate-700 mb-1">
                    Tipe Movement <span class="text-red-500">*</span>
                </label>
                <select id="movement_type" name="movement_type" required
                    class="w-full px-3 py-2 rounded-lg border border-slate-300 text-sm bg-white focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    @foreach ($types as $type => $label)
                    <option value="{{ $type }}">{{ $label }}</option>
                    @endforeach
                </select>
                @error('movement_type')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
            </div>

            <div>
                <label for="quantity" class="block text-sm font-medium text-slate-700 mb-1">
                    Jumlah <span class="text-red-500">*</span>
                </label>
                <div class="flex gap-2 items-center">
                    <input type="number" id="quantity" name="quantity" step="0.0001" min="0.0001" required
                        x-model="quantity" placeholder="0"
                        class="flex-1 px-3 py-2 rounded-lg border border-slate-300 text-sm text-right focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    <span class="w-16 text-sm font-mono text-slate-700" x-text="selectedIngredient ? selectedIngredient.unit_symbol : '—'"></span>
                </div>
                @error('quantity')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
            </div>

            <div x-show="previewAfter !== null" x-cloak class="p-3 rounded-lg border text-sm"
                :class="previewAfter < 0 ? 'bg-red-50 border-red-200 text-red-800' : 'bg-blue-50 border-blue-200 text-blue-800'">
                <template x-if="previewAfter < 0">
                    <p>
                        <strong>⚠ Peringatan:</strong> Setelah movement, stok akan menjadi
                        <strong class="font-mono" x-text="new Intl.NumberFormat('id-ID', {minimumFractionDigits: 4}).format(previewAfter)"></strong>
                        <span x-text="selectedIngredient.unit_symbol"></span> (negatif). Sistem tetap mengizinkan, tapi perlu ditindaklanjuti.
                    </p>
                </template>
                <template x-if="previewAfter >= 0">
                    <p>
                        Stok setelah movement: <strong class="font-mono" x-text="new Intl.NumberFormat('id-ID', {minimumFractionDigits: 4}).format(previewAfter)"></strong>
                        <span x-text="selectedIngredient.unit_symbol"></span>
                    </p>
                </template>
            </div>

            <div>
                <label for="notes" class="block text-sm font-medium text-slate-700 mb-1">Catatan</label>
                <textarea id="notes" name="notes" rows="2" placeholder="Alasan movement (opsional tapi direkomendasikan)"
                    class="w-full px-3 py-2 rounded-lg border border-slate-300 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500">{{ old('notes') }}</textarea>
                @error('notes')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
            </div>

            <div class="flex gap-2 pt-4 border-t border-slate-100">
                <x-button type="submit" :variant="$isOut ? 'danger' : 'primary'" icon="clipboard-check">
                    Simpan {{ $title }}
                </x-button>
                <x-button :href="route('inventory.movements.index')" tag="a" variant="outline">Batal</x-button>
            </div>
        </form>
    </x-card>
</x-app-layout>
