@props(['variant' => 'neutral'])

@php
$variants = [
'success' => 'bg-green-50 text-green-700 border-green-200',
'warning' => 'bg-amber-50 text-amber-700 border-amber-200',
'danger' => 'bg-red-50 text-red-700 border-red-200',
'info' => 'bg-blue-50 text-blue-700 border-blue-200',
'neutral' => 'bg-slate-100 text-slate-700 border-slate-200',
];
@endphp

<span {{ $attributes->merge(['class' => 'inline-flex items-center px-2 py-0.5 rounded text-xs font-medium border ' . $variants[$variant]]) }}>
    {{ $slot }}
</span>
