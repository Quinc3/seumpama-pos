@php $exp = $expense ?? null; @endphp

<div class="space-y-4">
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <x-select name="expense_category_id" label="Kategori" required
            :options="$categories->pluck('name', 'id')->toArray()"
            placeholder="— Pilih Kategori —"
            :value="old('expense_category_id', $exp?->expense_category_id ?? '')" />

        <x-input name="expense_date" type="date" label="Tanggal" required
            :value="old('expense_date', isset($exp) ? $exp->expense_date->format('Y-m-d') : now()->format('Y-m-d'))" />

        <x-input name="amount" type="number" step="any" min="1" label="Nominal (Rp)" required
            :value="old('amount', $exp?->amount ?? '')"
            placeholder="0" />

        <x-select name="payment_method_id" label="Metode Pembayaran"
            :options="$paymentMethods->pluck('name', 'id')->toArray()"
            placeholder="— Opsional —"
            :value="old('payment_method_id', $exp?->payment_method_id ?? '')" />
    </div>

    <div>
        <label for="description" class="block text-sm font-medium text-slate-700 mb-1">
            Deskripsi <span class="text-red-500">*</span>
        </label>
        <textarea id="description" name="description" rows="2" required
            placeholder="Contoh: Beli gas elpiji 3kg, bayar listrik bulan November"
            class="w-full px-3 py-2 rounded-lg border border-slate-300 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500">{{ old('description', $exp?->description ?? '') }}</textarea>
        @error('description')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
    </div>
</div>
