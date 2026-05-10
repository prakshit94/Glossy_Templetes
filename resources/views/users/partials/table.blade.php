@if($users->hasPages())
    <div class="p-4 border-b border-border/40 flex justify-end items-center">
        {{ $users->links() }}
    </div>
@endif

<x-ui.table>
    <x-ui.table-header class="bg-muted/30">
        <x-ui.table-row>
            <x-ui.table-head class="w-10">
                <input type="checkbox" x-model="allSelected" @change="toggleAll" 
                    class="rounded border-border bg-background text-primary focus:ring-primary/20">
            </x-ui.table-head>
            <x-ui.table-head class="w-12 text-left">#</x-ui.table-head>
            <x-ui.table-head>User</x-ui.table-head>
            <x-ui.table-head>Email</x-ui.table-head>
            <x-ui.table-head>Status</x-ui.table-head>
            <x-ui.table-head class="text-left">2FA</x-ui.table-head>
            <x-ui.table-head class="text-left">Presence</x-ui.table-head>
            <x-ui.table-head>Roles</x-ui.table-head>
            <x-ui.table-head>Team</x-ui.table-head>
            <x-ui.table-head>Activity</x-ui.table-head>
            <x-ui.table-head class="text-right">Actions</x-ui.table-head>
        </x-ui.table-row>
    </x-ui.table-header>
    <x-ui.table-body>
        @forelse($users as $user)
        @php
            $isOnline = $user->last_session_activity && $user->last_session_activity >= now()->subMinutes(5)->getTimestamp();
            $deviceUA = $user->last_session_ua;
            $isMobile = $deviceUA && preg_match('/(android|bb\d+|meego).+mobile|avantgo|bada\/|blackberry|blazer|compal|elaine|fennec|hiptop|iemobile|ip(hone|od)|iris|kindle|lge |maemo|midp|mmp|mobile.+firefox|netfront|opera m(ob|in)i|palm( os)?|phone|p(ixi|re)\/|plucker|pocket|psp|series(4|6)0|symbian|treo|up\.(browser|link)|vodafone|wap|windows ce|xda|xiino/i', $deviceUA);
        @endphp
        <x-ui.table-row x-bind:class="selectedUsers.includes({{ $user->id }}) ? 'bg-primary/5' : 'hover:bg-muted/20 transition-colors'">
            <!-- Selection -->
            <x-ui.table-cell>
                <input type="checkbox" name="user_ids[]" value="{{ $user->id }}" :checked="selectedUsers.includes({{ $user->id }})" @change="toggleUser({{ $user->id }})"
                    class="rounded border-border bg-background text-primary focus:ring-primary/20">
            </x-ui.table-cell>

            <!-- Index -->
            <x-ui.table-cell class="text-left">
                <span class="text-[10px] font-mono font-medium text-muted-foreground/70">
                    {{ sprintf('%03d', ($users->currentPage() - 1) * $users->perPage() + $loop->iteration) }}
                </span>
            </x-ui.table-cell>

            <!-- User -->
            <x-ui.table-cell>
                <div class="flex items-center gap-3">
                    <div class="relative group">
                        <div class="size-10 rounded-2xl bg-gradient-to-br from-primary/20 to-primary/5 border border-primary/10 flex items-center justify-center font-bold text-primary shadow-sm group-hover:scale-105 transition-transform">
                            @if($user->avatar)
                                <img src="{{ $user->avatar }}" class="size-full object-cover rounded-2xl" alt="">
                            @else
                                {{ strtoupper(substr($user->name, 0, 1)) }}
                            @endif
                        </div>
                        @if($isOnline)
                            <div class="absolute -bottom-0.5 -right-0.5 size-3 bg-emerald-500 rounded-full border-2 border-background shadow-sm shadow-emerald-500/40">
                                <span class="absolute inset-0 rounded-full bg-emerald-400 animate-ping opacity-75"></span>
                            </div>
                        @endif
                    </div>
                    <div class="flex flex-col">
                        <span class="text-sm font-semibold tracking-tight text-foreground">{{ $user->name }}</span>
                        <span class="text-[10px] font-mono text-muted-foreground italic">@ {{ $user->username }}</span>
                    </div>
                </div>
            </x-ui.table-cell>

            <!-- Email -->
            <x-ui.table-cell>
                <span class="text-xs text-muted-foreground select-all">{{ $user->email }}</span>
            </x-ui.table-cell>

            <!-- Status -->
            <x-ui.table-cell>
                @if($user->trashed())
                    <x-ui.badge variant="destructive" className="uppercase text-[9px] font-black tracking-widest px-2 py-0.5 rounded-lg border-red-500/20 bg-red-500/10 text-red-500">Deleted</x-ui.badge>
                @else
                    <x-ui.badge variant="{{ $user->status === 'active' ? 'success' : 'destructive' }}" className="uppercase text-[9px] font-black tracking-widest px-2 py-0.5 rounded-lg">
                        {{ $user->status }}
                    </x-ui.badge>
                @endif
            </x-ui.table-cell>

            <!-- 2FA -->
            <x-ui.table-cell class="text-left">
                @if($user->two_factor_secret)
                    <div class="flex justify-start" title="2FA Enabled">
                        <x-ui.icon name="shield" size="4" class="text-blue-500 drop-shadow-[0_0_8px_rgba(59,130,246,0.3)]" />
                    </div>
                @else
                    <span class="text-[10px] text-muted-foreground/20 italic">-</span>
                @endif
            </x-ui.table-cell>

            <!-- Presence -->
            <x-ui.table-cell class="text-left">
                <div class="flex flex-col items-start gap-1">
                    @if($isOnline)
                        <div class="flex items-center gap-1.5 px-2 py-1 bg-emerald-500/10 border border-emerald-500/20 rounded-full">
                            @if($isMobile)
                                <x-ui.icon name="smartphone" size="3" class="text-emerald-500" />
                            @else
                                <x-ui.icon name="monitor" size="3" class="text-emerald-500" />
                            @endif
                            <span class="text-[8px] font-black text-emerald-600 uppercase tracking-tighter">Live</span>
                        </div>
                    @else
                        <span class="text-[9px] text-muted-foreground/30 font-medium">Offline</span>
                    @endif
                </div>
            </x-ui.table-cell>

            <!-- Roles -->
            <x-ui.table-cell>
                <div class="flex flex-wrap gap-1 max-w-[150px]">
                    @foreach($user->roles as $role)
                        <x-ui.badge variant="outline" className="text-[9px] px-1.5 py-0 rounded-md border-primary/20 bg-primary/5 text-primary">{{ $role->name }}</x-ui.badge>
                    @endforeach
                </div>
            </x-ui.table-cell>

            <!-- Team -->
            <x-ui.table-cell>
                @if($user->currentTeam)
                    <div class="flex items-center gap-1.5">
                        <div class="size-5 rounded bg-orange-500/10 border border-orange-500/20 flex items-center justify-center text-[8px] font-black text-orange-600">
                            {{ strtoupper(substr($user->currentTeam->name, 0, 1)) }}
                        </div>
                        <span class="text-xs text-muted-foreground">{{ $user->currentTeam->name }}</span>
                    </div>
                @else
                    <span class="text-[10px] text-muted-foreground/50 italic">Personal</span>
                @endif
            </x-ui.table-cell>

            <!-- Activity -->
            <x-ui.table-cell>
                <div class="flex flex-col">
                    <span class="text-[10px] font-semibold text-foreground/80">{{ $user->last_login_at?->diffForHumans() ?? 'Never' }}</span>
                    @if($user->last_login_ip)
                        <span class="text-[9px] font-mono text-muted-foreground/40">{{ $user->last_login_ip }}</span>
                    @endif
                </div>
            </x-ui.table-cell>

            <!-- Actions -->
            <x-ui.table-cell class="text-right">
                <div class="flex justify-end gap-1">
                    @if($user->trashed())
                        <form action="{{ route('users.restore', $user->id) }}" method="POST">
                            @csrf
                            <x-ui.button variant="ghost" size="sm" type="submit" className="h-8 px-2 text-[10px] font-bold text-green-600 hover:bg-green-500/10">
                                Restore
                            </x-ui.button>
                        </form>
                        <form action="{{ route('users.force-delete', $user->id) }}" method="POST" onsubmit="return confirm('Permanently delete this user?')">
                            @csrf
                            @method('DELETE')
                            <x-ui.button variant="ghost" size="icon" type="submit" className="size-8 text-red-500 hover:bg-red-500/10">
                                <x-ui.icon name="trash-2" size="3.5" />
                            </x-ui.button>
                        </form>
                    @else
                        <a href="{{ route('users.edit', $user) }}">
                            <x-ui.button variant="ghost" size="icon" className="size-8 text-muted-foreground hover:text-primary hover:bg-primary/5">
                                <x-ui.icon name="edit" size="3.5" />
                            </x-ui.button>
                        </a>
                        <form action="{{ route('users.destroy', $user) }}" method="POST" onsubmit="return confirm('Are you sure you want to move this user to trash?')">
                            @csrf
                            @method('DELETE')
                            <x-ui.button variant="ghost" size="icon" type="submit" className="size-8 text-muted-foreground hover:text-red-500 hover:bg-red-500/5">
                                <x-ui.icon name="trash" size="3.5" />
                            </x-ui.button>
                        </form>
                    @endif
                </div>
            </x-ui.table-cell>
        </x-ui.table-row>
        @empty
        <x-ui.table-row>
            <x-ui.table-cell colspan="11" class="h-40 text-center">
                <div class="flex flex-col items-center justify-center gap-2 opacity-50">
                    <x-ui.icon name="users" size="10" />
                    <p class="text-sm">No users found in your organization</p>
                </div>
            </x-ui.table-cell>
        </x-ui.table-row>
        @endforelse
    </x-ui.table-body>
</x-ui.table>

@if($users->hasPages())
    <div class="p-4 border-t border-border/40 bg-muted/5 flex justify-end items-center">
        {{ $users->links() }}
    </div>
@endif
