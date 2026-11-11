@props(['padding' => 'md', 'title' => null, 'subtitle' => null])

@php
$paddings = ['none' => 'p-0', 'sm' => 'p-4', 'md' => 'p-6', 'lg' => 'p-8'];
$class = 'bg-white rounded-lg border border-slate-200 ' . $paddings[$padding];
@endphp

<div {{ $attributes->merge(['class' => $class]) }}>
    @if ($title || $subtitle)
    <div class="mb-4">
        @if ($title)<h2 class="text-lg font-semibold text-slate-900">{{ $title }}</h2>@endif
        @if ($subtitle)<p class="text-sm text-slate-500 mt-1">{{ $subtitle }}</p>@endif
    </div>
    @endif
    {{ $slot }}
</div>
