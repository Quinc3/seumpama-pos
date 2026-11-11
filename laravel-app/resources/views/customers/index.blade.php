<x-app-layout title="Customers" subtitle="Data pelanggan">
    <x-slot name="actions">
        <x-button :href="route('customers.create')" icon="home" tag="a">Tambah Customer</x-button>
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
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Nama, telepon, atau email..."
                    class="w-full px-3 py-2 rounded-lg border border-slate-300 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
            </div>
            <button type="submit" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-sm font-medium rounded-lg">Filter</button>
            @if (request()->has('search'))
            <a href="{{ route('customers.index') }}" class="text-sm text-slate-500 hover:text-slate-700 py-2">Reset</a>
            @endif
        </form>
    </x-card>

    <x-card padding="none">
        @if ($customers->isEmpty())
        <div class="p-12 text-center">
            <x-icon name="users" :size="48" class="mx-auto text-slate-300 mb-3" />
            <p class="text-slate-600 font-medium">Belum ada customer</p>
            <p class="text-sm text-slate-400 mt-1">Tambahkan pelanggan pertama.</p>
            <div class="mt-4"><x-button :href="route('customers.create')" tag="a" icon="home">Tambah Customer</x-button></div>
        </div>
        @else
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-slate-50 border-b border-slate-200">
                    <tr>
                        <th class="text-left px-4 py-3 font-medium text-slate-600">Nama</th>
                        <th class="text-left px-4 py-3 font-medium text-slate-600">Telepon</th>
                        <th class="text-left px-4 py-3 font-medium text-slate-600">Email</th>
                        <th class="text-left px-4 py-3 font-medium text-slate-600">Catatan</th>
                        <th class="text-right px-4 py-3 font-medium text-slate-600">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach ($customers as $c)
                    <tr class="hover:bg-slate-50">
                        <td class="px-4 py-3 font-medium text-slate-900">{{ $c->name }}</td>
                        <td class="px-4 py-3 text-slate-600 font-mono text-xs">{{ $c->phone ?: '—' }}</td>
                        <td class="px-4 py-3 text-slate-600 text-xs">{{ $c->email ?: '—' }}</td>
                        <td class="px-4 py-3 text-slate-500 text-xs max-w-xs truncate">{{ $c->notes ?: '—' }}</td>
                        <td class="px-4 py-3 text-right">
                            <div class="inline-flex gap-2">
                                <a href="{{ route('customers.edit', $c) }}" class="text-sm text-blue-600 hover:text-blue-800 font-medium">Edit</a>
                                <form method="POST" action="{{ route('customers.destroy', $c) }}"
                                    onsubmit="return confirm('Hapus customer ini? Tindakan tidak dapat dibatalkan.');">
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
        @if ($customers->hasPages())
        <div class="px-4 py-4 border-t border-slate-200">{{ $customers->links() }}</div>
        @endif
        @endif
    </x-card>
</x-app-layout>
