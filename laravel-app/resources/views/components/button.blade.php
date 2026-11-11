@props([
'variant' => 'primary',
'type' => 'button',
'size' => 'md',
'icon' => null,
])

@php
$variants = [
'primary' => 'bg-blue-600 text-white hover:bg-blue-700 focus:ring-blue-500',
'secondary' => 'bg-slate-100 text-slate-900 hover:bg-slate-200 focus:ring-slate-400',
'outline' => 'bg-white text-slate-700 border border-slate-300 hover:bg-slate-50 focus:ring-blue-500',
'ghost' => 'bg-transparent text-slate-700 hover:bg-slate-100 focus:ring-slate-400',
'danger' => 'bg-red-600 text-white hover:bg-red-700 focus:ring-red-500',
];

$sizes = [
'sm' => 'text-xs px-3 py-1.5 gap-1.5',
'md' => 'text-sm px-4 py-2 gap-2',
'lg' => 'text-base px-5 py-2.5 gap-2',
];

$classes = 'inline-flex items-center justify-center font-medium rounded-lg transition-colors focus:outline-none focus:ring-2 focus:ring-offset-1 disabled:opacity-50 disabled:cursor-not-allowed ' . $variants[$variant] . ' ' . $sizes[$size];
@endphp

<button type="{{ $type }}" {{ $attributes->merge(['class' => $classes]) }}>
    @if ($icon)
    <x-icon :name="$icon" :size="$size === 'sm' ? 14 : ($size === 'lg' ? 20 : 16)" />
    @endif
    {{ $slot }}
</button>
