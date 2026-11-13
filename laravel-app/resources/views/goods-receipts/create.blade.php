<x-app-layout title="Terima Barang" :subtitle="'PO ' . $po->purchase_number . ' — ' . $po->supplier?->name">
    <x-slot name="actions">
        <x-button :href="route('purchase-orders.show', $po)" tag="a" variant="outline">Kembali</x-button>
    </x-slot>

    <x-card class="max-w-4xl">
        <form method="POST" action="{{ route('goods-receipts.store', $po) }}" class="space-y-6">
            @csrf

            <div class="p-4 bg-blue-50 border border-blue-200 rounded-lg text-sm text-blue-800">
                <strong>PO {{ $po->purchase_number }}</strong> — Supplier: {{ $po->supplier?->name }}
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <x-input name="received_at" type="datetime-local" label="Waktu Penerimaan" required
                    :value="old('received_at', now()->format('Y-m-d\TH:i'))" />
                <div></div>
            </div>

            <div>
                <label for="notes" class="block text-sm font-medium text-slate-700 mb-1">Catatan</label>
                <textarea id="notes" name="notes" rows="2"
                    class="w-full px-3 py-2 rounded-lg border border-slate-300 text-sm">{{ old('notes') }}</textarea>
            </div>

            <div>
                <h3 class="text-sm font-semibold text-slate-900 mb-3">Item Diterima</h3>

                @error('items')
                <div class="mb-3 p-3 bg-red-50 border border-red-200 rounded text-sm text-red-700">{{ $message }}</div>
                @enderror

                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead class="bg-slate-50 border-b border-slate-200">
                            <tr>
                                <th class="text-left px-3 py-2 font-medium text-slate-600">Bahan</th>
                                <th class="text-right px-3 py-2 font-medium text-slate-600">Outstanding</th>
                                <th class="text-right px-3 py-2 font-medium text-slate-600 w-40">Qty Diterima</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @foreach ($receivableItems as $i => $item)
                            <tr>
                                <td class="px-3 py-3">
                                    <div class="font-medium text-slate-900">{{ $item->ingredient->name }}</div>
                                    <div class="text-xs text-slate-400 font-mono">{{ $item->ingredient->sku }}</div>
                                    <input type="hidden" name="items[{{ $i }}][purchase_order_item_id]" value="{{ $item->id }}">
                                </td>
                                <td class="px-3 py-3 text-right font-mono font-medium text-amber-700">
                                    {{ number_format($item->outstanding_quantity, 4, '.', ',') }} {{ $item->unit?->symbol }}
                                </td>
                                <td class="px-3 py-3 text-right">
                                    <input type="number" step="0.0001" min="0" max="{{ $item->outstanding_quantity }}"
                                        name="items[{{ $i }}][received_quantity]"
                                        value="{{ old("items.{$i}.received_quantity", $item->outstanding_quantity) }}"
                                        class="w-full px-3 py-2 rounded-lg border border-slate-300 text-sm text-right focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                    <p class="mt-1 text-xs text-slate-500 text-right">Maks: {{ number_format($item->outstanding_quantity, 4, '.', ',') }} {{ $item->unit?->symbol }}</p>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <p class="mt-2 text-xs text-slate-500">
                    Default quantity = outstanding (terima penuh). Ubah jika partial receive.
                </p>
            </div>

            <div class="flex gap-2 pt-4 border-t border-slate-100">
                <x-button type="submit" icon="truck">Simpan Penerimaan</x-button>
                <x-button :href="route('purchase-orders.show', $po)" tag="a" variant="outline">Batal</x-button>
            </div>
        </form>
    </x-card>
</x-app-layout>
