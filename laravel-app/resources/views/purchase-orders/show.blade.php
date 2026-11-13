<x-app-layout :title="'PO ' . $po->purchase_number" :subtitle="$po->supplier?->name">
    <x-slot name="actions">
        @permission('inventory.manage')
        @if ($po->status === 'draft')
        <form method="POST" action="{{ route('purchase-orders.approve', $po) }}" class="inline"
            onsubmit="return confirm('Approve PO ini? Setelah approve, tidak bisa edit lagi.');">
            @csrf
            <x-button type="submit" variant="primary" icon="clipboard-check">Approve</x-button>
        </form>
        @endif
        @if ($po->canBeCancelled())
        <form method="POST" action="{{ route('purchase-orders.cancel', $po) }}" class="inline"
            onsubmit="return confirm('Batalkan PO ini?');">
            @csrf
            <x-button type="submit" variant="danger" icon="x">Batalkan</x-button>
        </form>
        @endif
        @if ($po->canReceive() && $po->hasOutstanding())
        <x-button :href="route('goods-receipts.create', $po)" tag="a" variant="primary" icon="truck">
            Terima Barang
        </x-button>
        @endif
        @endpermission
        <x-button :href="route('purchase-orders.index')" tag="a" variant="outline">Kembali</x-button>
    </x-slot>

    @if (session('success'))
    <div class="mb-4"><x-alert variant="success" icon="clipboard-check" dismissible>{{ session('success') }}</x-alert></div>
    @endif
    @if (session('error'))
    <div class="mb-4"><x-alert variant="error" icon="shield" dismissible>{{ session('error') }}</x-alert></div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-4 gap-4 mb-4">
        <x-card title="Info PO" class="lg:col-span-1">
            <dl class="space-y-3 text-sm">
                <div>
                    <dt class="text-slate-500 text-xs">PO#</dt>
                    <dd class="font-mono font-medium text-slate-900">{{ $po->purchase_number }}</dd>
                </div>
                <div>
                    <dt class="text-slate-500 text-xs">Supplier</dt>
                    <dd class="text-slate-900">{{ $po->supplier?->name }}</dd>
                </div>
                <div>
                    <dt class="text-slate-500 text-xs">Tanggal PO</dt>
                    <dd class="text-slate-900">{{ $po->purchase_date->format('d M Y') }}</dd>
                </div>
                @if ($po->expected_date)
                <div>
                    <dt class="text-slate-500 text-xs">Estimasi Datang</dt>
                    <dd class="text-slate-900">{{ $po->expected_date->format('d M Y') }}</dd>
                </div>
                @endif
                <div>
                    <dt class="text-slate-500 text-xs">Status</dt>
                    <dd>
                        @php
                        $v = match($po->status) {
                        'draft' => 'neutral',
                        'approved' => 'info',
                        'partially_received' => 'warning',
                        'received' => 'success',
                        'cancelled' => 'danger',
                        default => 'neutral',
                        };
                        @endphp
                        <x-badge :variant="$v">{{ ucfirst(str_replace('_',' ',$po->status)) }}</x-badge>
                    </dd>
                </div>
                <div>
                    <dt class="text-slate-500 text-xs">Total</dt>
                    <dd class="font-mono font-bold text-slate-900">Rp {{ number_format((float) $po->total_amount, 0, ',', '.') }}</dd>
                </div>
                @if ($po->approved_at)
                <div class="pt-3 border-t border-slate-100">
                    <dt class="text-slate-500 text-xs">Approved</dt>
                    <dd class="text-sm text-slate-900">{{ $po->approvedBy?->name }}</dd>
                    <dd class="text-xs text-slate-500">{{ $po->approved_at->format('d M Y H:i') }}</dd>
                </div>
                @endif
            </dl>
        </x-card>

        <x-card title="Item PO" :subtitle="$po->items->count() . ' item'" class="lg:col-span-3">
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-slate-50 border-b border-slate-200">
                        <tr>
                            <th class="text-left px-3 py-2 font-medium text-slate-600">Bahan</th>
                            <th class="text-right px-3 py-2 font-medium text-slate-600">Qty</th>
                            <th class="text-right px-3 py-2 font-medium text-slate-600">Received</th>
                            <th class="text-right px-3 py-2 font-medium text-slate-600">Outstanding</th>
                            <th class="text-right px-3 py-2 font-medium text-slate-600">Harga</th>
                            <th class="text-right px-3 py-2 font-medium text-slate-600">Subtotal</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach ($po->items as $item)
                        <tr>
                            <td class="px-3 py-2">
                                <div class="font-medium text-slate-900">{{ $item->ingredient->name }}</div>
                                <div class="text-xs text-slate-400 font-mono">{{ $item->ingredient->sku }}</div>
                            </td>
                            <td class="px-3 py-2 text-right font-mono text-slate-900">
                                {{ number_format((float) $item->quantity, 4, '.', ',') }} {{ $item->unit?->symbol }}
                            </td>
                            <td class="px-3 py-2 text-right font-mono text-green-700">
                                {{ number_format((float) $item->received_quantity, 4, '.', ',') }}
                            </td>
                            <td class="px-3 py-2 text-right font-mono {{ $item->isFullyReceived() ? 'text-slate-400' : 'text-amber-700 font-medium' }}">
                                {{ number_format($item->outstanding_quantity, 4, '.', ',') }}
                            </td>
                            <td class="px-3 py-2 text-right font-mono text-slate-600">
                                Rp {{ number_format((float) $item->unit_price, 0, ',', '.') }}
                            </td>
                            <td class="px-3 py-2 text-right font-mono text-slate-900">
                                Rp {{ number_format((float) $item->subtotal, 0, ',', '.') }}
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </x-card>
    </div>

    @if ($po->receipts->isNotEmpty())
    <x-card title="Riwayat Penerimaan" :subtitle="$po->receipts->count() . ' penerimaan'">
        <div class="space-y-2">
            @foreach ($po->receipts as $gr)
            <div class="flex items-center gap-3 p-3 rounded-lg border bg-slate-50 border-slate-200">
                <div class="shrink-0">
                    <x-icon name="truck" :size="20" class="text-slate-600" />
                </div>
                <div class="flex-1 min-w-0">
                    <div class="font-medium text-slate-900 font-mono text-sm">{{ $gr->receipt_number }}</div>
                    <div class="text-xs text-slate-500">
                        {{ $gr->received_at->format('d M Y H:i') }} — {{ $gr->receivedBy?->name }}
                    </div>
                </div>
                <a href="{{ route('goods-receipts.show', $gr) }}" class="text-sm text-blue-600 hover:text-blue-800 font-medium">Detail</a>
            </div>
            @endforeach
        </div>
    </x-card>
    @endif
</x-app-layout>
