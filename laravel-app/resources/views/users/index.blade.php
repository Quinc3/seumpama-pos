<x-app-layout title="Users & Roles" subtitle="Kelola akun pengguna sistem">
    <x-slot name="actions">
        <x-button :href="route('users.create')" icon="home" tag="a">Tambah User</x-button>
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
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Nama atau email..."
                    class="w-full px-3 py-2 rounded-lg border border-slate-300 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
            </div>
            <button type="submit" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-sm font-medium rounded-lg">Filter</button>
            @if (request()->has('search'))
            <a href="{{ route('users.index') }}" class="text-sm text-slate-500 hover:text-slate-700 py-2">Reset</a>
            @endif
        </form>
    </x-card>

    <x-card padding="none">
        @if ($users->isEmpty())
        <div class="p-12 text-center">
            <x-icon name="users" :size="48" class="mx-auto text-slate-300 mb-3" />
            <p class="text-slate-600 font-medium">Belum ada user</p>
        </div>
        @else
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-slate-50 border-b border-slate-200">
                    <tr>
                        <th class="text-left px-4 py-3 font-medium text-slate-600">Nama</th>
                        <th class="text-left px-4 py-3 font-medium text-slate-600">Email</th>
                        <th class="text-left px-4 py-3 font-medium text-slate-600">Role</th>
                        <th class="text-left px-4 py-3 font-medium text-slate-600">Status</th>
                        <th class="text-right px-4 py-3 font-medium text-slate-600">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach ($users as $u)
                    <tr class="hover:bg-slate-50">
                        <td class="px-4 py-3">
                            <div class="font-medium text-slate-900">
                                {{ $u->name }}
                                @if ($u->id === auth()->id())
                                <span class="text-xs text-slate-400">(Anda)</span>
                                @endif
                            </div>
                            @if ($u->phone)
                            <div class="text-xs text-slate-400 font-mono">{{ $u->phone }}</div>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-slate-600 text-xs">{{ $u->email }}</td>
                        <td class="px-4 py-3">
                            @if ($u->role())
                            <x-badge variant="info">{{ $u->role()->name }}</x-badge>
                            @else
                            <span class="text-xs text-slate-400">—</span>
                            @endif
                        </td>
                        <td class="px-4 py-3">
                            @if ($u->is_active)
                            <x-badge variant="success">Aktif</x-badge>
                            @else
                            <x-badge variant="neutral">Nonaktif</x-badge>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-right">
                            <div class="inline-flex gap-2">
                                <a href="{{ route('users.edit', $u) }}" class="text-sm text-blue-600 hover:text-blue-800 font-medium">Edit</a>
                                @if ($u->id !== auth()->id() && ! $u->hasRole('Owner'))
                                <form method="POST" action="{{ route('users.destroy', $u) }}"
                                    onsubmit="return confirm('Hapus user ini? Tindakan tidak dapat dibatalkan.');">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="text-sm text-red-600 hover:text-red-800 font-medium">Hapus</button>
                                </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @if ($users->hasPages())
        <div class="px-4 py-4 border-t border-slate-200">{{ $users->links() }}</div>
        @endif
        @endif
    </x-card>
</x-app-layout>
