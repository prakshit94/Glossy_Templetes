@php
    $hideSidebar = $hideSidebar ?? false;
    $hideHeaderSearch = $hideHeaderSearch ?? false;
    $hideDashboardLink = $hideDashboardLink ?? false;
    $pageTitle = $pageTitle ?? '';
@endphp
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" x-data="{ 
      sidebarCollapsed: localStorage.getItem('sidebarCollapsed') === 'true',
      mobileMenuOpen: false,
      theme: localStorage.getItem('theme') || 'system',
      colorTheme: localStorage.getItem('colorTheme') || 'zinc',
      
      init() {
          this.$watch('theme', val => localStorage.setItem('theme', val));
          this.$watch('colorTheme', val => localStorage.setItem('colorTheme', val));
          
          window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', e => {
              if (this.theme === 'system') {
                  this.theme = 'system'; 
              }
          });
      },
      
      get isDark() {
          return this.theme === 'dark' || (this.theme === 'system' && window.matchMedia('(prefers-color-scheme: dark)').matches);
      },
      
      toggleSidebar() {
          this.sidebarCollapsed = !this.sidebarCollapsed;
          localStorage.setItem('sidebarCollapsed', this.sidebarCollapsed);
      }
  }" :class="[
      isDark ? 'dark' : '', 
      colorTheme !== 'zinc' ? 'theme-' + colorTheme : ''
  ]">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>
        {{ config('app.name', 'Laravel') }}{{ $pageTitle ? ' - ' . $pageTitle : '' }}
    </title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap"
        rel="stylesheet">

    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        [x-cloak] {
            display: none !important;
        }

        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            scrollbar-gutter: stable;
        }

        h1, h2, h3, h4, .font-heading {
            font-family: 'Outfit', sans-serif;
        }

        /* Custom Scrollbar */
        ::-webkit-scrollbar { width: 6px; height: 6px; }
        ::-webkit-scrollbar-track { background: transparent; }
        ::-webkit-scrollbar-thumb {
            background-color: rgba(161, 161, 170, 0.2);
            border-radius: 9999px;
            transition: background-color 0.3s;
        }
        ::-webkit-scrollbar-thumb:hover { background-color: rgba(161, 161, 170, 0.4); }

        ::selection {
            background-color: rgba(var(--primary), 0.2);
            color: rgb(var(--primary));
        }

        html { scroll-behavior: smooth; }
    </style>
</head>

<body
    class="min-h-svh w-full bg-background text-foreground antialiased overflow-x-hidden selection:bg-primary/20 selection:text-primary transition-colors duration-300">

    <!-- Global Background Pattern -->
    <div class="fixed inset-0 z-[-1] bg-[#fafafa] dark:bg-[#09090b]">
        <div
            class="absolute inset-0 bg-[linear-gradient(to_right,#80808008_1px,transparent_1px),linear-gradient(to_bottom,#80808008_1px,transparent_1px)] bg-[size:24px_24px] [mask-image:radial-gradient(ellipse_60%_50%_at_50%_0%,#000_70%,transparent_100%)]">
        </div>
    </div>

    <!-- Mobile Menu Overlay -->
    <div x-show="mobileMenuOpen" 
         x-cloak
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed inset-0 z-[100] bg-zinc-950/60 backdrop-blur-sm md:hidden"
         @click="mobileMenuOpen = false">
    </div>

    <!-- Mobile Sidebar -->
    <div x-show="mobileMenuOpen" 
         x-cloak
         x-transition:enter="transition ease-out duration-500"
         x-transition:enter-start="-translate-x-full"
         x-transition:enter-end="translate-x-0"
         x-transition:leave="transition ease-in duration-300"
         x-transition:leave-start="translate-x-0"
         x-transition:leave-end="-translate-x-full"
         class="fixed inset-y-0 left-0 z-[101] w-[280px] bg-background border-r border-border shadow-2xl md:hidden overflow-hidden flex flex-col">
        
        <div class="h-20 flex items-center justify-between px-6 border-b border-border/50">
            <span class="font-heading font-black text-xl tracking-tight text-primary">{{ config('app.name') }}</span>
            <button @click="mobileMenuOpen = false" class="p-2 rounded-xl bg-secondary/50 text-muted-foreground">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="size-5"><path d="M18 6 6 18"/><path d="m6 6 12 12"/></svg>
            </button>
        </div>

        <div class="flex-1 overflow-y-auto p-4">
             <x-layout.app-sidebar :is-mobile="true" />
        </div>
    </div>

    <x-ui.toaster />

    <!-- Sidebar Component (Desktop) -->
    @if(!$hideSidebar)
        <div class="hidden md:block">
            <x-layout.app-sidebar />
        </div>
    @endif

    <!-- Main Content Wrapper -->
    <div class="relative min-h-svh flex flex-col transition-all duration-300 ease-in-out {{ $hideSidebar ? '' : 'md:pl-72' }}"
        :class="sidebarCollapsed ? '{{ $hideSidebar ? '' : 'md:!pl-[4.5rem]' }}' : ''">

        <!-- Header (Sticky) -->
        <x-layout.header :hide-search="$hideHeaderSearch" :hide-dashboard-link="$hideDashboardLink"
            :hide-sidebar-toggle="$hideSidebar" :page-title="$pageTitle" />

        <!-- Page Content -->
        <main class="flex-1 w-full max-w-full relative overflow-hidden">
            <div class="relative flex-1 flex flex-col min-h-full">
                @isset($slot)
                    {{ $slot }}
                @else
                    @yield('content')
                @endisset
            </div>
        </main>

        <!-- Footer -->
        <footer class="py-6 px-8 text-center text-xs text-muted-foreground border-t border-border/40 mt-auto">
            <p>&copy; {{ date('Y') }} {{ config('app.name') }}. All rights reserved.</p>
        </footer>
    </div>

    @include('partials.chat_widget')

</body>

</html>
