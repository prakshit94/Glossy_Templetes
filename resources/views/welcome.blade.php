<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name', 'Laravel') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-background text-foreground">
    <div class="min-h-screen flex flex-col items-center justify-center px-6 py-10">
        <div class="w-full max-w-5xl app-surface overflow-hidden">
            <div class="grid grid-cols-1 lg:grid-cols-2">
                <div class="p-10 lg:p-14 space-y-6 bg-card/20">
                    <div class="size-12 rounded-2xl bg-primary/10 border border-primary/20 text-primary flex items-center justify-center shadow-inner">
                        <x-ui.icon name="rocket" size="6" />
                    </div>
                    <h1 class="text-4xl font-black tracking-tight">{{ config('app.name', 'Laravel Starter') }}</h1>
                    <p class="text-muted-foreground leading-relaxed">
                        Unified, professional Blade UI with consistent components, day/night support, and production-ready module workflows.
                    </p>
                    <div class="grid grid-cols-2 gap-3 text-xs font-semibold">
                        <div class="rounded-xl border border-border/60 bg-background/40 p-3">Theme-ready UI</div>
                        <div class="rounded-xl border border-border/60 bg-background/40 p-3">Inventory modules</div>
                        <div class="rounded-xl border border-border/60 bg-background/40 p-3">Responsive layout</div>
                        <div class="rounded-xl border border-border/60 bg-background/40 p-3">Component library</div>
                    </div>
                </div>
                <div class="p-10 lg:p-14 border-t lg:border-t-0 lg:border-l border-border/40 flex flex-col justify-center gap-5">
                    <h2 class="text-2xl font-black tracking-tight">Get Started</h2>
                    <p class="text-sm text-muted-foreground">Open the dashboard to continue.</p>
                    <div class="flex gap-3">
                        @auth
                            <a href="{{ url('/dashboard') }}">
                                <x-ui.button class="rounded-xl px-6">Dashboard</x-ui.button>
                            </a>
                        @else
                            @if (Route::has('login'))
                                <a href="{{ route('login') }}">
                                    <x-ui.button class="rounded-xl px-6">Log in</x-ui.button>
                                </a>
                            @endif
                            @if (Route::has('register'))
                                <a href="{{ route('register') }}">
                                    <x-ui.button variant="outline" class="rounded-xl px-6">Register</x-ui.button>
                                </a>
                            @endif
                        @endauth
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
