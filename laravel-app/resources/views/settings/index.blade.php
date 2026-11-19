<x-app-layout title="Settings" subtitle="Konfigurasi bisnis dan struk">
    @if (session('success'))
    <div class="mb-4"><x-alert variant="success" icon="clipboard-check" dismissible>{{ session('success') }}</x-alert></div>
    @endif
    @if (session('error'))
    <div class="mb-4"><x-alert variant="error" icon="shield" dismissible>{{ session('error') }}</x-alert></div>
    @endif

    <x-card class="max-w-2xl">
        <form method="POST" action="{{ route('settings.update') }}" class="space-y-4">
            @csrf @method('PUT')

            <div>
                <h3 class="text-sm font-semibold text-slate-900 mb-3 pb-2 border-b border-slate-100">Identitas Bisnis</h3>
                <div class="space-y-4">
                    <x-input name="shop_name" label="Nama Coffee Shop" required
                        :value="old('shop_name', $settings['shop_name'])"
                        placeholder="Seumpama Bunga" />

                    <div>
                        <label for="shop_address" class="block text-sm font-medium text-slate-700 mb-1">Alamat</label>
                        <textarea id="shop_address" name="shop_address" rows="2"
                            class="w-full px-3 py-2 rounded-lg border border-slate-300 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500">{{ old('shop_address', $settings['shop_address']) }}</textarea>
                        @error('shop_address')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                    </div>

                    <x-input name="shop_phone" label="Nomor Telepon"
                        :value="old('shop_phone', $settings['shop_phone'])"
                        placeholder="0812-0000-0000" />

                    <x-input name="currency" label="Mata Uang" required
                        :value="old('currency', $settings['currency'])"
                        placeholder="IDR" />
                </div>
            </div>

            <div>
                <h3 class="text-sm font-semibold text-slate-900 mb-3 pb-2 border-b border-slate-100">Struk</h3>
                <div>
                    <label for="receipt_footer" class="block text-sm font-medium text-slate-700 mb-1">Footer Struk</label>
                    <textarea id="receipt_footer" name="receipt_footer" rows="2"
                        placeholder="Terima kasih atas kunjungan Anda!"
                        class="w-full px-3 py-2 rounded-lg border border-slate-300 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500">{{ old('receipt_footer', $settings['receipt_footer']) }}</textarea>
                    @error('receipt_footer')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                </div>
            </div>

            <div class="flex gap-2 pt-4 border-t border-slate-100">
                <x-button type="submit" icon="clipboard-check">Simpan Pengaturan</x-button>
            </div>
        </form>
    </x-card>
</x-app-layout>
