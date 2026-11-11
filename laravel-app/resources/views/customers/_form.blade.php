@php $cu = $customer ?? null; @endphp

<div class="space-y-4">
    <x-input name="name" label="Nama Customer" required
        :value="old('name', $cu?->name ?? '')"
        placeholder="Nama pelanggan" />

    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <x-input name="phone" label="Telepon"
            :value="old('phone', $cu?->phone ?? '')"
            placeholder="08xxxxxxxxxx" />

        <x-input name="email" type="email" label="Email"
            :value="old('email', $cu?->email ?? '')"
            placeholder="customer@example.com" />
    </div>

    <div>
        <label for="notes" class="block text-sm font-medium text-slate-700 mb-1">Catatan</label>
        <textarea id="notes" name="notes" rows="2"
            placeholder="Catatan tambahan (opsional)"
            class="w-full px-3 py-2 rounded-lg border border-slate-300 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">{{ old('notes', $cu?->notes ?? '') }}</textarea>
        @error('notes')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
    </div>
</div>
