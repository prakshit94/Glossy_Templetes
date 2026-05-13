@if($users->hasPages())
    <div class="p-4 border-b border-border/40 bg-muted/10 flex justify-end items-center">
        {{ $users->links() }}
    </div>
@endif

<div class="relative">
    <div class="pointer-events-none absolute inset-x-8 top-0 h-px bg-gradient-to-r from-transparent via-primary/15 to-transparent hidden sm:block"></div>

    <x-ui.table>
        <x-ui.table-header class="bg-muted/30">
            <x-ui.table-row class="border-b border-border/60">
                <x-ui.table-head class="w-12 pl-5">
                    <span class="sr-only">Select row</span>
                    <input type="checkbox" x-model="allSelected" @change="toggleAll"
                        class="rounded-md border-border bg-background text-primary focus:ring-primary/25 shadow-sm">
                </x-ui.table-head>
                <x-ui.table-head class="text-[10px] font-black uppercase tracking-widest text-muted-foreground/70">Member Identity</x-ui.table-head>
                <x-ui.table-head class="text-[10px] font-black uppercase tracking-widest text-muted-foreground/70">Account Status</x-ui.table-head>
                <x-ui.table-head class="text-[10px] font-black uppercase tracking-widest text-muted-foreground/70 text-center">Security Profile</x-ui.table-head>
                <x-ui.table-head class="text-[10px] font-black uppercase tracking-widest text-muted-foreground/70">Affiliation & Roles</x-ui.table-head>
                <x-ui.table-head class="text-[10px] font-black uppercase tracking-widest text-muted-foreground/70">Activity Ledger</x-ui.table-head>
                <x-ui.table-head class="text-right text-[10px] font-black uppercase tracking-widest text-muted-foreground/70 pr-5">Actions</x-ui.table-head>
            </x-ui.table-row>
        </x-ui.table-header>
        <x-ui.table-body>
            @forelse($users as $user)
            @php
                $isOnline = $user->last_session_activity && $user->last_session_activity >= now()->subMinutes(5)->getTimestamp();
                $deviceUA = $user->last_session_ua;
                $isMobile = $deviceUA && preg_match('/(android|bb\d+|meego).+mobile|avantgo|bada\/|blackberry|blazer|compal|elaine|fennec|hiptop|iemobile|ip(hone|od)|iris|kindle|lge |maemo|midp|mmp|mobile.+firefox|netfront|opera m(ob|in)i|palm( os)?|phone|p(ixi|re)\/|plucker|pocket|psp|series(4|6)0|symbian|treo|up\.(browser|link)|vodafone|wap|windows ce|xda|xiino/i', $deviceUA);
            @endphp
            <x-ui.table-row
                x-bind:class="selectedUsers.includes({{ $user->id }}) ? 'bg-primary/[0.06] ring-1 ring-inset ring-primary/15' : 'hover:bg-primary/[0.03]'"
                class="border-b border-border/40 group/row transition-colors duration-200">
                <x-ui.table-cell class="pl-5 align-middle">
                    <input type="checkbox" name="user_ids[]" value="{{ $user->id }}" :checked="selectedUsers.includes({{ $user->id }})" @change="toggleUser({{ $user->id }})"
                        class="rounded-md border-border bg-background text-primary focus:ring-primary/25 shadow-sm">
                </x-ui.table-cell>

                <x-ui.table-cell class="align-middle">
                    <div class="flex items-center gap-4 py-0.5">
                        <div class="relative shrink-0">
                            <div class="size-12 rounded-2xl bg-gradient-to-br from-primary/25 to-primary/5 border border-primary/15 flex items-center justify-center font-black text-primary shadow-inner ring-1 ring-primary/10 group-hover/row:scale-[1.02] transition-transform duration-300 overflow-hidden">
                                @if($user->avatar)
                                    <img src="{{ $user->avatar }}" class="size-full object-cover" alt="">
                                @else
                                    <span class="text-lg">{{ strtoupper(substr($user->name, 0, 1)) }}</span>
                                @endif
                            </div>
                            @if($isOnline)
                                <div class="absolute -bottom-0.5 -right-0.5 size-4 bg-emerald-500 rounded-full border-2 border-background shadow-[0_0_12px_rgba(16,185,129,0.45)]">
                                    <span class="absolute inset-0 rounded-full bg-emerald-400 animate-ping opacity-75"></span>
                                </div>
                            @endif
                        </div>
                        <div class="flex flex-col min-w-0">
                            <div class="flex items-center gap-2 flex-wrap">
                                <span class="text-sm font-black tracking-tight text-foreground truncate uppercase">{{ $user->name }}</span>
                                <span class="text-[9px] font-mono font-bold text-muted-foreground/35 tabular-nums">#{{ sprintf('%03d', $user->id) }}</span>
                            </div>
                            <span class="text-[11px] font-medium text-muted-foreground/65 truncate lowercase select-all">{{ $user->email }}</span>
                        </div>
                    </div>
                </x-ui.table-cell>

                <x-ui.table-cell class="align-middle">
                    @if($user->trashed())
                        <x-ui.badge variant="destructive" className="uppercase text-[9px] font-black tracking-[0.12em] px-2.5 py-1 rounded-lg shadow-sm ring-1 ring-destructive/30">
                            Terminated
                        </x-ui.badge>
                    @else
                        @php
                            $statusKey = strtolower((string) $user->status);
                            $statusVariant = match ($statusKey) {
                                'active' => 'success',
                                'suspended' => 'warning',
                                default => 'outline',
                            };
                        @endphp
                        <x-ui.badge :variant="$statusVariant" className="uppercase text-[9px] font-black tracking-[0.12em] px-2.5 py-1 rounded-lg shadow-sm ring-1 ring-black/5 dark:ring-white/10">
                            {{ $user->status }}
                        </x-ui.badge>
                    @endif
                </x-ui.table-cell>

                <x-ui.table-cell class="align-middle">
                    <div class="flex flex-col items-center gap-2">
                        <div class="flex items-center gap-1.5">
                            <div class="size-8 rounded-xl flex items-center justify-center transition-all duration-300 {{ $user->two_factor_secret ? 'bg-blue-500/12 text-blue-600 dark:text-blue-400 border border-blue-500/30 shadow-sm' : 'bg-muted/20 text-muted-foreground border border-border/50' }}">
                                <x-ui.icon name="shield" size="4" class="shrink-0" />
                            </div>
                            <div class="size-8 rounded-xl flex items-center justify-center transition-all duration-300 {{ $isOnline ? 'bg-emerald-500/12 text-emerald-600 dark:text-emerald-400 border border-emerald-500/30 shadow-sm' : 'bg-muted/20 text-muted-foreground border border-border/50' }}">
                                @php $deviceIcon = $isMobile ? 'smartphone' : 'monitor'; @endphp
                                <x-ui.icon :name="$deviceIcon" size="4" class="shrink-0" />
                            </div>
                        </div>
                        @if($isOnline)
                            <span class="text-[8px] font-black text-emerald-500 uppercase tracking-widest">Live</span>
                        @endif
                    </div>
                </x-ui.table-cell>

                <x-ui.table-cell class="align-middle">
                    <div class="flex flex-col gap-2 max-w-[200px]">
                        @if($user->currentTeam)
                            <div class="flex items-center gap-2 px-2.5 py-1 rounded-xl bg-orange-500/[0.07] border border-orange-500/15 w-fit">
                                <x-ui.icon name="briefcase" size="3" class="text-orange-500 shrink-0" />
                                <span class="text-[10px] font-black text-orange-600/85 uppercase tracking-tight truncate max-w-[140px]">{{ $user->currentTeam->name }}</span>
                            </div>
                        @endif
                        <div class="flex flex-wrap gap-1.5">
                            @foreach($user->roles as $role)
                                <span class="text-[8px] font-black px-2 py-0.5 rounded-md bg-muted/25 border border-border/50 text-muted-foreground uppercase tracking-tight">{{ $role->name }}</span>
                            @endforeach
                        </div>
                    </div>
                </x-ui.table-cell>

                <x-ui.table-cell class="align-middle">
                    <div class="flex flex-col gap-1">
                        <div class="flex items-center gap-1.5">
                            <x-ui.icon name="clock" size="4" class="text-muted-foreground shrink-0 opacity-80" />
                            <span class="text-[10px] font-bold text-foreground/85 tracking-tight">{{ $user->last_login_at?->diffForHumans() ?? 'No records' }}</span>
                        </div>
                        @if($user->last_login_ip)
                            <div class="flex items-center gap-1.5">
                                <x-ui.icon name="hash" size="4" class="text-muted-foreground shrink-0 opacity-70" />
                                <span class="text-[9px] font-mono font-semibold text-muted-foreground/45 tracking-tight">{{ $user->last_login_ip }}</span>
                            </div>
                        @endif
                    </div>
                </x-ui.table-cell>

                <x-ui.table-cell class="text-right align-middle pr-5">
                    <div class="flex justify-end gap-1">
                        @if($user->trashed())
                            <form action="{{ route('users.restore', $user->id) }}" method="POST">
                                @csrf
                                <x-ui.button variant="ghost" size="sm" type="submit" className="h-9 px-3 text-[10px] font-black uppercase tracking-widest text-emerald-600 hover:bg-emerald-500/10 rounded-xl border border-transparent hover:border-emerald-500/25">
                                    Restore
                                </x-ui.button>
                            </form>
                            <form action="{{ route('users.force-delete', $user->id) }}" method="POST" onsubmit="return confirm('PERMANENTLY delete this user?')">
                                @csrf
                                @method('DELETE')
                                <x-ui.button variant="ghost" size="icon" type="submit" className="size-9 text-destructive hover:bg-destructive/10 rounded-xl border border-transparent hover:border-destructive/25">
                                    <x-ui.icon name="trash-2" size="4" />
                                </x-ui.button>
                            </form>
                        @else
                            <a href="{{ route('users.edit', $user) }}">
                                <x-ui.button variant="ghost" size="icon" className="size-9 text-muted-foreground hover:text-primary hover:bg-primary/10 rounded-xl border border-transparent hover:border-primary/20 transition-all">
                                    <x-ui.icon name="edit-3" size="4" />
                                </x-ui.button>
                            </a>
                            <form action="{{ route('users.destroy', $user) }}" method="POST" onsubmit="return confirm('Move this user to trash?')" class="inline">
                                @csrf
                                @method('DELETE')
                                <x-ui.button variant="ghost" size="icon" type="submit" className="size-9 text-muted-foreground hover:text-destructive hover:bg-destructive/10 rounded-xl border border-transparent hover:border-destructive/25 transition-all">
                                    <x-ui.icon name="trash" size="4" />
                                </x-ui.button>
                            </form>
                        @endif
                    </div>
                </x-ui.table-cell>
            </x-ui.table-row>
            @empty
            <x-ui.table-row>
                <x-ui.table-cell colspan="7" class="h-72 text-center align-middle p-0">
                    <div class="flex flex-col items-center justify-center gap-5 py-12 px-6">
                        <div class="size-24 rounded-3xl bg-gradient-to-br from-primary/25 via-primary/8 to-transparent border border-primary/20 flex items-center justify-center text-primary shadow-inner ring-1 ring-primary/10">
                            <x-ui.icon name="users" size="12" />
                        </div>
                        <div class="space-y-2 max-w-md text-center">
                            <p class="text-sm font-black uppercase tracking-[0.2em] text-foreground">No personnel matching criteria</p>
                            <p class="text-[11px] text-muted-foreground font-medium leading-relaxed">Try another search, clear filters, or switch between Active and Archived.</p>
                        </div>
                        <x-ui.button variant="outline" size="sm" onclick="location.reload()" class="rounded-xl border-border/60 font-bold uppercase tracking-widest text-[10px] h-10 px-6">
                            Reset view
                        </x-ui.button>
                    </div>
                </x-ui.table-cell>
            </x-ui.table-row>
            @endforelse
        </x-ui.table-body>
    </x-ui.table>
</div>

@if($users->hasPages())
    <div class="p-4 border-t border-border/40 bg-muted/10 flex justify-end items-center rounded-b-3xl">
        {{ $users->links() }}
    </div>
@endif
