<x-app-layout title="Expenses" subtitle="Pengeluaran operasional coffee shop">
    <x-slot name="actions">
        <x-button :href="route('reports.expenses')" tag="a" variant="outline" icon="bar-chart">
            Lihat Report
        </x-button>
        <x-button :href="route('expenses.create')" tag="a" variant="primary" icon="home">
            Tambah Pengeluaran
        </x-button>
    </x-slot>

    @if (session('success'))
    <div class="mb-4"><x-alert variant="success" icon="clipboard-check" dismissible>{{ session('success') }}</x-alert></div>
    @endif
    @if (session('error'))
    <div class="mb-4"><x-alert variant="error" icon="shield" dismissible>{{ session('error') }}</x-alert></div>
    @endif

    {{-- Summary --}}
    <x-card padding="sm" class="mb-4">
        <div class="flex justify-between items-center">
            <span class="text-sm text-slate-600">Total pengeluaran (filter aktif)</span>
            <span class="text-xl font-bold text-slate-900 font-mono">Rp {{ number_format((float) $summary, 0, ',', '.') }}</span>
        </div>
    </x-card>

    {{-- Filter --}}
    <x-card padding="none" class="mb-4">
        <form method="GET" class="p-4 flex flex-wrap gap-3 items-end">
            <div class="w-52">
                <label class="block text-xs font-medium text-slate-600 mb-1">Kategori</label>
                <select name="category_id" class="w-full px-3 py-2 rounded-lg border border-slate-300 text-sm bg-white">
                    <option value="">Semua</option>
                    @foreach ($categories as $cat)
                    <option value="{{ $cat->id }}" @selected(request('category_id')==$cat->id)>{{ $cat->name }}</option>
                    @endforeach
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
            @if (request()->hasAny(['category_id','from','to']))
            <a href="{{ route('expenses.index') }}" class="text-sm text-slate-500 hover:text-slate-700 py-2">Reset</a>
            @endif
        </form>
    </x-card>

    {{-- Table --}}
    <x-card padding="none">
        @if ($expenses->isEmpty())
        <div class="p-12 text-center">
            <x-icon name="wallet" :size="48" class="mx-auto text-slate-300 mb-3" />
            <p class="text-slate-600 font-medium">Belum ada pengeluaran</p>
        </div>
        @else
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-slate-50 border-b border-slate-200">
                    <tr>
                        <th class="text-left px-3 py-3 font-medium text-slate-600">Tanggal</th>
                        <th class="text-left px-3 py-3 font-medium text-slate-600">Kategori</th>
                        <th class="text-left px-3 py-3 font-medium text-slate-600">Deskripsi</th>
                        <th class="text-right px-3 py-3 font-medium text-slate-600">Nominal</th>
                        <th class="text-left px-3 py-3 font-medium text-slate-600">Metode</th>
                        <th class="text-left px-3 py-3 font-medium text-slate-600">Dicatat</th>
                        <th class="text-right px-3 py-3 font-medium text-slate-600">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach ($expenses as $exp)
                    <tr class="hover:bg-slate-50">
                        <td class="px-3 py-2 text-xs text-slate-600">{{ $exp->expense_date->format('d M Y') }}</td>
                        <td class="px-3 py-2"><x-badge variant="info">{{ $exp->category?->name }}</x-badge></td>
                        <td class="px-3 py-2 text-slate-900">{{ $exp->description }}</td>
                        <td class="px-3 py-2 text-right font-mono font-medium text-slate-900">Rp {{ number_format((float) $exp->amount, 0, ',', '.') }}</td>
                        <td class="px-3 py-2 text-xs text-slate-600">{{ $exp->paymentMethod?->name ?? '—' }}</td>
                        <td class="px-3 py-2 text-xs text-slate-600">{{ $exp->createdBy?->name }}</td>
                        <td class="px-3 py-2 text-right">
                            <div class="inline-flex gap-2">
                                <a href="{{ route('expenses.edit', $exp) }}" class="text-sm text-blue-600 hover:text-blue-800 font-medium">Edit</a>
                                <form method="POST" action="{{ route('expenses.destroy', $exp) }}"
                                    onsubmit="return confirm('Hapus pengeluaran ini?');">
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
        @if ($expenses->hasPages())
        <div class="px-4 py-3 border-t border-slate-200">{{ $expenses->links() }}</div>
        @endif
        @endif
    </x-card>
</x-app-layout>
