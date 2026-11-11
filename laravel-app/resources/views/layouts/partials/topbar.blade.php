<header class="sticky top-0 z-30 bg-white border-b border-slate-200 h-16 flex items-center px-4 lg:px-6 gap-4">
    {{-- Hamburger (mobile) --}}
    <button type="button"
        @click="sidebarOpen = true"
        class="lg:hidden p-2 -ml-2 rounded-lg text-slate-600 hover:bg-slate-100">
        <x-icon name="menu" :size="20" />
    </button>

    {{-- Page context --}}
    <div class="flex-1 min-w-0">
        <p class="text-sm font-medium text-slate-900 truncate">{{ $title ?? 'Dashboard' }}</p>
    </div>

    {{-- User dropdown --}}
    <div class="relative" x-data="{ userOpen: false }">
        <button type="button"
            @click="userOpen = !userOpen"
            @click.outside="userOpen = false"
            class="flex items-center gap-2 px-2 py-1.5 rounded-lg hover:bg-slate-100 transition-colors">
            <div class="w-8 h-8 rounded-full bg-blue-600 flex items-center justify-center text-white text-sm font-semibold">
                {{ strtoupper(substr(auth()->user()->name ?? 'U', 0, 1)) }}
            </div>
            <div class="hidden sm:block text-left">
                <div class="text-sm font-medium text-slate-900 leading-tight">{{ auth()->user()->name ?? 'User' }}</div>
                <div class="text-xs text-slate-500 leading-tight">{{ auth()->user()?->role()?->name ?? 'No role' }}</div>
            </div>
            <x-icon name="chevron-down" :size="16" class="hidden sm:block text-slate-400" />
        </button>

        <div x-show="userOpen"
            x-transition
            @click.outside="userOpen = false"
            class="absolute right-0 mt-2 w-56 bg-white border border-slate-200 rounded-lg shadow-lg py-1 z-50"
            style="display: none;">
            <div class="px-4 py-2 border-b border-slate-100">
                <div class="text-sm font-medium text-slate-900 truncate">{{ auth()->user()->name ?? 'User' }}</div>
                <div class="text-xs text-slate-500 truncate">{{ auth()->user()->email ?? '' }}</div>
            </div>

            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit"
                    class="w-full flex items-center gap-2 px-4 py-2 text-sm text-slate-700 hover:bg-slate-50">
                    <x-icon name="log-out" :size="16" />
                    <span>Logout</span>
                </button>
            </form>
        </div>
    </div>
</header>
