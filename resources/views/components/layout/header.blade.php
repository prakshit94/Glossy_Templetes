@props(['hideSearch' => false, 'hideDashboardLink' => false, 'hideSidebarToggle' => false, 'pageTitle' => ''])
@php
    $activePageName = 'Dashboard';
    $activePageUrl = '/dashboard';
    $activeGroupName = 'Home';

    $sections = [
        'dashboard*' => ['Dashboard', '/dashboard', 'Home'],
        'users*'     => ['Users', '/users', 'System'],
        'roles*'     => ['Roles', '/roles', 'System'],
        'settings*'  => ['Settings', '/settings', 'System'],
        'profile*'   => ['Profile', '/profile', 'Account'],
        // Add more sections as needed for your project
    ];

    foreach ($sections as $pattern => $data) {
        if (request()->is($pattern)) {
            $activePageName = $data[0];
            $activePageUrl  = $data[1];
            $activeGroupName = $data[2] ?? 'Home';
            break;
        }
    }
@endphp
<header
    class="sticky top-0 z-40 flex h-20 w-full items-center justify-between border-b border-border/60 bg-background/70 px-6 backdrop-blur-3xl transition-all duration-500 ease-in-out shadow-[0_4px_30px_rgba(0,0,0,0.03)] group/header">

    <!-- Premium Ambient Glow -->
    <div class="absolute inset-0 z-[-1] overflow-hidden pointer-events-none">
        <div
            class="absolute top-0 left-1/4 w-[500px] h-full bg-primary/5 blur-[80px] opacity-50 transform -translate-y-1/2 rounded-full transition-opacity duration-700 group-hover/header:opacity-80">
        </div>
        <div
            class="absolute top-0 right-1/4 w-[400px] h-full bg-primary/10 blur-[100px] opacity-20 transform -translate-y-1/2 rounded-full transition-opacity duration-700 group-hover/header:opacity-40">
        </div>
    </div>

    <!-- Left Side: Nav & Branding -->
    <div class="flex items-center gap-6">
        <!-- Mobile Trigger -->
        <button @click="mobileMenuOpen = true" 
            class="md:hidden flex items-center justify-center rounded-2xl p-2.5 text-muted-foreground hover:bg-secondary/50 transition-all">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="size-6"><line x1="4" x2="20" y1="12" y2="12"/><line x1="4" x2="20" y1="6" y2="6"/><line x1="4" x2="20" y1="18" y2="18"/></svg>
        </button>

        @if(!$hideSidebarToggle)
            <button
                class="hidden md:flex items-center justify-center rounded-2xl p-2.5 text-muted-foreground hover:bg-secondary/40 hover:text-foreground hover:shadow-inner transition-all duration-300 active:scale-95 focus-visible:outline-none focus:ring-2 focus:ring-primary/20 backdrop-blur-md border border-transparent hover:border-border/70"
                @click="toggleSidebar()">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"
                    stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"
                    class="size-5 transition-transform duration-500 group-hover:rotate-180">
                    <path d="M4 6h16M4 12h16M14 18h6" />
                </svg>
                <span class="sr-only">Toggle Sidebar</span>
            </button>

            <div class="hidden md:block h-8 w-px bg-gradient-to-b from-transparent via-border/50 to-transparent"></div>
        @endif

        <!-- Premium Breadcrumbs -->
        <nav class="hidden md:flex items-center gap-2">
            <div
                class="flex items-center p-1 bg-secondary/20 rounded-xl border border-border/60 backdrop-blur-md shadow-sm">
                <a href="#"
                    class="flex items-center gap-2.5 px-3 py-1.5 rounded-lg text-xs font-bold uppercase tracking-wider text-muted-foreground hover:text-primary hover:bg-secondary/40 transition-all">
                    <div
                        class="size-2 rounded-full bg-primary animate-pulse shadow-[0_0_8px_rgba(var(--primary-rgb),0.5)]">
                    </div>
                    {{ $activeGroupName }}
                </a>

                <div class="px-1 opacity-20">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"
                        stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                        class="size-4">
                        <path d="m9 18 6-6-6-6" />
                    </svg>
                </div>

                @if($hideDashboardLink)
                    <span
                        class="group flex items-center gap-2 px-3 py-1.5 rounded-lg text-xs font-bold uppercase tracking-wider transition-all bg-primary text-primary-foreground shadow-lg shadow-primary/20">
                        {{ $pageTitle ?: $activePageName }}
                    </span>
                @else
                    <a href="{{ $activePageUrl }}"
                        class="group flex items-center gap-2 px-3 py-1.5 rounded-lg text-xs font-bold uppercase tracking-wider transition-all bg-primary text-primary-foreground shadow-lg shadow-primary/20">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"
                            stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                            class="size-3.5 transition-transform group-hover:scale-110">
                            <path d="m3 9 9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z" />
                            <polyline points="9 22 9 12 15 12 15 22" />
                        </svg>
                        {{ $activePageName }}
                    </a>
                @endif
            </div>
        </nav>
    </div>

    <!-- Right Side: Actions & User -->
    <div class="flex items-center gap-3 md:gap-6">
        
        <!-- Search -->
        <div x-data="{ searchOpen: false }">
            <button @click="searchOpen = true"
                class="group flex items-center justify-center rounded-2xl p-2.5 text-muted-foreground hover:bg-secondary/40 hover:text-foreground transition-all">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="size-5"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/></svg>
            </button>

            <!-- Generic Search Modal -->
            <template x-teleport="body">
                <div x-show="searchOpen" x-cloak x-transition.opacity
                    class="fixed inset-0 z-[9999] flex items-start justify-center pt-20 bg-background/80 backdrop-blur-sm p-4"
                    @keydown.escape.window="searchOpen = false">
                    <div class="bg-card w-full max-w-2xl rounded-[32px] shadow-2xl border border-border/60 overflow-hidden flex flex-col animate-in slide-in-from-top-4 duration-300"
                        @click.away="searchOpen = false">
                        <div class="p-4 border-b border-border/50">
                            <div class="relative flex items-center">
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="absolute left-4 size-5 text-muted-foreground"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/></svg>
                                <input type="text" placeholder="Search anything (Cmd+K)..." 
                                    class="w-full pl-12 pr-4 py-3 bg-secondary/30 rounded-2xl border border-border/50 focus:outline-none focus:ring-2 focus:ring-primary/50 transition-all">
                            </div>
                        </div>
                        <div class="p-8 text-center text-muted-foreground">
                            <p class="text-sm">No recent searches found.</p>
                        </div>
                    </div>
                </div>
            </template>
        </div>

        <!-- Premium Action Group -->
        <div
            class="flex items-center gap-1.5 p-1 bg-secondary/20 border border-border/60 rounded-2xl shadow-inner backdrop-blur-md">
            <!-- Theme Toggle -->
            <x-layout.theme-toggle />

            <!-- Notifications Bell -->
            @php
                $unreadCount = auth()->check() ? \Spatie\Activitylog\Models\Activity::where('created_at', '>', auth()->user()->last_activity_read_at ?? '2000-01-01')->count() : 0;
            @endphp
            <div class="relative" x-data="{ open: false, count: {{ $unreadCount }} }" @click.away="open = false">
                <button @click="open = !open"
                    class="group relative inline-flex items-center justify-center rounded-xl size-10 text-muted-foreground hover:bg-secondary/40 hover:text-primary transition-all duration-300 active:scale-90">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"
                        stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"
                        class="size-5 relative z-10 transition-all group-hover:rotate-[15deg] group-hover:scale-110 active:scale-95">
                        <path d="M6 8a6 0 0 1 12 0c0 7 3 9 3 9H3s3-2 3-9" />
                        <path d="M10.3 21a1.94 1.94 0 0 0 3.4 0" />
                    </svg>
                    <!-- Unread Badge -->
                    <template x-if="count > 0">
                        <span class="absolute -top-1 -right-1 min-w-[18px] h-[18px] flex items-center justify-center px-1 rounded-full bg-red-500 border-2 border-background z-20 text-[9px] font-bold text-white shadow-sm">
                            <span x-text="count > 99 ? '99+' : count"></span>
                        </span>
                    </template>
                </button>

                <!-- Notifications Dropdown -->
                <div x-show="open" x-cloak x-transition:enter="transition ease-out duration-300"
                    x-transition:enter-start="opacity-0 translate-y-4 scale-[0.98]"
                    x-transition:enter-end="opacity-100 translate-y-0 scale-100"
                    class="absolute right-0 mt-3 w-80 sm:w-96 rounded-3xl border border-border/70 bg-popover/95 backdrop-blur-2xl shadow-[0_20px_60px_-15px_rgba(0,0,0,0.3)] z-50 overflow-hidden ring-1 ring-black/5 dark:ring-white/10">

                    <div class="flex items-center justify-between px-6 py-4 border-b border-border/50 bg-secondary/20">
                        <div class="flex items-center gap-2">
                            <h3 class="text-xs font-black uppercase tracking-widest">Recent Activity</h3>
                            <template x-if="count > 0">
                                <span class="text-[9px] font-bold bg-primary/10 text-primary px-2 py-0.5 rounded-full"><span x-text="count"></span> New</span>
                            </template>
                        </div>
                        <template x-if="count > 0">
                            <button @click.prevent="fetch('{{ route('activities.read') }}', { method: 'POST', headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content } }).then(() => count = 0);"
                                class="text-[9px] font-bold text-muted-foreground hover:text-primary transition-colors uppercase tracking-widest">
                                Mark as read
                            </button>
                        </template>
                    </div>

                    @php
                        $recentActivities = \Spatie\Activitylog\Models\Activity::latest()->take(5)->get();
                        $lastReadAt = auth()->check() ? auth()->user()->last_activity_read_at : null;
                    @endphp

                    @if($recentActivities->isNotEmpty())
                        <div class="max-h-[300px] overflow-y-auto">
                            @foreach($recentActivities as $activity)
                                @php
                                    $isUnread = $lastReadAt === null || $activity->created_at > $lastReadAt;
                                @endphp
                                <div @if($isUnread) 
                                        @click="fetch('{{ route('activities.read') }}', { method: 'POST', headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content } }).then(() => { $el.classList.remove('bg-primary/5', 'dark:bg-primary/10'); $el.querySelector('.unread-indicator')?.remove(); count = Math.max(0, count - 1); })"
                                     @endif
                                     class="flex p-4 border-b border-border/40 last:border-0 hover:bg-muted/30 transition-colors text-left relative cursor-pointer {{ $isUnread ? 'bg-primary/5 dark:bg-primary/10' : '' }}">
                                    @if($isUnread)
                                        <div class="unread-indicator absolute left-1.5 top-1/2 -translate-y-1/2 size-1.5 rounded-full bg-primary animate-pulse"></div>
                                    @endif
                                    <div class="shrink-0 mr-3 mt-1 ml-1">
                                        @php
                                            $icon = match($activity->event) {
                                                'created' => 'plus-circle',
                                                'updated' => 'edit',
                                                'deleted' => 'trash-2',
                                                'restored' => 'refresh-cw',
                                                default => 'info'
                                            };
                                            $color = match($activity->event) {
                                                'created' => 'text-emerald-600 dark:text-emerald-400 bg-emerald-500/10',
                                                'updated' => 'text-primary bg-primary/10',
                                                'deleted' => 'text-destructive bg-destructive/10',
                                                'restored' => 'text-amber-600 dark:text-amber-400 bg-amber-500/10',
                                                default => 'text-muted-foreground bg-muted'
                                            };
                                        @endphp
                                        <div class="size-8 rounded-full flex items-center justify-center {{ $color }}">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="size-4">
                                                @if($icon === 'plus-circle') <circle cx="12" cy="12" r="10"/><path d="M8 12h8"/><path d="M12 8v8"/>
                                                @elseif($icon === 'edit') <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/>
                                                @elseif($icon === 'trash-2') <polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/><line x1="10" y1="11" x2="10" y2="17"/><line x1="14" y1="11" x2="14" y2="17"/>
                                                @elseif($icon === 'refresh-cw') <polyline points="23 4 23 10 17 10"/><polyline points="1 20 1 14 7 14"/><path d="M3.51 9a9 9 0 0 1 14.85-3.36L23 10M1 14l4.64 4.36A9 9 0 0 0 20.49 15"/>
                                                @else <circle cx="12" cy="12" r="10"/><line x1="12" y1="16" x2="12" y2="12"/><line x1="12" y1="8" x2="12.01" y2="8"/>
                                                @endif
                                            </svg>
                                        </div>
                                    </div>
                                    <div class="flex-grow">
                                        <div class="flex justify-between items-start mb-1">
                                            <p class="text-[11px] font-medium leading-tight">
                                                @if($activity->causer)
                                                    <span class="font-bold text-primary">{{ $activity->causer->name }}</span>
                                                @else
                                                    <span class="font-bold text-muted-foreground">System</span>
                                                @endif
                                                {{ $activity->description }}
                                                @if($activity->subject)
                                                    <span class="font-bold">- {{ class_basename($activity->subject_type) }} #{{ $activity->subject_id }}</span>
                                                @endif
                                            </p>
                                        </div>
                                        <div class="text-[9px] text-muted-foreground mt-1">{{ $activity->created_at->diffForHumans() }}</div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                        <div class="p-3 border-t border-border/50 bg-muted/10 text-center">
                            <a href="{{ route('activities.index') }}" class="text-[10px] font-bold text-primary uppercase tracking-widest hover:underline">View All Activity</a>
                        </div>
                    @else
                        <div class="flex flex-col items-center justify-center py-12 text-center text-muted-foreground">
                            <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24" fill="none"
                                stroke="currentColor" stroke-width="1.5" class="mb-3 opacity-40">
                                <path d="M6 8a6 0 0 1 12 0c0 7 3 9 3 9H3s3-2 3-9" />
                                <path d="M10.3 21a1.94 1.94 0 0 0 3.4 0" />
                            </svg>
                            <p class="text-sm font-medium">No recent activity</p>
                            <p class="text-xs opacity-60 mt-1">System events will appear here.</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- User Dropdown -->
        <x-layout.user-dropdown />
    </div>
</header>
