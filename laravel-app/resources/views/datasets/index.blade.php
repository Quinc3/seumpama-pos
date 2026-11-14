<x-app-layout title="Datasets" subtitle="Versi dataset untuk training ANN">
    <x-slot name="actions">
        <x-button :href="route('datasets.create')" tag="a" variant="primary" icon="database">
            Buat Dataset Baru
        </x-button>
    </x-slot>

    @if (session('success'))
    <div class="mb-4"><x-alert variant="success" icon="clipboard-check" dismissible>{{ session('success') }}</x-alert></div>
    @endif
    @if (session('error'))
    <div class="mb-4"><x-alert variant="error" icon="shield" dismissible>{{ session('error') }}</x-alert></div>
    @endif

    <x-card padding="none">
        @if ($versions->isEmpty())
        <div class="p-12 text-center">
            <x-icon name="database" :size="48" class="mx-auto text-slate-300 mb-3" />
            <p class="text-slate-600 font-medium">Belum ada dataset</p>
            <p class="text-sm text-slate-400 mt-1">Buat dataset pertama untuk training ANN.</p>
            <div class="mt-4">
                <x-button :href="route('datasets.create')" tag="a" icon="database">Buat Dataset Baru</x-button>
            </div>
        </div>
        @else
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-slate-50 border-b border-slate-200">
                    <tr>
                        <th class="text-left px-3 py-3 font-medium text-slate-600">Version</th>
                        <th class="text-left px-3 py-3 font-medium text-slate-600">Name</th>
                        <th class="text-left px-3 py-3 font-medium text-slate-600">Period</th>
                        <th class="text-right px-3 py-3 font-medium text-slate-600">Records</th>
                        <th class="text-left px-3 py-3 font-medium text-slate-600">Status</th>
                        <th class="text-left px-3 py-3 font-medium text-slate-600">Dibuat</th>
                        <th class="text-right px-3 py-3 font-medium text-slate-600">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach ($versions as $v)
                    <tr class="hover:bg-slate-50">
                        <td class="px-3 py-2 font-mono text-xs font-medium text-slate-900">{{ $v->version }}</td>
                        <td class="px-3 py-2 text-slate-700">{{ $v->dataset_name }}</td>
                        <td class="px-3 py-2 text-xs text-slate-600 font-mono">
                            {{ $v->start_date->format('d/m/Y') }} — {{ $v->end_date->format('d/m/Y') }}
                        </td>
                        <td class="px-3 py-2 text-right font-mono text-slate-900">{{ number_format($v->total_records) }}</td>
                        <td class="px-3 py-2">
                            @php
                            $variant = match($v->status) {
                            'draft' => 'neutral',
                            'validated' => 'info',
                            'approved' => 'success',
                            'archived' => 'warning',
                            default => 'neutral',
                            };
                            @endphp
                            <x-badge :variant="$variant">{{ ucfirst($v->status) }}</x-badge>
                        </td>
                        <td class="px-3 py-2 text-xs text-slate-600">
                            {{ $v->created_at->format('d M Y H:i') }}
                            <div class="text-slate-400">{{ $v->createdBy?->name }}</div>
                        </td>
                        <td class="px-3 py-2 text-right">
                            <a href="{{ route('datasets.show', $v) }}" class="text-sm text-blue-600 hover:text-blue-800 font-medium">Detail</a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @if ($versions->hasPages())
        <div class="px-4 py-3 border-t border-slate-200">{{ $versions->links() }}</div>
        @endif
        @endif
    </x-card>
</x-app-layout>
