@props(['isMobile' => false])
<aside
    class="{{ $isMobile ? 'flex flex-col h-full bg-transparent' : 'fixed inset-y-0 left-0 z-50 flex flex-col border-r border-sidebar-border/50 bg-sidebar/95 backdrop-blur-2xl transition-all duration-300 ease-[cubic-bezier(0.2,0,0,1)] md:translate-x-0 group/sidebar shadow-2xl shadow-primary/5 dark:shadow-black/50' }}"
    :class="{
        'w-72': !sidebarCollapsed || {{ $isMobile ? 'true' : 'false' }},
        'w-[4.5rem]': sidebarCollapsed && !{{ $isMobile ? 'true' : 'false' }},
        '-translate-x-full': !mobileMenuOpen && !{{ $isMobile ? 'true' : 'false' }} && window.innerWidth < 768,
        'translate-x-0': mobileMenuOpen && !{{ $isMobile ? 'true' : 'false' }} && window.innerWidth < 768
    }" @click.away="mobileMenuOpen = false">
    <!-- Logo Area -->
    <div
        class="h-20 flex items-center px-5 border-b border-sidebar-border/50 relative overflow-hidden shrink-0 group/logo">
        <div
            class="absolute inset-0 bg-gradient-to-r from-primary/10 via-primary/5 to-transparent opacity-0 group-hover/logo:opacity-100 transition-opacity duration-700">
        </div>

        <a href="{{ url('/dashboard') }}" class="flex items-center gap-3.5 relative z-10 w-full"
            :class="sidebarCollapsed && !{{ $isMobile ? 'true' : 'false' }} ? 'justify-center' : ''">
            <div
                class="h-10 w-10 min-w-10 rounded-xl bg-gradient-to-br from-primary via-indigo-500 to-violet-600 flex items-center justify-center shadow-lg shadow-primary/25 shrink-0 transition-all duration-500 group-hover/logo:scale-110 group-hover/logo:rotate-3 ring-1 ring-white/10">
                <!-- App Logo Icon -->
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                    stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"
                    class="w-5 h-5 text-white drop-shadow-md">
                    <path d="M15 6v12a3 3 0 1 0 3-3H6a3 3 0 1 0 3 3V6a3 3 0 1 0-3 3h12a3 3 0 1 0-3-3" />
                </svg>
            </div>
            <div class="flex flex-col overflow-hidden transition-all duration-500 ease-out"
                :class="sidebarCollapsed && !{{ $isMobile ? 'true' : 'false' }} ? 'w-0 opacity-0 absolute translate-x-10' : 'w-auto opacity-100 translate-x-0'">

                <!-- App Name -->
                <span
                    class="font-heading font-bold text-lg tracking-tight leading-none text-foreground group-hover/logo:text-primary transition-colors duration-300 whitespace-nowrap">
                    {{ config('app.name') }}
                </span>

                <!-- Subtitle -->
                <span
                    class="text-[10px] font-bold text-muted-foreground uppercase tracking-[0.25em] mt-1 pl-0.5 whitespace-nowrap">
                    {{ auth()->check() ? 'Welcome, ' . explode(' ', auth()->user()->name)[0] : 'Premium Template' }}
                </span>

            </div>
        </a>
    </div>

    <!-- Content -->
    <div class="flex-1 overflow-y-auto custom-scrollbar py-6 px-3 space-y-8">

        <!-- SECTION: OVERVIEW -->
        <div class="space-y-1">
            <div class="px-3 mb-2 transition-opacity duration-300"
                :class="sidebarCollapsed ? 'opacity-0 h-0 hidden' : 'opacity-100'">
                <h3 class="text-[10px] font-extrabold uppercase tracking-widest text-muted-foreground/60">
                    Overview
                </h3>
            </div>

            <x-layout.nav-link title="Dashboard" url="/dashboard" :active="request()->is('dashboard')">
                <x-slot name="icon">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"
                        stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"
                        class="size-5">
                        <rect width="7" height="9" x="3" y="3" rx="1" />
                        <rect width="7" height="5" x="14" y="3" rx="1" />
                        <rect width="7" height="9" x="14" y="12" rx="1" />
                        <rect width="7" height="5" x="3" y="16" rx="1" />
                    </svg>
                </x-slot>
            </x-layout.nav-link>
        </div>

        <!-- SECTION: MAIN CONTENT (customize for your project) -->
        <div class="space-y-1">
            <div class="px-3 mb-2 transition-opacity duration-300"
                :class="sidebarCollapsed ? 'opacity-0 h-0 hidden' : 'opacity-100'">
                <h3 class="text-[10px] font-extrabold uppercase tracking-widest text-muted-foreground/60">
                    Management
                </h3>
            </div>

            @php
                $allManagementItems = [
                    [
                        'title' => 'Users',
                        'url'   => '/users',
                        'active' => request()->is('users*'),
                        'permission' => 'users.view',
                        'icon'  => '<svg xmlns=\'http://www.w3.org/2000/svg\' width=\'24\' height=\'24\' viewBox=\'0 0 24 24\' fill=\'none\' stroke=\'currentColor\' stroke-width=\'1.5\' stroke-linecap=\'round\' stroke-linejoin=\'round\' class=\'size-4\'><path d=\'M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2\'/><circle cx=\'9\' cy=\'7\' r=\'4\'/><path d=\'M22 21v-2a4 4 0 0 0-3-3.87\'/><path d=\'M16 3.13a4 4 0 0 1 0 7.75\'/></svg>',
                    ],
                    [
                        'title' => 'Teams',
                        'url'   => '/teams',
                        'active' => request()->is('teams*'),
                        'permission' => 'teams.view',
                        'icon'  => '<svg xmlns=\'http://www.w3.org/2000/svg\' width=\'24\' height=\'24\' viewBox=\'0 0 24 24\' fill=\'none\' stroke=\'currentColor\' stroke-width=\'1.5\' stroke-linecap=\'round\' stroke-linejoin=\'round\' class=\'size-4\'><path d=\'M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2\'/><circle cx=\'9\' cy=\'7\' r=\'4\'/><path d=\'M23 21v-2a4 4 0 0 0-3-3.87\'/><path d=\'M17 3.13a4 4 0 0 1 0 7.75\'/></svg>',
                    ],
                    [
                        'title' => 'Roles',
                        'url'   => '/roles',
                        'active' => request()->is('roles*'),
                        'permission' => 'roles.view',
                        'icon'  => '<svg xmlns=\'http://www.w3.org/2000/svg\' width=\'24\' height=\'24\' viewBox=\'0 0 24 24\' fill=\'none\' stroke=\'currentColor\' stroke-width=\'1.5\' stroke-linecap=\'round\' stroke-linejoin=\'round\' class=\'size-4\'><rect width=\'18\' height=\'18\' x=\'3\' y=\'3\' rx=\'2\'/><path d=\'M9 3v18\'/></svg>',
                    ],
                    [
                        'title' => 'Permissions',
                        'url'   => '/permissions',
                        'active' => request()->is('permissions*'),
                        'permission' => 'permissions.view',
                        'icon'  => '<svg xmlns=\'http://www.w3.org/2000/svg\' width=\'24\' height=\'24\' viewBox=\'0 0 24 24\' fill=\'none\' stroke=\'currentColor\' stroke-width=\'1.5\' stroke-linecap=\'round\' stroke-linejoin=\'round\' class=\'size-4\'><path d=\'M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10\'/></svg>',
                    ],
                    [
                        'title' => 'System Activity',
                        'url'   => '/activities',
                        'active' => request()->is('activities*'),
                        'permission' => 'audit.view',
                        'icon'  => '<svg xmlns=\'http://www.w3.org/2000/svg\' width=\'24\' height=\'24\' viewBox=\'0 0 24 24\' fill=\'none\' stroke=\'currentColor\' stroke-width=\'1.5\' stroke-linecap=\'round\' stroke-linejoin=\'round\' class=\'size-4\'><path d=\'M12 20h9\'/><path d=\'M16.5 3.5a2.12 2.12 0 0 1 3 3L7 19l-4 1 1-4Z\'/></svg>',
                    ],
                ];

                $managementItems = array_filter($allManagementItems, function($item) {
                    return auth()->user()->can($item['permission']);
                });
            @endphp

            @if(count($managementItems) > 0)
                <x-layout.nav-collapsible title="Access Control" :active="request()->is('users*') || request()->is('roles*') || request()->is('permissions*') || request()->is('activities*')" :items="$managementItems">
                    <x-slot name="icon">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"
                            stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"
                            class="size-5">
                            <rect width="18" height="11" x="3" y="11" rx="2" ry="2" />
                            <path d="M7 11V7a5 5 0 0 1 10 0v4" />
                        </svg>
                    </x-slot>
                </x-layout.nav-collapsible>
            @endif
        </div>

        <!-- SECTION: LOGISTICS -->
        <div class="space-y-1">
            @if(auth()->user()->can('villages.view') || auth()->user()->can('services.view'))
            <div class="px-3 mb-2 transition-opacity duration-300"
                :class="sidebarCollapsed ? 'opacity-0 h-0 hidden' : 'opacity-100'">
                <h3 class="text-[10px] font-extrabold uppercase tracking-widest text-muted-foreground/60">
                    Logistics
                </h3>
            </div>
            @endif

            @can('villages.view')
            <x-layout.nav-link title="Villages" url="/villages" :active="request()->is('villages*')">
                <x-slot name="icon">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"
                        stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"
                        class="size-5">
                        <path d="M20 10c0 6-8 12-8 12s-8-6-8-12a8 8 0 0 1 16 0Z" />
                        <circle cx="12" cy="10" r="3" />
                    </svg>
                </x-slot>
            </x-layout.nav-link>
            @endcan

            @can('services.view')
            <x-layout.nav-link title="Services" url="/services" :active="request()->is('services*')">
                <x-slot name="icon">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"
                        stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"
                        class="size-5">
                        <path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z" />
                        <polyline points="3.29 7 12 12 20.71 7" />
                        <line x1="12" y1="22" x2="12" y2="12" />
                    </svg>
                </x-slot>
            </x-layout.nav-link>
            @endcan
        </div>

        <!-- SECTION: SYSTEM -->
        @can('settings.view')
        <div class="space-y-1">
            <div class="px-3 mb-2 transition-opacity duration-300"
                :class="sidebarCollapsed ? 'opacity-0 h-0 hidden' : 'opacity-100'">
                <h3 class="text-[10px] font-extrabold uppercase tracking-widest text-muted-foreground/60">
                    System
                </h3>
            </div>

            <x-layout.nav-link title="Settings" url="/settings" :active="request()->is('settings*')">
                <x-slot name="icon">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"
                        stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"
                        class="size-5">
                        <path
                            d="M12.22 2h-.44a2 2 0 0 0-2 2v.18a2 2 0 0 1-1 1.73l-.43.25a2 2 0 0 1-2 0l-.15-.08a2 2 0 0 0-2.73.73l-.22.38a2 2 0 0 0 .73 2.73l.15.1a2 2 0 0 1 1 1.72v.51a2 2 0 0 1-1 1.74l-.15.09a2 2 0 0 0-.73 2.73l.22.38a2 2 0 0 0 2.73.73l.15-.08a2 2 0 0 1 2 0l.43.25a2 2 0 0 1 1 1.72V20a2 2 0 0 0 2 2h.44a2 2 0 0 0 2-2v-.17a2 2 0 0 1 1-1.74l.43-.25a2 2 0 0 1 2 0l.15.08a2 2 0 0 0 2.73-.73l.22-.39a2 2 0 0 0-.73-2.73l-.15-.08a2 2 0 0 1-1-1.74v-.5a2 2 0 0 1 1-1.74l.15-.09a2 2 0 0 0 .73-2.73l-.22-.38a2 2 0 0 0-2.73-.73l-.15.08a2 2 0 0 1-2 0l-.43-.25a2 2 0 0 1-1-1.73V4a2 2 0 0 0-2-2z" />
                        <circle cx="12" cy="12" r="3" />
                    </svg>
                </x-slot>
            </x-layout.nav-link>
        </div>
        @endcan

    </div>

</aside>
