<x-app-layout :title="$annModel->model_version" :subtitle="$annModel->model_name">
    <x-slot name="actions">
        @if ($annModel->status === 'candidate')
        <form method="POST" action="{{ route('ann.models.activate', $annModel) }}" class="inline"
            onsubmit="return confirm('Aktifkan model ini? Model aktif lain untuk ingredient yang sama akan diarsipkan.');">
            @csrf
            <x-button type="submit" variant="primary" icon="clipboard-check">Aktifkan</x-button>
        </form>
        @elseif ($annModel->status === 'active')
        <form method="POST" action="{{ route('ann.models.archive', $annModel) }}" class="inline"
            onsubmit="return confirm('Arsipkan model ini?');">
            @csrf
            <x-button type="submit" variant="outline" icon="package">Arsipkan</x-button>
        </form>
        @endif
        <x-button :href="route('ann.models.index')" tag="a" variant="outline">Kembali</x-button>
    </x-slot>

    @if (session('success'))
    <div class="mb-4"><x-alert variant="success" icon="clipboard-check" dismissible>{{ session('success') }}</x-alert></div>
    @endif
    @if (session('error'))
    <div class="mb-4"><x-alert variant="error" icon="shield" dismissible>{{ session('error') }}</x-alert></div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-4 mb-4">
        <x-card title="Info Model" class="lg:col-span-1">
            <dl class="space-y-3 text-sm">
                <div>
                    <dt class="text-slate-500 text-xs mb-1">Model Version</dt>
                    <dd class="font-mono font-medium text-slate-900">{{ $annModel->model_version }}</dd>
                </div>
                <div>
                    <dt class="text-slate-500 text-xs mb-1">Algorithm</dt>
                    <dd class="text-slate-900">{{ $annModel->algorithm }}</dd>
                </div>
                <div>
                    <dt class="text-slate-500 text-xs mb-1">Status</dt>
                    <dd>
                        @php
                        $variant = match($annModel->status) {
                        'active' => 'success',
                        'candidate' => 'info',
                        'archived' => 'neutral',
                        default => 'neutral',
                        };
                        @endphp
                        <x-badge :variant="$variant">{{ ucfirst($annModel->status) }}</x-badge>
                    </dd>
                </div>
                <div>
                    <dt class="text-slate-500 text-xs mb-1">Dataset</dt>
                    <dd class="font-mono text-xs text-slate-900">
                        <a href="{{ route('datasets.show', $annModel->dataset_version_id) }}" class="text-blue-600 hover:underline">
                            {{ $annModel->datasetVersion?->version }}
                        </a>
                    </dd>
                </div>
                <div>
                    <dt class="text-slate-500 text-xs mb-1">Dibuat</dt>
                    <dd class="text-slate-900">{{ $annModel->created_at->format('d M Y H:i') }}</dd>
                    <dd class="text-xs text-slate-500">{{ $annModel->createdBy?->name }}</dd>
                </div>
            </dl>
        </x-card>

        <x-card title="Arsitektur" class="lg:col-span-2">
            <dl class="grid grid-cols-2 gap-4 text-sm">
                <div>
                    <dt class="text-slate-500 text-xs">Input Features</dt>
                    <dd class="font-mono text-slate-900">{{ $annModel->architecture['input_features'] ?? '—' }}</dd>
                </div>
                <div>
                    <dt class="text-slate-500 text-xs">Hidden Layers</dt>
                    <dd class="font-mono text-slate-900">{{ implode(' → ', $annModel->architecture['hidden_layers'] ?? []) }}</dd>
                </div>
                <div>
                    <dt class="text-slate-500 text-xs">Output</dt>
                    <dd class="font-mono text-slate-900">{{ $annModel->architecture['output'] ?? '—' }}</dd>
                </div>
                <div>
                    <dt class="text-slate-500 text-xs">Activation</dt>
                    <dd class="font-mono text-slate-900">{{ $annModel->architecture['activation'] ?? '—' }} / {{ $annModel->architecture['output_activation'] ?? '—' }}</dd>
                </div>
            </dl>

            <div class="mt-4 pt-4 border-t border-slate-100">
                <dt class="text-slate-500 text-xs mb-2">Feature Schema</dt>
                <div class="flex flex-wrap gap-1.5">
                    @foreach ($annModel->feature_schema['features'] ?? [] as $f)
                    <span class="px-2 py-0.5 rounded bg-slate-100 text-slate-700 text-xs font-mono">{{ $f }}</span>
                    @endforeach
                </div>
            </div>
        </x-card>
    </div>

    <x-card title="Ingredients & Metrics" :subtitle="$annModel->ingredients->count() . ' ingredient'">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-slate-50 border-b border-slate-200">
                    <tr>
                        <th class="text-left px-3 py-2 font-medium text-slate-600">Ingredient</th>
                        <th class="text-right px-3 py-2 font-medium text-slate-600">MAE</th>
                        <th class="text-right px-3 py-2 font-medium text-slate-600">RMSE</th>
                        <th class="text-right px-3 py-2 font-medium text-slate-600">Baseline MAE</th>
                        <th class="text-right px-3 py-2 font-medium text-slate-600">Baseline RMSE</th>
                        <th class="text-center px-3 py-2 font-medium text-slate-600">Samples</th>
                        <th class="text-left px-3 py-2 font-medium text-slate-600">Active Model</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach ($annModel->ingredients as $mi)
                    @php $em = $mi->evaluation_metrics ?? []; @endphp
                    <tr>
                        <td class="px-3 py-2">
                            <div class="font-medium text-slate-900">{{ $mi->ingredient?->name }}</div>
                            <div class="text-xs text-slate-400 font-mono">{{ $mi->ingredient?->sku }}</div>
                        </td>
                        <td class="px-3 py-2 text-right font-mono text-slate-900 font-medium">{{ isset($em['mae']) ? number_format((float) $em['mae'], 4) : '—' }}</td>
                        <td class="px-3 py-2 text-right font-mono text-slate-700">{{ isset($em['rmse']) ? number_format((float) $em['rmse'], 4) : '—' }}</td>
                        <td class="px-3 py-2 text-right font-mono text-slate-500">{{ isset($em['baseline_mae']) ? number_format((float) $em['baseline_mae'], 4) : '—' }}</td>
                        <td class="px-3 py-2 text-right font-mono text-slate-500">{{ isset($em['baseline_rmse']) ? number_format((float) $em['baseline_rmse'], 4) : '—' }}</td>
                        <td class="px-3 py-2 text-center text-xs text-slate-600">
                            @if (! empty($em['samples']))
                            {{ $em['samples']['train'] ?? 0 }}/{{ $em['samples']['val'] ?? 0 }}/{{ $em['samples']['test'] ?? 0 }}
                            @else
                            —
                            @endif
                        </td>
                        <td class="px-3 py-2 text-xs">
                            @if (($currentActive[$mi->ingredient_id] ?? null)?->id === $annModel->id)
                            <span class="text-green-600 font-medium">Model ini</span>
                            @elseif ($currentActive[$mi->ingredient_id] ?? null)
                            <a href="{{ route('ann.models.show', $currentActive[$mi->ingredient_id]) }}" class="text-blue-600 hover:underline font-mono">
                                {{ $currentActive[$mi->ingredient_id]->model_version }}
                            </a>
                            @else
                            <span class="text-slate-400">Belum ada</span>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </x-card>
</x-app-layout>
