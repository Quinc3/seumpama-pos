<x-app-layout title="Stock Opname" subtitle="Rekonsiliasi stok fisik vs sistem">
    <x-slot name="actions">
        @permission('inventory.manage')
        <x-button :href="route('stock-opnames.create')" tag="a" variant="primary" icon="clipboard-check">
            Buat Opname
        </x-button>
        @endpermission
    </x-slot>

    @if (session('success'))
    <div class="mb-4"><x-alert variant="success" icon="clipboard-check" dismissible>{{ session('success') }}</x-alert></div>
    @endif
    @if (session('error'))
    <div class="mb-4"><x-alert variant="error" icon="shield" dismissible>{{ session('error') }}</x-alert></div>
    @endif

    <x-card padding="none" class="mb-4">
        <form method="GET" class="p-4 flex flex-wrap gap-3 items-end">
            <div class="w-40">
                <label class="block text-xs font-medium text-slate-600 mb-1">Status</label>
                <select name="status" class="w-full px-3 py-2 rounded-lg border border-slate-300 text-sm bg-white">
                    <option value="">Semua</option>
                    <option value="draft" @selected(request('status')==='draft' )>Draft</option>
                    <option value="approved" @selected(request('status')==='approved' )>Approved</option>
                    <option value="cancelled" @selected(request('status')==='cancelled' )>Cancelled</option>
                </select>
            </div>
            <div class="w-36">
                <label class="block text-xs font-medium text-slate-600 mb-1">Dari</label>
                <input type="date" name="from" value="{{ request('from') }}" class="w-full px-3 py-2 rounded-lg border border-slate-300 text-sm">
            </div>
            <div class="w-36">
                <label class="block text-xs font-medium text-slate-600 mb-1">Sampai</label>
                <input type="date" name="to" value="{{ request('to') }}" class="w-full px-3 py-2 rounded-lg border border-slate-300 text-sm">
            </div>
            <button type="submit" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-sm font-medium rounded-lg">Filter</button>
            @if (request()->hasAny(['status', 'from', 'to']))
            <a href="{{ route('stock-opnames.index') }}" class="text-sm text-slate-500 hover:text-slate-700 py-2">Reset</a>
            @endif
        </form>
    </x-card>

    <x-card padding="none">
        @if ($opnames->isEmpty())
        <div class="p-12 text-center">
            <x-icon name="clipboard-check" :size="48" class="mx-auto text-slate-300 mb-3" />
            <p class="text-slate-600 font-medium">Belum ada stock opname</p>
            <p class="text-sm text-slate-400 mt-1">Buat opname untuk merekonsiliasi stok fisik.</p>
        </div>
        @else
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-slate-50 border-b border-slate-200">
                    <tr>
                        <th class="text-left px-4 py-3 font-medium text-slate-600">Ref</th>
                        <th class="text-left px-4 py-3 font-medium text-slate-600">Tanggal</th>
                        <th class="text-center px-4 py-3 font-medium text-slate-600">Items</th>
                        <th class="text-left px-4 py-3 font-medium text-slate-600">Status</th>
                        <th class="text-left px-4 py-3 font-medium text-slate-600">Dibuat</th>
                        <th class="text-right px-4 py-3 font-medium text-slate-600">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach ($opnames as $op)
                    <tr class="hover:bg-slate-50">
                        <td class="px-4 py-3 font-mono text-xs text-slate-900 font-medium">{{ $op->reference_number }}</td>
                        <td class="px-4 py-3 text-slate-600 text-xs">{{ $op->opname_date->format('d M Y') }}</td>
                        <td class="px-4 py-3 text-center font-mono text-slate-700">{{ $op->items_count }}</td>
                        <td class="px-4 py-3">
                            @if ($op->status === 'draft')
                            <x-badge variant="warning">Draft</x-badge>
                            @elseif ($op->status === 'approved')
                            <x-badge variant="success">Approved</x-badge>
                            @else
                            <x-badge variant="neutral">Cancelled</x-badge>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-slate-600 text-xs">{{ $op->createdBy?->name }}</td>
                        <td class="px-4 py-3 text-right">
                            <a href="{{ route('stock-opnames.show', $op) }}"
                                class="text-sm text-blue-600 hover:text-blue-800 font-medium">Detail</a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @if ($opnames->hasPages())
        <div class="px-4 py-4 border-t border-slate-200">{{ $opnames->links() }}</div>
        @endif
        @endif
    </x-card>
</x-app-layout>
