@php $sp = $supplier ?? null; @endphp

<div class="space-y-4">
    <x-input name="name" label="Nama Supplier" required
        :value="old('name', $sp?->name ?? '')"
        placeholder="Contoh: PT Kopi Nusantara" />

    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <x-input name="contact_person" label="Kontak Person"
            :value="old('contact_person', $sp?->contact_person ?? '')"
            placeholder="Nama PIC (opsional)" />

        <x-input name="phone" label="Telepon"
            :value="old('phone', $sp?->phone ?? '')"
            placeholder="08xxxxxxxxxx" />
    </div>

    <x-input name="email" type="email" label="Email"
        :value="old('email', $sp?->email ?? '')"
        placeholder="supplier@example.com (opsional)" />

    <div>
        <label for="address" class="block text-sm font-medium text-slate-700 mb-1">Alamat</label>
        <textarea id="address" name="address" rows="3"
            placeholder="Alamat lengkap (opsional)"
            class="w-full px-3 py-2 rounded-lg border border-slate-300 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">{{ old('address', $sp?->address ?? '') }}</textarea>
        @error('address')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
    </div>

    <div class="flex items-center gap-2">
        <input type="checkbox" id="is_active" name="is_active" value="1"
            @checked(old('is_active', $sp?->is_active ?? true))
        class="h-4 w-4 text-blue-600 border-slate-300 rounded focus:ring-blue-500">
        <label for="is_active" class="text-sm text-slate-700">Aktif</label>
    </div>
</div>
