@props([
'name',
'label' => 'Upload File',
'existingPath' => null,
'accept' => 'image/*',
'maxSize' => '2MB',
'hint' => null,
'removeName' => null,
])

@php
$hasError = $errors->has($name);
$id = 'file-' . $name;
@endphp

<div x-data="{
        fileName: null,
        previewUrl: null,
        isDragging: false,
        handleChange($event) {
            const file = $event.target.files[0];
            if (!file) { this.fileName = null; this.previewUrl = null; return; }
            this.fileName = file.name;
            this.previewUrl = URL.createObjectURL(file);
        },
        handleDrop($event) {
            this.isDragging = false;
            const files = $event.dataTransfer?.files;
            if (!files || files.length === 0) return;

            const file = files[0];
            const accept = '{{ $accept }}';
            if (accept === 'image/*' && !file.type.startsWith('image/')) {
                alert('Hanya file gambar yang diperbolehkan.');
                return;
            }

            const dt = new DataTransfer();
            dt.items.add(file);
            this.$refs.input.files = dt.files;
            this.handleChange({ target: { files: dt.files } });
        },
        clear() {
            this.$refs.input.value = '';
            this.fileName = null;
            this.previewUrl = null;
            this.isDragging = false;
        }
    }"
    class="space-y-3">
    @if ($label)
    <label class="block text-sm font-medium text-slate-700">{{ $label }}</label>
    @endif

    {{-- Existing image (edit mode) --}}
    @if ($existingPath)
    <div x-show="!previewUrl" class="flex items-center gap-4 p-3 bg-slate-50 rounded-lg border border-slate-200">
        <img src="{{ Storage::url($existingPath) }}" alt="Current"
            class="w-20 h-20 object-cover rounded-lg border border-slate-200 bg-white">
        <div class="flex-1 min-w-0">
            <p class="text-sm font-medium text-slate-900">Gambar saat ini</p>
            <p class="text-xs text-slate-500 truncate">{{ basename($existingPath) }}</p>

            @if ($removeName)
            <label class="inline-flex items-center gap-1.5 mt-2 text-xs text-red-600 cursor-pointer hover:text-red-800">
                <input type="checkbox" name="{{ $removeName }}" value="1"
                    class="h-3.5 w-3.5 text-red-600 border-slate-300 rounded focus:ring-red-500">
                Hapus gambar ini
            </label>
            @endif
        </div>
    </div>
    @endif

    {{-- Drop zone --}}
    <div x-show="!previewUrl"
        @click="$refs.input.click()"
        @dragover.prevent="isDragging = true"
        @dragenter.prevent="isDragging = true"
        @dragleave.prevent="isDragging = false"
        @drop.prevent="handleDrop($event)"
        :class="isDragging
             ? 'border-blue-500 bg-blue-50 ring-2 ring-blue-200'
             : '{{ $hasError
                 ? 'border-red-300 bg-red-50 hover:bg-red-100'
                 : 'border-slate-300 bg-slate-50 hover:border-blue-400 hover:bg-blue-50' }}'"
        class="relative flex flex-col items-center justify-center gap-2 px-6 py-8 rounded-lg border-2 border-dashed cursor-pointer transition-all">
        <div :class="isDragging ? 'bg-blue-100 border-blue-300' : 'bg-white border-slate-200'"
            class="w-12 h-12 rounded-full border flex items-center justify-center transition-colors">
            <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24"
                fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                :class="isDragging ? 'text-blue-600' : '{{ $hasError ? 'text-red-400' : 'text-slate-400' }}'">
                <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4" />
                <polyline points="17 8 12 3 7 8" />
                <line x1="12" x2="12" y1="3" y2="15" />
            </svg>
        </div>

        <div class="text-center">
            <p class="text-sm font-medium" :class="isDragging ? 'text-blue-900' : '{{ $hasError ? 'text-red-700' : 'text-slate-700' }}'">
                <span x-show="!isDragging" :class="'{{ $hasError ? 'text-red-600' : 'text-blue-600' }}'">Klik untuk pilih file</span>
                <span x-show="isDragging" x-cloak class="text-blue-700">Lepaskan file di sini</span>
                <span x-show="!isDragging">atau seret gambar ke sini</span>
            </p>
            <p class="text-xs text-slate-500 mt-1">
                {{ $hint ?: 'Format: JPG, PNG, WebP — Maksimal ' . $maxSize }}
            </p>
        </div>

        <input type="file"
            id="{{ $id }}"
            name="{{ $name }}"
            accept="{{ $accept }}"
            x-ref="input"
            @change="handleChange"
            class="hidden">
    </div>

    {{-- New file preview --}}
    <div x-show="previewUrl" x-cloak class="flex items-center gap-4 p-3 bg-blue-50 rounded-lg border border-blue-200">
        <img :src="previewUrl" alt="Preview" class="w-20 h-20 object-cover rounded-lg border border-blue-200 bg-white">
        <div class="flex-1 min-w-0">
            <p class="text-sm font-medium text-blue-900">File dipilih</p>
            <p class="text-xs text-blue-700 truncate" x-text="fileName"></p>
            <button type="button" @click="clear()"
                class="mt-2 inline-flex items-center gap-1 text-xs text-red-600 hover:text-red-800 font-medium">
                <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24"
                    fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M18 6 6 18" />
                    <path d="m6 6 12 12" />
                </svg>
                Batalkan pilihan
            </button>
        </div>
    </div>

    @error($name)
    <p class="text-xs text-red-600">{{ $message }}</p>
    @enderror
</div>
