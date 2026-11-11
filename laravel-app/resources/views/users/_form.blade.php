@php
$us = $user ?? null;
$roleOptions = $roles->pluck('name', 'id')->toArray();
$defaultRoleId = old('role_id', $currentRoleId ?? null);
@endphp

<div class="space-y-4">
    <x-input name="name" label="Nama" required
        :value="old('name', $us?->name ?? '')"
        placeholder="Nama lengkap" />

    <x-input name="email" type="email" label="Email" required
        :value="old('email', $us?->email ?? '')"
        placeholder="user@seumpama.com" />

    <x-input name="phone" label="Telepon"
        :value="old('phone', $us?->phone ?? '')"
        placeholder="08xxxxxxxxxx (opsional)" />

    <x-select name="role_id" label="Role" required
        :options="$roleOptions" placeholder="— Pilih Role —"
        :value="$defaultRoleId" />

    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <x-password-input
            name="password"
            :required="! $us"
            :label="$us ? 'Password Baru (kosongkan jika tidak ganti)' : 'Password'"
            placeholder="Minimal 8 karakter" />
        <x-password-input
            name="password_confirmation"
            :required="! $us"
            label="Konfirmasi Password"
            placeholder="Ulangi password" />
    </div>

    <div class="flex items-center gap-2">
        @if($us && $us->id === auth()->id())
        <input type="hidden" name="is_active" value="1">
        <input type="checkbox" checked disabled
            class="h-4 w-4 text-blue-600 border-slate-300 rounded">
        @else
        <input type="checkbox" id="is_active" name="is_active" value="1"
            @checked(old('is_active', $us?->is_active ?? true))
        class="h-4 w-4 text-blue-600 border-slate-300 rounded focus:ring-blue-500">
        @endif
        <label for="is_active" class="text-sm text-slate-700">
            Aktif
            @if($us && $us->id === auth()->id())
            <span class="text-xs text-slate-400">(akun sendiri selalu aktif)</span>
            @endif
        </label>
    </div>
</div>
