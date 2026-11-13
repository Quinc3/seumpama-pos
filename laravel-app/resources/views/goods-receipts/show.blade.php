<x-app-layout :title="'GR ' . $gr->receipt_number" :subtitle="'PO ' . $gr->purchaseOrder->purchase_number">
    <x-slot name="actions">
        <x-button :href="route('purchase-orders.show', $gr->purchaseOrder)" tag="a" variant="outline">Kembali ke PO</x-button>
    </x-slot>

    @if (session('success'))
    <div class="mb-4"><x-alert variant="success" icon="clipboard-check" dismissible>{{ session('success') }}</x-alert></div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-4 gap-4">
        <x-card title="Info" class="lg:col-span-1">
            <dl class="space-y-3 text-sm">
                <div>
                    <dt class="text-slate-500 text-xs">GR#</dt>
                    <dd class="font-mono font-medium text-slate-900">{{ $gr->receipt_number }}</dd>
                </div>
                <div>
                    <dt class="text-slate-500 text-xs">PO Ref</dt>
                    <dd class="font-mono text-slate-900">{{ $gr->purchaseOrder->purchase_number }}</dd>
                </div>
                <div>
                    <dt class="text-slate-500 text-xs">Supplier</dt>
                    <dd class="text-slate-900">{{ $gr->purchaseOrder->supplier?->name }}</dd>
                </div>
                <div>
                    <dt class="text-slate-500 text-xs">Diterima</dt>
                    <dd class="text-slate-900">{{ $gr->received_at->format('d M Y H:i') }}</dd>
                </div>
                <div>
                    <dt class="text-slate-500 text-xs">Oleh</dt>
                    <dd class="text-slate-900">{{ $gr->receivedBy?->name }}</dd>
                </div>
            </dl>
            @if ($gr->notes)
            <div class="mt-3 pt-3 border-t border-slate-100">
                <dt class="text-slate-500 text-xs mb-1">Catatan</dt>
                <dd class="text-sm text-slate-700">{{ $gr->notes }}</dd>
            </div>
            @endif
        </x-card>

        <x-card title="Item Diterima" :subtitle="$gr->items->count() . ' item'" class="lg:col-span-3">
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-slate-50 border-b border-slate-200">
                        <tr>
                            <th class="text-left px-3 py-2 font-medium text-slate-600">Bahan</th>
                            <th class="text-right px-3 py-2 font-medium text-slate-600">Qty Diterima</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach ($gr->items as $item)
                        <tr>
                            <td class="px-3 py-2">
                                <div class="font-medium text-slate-900">{{ $item->ingredient->name }}</div>
                                <div class="text-xs text-slate-400 font-mono">{{ $item->ingredient->sku }}</div>
                            </td>
                            <td class="px-3 py-2 text-right font-mono font-medium text-green-700">
                                +{{ number_format((float) $item->received_quantity, 4, '.', ',') }} {{ $item->unit?->symbol }}
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </x-card>
    </div>
</x-app-layout>
