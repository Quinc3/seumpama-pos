<x-app-layout title="Training Runs" subtitle="Riwayat training model ANN">
    <x-slot name="actions">
        <x-button :href="route('ann.models.index')" tag="a" variant="outline" icon="brain">
            ANN Models
        </x-button>
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
                    @foreach (['queued'=>'Queued','running'=>'Running','completed'=>'Completed','failed'=>'Failed'] as $v=>$l)
                    <option value="{{ $v }}" @selected(request('status')===$v)>{{ $l }}</option>
                    @endforeach
                </select>
            </div>
            <div class="w-56">
                <label class="block text-xs font-medium text-slate-600 mb-1">Dataset</label>
                <select name="dataset_version_id" class="w-full px-3 py-2 rounded-lg border border-slate-300 text-sm bg-white">
                    <option value="">Semua</option>
                    @foreach ($datasets as $d)
                    <option value="{{ $d->id }}" @selected(request('dataset_version_id')==$d->id)>{{ $d->version }}</option>
                    @endforeach
                </select>
            </div>
            <button type="submit" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-sm font-medium rounded-lg">Filter</button>
            @if (request()->hasAny(['status','dataset_version_id']))
            <a href="{{ route('ann.training.index') }}" class="text-sm text-slate-500 hover:text-slate-700 py-2">Reset</a>
            @endif
        </form>
    </x-card>

    <x-card padding="none">
        @if ($runs->isEmpty())
        <div class="p-12 text-center">
            <x-icon name="brain" :size="48" class="mx-auto text-slate-300 mb-3" />
            <p class="text-slate-600 font-medium">Belum ada training run</p>
            <p class="text-sm text-slate-400 mt-1">Mulai training dari halaman dataset.</p>
            <div class="mt-4">
                <x-button :href="route('datasets.index')" tag="a" icon="database">Ke Dataset</x-button>
            </div>
        </div>
        @else
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-slate-50 border-b border-slate-200">
                    <tr>
                        <th class="text-left px-3 py-3 font-medium text-slate-600">Training ID</th>
                        <th class="text-left px-3 py-3 font-medium text-slate-600">Dataset</th>
                        <th class="text-left px-3 py-3 font-medium text-slate-600">Status</th>
                        <th class="text-left px-3 py-3 font-medium text-slate-600">Started</th>
                        <th class="text-left px-3 py-3 font-medium text-slate-600">Completed</th>
                        <th class="text-right px-3 py-3 font-medium text-slate-600">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach ($runs as $r)
                    <tr class="hover:bg-slate-50">
                        <td class="px-3 py-2 font-mono text-xs font-medium text-slate-900">{{ $r->training_id }}</td>
                        <td class="px-3 py-2 text-xs text-slate-600 font-mono">{{ $r->datasetVersion?->version }}</td>
                        <td class="px-3 py-2">
                            @php
                            $variant = match($r->training_status) {
                            'completed' => 'success',
                            'running' => 'info',
                            'queued' => 'warning',
                            'failed' => 'danger',
                            default => 'neutral',
                            };
                            @endphp
                            <x-badge :variant="$variant">{{ ucfirst($r->training_status) }}</x-badge>
                        </td>
                        <td class="px-3 py-2 text-xs text-slate-600">{{ $r->training_started_at?->format('d M Y H:i') ?? '—' }}</td>
                        <td class="px-3 py-2 text-xs text-slate-600">{{ $r->training_completed_at?->format('d M Y H:i') ?? '—' }}</td>
                        <td class="px-3 py-2 text-right">
                            <a href="{{ route('ann.training.show', $r) }}" class="text-sm text-blue-600 hover:text-blue-800 font-medium">Detail</a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @if ($runs->hasPages())
        <div class="px-4 py-3 border-t border-slate-200">{{ $runs->links() }}</div>
        @endif
        @endif
    </x-card>
</x-app-layout>
