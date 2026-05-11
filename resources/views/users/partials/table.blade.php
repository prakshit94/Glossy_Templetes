@if($users->hasPages())
    <div class="p-4 border-b border-border/40 flex justify-end items-center">
        {{ $users->links() }}
    </div>
@endif

<x-ui.table>
    <x-ui.table-header class="bg-muted/20">
        <x-ui.table-row class="border-b border-border/60">
            <x-ui.table-head class="w-10">
                <input type="checkbox" x-model="allSelected" @change="toggleAll" 
                    class="rounded border-border bg-background text-primary focus:ring-primary/20">
            </x-ui.table-head>
            <x-ui.table-head class="text-[10px] font-black uppercase tracking-widest text-muted-foreground/60">Member Identity</x-ui.table-head>
            <x-ui.table-head class="text-[10px] font-black uppercase tracking-widest text-muted-foreground/60">Account Status</x-ui.table-head>
            <x-ui.table-head class="text-[10px] font-black uppercase tracking-widest text-muted-foreground/60 text-center">Security Profile</x-ui.table-head>
            <x-ui.table-head class="text-[10px] font-black uppercase tracking-widest text-muted-foreground/60">Affiliation & Roles</x-ui.table-head>
            <x-ui.table-head class="text-[10px] font-black uppercase tracking-widest text-muted-foreground/60">Activity Ledger</x-ui.table-head>
            <x-ui.table-head class="text-right text-[10px] font-black uppercase tracking-widest text-muted-foreground/60">Actions</x-ui.table-head>
        </x-ui.table-row>
    </x-ui.table-header>
    <x-ui.table-body>
        @forelse($users as $user)
        @php
            $isOnline = $user->last_session_activity && $user->last_session_activity >= now()->subMinutes(5)->getTimestamp();
            $deviceUA = $user->last_session_ua;
            $isMobile = $deviceUA && preg_match('/(android|bb\d+|meego).+mobile|avantgo|bada\/|blackberry|blazer|compal|elaine|fennec|hiptop|iemobile|ip(hone|od)|iris|kindle|lge |maemo|midp|mmp|mobile.+firefox|netfront|opera m(ob|in)i|palm( os)?|phone|p(ixi|re)\/|plucker|pocket|psp|series(4|6)0|symbian|treo|up\.(browser|link)|vodafone|wap|windows ce|xda|xiino/i', $deviceUA);
        @endphp
        <x-ui.table-row x-bind:class="selectedUsers.includes({{ $user->id }}) ? 'bg-primary/5' : 'hover:bg-primary/[0.02] transition-colors'" class="border-b border-border/40 group">
            <!-- Selection -->
            <x-ui.table-cell>
                <input type="checkbox" name="user_ids[]" value="{{ $user->id }}" :checked="selectedUsers.includes({{ $user->id }})" @change="toggleUser({{ $user->id }})"
                    class="rounded border-border bg-background text-primary focus:ring-primary/20">
            </x-ui.table-cell>

            <!-- User Identity -->
            <x-ui.table-cell>
                <div class="flex items-center gap-4">
                    <div class="relative shrink-0">
                        <div class="size-12 rounded-2xl bg-gradient-to-br from-primary/20 to-primary/5 border border-primary/10 flex items-center justify-center font-black text-primary shadow-inner group-hover:scale-110 transition-transform duration-500 overflow-hidden">
                            @if($user->avatar)
                                <img src="{{ $user->avatar }}" class="size-full object-cover" alt="">
                            @else
                                <span class="text-lg">{{ strtoupper(substr($user->name, 0, 1)) }}</span>
                            @endif
                        </div>
                        @if($isOnline)
                            <div class="absolute -bottom-1 -right-1 size-4 bg-emerald-500 rounded-full border-2 border-background shadow-[0_0_10px_rgba(16,185,129,0.5)]">
                                <span class="absolute inset-0 rounded-full bg-emerald-400 animate-ping opacity-75"></span>
                            </div>
                        @endif
                    </div>
                    <div class="flex flex-col min-w-0">
                        <div class="flex items-center gap-2">
                            <span class="text-sm font-black tracking-tight text-foreground truncate uppercase">{{ $user->name }}</span>
                            <span class="text-[9px] font-mono font-bold text-muted-foreground/30">#{{ sprintf('%03d', $user->id) }}</span>
                        </div>
                        <span class="text-[11px] font-medium text-muted-foreground/60 truncate lowercase select-all italic">{{ $user->email }}</span>
                    </div>
                </div>
            </x-ui.table-cell>

            <!-- Status -->
            <x-ui.table-cell>
                @if($user->trashed())
                    <x-ui.badge variant="destructive" className="uppercase text-[9px] font-black tracking-[0.1em] px-2.5 py-1 rounded-lg border-red-500/20 bg-red-500/10 text-red-500 shadow-sm">Terminated</x-ui.badge>
                @else
                    <x-ui.badge variant="{{ $user->status === 'active' ? 'success' : 'destructive' }}" className="uppercase text-[9px] font-black tracking-[0.1em] px-2.5 py-1 rounded-lg border-emerald-500/20 bg-emerald-500/10 shadow-sm">
                        {{ $user->status }}
                    </x-ui.badge>
                @endif
            </x-ui.table-cell>

            <!-- Security Profile -->
            <x-ui.table-cell>
                <div class="flex flex-col items-center gap-2">
                    <div class="flex items-center gap-1.5">
                        <div class="size-6 rounded-lg flex items-center justify-center transition-colors {{ $user->two_factor_secret ? 'bg-blue-500/10 text-blue-500 border border-blue-500/20' : 'bg-muted/10 text-muted-foreground/20' }}">
                            <x-ui.icon name="shield" size="3.5" />
                        </div>
                        <div class="size-6 rounded-lg flex items-center justify-center transition-colors {{ $isOnline ? 'bg-emerald-500/10 text-emerald-500 border border-emerald-500/20' : 'bg-muted/10 text-muted-foreground/20' }}">
                            <x-ui.icon name="{{ $isMobile ? 'smartphone' : 'monitor' }}" size="3.5" />
                        </div>
                    </div>
                    @if($isOnline)
                        <span class="text-[8px] font-black text-emerald-500 uppercase tracking-widest animate-pulse">Live Now</span>
                    @endif
                </div>
            </x-ui.table-cell>

            <!-- Affiliation & Roles -->
            <x-ui.table-cell>
                <div class="flex flex-col gap-2 max-w-[180px]">
                    @if($user->currentTeam)
                        <div class="flex items-center gap-2 px-2 py-1 rounded-lg bg-orange-500/5 border border-orange-500/10 w-fit">
                            <x-ui.icon name="briefcase" size="3" class="text-orange-500" />
                            <span class="text-[10px] font-black text-orange-600/80 uppercase tracking-tighter">{{ $user->currentTeam->name }}</span>
                        </div>
                    @endif
                    <div class="flex flex-wrap gap-1.5">
                        @foreach($user->roles as $role)
                            <span class="text-[8px] font-black px-1.5 py-0.5 rounded-md bg-muted/20 border border-border/40 text-muted-foreground uppercase tracking-tight">{{ $role->name }}</span>
                        @endforeach
                    </div>
                </div>
            </x-ui.table-cell>

            <!-- Activity Ledger -->
            <x-ui.table-cell>
                <div class="flex flex-col gap-1">
                    <div class="flex items-center gap-1.5">
                        <x-ui.icon name="clock" size="3" class="text-muted-foreground/40" />
                        <span class="text-[10px] font-bold text-foreground/80 tracking-tight">{{ $user->last_login_at?->diffForHumans() ?? 'No Records' }}</span>
                    </div>
                    @if($user->last_login_ip)
                        <div class="flex items-center gap-1.5">
                            <x-ui.icon name="hash" size="3" class="text-muted-foreground/30" />
                            <span class="text-[9px] font-mono font-bold text-muted-foreground/40 tracking-tighter">{{ $user->last_login_ip }}</span>
                        </div>
                    @endif
                </div>
            </x-ui.table-cell>

            <!-- Actions -->
            <x-ui.table-cell class="text-right">
                <div class="flex justify-end gap-1.5">
                    @if($user->trashed())
                        <form action="{{ route('users.restore', $user->id) }}" method="POST">
                            @csrf
                            <x-ui.button variant="ghost" size="sm" type="submit" className="h-8 px-3 text-[10px] font-black uppercase tracking-widest text-emerald-600 hover:bg-emerald-500/10 rounded-xl border border-transparent hover:border-emerald-500/20">
                                Restore
                            </x-ui.button>
                        </form>
                        <form action="{{ route('users.force-delete', $user->id) }}" method="POST" onsubmit="return confirm('PERMANENTLY delete this user?')">
                            @csrf
                            @method('DELETE')
                            <x-ui.button variant="ghost" size="icon" type="submit" className="size-8 text-destructive hover:bg-destructive/10 rounded-xl border border-transparent hover:border-destructive/20">
                                <x-ui.icon name="trash-2" size="4" />
                            </x-ui.button>
                        </form>
                    @else
                        <a href="{{ route('users.edit', $user) }}">
                            <x-ui.button variant="ghost" size="icon" className="size-8 text-muted-foreground hover:text-primary hover:bg-primary/10 rounded-xl border border-transparent hover:border-primary/20 transition-all">
                                <x-ui.icon name="edit-3" size="4" />
                            </x-ui.button>
                        </a>
                        <form action="{{ route('users.destroy', $user) }}" method="POST" onsubmit="return confirm('Move this user to trash?')">
                            @csrf
                            @method('DELETE')
                            <x-ui.button variant="ghost" size="icon" type="submit" className="size-8 text-muted-foreground hover:text-destructive hover:bg-destructive/10 rounded-xl border border-transparent hover:border-destructive/20 transition-all">
                                <x-ui.icon name="trash" size="4" />
                            </x-ui.button>
                        </form>
                    @endif
                </div>
            </x-ui.table-cell>
        </x-ui.table-row>
        @empty
        <x-ui.table-row>
            <x-ui.table-cell colspan="7" class="h-60 text-center">
                <div class="flex flex-col items-center justify-center gap-4 opacity-30">
                    <x-ui.icon name="users" size="16" stroke-width="1" />
                    <p class="text-sm font-black uppercase tracking-[0.2em]">No personnel matching criteria</p>
                    <x-ui.button variant="outline" size="sm" onclick="location.reload()" class="rounded-xl border-border">Reset View</x-ui.button>
                </div>
            </x-ui.table-cell>
        </x-ui.table-row>
        @endforelse
    </x-ui.table-body>
</x-ui.table>

@if($users->hasPages())
    <div class="p-4 border-t border-border/40 bg-muted/5 flex justify-end items-center rounded-b-3xl">
        {{ $users->links() }}
    </div>
@endif
