@props(['title' => null, 'subtitle' => null, 'actions' => null])

<!DOCTYPE html>
<html lang="id" class="h-full">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ? $title . ' — ' : '' }}Seumpama POS</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="h-full bg-slate-50 text-slate-900 antialiased">
    <div x-data="{ sidebarOpen: false }" class="h-full">
        {{-- Mobile sidebar overlay --}}
        <div x-show="sidebarOpen"
            x-transition.opacity
            @click="sidebarOpen = false"
            class="fixed inset-0 bg-slate-900/50 z-40 lg:hidden"
            style="display: none;"></div>

        {{-- Sidebar --}}
        <aside :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'"
            class="fixed inset-y-0 left-0 z-50 w-64 bg-white border-r border-slate-200 transform transition-transform duration-200 ease-in-out lg:translate-x-0">
            {{-- Mobile close button --}}
            <button type="button"
                @click="sidebarOpen = false"
                class="lg:hidden absolute top-3 right-3 p-1 rounded text-slate-400 hover:text-slate-600">
                <x-icon name="x" :size="18" />
            </button>

            @include('layouts.partials.sidebar')
        </aside>

        {{-- Main area --}}
        <div class="lg:pl-64 h-full flex flex-col">
            @include('layouts.partials.topbar')

            <main class="flex-1 p-4 lg:p-6 overflow-y-auto">
                @if ($title || $subtitle || $actions)
                <div class="mb-6 flex flex-col sm:flex-row sm:items-start sm:justify-between gap-3">
                    <div>
                        @if ($title)
                        <h1 class="text-2xl font-bold text-slate-900">{{ $title }}</h1>
                        @endif
                        @if ($subtitle)
                        <p class="text-slate-500 mt-1">{{ $subtitle }}</p>
                        @endif
                    </div>
                    @if ($actions)
                    <div class="shrink-0">{{ $actions }}</div>
                    @endif
                </div>
                @endif

                {{ $slot }}
            </main>
        </div>
    </div>
</body>

</html>
