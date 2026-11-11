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

            <form method="POST" action="{{ route('login.attempt') }}" class="space-y-4">
                @csrf

                <x-input
                    name="email"
                    type="email"
                    label="Email"
                    placeholder="nama@seumpama.com"
                    required
                    autofocus
                    :value="old('email')" />

                <x-password-input
                    name="password"
                    label="Password"
                    required />

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
