@props([
'variant' => 'info',
'dismissible' => false,
'icon' => null,
])

@php
$variants = [
'success' => 'bg-green-50 border-green-200 text-green-800',
'error' => 'bg-red-50 border-red-200 text-red-800',
'warning' => 'bg-amber-50 border-amber-200 text-amber-800',
'info' => 'bg-blue-50 border-blue-200 text-blue-800',
];
@endphp

<div @if ($dismissible) x-data="{ show: true }" x-show="show" @endif
    class="border rounded-lg px-4 py-3 text-sm flex items-start gap-3 {{ $variants[$variant] }}">
    @if ($icon)
    <x-icon :name="$icon" :size="18" class="shrink-0 mt-0.5" />
    @endif
    <div class="flex-1">{{ $slot }}</div>
    @if ($dismissible)
    <button type="button" @click="show = false" class="shrink-0 opacity-60 hover:opacity-100">
        <x-icon name="x" :size="16" />
    </button>
    @endif
</div>
