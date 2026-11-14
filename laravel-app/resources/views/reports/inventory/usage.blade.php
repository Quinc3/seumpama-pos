<x-app-layout title="Ingredient Usage Report" subtitle="Pemakaian bahan per periode (dari transaksi paid)">
    <x-slot name="actions">
        <x-button :href="route('reports.usage.pdf', request()->query())" tag="a" variant="outline" icon="receipt">
            Export PDF
        </x-button>
    </x-slot>

    <x-card padding="none" class="mb-4">
        <form method="GET" class="p-4 flex flex-wrap gap-3 items-end">
            <div class="w-36">
                <label class="block text-xs font-medium text-slate-600 mb-1">Dari</label>
                <input type="date" name="from" value="{{ $from->format('Y-m-d') }}" class="w-full px-3 py-2 rounded-lg border border-slate-300 text-sm">
            </div>
            <div class="w-36">
                <label class="block text-xs font-medium text-slate-600 mb-1">Sampai</label>
                <input type="date" name="to" value="{{ $to->format('Y-m-d') }}" class="w-full px-3 py-2 rounded-lg border border-slate-300 text-sm">
            </div>
            <button type="submit" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-sm font-medium rounded-lg">Terapkan</button>
        </form>
    </x-card>

    <x-card title="Pemakaian per Bahan" :subtitle="$from->format('d M Y') . ' — ' . $to->format('d M Y')">
        @if ($usage->isEmpty())
        <p class="text-sm text-slate-500 text-center py-6">Tidak ada pemakaian pada periode ini.</p>
        @else
        @php $maxUsed = $usage->max('total_used'); @endphp
        <div class="space-y-3">
            @foreach ($usage as $u)
            <div>
                <div class="flex justify-between text-sm mb-1">
                    <div>
                        <span class="font-medium text-slate-900">{{ $u->name }}</span>
                        <span class="text-xs text-slate-500 ml-2">{{ $u->category ?: '—' }}</span>
                    </div>
                    <span class="font-mono font-medium text-slate-900">
                        {{ number_format((float) $u->total_used, 2, '.', ',') }} {{ $u->unit_symbol }}
                    </span>
                </div>
                <div class="h-2 bg-slate-100 rounded overflow-hidden">
                    <div class="h-full bg-blue-500"
                        style="width: {{ $maxUsed > 0 ? ($u->total_used / $maxUsed) * 100 : 0 }}%"></div>
                </div>
                <div class="text-xs text-slate-500 mt-1">{{ $u->order_count }} order terkait</div>
            </div>
            @endforeach
        </div>
        @endif
    </x-card>
</x-app-layout>
