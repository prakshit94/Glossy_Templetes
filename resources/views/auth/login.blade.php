<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Sign In - {{ config('app.name', 'Laravel Starter') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="h-full bg-background text-foreground antialiased">
    <div class="min-h-screen grid grid-cols-1 lg:grid-cols-2">
        <div class="hidden lg:flex relative overflow-hidden border-r border-border/60 bg-card/30 backdrop-blur-2xl">
            <div class="absolute inset-0 bg-[radial-gradient(circle_at_top_right,rgba(var(--primary),0.15),transparent_60%)]"></div>
            <div class="relative z-10 flex flex-col justify-center p-14">
                <div class="size-14 rounded-2xl bg-primary/10 border border-primary/20 text-primary flex items-center justify-center shadow-inner mb-6">
                    <span class="text-2xl">N</span>
                </div>
                <h1 class="text-4xl font-black tracking-tight mb-4">{{ config('app.name', 'Laravel Starter') }}</h1>
                <p class="text-muted-foreground text-base leading-relaxed max-w-lg">
                    Professional operations suite with unified theme support, responsive modules, and reliable day/night usability.
                </p>
                <div class="mt-10 grid grid-cols-2 gap-4 text-xs">
                    <div class="rounded-2xl border border-border/60 bg-background/40 p-4">Unified components</div>
                    <div class="rounded-2xl border border-border/60 bg-background/40 p-4">Dark/light optimized</div>
                    <div class="rounded-2xl border border-border/60 bg-background/40 p-4">Inventory ready</div>
                    <div class="rounded-2xl border border-border/60 bg-background/40 p-4">Enterprise UI polish</div>
                </div>
            </div>
        </div>

        <div class="relative flex items-center justify-center p-6 lg:p-12">
            <div class="w-full max-w-md app-surface p-8">
                <div class="text-center mb-8">
                    <h2 class="text-2xl font-black tracking-tight">Welcome Back</h2>
                    <p class="text-sm text-muted-foreground mt-1">Sign in to continue</p>
                </div>

                @if ($errors->any())
                    <div class="rounded-2xl border border-destructive/30 bg-destructive/10 p-4 mb-5">
                        @foreach ($errors->all() as $error)
                            <p class="text-sm text-destructive">{{ $error }}</p>
                        @endforeach
                    </div>
                @endif

                @if (session('status'))
                    <div class="rounded-2xl border border-emerald-500/30 bg-emerald-500/10 p-4 mb-5">
                        <p class="text-sm text-emerald-600 dark:text-emerald-400">{{ session('status') }}</p>
                    </div>
                @endif

                <form class="space-y-5" action="{{ route('login') }}" method="POST"
                      x-data="{ email: localStorage.getItem('remembered_email') || '', remember: localStorage.getItem('remembered_email') ? true : false, saveEmail() { if (this.remember) { localStorage.setItem('remembered_email', this.email); } else { localStorage.removeItem('remembered_email'); } } }"
                      @submit="saveEmail()">
                    @csrf

                    <div>
                        <label for="email" class="app-label">Email</label>
                        <input type="email" id="email" name="email" x-model="email" required autofocus autocomplete="username"
                               class="mt-2 app-form-control h-12 px-4">
                    </div>

                    <div>
                        <label for="password" class="app-label">Password</label>
                        <input type="password" id="password" name="password" required autocomplete="current-password"
                               class="mt-2 app-form-control h-12 px-4">
                    </div>

                    <label for="remember" class="flex items-center gap-2 text-sm text-muted-foreground cursor-pointer">
                        <input type="checkbox" id="remember" name="remember" value="1" x-model="remember"
                               class="rounded border-input bg-background text-primary focus:ring-primary/20">
                        Remember me
                    </label>

                    <x-ui.button type="submit" class="w-full h-12 rounded-2xl font-black uppercase tracking-widest text-[10px]">
                        Sign in
                    </x-ui.button>
                </form>
            </div>
        </div>
    </div>
</body>
</html>
