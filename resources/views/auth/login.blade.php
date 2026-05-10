<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Sign In - {{ config('app.name', 'Laravel Starter') }}</title>

    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="h-full font-[Outfit] bg-[#020617] text-white overflow-hidden">

<div class="flex min-h-screen">

    <!-- LEFT SIDE (BRANDING) -->
    <div class="hidden lg:flex w-1/2 relative items-center justify-center overflow-hidden">

        <!-- Premium Gradient -->
        <div class="absolute inset-0 bg-gradient-to-br from-indigo-900 via-purple-700 to-pink-600"></div>

        <!-- Multi Glow System -->
        <div class="absolute w-[700px] h-[700px] bg-blue-300/20 blur-[180px] rounded-full"></div>
        <div class="absolute w-[500px] h-[500px] bg-purple-400/20 blur-[160px] rounded-full animate-pulse"></div>

        <!-- Content -->
        <div class="relative z-10 px-12 text-white max-w-xl">

            <!-- Brand -->
            <h1 class="text-5xl font-extrabold tracking-tight mb-6 leading-tight">
                Premium Starter 🚀
            </h1>

            <!-- Tagline -->
            <p class="text-lg text-white/90 leading-relaxed">
                A high-end Laravel 12 theme template with dark mode, multiple color themes, and a modern UI components library.
            </p>

            <!-- Feature Cards -->
            <div class="mt-12 grid grid-cols-2 gap-4 text-sm">
                <div class="bg-white/5 border border-white/10 rounded-xl p-4 backdrop-blur hover:bg-white/10 transition">
                    🎨 Tailwind CSS 4
                </div>
                <div class="bg-white/5 border border-white/10 rounded-xl p-4 backdrop-blur hover:bg-white/10 transition">
                    ⚡ Alpine.js
                </div>
                <div class="bg-white/5 border border-white/10 rounded-xl p-4 backdrop-blur hover:bg-white/10 transition">
                    🌙 Dark/Light Modes
                </div>
                <div class="bg-white/5 border border-white/10 rounded-xl p-4 backdrop-blur hover:bg-white/10 transition">
                    🌈 Multi-Color Themes
                </div>
            </div>

            <!-- Footer -->
            <div class="mt-12 text-xs text-white/60 tracking-widest uppercase">
                Modern Stack • Premium Design ✨
            </div>

        </div>
    </div>


    <!-- RIGHT SIDE (LOGIN) -->
    <div class="flex w-full lg:w-1/2 items-center justify-center px-6 py-12 relative">

        <!-- Background Glow -->
        <div class="absolute inset-0">
            <div class="absolute top-[-10%] left-[-10%] w-[400px] h-[400px] bg-blue-500/20 blur-[150px] rounded-full"></div>
            <div class="absolute bottom-[-10%] right-[-10%] w-[400px] h-[400px] bg-purple-500/20 blur-[150px] rounded-full"></div>
        </div>

        <!-- Notifications -->
        <div class="absolute top-6 left-1/2 -translate-x-1/2 w-full max-w-md px-4 space-y-4 z-50">
            @if ($errors->any())
                <div class="rounded-2xl bg-red-500/10 p-4 border border-red-500/20 backdrop-blur-xl">
                    @foreach ($errors->all() as $error)
                        <p class="text-sm text-red-400">{{ $error }}</p>
                    @endforeach
                </div>
            @endif

            @if (session('status'))
                <div class="rounded-2xl bg-green-500/10 p-4 border border-green-500/20 backdrop-blur-xl">
                    <p class="text-sm text-green-400">{{ session('status') }}</p>
                </div>
            @endif
        </div>

        <!-- Card -->
        <div class="relative z-10 w-full max-w-md 
                    bg-white/[0.07] backdrop-blur-2xl 
                    border border-white/10 
                    rounded-3xl p-8 
                    shadow-[0_30px_100px_rgba(0,0,0,0.7)]">

            <!-- Logo -->
            <div class="flex justify-center mb-6">
                <div class="h-16 w-16 rounded-2xl bg-gradient-to-tr from-indigo-500 to-purple-500 flex items-center justify-center shadow-xl shadow-indigo-500/40">
                    <span class="text-xl">🚀</span>
                </div>
            </div>

            <!-- Title -->
            <h2 class="text-2xl font-semibold text-center mb-1">Welcome Back</h2>
            <p class="text-center text-sm text-gray-400 mb-6">Access your account</p>

            <!-- Form -->
            <form class="space-y-5" action="{{ route('login') }}" method="POST" 
                  x-data="{ 
                      email: localStorage.getItem('remembered_email') || '',
                      remember: localStorage.getItem('remembered_email') ? true : false,
                      saveEmail() {
                          if (this.remember) {
                              localStorage.setItem('remembered_email', this.email);
                          } else {
                              localStorage.removeItem('remembered_email');
                          }
                      }
                  }" 
                  @submit="saveEmail()">
                @csrf

                <!-- Email -->
                <div>
                    <label for="email" class="text-sm text-gray-300 cursor-pointer">Email</label>
                    <input type="email" id="email" name="email" x-model="email" required autofocus
                        autocomplete="username"
                        class="mt-2 w-full px-4 py-3 rounded-xl bg-white/5 border border-white/10 focus:border-indigo-400 focus:ring-2 focus:ring-indigo-400/20 outline-none transition">
                    @error('email')
                        <p class="text-xs text-red-400 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Password -->
                <div>
                    <label for="password" class="text-sm text-gray-300 cursor-pointer">Password</label>
                    <input type="password" id="password" name="password" required
                        autocomplete="current-password"
                        class="mt-2 w-full px-4 py-3 rounded-xl bg-white/5 border border-white/10 focus:border-indigo-400 focus:ring-2 focus:ring-indigo-400/20 outline-none transition">
                </div>

                <!-- Remember -->
                <div class="flex items-center justify-between">
                    <label for="remember" class="flex items-center gap-2 text-sm text-gray-400 cursor-pointer">
                        <input type="checkbox" id="remember" name="remember" value="1" x-model="remember"
                            class="rounded bg-white/10 border-white/20 text-indigo-500 focus:ring-offset-0 focus:ring-indigo-500/20">
                        Remember me
                    </label>
                </div>

                <!-- Button -->
                <button type="submit"
                    class="w-full py-3 rounded-xl bg-gradient-to-r from-indigo-500 via-purple-500 to-pink-500 font-semibold shadow-lg shadow-indigo-500/40 hover:scale-[1.03] active:scale-95 transition-all">
                    Sign in
                </button>
            </form>

        </div>

        <!-- Footer -->
        <p class="absolute bottom-6 text-xs text-gray-500 text-center w-full">
            Secure • Scalable • Built for Innovation 🚀
        </p>

    </div>

</div>

</body>
</html>
