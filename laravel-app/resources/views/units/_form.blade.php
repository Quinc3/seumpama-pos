@php
$types = ['weight' => 'Weight (berat)', 'volume' => 'Volume (cairan)', 'count' => 'Count (jumlah)'];
@endphp

<div class="space-y-4">
    <x-input
        name="name"
        label="Nama Unit"
        required
        :value="old('name', $unit->name ?? '')"
        placeholder="Contoh: Gram, Kilogram" />

    <x-input
        name="symbol"
        label="Simbol"
        required
        :value="old('symbol', $unit->symbol ?? '')"
        placeholder="Contoh: g, kg" />

    <x-select
        name="unit_type"
        label="Tipe Unit"
        required
        :options="$types"
        :value="old('unit_type', $unit->unit_type ?? '')" />

    <div class="flex items-center gap-2">
        <input type="checkbox" id="is_active" name="is_active" value="1"
            @checked(old('is_active', $unit->is_active ?? true))
        class="h-4 w-4 text-blue-600 border-slate-300 rounded focus:ring-blue-500">
        <label for="is_active" class="text-sm text-slate-700">Aktif</label>
    </div>
</div>
