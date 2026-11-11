<x-app-layout title="Suppliers" subtitle="Data pemasok bahan baku">
    <x-slot name="actions">
        @permission('inventory.manage')
        <x-button :href="route('suppliers.create')" icon="home" tag="a">Tambah Supplier</x-button>
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
            <div class="flex-1 min-w-[200px]">
                <label class="block text-xs font-medium text-slate-600 mb-1">Cari</label>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Nama, kontak, atau telepon..."
                    class="w-full px-3 py-2 rounded-lg border border-slate-300 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
            </div>
            <button type="submit" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-sm font-medium rounded-lg">Filter</button>
            @if (request()->has('search'))
            <a href="{{ route('suppliers.index') }}" class="text-sm text-slate-500 hover:text-slate-700 py-2">Reset</a>
            @endif
        </form>
    </x-card>

    <x-card padding="none">
        @if ($suppliers->isEmpty())
        <div class="p-12 text-center">
            <x-icon name="truck" :size="48" class="mx-auto text-slate-300 mb-3" />
            <p class="text-slate-600 font-medium">Belum ada supplier</p>
            <p class="text-sm text-slate-400 mt-1">Tambahkan supplier pertama.</p>
            @permission('inventory.manage')
            <div class="mt-4"><x-button :href="route('suppliers.create')" tag="a" icon="home">Tambah Supplier</x-button></div>
            @endpermission
        </div>
        @else
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-slate-50 border-b border-slate-200">
                    <tr>
                        <th class="text-left px-4 py-3 font-medium text-slate-600">Nama</th>
                        <th class="text-left px-4 py-3 font-medium text-slate-600">Kontak</th>
                        <th class="text-left px-4 py-3 font-medium text-slate-600">Telepon</th>
                        <th class="text-left px-4 py-3 font-medium text-slate-600">Email</th>
                        <th class="text-left px-4 py-3 font-medium text-slate-600">Status</th>
                        <th class="text-right px-4 py-3 font-medium text-slate-600">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach ($suppliers as $s)
                    <tr class="hover:bg-slate-50">
                        <td class="px-4 py-3 font-medium text-slate-900">{{ $s->name }}</td>
                        <td class="px-4 py-3 text-slate-600">{{ $s->contact_person ?: '—' }}</td>
                        <td class="px-4 py-3 text-slate-600 font-mono text-xs">{{ $s->phone ?: '—' }}</td>
                        <td class="px-4 py-3 text-slate-600 text-xs">{{ $s->email ?: '—' }}</td>
                        <td class="px-4 py-3">
                            @if ($s->is_active)
                            <x-badge variant="success">Aktif</x-badge>
                            @else
                            <x-badge variant="neutral">Nonaktif</x-badge>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-right">
                            @permission('inventory.manage')
                            <div class="inline-flex gap-2">
                                <a href="{{ route('suppliers.edit', $s) }}" class="text-sm text-blue-600 hover:text-blue-800 font-medium">Edit</a>
                                <form method="POST" action="{{ route('suppliers.destroy', $s) }}"
                                    onsubmit="return confirm('Hapus supplier ini? Tindakan tidak dapat dibatalkan.');">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="text-sm text-red-600 hover:text-red-800 font-medium">Hapus</button>
                                </form>
                            </div>
                            @else
                            <span class="text-sm text-slate-400">—</span>
                            @endpermission
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @if ($suppliers->hasPages())
        <div class="px-4 py-4 border-t border-slate-200">{{ $suppliers->links() }}</div>
        @endif
        @endif
    </x-card>
</x-app-layout>
