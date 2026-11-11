@props([
'name',
'label' => null,
'type' => 'text',
'required' => false,
'placeholder' => null,
])

@php
$hasError = $errors->has($name);
$inputClasses = 'w-full px-3 py-2 rounded-lg border text-sm transition-colors focus:outline-none focus:ring-2 '
. ($hasError
? 'border-red-300 focus:ring-red-500 focus:border-red-500'
: 'border-slate-300 focus:ring-blue-500 focus:border-blue-500');
@endphp

<div>
    @if ($label)
    <label for="{{ $name }}" class="block text-sm font-medium text-slate-700 mb-1">
        {{ $label }}
        @if ($required)<span class="text-red-500">*</span>@endif
    </label>
    @endif

    <input
        type="{{ $type }}"
        id="{{ $name }}"
        name="{{ $name }}"
        placeholder="{{ $placeholder }}"
        @if ($required) required @endif
        {{ $attributes->merge(['class' => $inputClasses]) }}>

    @error($name)
    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
    @enderror
</div>
