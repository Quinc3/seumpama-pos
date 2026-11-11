@props([
'name',
'label' => null,
'required' => false,
'options' => [],
'placeholder' => null,
])

@php
$hasError = $errors->has($name);
$selectClasses = 'w-full px-3 py-2 rounded-lg border text-sm bg-white transition-colors focus:outline-none focus:ring-2 '
. ($hasError
? 'border-red-300 focus:ring-red-500 focus:border-red-500'
: 'border-slate-300 focus:ring-blue-500 focus:border-blue-500');
$currentValue = old($name, $attributes->get('value'));
@endphp

<div>
    @if ($label)
    <label for="{{ $name }}" class="block text-sm font-medium text-slate-700 mb-1">
        {{ $label }}
        @if ($required)<span class="text-red-500">*</span>@endif
    </label>
    @endif

    <select
        id="{{ $name }}"
        name="{{ $name }}"
        @if ($required) required @endif
        {{ $attributes->except('value')->merge(['class' => $selectClasses]) }}>
        @if ($placeholder)
        <option value="">{{ $placeholder }}</option>
        @endif
        @foreach ($options as $value => $labelText)
        <option value="{{ $value }}" @selected((string) $currentValue===(string) $value)>
            {{ $labelText }}
        </option>
        @endforeach
    </select>

    @error($name)
    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
    @enderror
</div>
