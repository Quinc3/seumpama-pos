<x-app-layout :title="'Training ' . $run->training_id" :subtitle="$run->datasetVersion?->version">
    <x-slot name="actions">
        @if (! in_array($run->training_status, ['completed']))
        <form method="POST" action="{{ route('ann.training.refresh', $run) }}" class="inline">
            @csrf
            <x-button type="submit" variant="outline" icon="arrow-right-left">
                Refresh Status
            </x-button>
        </form>
        @else
        <form method="POST" action="{{ route('ann.training.refresh', $run) }}" class="inline">
            @csrf
            <x-button type="submit" variant="outline" icon="arrow-right-left">
                Refresh dari Python
            </x-button>
        </form>
        @endif
        <x-button :href="route('ann.training.index')" tag="a" variant="outline">Kembali</x-button>
    </x-slot>

    @if (session('success'))
    <div class="mb-4"><x-alert variant="success" icon="clipboard-check" dismissible>{{ session('success') }}</x-alert></div>
    @endif
    @if (session('error'))
    <div class="mb-4"><x-alert variant="error" icon="shield" dismissible>{{ session('error') }}</x-alert></div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-4 mb-4">
        <x-card title="Info Training" class="lg:col-span-1">
            <dl class="space-y-3 text-sm">
                <div>
                    <dt class="text-slate-500 text-xs mb-1">Training ID</dt>
                    <dd class="font-mono text-xs font-medium text-slate-900 break-all">{{ $run->training_id }}</dd>
                </div>
                <div>
                    <dt class="text-slate-500 text-xs mb-1">Dataset</dt>
                    <dd class="font-mono text-xs">
                        @if ($run->datasetVersion)
                        <a href="{{ route('datasets.show', $run->datasetVersion) }}" class="text-blue-600 hover:underline">
                            {{ $run->datasetVersion->version }}
                        </a>
                        @else
                        —
                        @endif
                    </dd>
                </div>
                <div>
                    <dt class="text-slate-500 text-xs mb-1">Status</dt>
                    <dd>
                        @php
                        $variant = match($run->training_status) {
                        'completed' => 'success',
                        'running' => 'info',
                        'queued' => 'warning',
                        'failed' => 'danger',
                        default => 'neutral',
                        };
                        @endphp
                        <x-badge :variant="$variant">{{ ucfirst($run->training_status) }}</x-badge>
                    </dd>
                </div>
                <div>
                    <dt class="text-slate-500 text-xs mb-1">Started</dt>
                    <dd class="text-slate-900">{{ $run->training_started_at?->format('d M Y H:i') ?? '—' }}</dd>
                </div>
                <div>
                    <dt class="text-slate-500 text-xs mb-1">Completed</dt>
                    <dd class="text-slate-900">{{ $run->training_completed_at?->format('d M Y H:i') ?? '—' }}</dd>
                </div>
            </dl>

            @if ($run->error_message)
            <div class="mt-4 pt-4 border-t border-slate-100">
                <dt class="text-red-600 text-xs font-medium mb-1">Error</dt>
                <dd class="text-xs text-red-700 bg-red-50 p-2 rounded">{{ $run->error_message }}</dd>
            </div>
            @endif
        </x-card>

        <x-card title="Hyperparameters" class="lg:col-span-2">
            @php $hp = $run->hyperparameters ?? []; @endphp
            <dl class="grid grid-cols-2 md:grid-cols-4 gap-4 text-sm">
                <div>
                    <dt class="text-slate-500 text-xs">Epochs</dt>
                    <dd class="font-mono text-slate-900">{{ $hp['epochs'] ?? '—' }}</dd>
                </div>
                <div>
                    <dt class="text-slate-500 text-xs">Batch Size</dt>
                    <dd class="font-mono text-slate-900">{{ $hp['batch_size'] ?? '—' }}</dd>
                </div>
                <div>
                    <dt class="text-slate-500 text-xs">Learning Rate</dt>
                    <dd class="font-mono text-slate-900">{{ $hp['learning_rate'] ?? '—' }}</dd>
                </div>
                <div>
                    <dt class="text-slate-500 text-xs">Early Stop</dt>
                    <dd class="font-mono text-slate-900">{{ $hp['early_stopping_patience'] ?? '—' }}</dd>
                </div>
            </dl>

            @if (! empty($run->test_metrics))
            <div class="mt-4 pt-4 border-t border-slate-100">
                <dt class="text-slate-500 text-xs mb-2">Test Metrics per Ingredient</dt>
                <div class="overflow-x-auto">
                    <table class="w-full text-xs">
                        <thead class="bg-slate-50 border-b border-slate-200">
                            <tr>
                                <th class="text-left px-2 py-1.5 font-medium text-slate-600">Ingredient ID</th>
                                <th class="text-right px-2 py-1.5 font-medium text-slate-600">MAE</th>
                                <th class="text-right px-2 py-1.5 font-medium text-slate-600">RMSE</th>
                                <th class="text-right px-2 py-1.5 font-medium text-slate-600">Baseline MAE</th>
                                <th class="text-right px-2 py-1.5 font-medium text-slate-600">Baseline RMSE</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @foreach ($run->test_metrics as $tm)
                            <tr>
                                <td class="px-2 py-1.5 font-mono text-slate-700">{{ $tm['ingredient_id'] ?? '—' }}</td>
                                <td class="px-2 py-1.5 text-right font-mono text-slate-900">{{ isset($tm['mae']) ? number_format((float) $tm['mae'], 4) : '—' }}</td>
                                <td class="px-2 py-1.5 text-right font-mono text-slate-700">{{ isset($tm['rmse']) ? number_format((float) $tm['rmse'], 4) : '—' }}</td>
                                <td class="px-2 py-1.5 text-right font-mono text-slate-500">{{ isset($tm['baseline_mae']) ? number_format((float) $tm['baseline_mae'], 4) : '—' }}</td>
                                <td class="px-2 py-1.5 text-right font-mono text-slate-500">{{ isset($tm['baseline_rmse']) ? number_format((float) $tm['baseline_rmse'], 4) : '—' }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            @endif
        </x-card>
    </div>

    @if ($models->isNotEmpty())
    <x-card title="Models Dibuat" :subtitle="$models->count() . ' model'">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-slate-50 border-b border-slate-200">
                    <tr>
                        <th class="text-left px-3 py-2 font-medium text-slate-600">Model Version</th>
                        <th class="text-left px-3 py-2 font-medium text-slate-600">Ingredient</th>
                        <th class="text-right px-3 py-2 font-medium text-slate-600">MAE</th>
                        <th class="text-right px-3 py-2 font-medium text-slate-600">Baseline MAE</th>
                        <th class="text-left px-3 py-2 font-medium text-slate-600">Status</th>
                        <th class="text-right px-3 py-2 font-medium text-slate-600"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach ($models as $m)
                    @php
                    $mi = $m->ingredients->first();
                    $em = $mi?->evaluation_metrics ?? [];
                    @endphp
                    <tr class="hover:bg-slate-50">
                        <td class="px-3 py-2 font-mono text-xs text-slate-900">{{ $m->model_version }}</td>
                        <td class="px-3 py-2">
                            <div class="text-slate-900">{{ $mi?->ingredient?->name ?? '—' }}</div>
                            <div class="text-xs text-slate-400 font-mono">{{ $mi?->ingredient?->sku }}</div>
                        </td>
                        <td class="px-3 py-2 text-right font-mono text-slate-900 font-medium">{{ isset($em['mae']) ? number_format((float) $em['mae'], 4) : '—' }}</td>
                        <td class="px-3 py-2 text-right font-mono text-slate-500">{{ isset($em['baseline_mae']) ? number_format((float) $em['baseline_mae'], 4) : '—' }}</td>
                        <td class="px-3 py-2">
                            @php
                            $variant = match($m->status) {
                            'active' => 'success',
                            'candidate' => 'info',
                            'archived' => 'neutral',
                            default => 'neutral',
                            };
                            @endphp
                            <x-badge :variant="$variant">{{ ucfirst($m->status) }}</x-badge>
                        </td>
                        <td class="px-3 py-2 text-right">
                            <a href="{{ route('ann.models.show', $m) }}" class="text-sm text-blue-600 hover:text-blue-800 font-medium">Detail</a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </x-card>
    @endif
</x-app-layout>
