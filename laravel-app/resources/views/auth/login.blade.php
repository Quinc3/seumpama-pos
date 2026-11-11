<!DOCTYPE html>
<html lang="id" class="h-full">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Login — Seumpama POS</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="h-full bg-slate-50 text-slate-900 antialiased flex items-center justify-center p-4">
    <div class="w-full max-w-md">
        {{-- Brand --}}
        <div class="text-center mb-8">
            <div class="inline-flex items-center justify-center w-14 h-14 rounded-2xl bg-blue-600 mb-4">
                <x-icon name="coffee" :size="28" class="text-white" />
            </div>
            <h1 class="text-2xl font-bold text-slate-900">Seumpama POS</h1>
            <p class="text-slate-500 mt-1 text-sm">Point of Sale &amp; Inventory Management</p>
        </div>

        {{-- Card --}}
        <x-card padding="lg">
            <h2 class="text-lg font-semibold text-slate-900 mb-1">Masuk ke Akun</h2>
            <p class="text-sm text-slate-500 mb-6">Gunakan kredensial yang diberikan administrator.</p>

            @if ($errors->any())
            <div class="mb-5">
                <x-alert variant="error" icon="shield">
                    {{ $errors->first() }}
                </x-alert>
            </div>
            @endif

            <form method="POST" action="{{ route('login.attempt') }}" class="space-y-4" x-data="{ showPassword: false }">
                @csrf

                <x-input
                    name="email"
                    type="email"
                    label="Email"
                    placeholder="nama@seumpama.com"
                    required
                    autofocus
                    :value="old('email')" />

                <div>
                    <label for="password" class="block text-sm font-medium text-slate-700 mb-1">
                        Password <span class="text-red-500">*</span>
                    </label>
                    <div class="relative">
                        <input
                            :type="showPassword ? 'text' : 'password'"
                            id="password"
                            name="password"
                            required
                            class="w-full px-3 py-2 pr-10 rounded-lg border border-slate-300 text-sm transition-colors
                                   focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        <button
                            type="button"
                            @click="showPassword = !showPassword"
                            class="absolute right-2 top-1/2 -translate-y-1/2 p-1 rounded text-slate-400 hover:text-slate-600 focus:outline-none focus:text-slate-700"
                            aria-label="Toggle password visibility">
                            <svg x-show="!showPassword" xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M2 12s3-7 10-7 10 7 10 7-3 7-10 7-10-7-10-7Z" />
                                <circle cx="12" cy="12" r="3" />
                            </svg>
                            <svg x-show="showPassword" style="display: none;" xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M9.88 9.88a3 3 0 1 0 4.24 4.24" />
                                <path d="M10.73 5.08A10.43 10.43 0 0 1 12 5c7 0 10 7 10 7a13.16 13.16 0 0 1-1.67 2.68" />
                                <path d="M6.61 6.61A13.526 13.526 0 0 0 2 12s3 7 10 7a9.74 9.74 0 0 0 5.39-1.61" />
                                <line x1="2" x2="22" y1="2" y2="22" />
                            </svg>
                        </button>
                    </div>
                    @error('password')
                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div class="flex items-center">
                    <input type="checkbox" id="remember" name="remember"
                        class="h-4 w-4 text-blue-600 border-slate-300 rounded focus:ring-blue-500">
                    <label for="remember" class="ml-2 text-sm text-slate-600">Ingat saya</label>
                </div>

                <x-button type="submit" variant="primary" class="w-full">
                    Masuk
                </x-button>
            </form>
        </x-card>

        <p class="text-center text-xs text-slate-400 mt-6">
            &copy; {{ date('Y') }} Seumpama POS — Coffee Shop Seumpama Bunga
        </p>
    </div>
</body>

</html>
