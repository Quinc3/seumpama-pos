<x-app-layout
    title="Units"
    subtitle="Satuan pengukuran bahan baku">
    <x-slot name="actions">
        <x-button :href="route('units.create')" icon="home" tag="a">
            Tambah Unit
        </x-button>
    </x-slot>

    @if (session('success'))
    <div class="mb-4">
        <x-alert variant="success" icon="clipboard-check" dismissible>{{ session('success') }}</x-alert>
    </div>
    @endif

    @if (session('error'))
    <div class="mb-4">
        <x-alert variant="error" icon="shield" dismissible>{{ session('error') }}</x-alert>
    </div>
    @endif

    <x-card padding="none">
        @if ($units->isEmpty())
        <div class="p-12 text-center">
            <x-icon name="layers" :size="48" class="mx-auto text-slate-300 mb-3" />
            <p class="text-slate-600 font-medium">Belum ada unit</p>
            <p class="text-sm text-slate-400 mt-1">Tambahkan unit pertama untuk memulai.</p>
            <div class="mt-4">
                <x-button :href="route('units.create')" tag="a" variant="primary" icon="home">
                    Tambah Unit
                </x-button>
            </div>
        </div>
        @else
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-slate-50 border-b border-slate-200">
                    <tr>
                        <th class="text-left px-6 py-3 font-medium text-slate-600">Nama</th>
                        <th class="text-left px-6 py-3 font-medium text-slate-600">Simbol</th>
                        <th class="text-left px-6 py-3 font-medium text-slate-600">Tipe</th>
                        <th class="text-left px-6 py-3 font-medium text-slate-600">Status</th>
                        <th class="text-right px-6 py-3 font-medium text-slate-600">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach ($units as $unit)
                    <tr class="hover:bg-slate-50">
                        <td class="px-6 py-3 text-slate-900 font-medium">{{ $unit->name }}</td>
                        <td class="px-6 py-3">
                            <span class="inline-flex px-2 py-0.5 rounded bg-slate-100 text-slate-700 font-mono text-xs">
                                {{ $unit->symbol }}
                            </span>
                        </td>
                        <td class="px-6 py-3 text-slate-600 capitalize">{{ $unit->unit_type }}</td>
                        <td class="px-6 py-3">
                            @if ($unit->is_active)
                            <x-badge variant="success">Aktif</x-badge>
                            @else
                            <x-badge variant="neutral">Nonaktif</x-badge>
                            @endif
                        </td>
                        <td class="px-6 py-3 text-right">
                            <div class="inline-flex gap-2">
                                <a href="{{ route('units.edit', $unit) }}"
                                    class="text-sm text-blue-600 hover:text-blue-800 font-medium">Edit</a>
                                <form method="POST" action="{{ route('units.destroy', $unit) }}"
                                    onsubmit="return confirm('Hapus unit {{ $unit->name }}? Tindakan ini tidak dapat dibatalkan.');">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="text-sm text-red-600 hover:text-red-800 font-medium">
                                        Hapus
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        @if ($units->hasPages())
        <div class="px-6 py-4 border-t border-slate-200">
            {{ $units->links() }}
        </div>
        @endif
        @endif
    </x-card>
</x-app-layout>
