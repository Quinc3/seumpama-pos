@props([
'name',
'label' => null,
'required' => false,
'placeholder' => null,
])

@php
$hasError = $errors->has($name);
$inputClasses = 'w-full px-3 py-2 pr-10 rounded-lg border text-sm transition-colors focus:outline-none focus:ring-2 '
. ($hasError
? 'border-red-300 focus:ring-red-500 focus:border-red-500'
: 'border-slate-300 focus:ring-blue-500 focus:border-blue-500');
@endphp

<div x-data="{ show: false }">
    @if ($label)
    <label for="{{ $name }}" class="block text-sm font-medium text-slate-700 mb-1">
        {{ $label }}
        @if ($required)<span class="text-red-500">*</span>@endif
    </label>
    @endif

    <div class="relative">
        <input
            :type="show ? 'text' : 'password'"
            id="{{ $name }}"
            name="{{ $name }}"
            placeholder="{{ $placeholder }}"
            @if ($required) required @endif
            {{ $attributes->merge(['class' => $inputClasses]) }}>

        <button type="button"
            @click="show = !show"
            class="absolute right-2 top-1/2 -translate-y-1/2 p-1 rounded text-slate-400 hover:text-slate-600 focus:outline-none focus:text-slate-700"
            aria-label="Toggle password visibility"
            :aria-label="show ? 'Sembunyikan password' : 'Tampilkan password'">
            {{-- Eye icon (hidden password) --}}
            <svg x-show="!show" xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24"
                fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M2 12s3-7 10-7 10 7 10 7-3 7-10 7-10-7-10-7Z" />
                <circle cx="12" cy="12" r="3" />
            </svg>
            {{-- Eye-off icon (visible password) --}}
            <svg x-show="show" x-cloak xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24"
                fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M9.88 9.88a3 3 0 1 0 4.24 4.24" />
                <path d="M10.73 5.08A10.43 10.43 0 0 1 12 5c7 0 10 7 10 7a13.16 13.16 0 0 1-1.67 2.68" />
                <path d="M6.61 6.61A13.526 13.526 0 0 0 2 12s3 7 10 7a9.74 9.74 0 0 0 5.39-1.61" />
                <line x1="2" x2="22" y1="2" y2="22" />
            </svg>
        </button>
    </div>

    @error($name)
    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
    @enderror
</div>
