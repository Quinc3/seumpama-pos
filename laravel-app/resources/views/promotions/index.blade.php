<x-app-layout title="Promotions" subtitle="Diskon dan promosi produk">
    <x-slot name="actions">
        <x-button :href="route('promotions.create')" tag="a" variant="primary" icon="percent">
            Tambah Promosi
        </x-button>
    </x-slot>

    @if (session('success'))
    <div class="mb-4"><x-alert variant="success" icon="clipboard-check" dismissible>{{ session('success') }}</x-alert></div>
    @endif

    <x-card padding="none" class="mb-4">
        <form method="GET" class="p-4 flex flex-wrap gap-3 items-end">
            <div class="w-44">
                <label class="block text-xs font-medium text-slate-600 mb-1">Status</label>
                <select name="status" class="w-full px-3 py-2 rounded-lg border border-slate-300 text-sm bg-white">
                    <option value="">Semua</option>
                    <option value="active" @selected(request('status')==='active' )>Aktif Sekarang</option>
                    <option value="inactive" @selected(request('status')==='inactive' )>Nonaktif</option>
                    <option value="expired" @selected(request('status')==='expired' )>Expired</option>
                </select>
            </div>
            <button type="submit" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-sm font-medium rounded-lg">Filter</button>
            @if (request()->has('status'))
            <a href="{{ route('promotions.index') }}" class="text-sm text-slate-500 hover:text-slate-700 py-2">Reset</a>
            @endif
        </form>
    </x-card>

    <x-card padding="none">
        @if ($promotions->isEmpty())
        <div class="p-12 text-center">
            <x-icon name="percent" :size="48" class="mx-auto text-slate-300 mb-3" />
            <p class="text-slate-600 font-medium">Belum ada promosi</p>
            <p class="text-sm text-slate-400 mt-1">Tambahkan promosi untuk diskon di POS.</p>
            <div class="mt-4">
                <x-button :href="route('promotions.create')" tag="a" icon="percent">Tambah Promosi</x-button>
            </div>
        </div>
        @else
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-slate-50 border-b border-slate-200">
                    <tr>
                        <th class="text-left px-3 py-3 font-medium text-slate-600">Nama</th>
                        <th class="text-left px-3 py-3 font-medium text-slate-600">Diskon</th>
                        <th class="text-left px-3 py-3 font-medium text-slate-600">Periode</th>
                        <th class="text-center px-3 py-3 font-medium text-slate-600">Produk</th>
                        <th class="text-left px-3 py-3 font-medium text-slate-600">Status</th>
                        <th class="text-right px-3 py-3 font-medium text-slate-600">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach ($promotions as $p)
                    <tr class="hover:bg-slate-50">
                        <td class="px-3 py-2">
                            <div class="font-medium text-slate-900">{{ $p->name }}</div>
                            @if ($p->code)
                            <div class="text-xs text-slate-400 font-mono">{{ $p->code }}</div>
                            @endif
                        </td>
                        <td class="px-3 py-2">
                            @if ($p->discount_type === 'percentage')
                            <span class="font-mono font-medium text-slate-900">{{ number_format((float) $p->discount_value, 2) }}%</span>
                            @if ($p->maximum_discount)
                            <div class="text-xs text-slate-500">Max Rp {{ number_format((float) $p->maximum_discount, 0, ',', '.') }}</div>
                            @endif
                            @else
                            <span class="font-mono font-medium text-slate-900">Rp {{ number_format((float) $p->discount_value, 0, ',', '.') }}</span>
                            @endif
                            @if ($p->minimum_purchase)
                            <div class="text-xs text-slate-500">Min Rp {{ number_format((float) $p->minimum_purchase, 0, ',', '.') }}</div>
                            @endif
                        </td>
                        <td class="px-3 py-2 text-xs text-slate-600">
                            {{ $p->start_at->format('d M Y H:i') }}<br>
                            — {{ $p->end_at->format('d M Y H:i') }}
                        </td>
                        <td class="px-3 py-2 text-center font-mono text-slate-700">
                            {{ $p->products_count === 0 ? 'Semua' : $p->products_count }}
                        </td>
                        <td class="px-3 py-2">
                            @if (! $p->is_active)
                            <x-badge variant="neutral">Nonaktif</x-badge>
                            @elseif ($p->isCurrentlyActive())
                            <x-badge variant="success">Aktif</x-badge>
                            @elseif ($p->end_at < now())
                                <x-badge variant="danger">Expired</x-badge>
                                @else
                                <x-badge variant="warning">Terjadwal</x-badge>
                                @endif
                        </td>
                        <td class="px-3 py-2 text-right">
                            <div class="inline-flex gap-2">
                                <a href="{{ route('promotions.edit', $p) }}" class="text-sm text-blue-600 hover:text-blue-800 font-medium">Edit</a>
                                <form method="POST" action="{{ route('promotions.destroy', $p) }}"
                                    onsubmit="return confirm('Hapus promosi ini? Tindakan tidak dapat dibatalkan.');">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="text-sm text-red-600 hover:text-red-800 font-medium">Hapus</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @if ($promotions->hasPages())
        <div class="px-4 py-3 border-t border-slate-200">{{ $promotions->links() }}</div>
        @endif
        @endif
    </x-card>
</x-app-layout>
