@if($users->hasPages())
    <div class="p-4 border-b border-border/40">
        {{ $users->links() }}
    </div>
@endif

<x-ui.table>
    <x-ui.table-header>
        <x-ui.table-row>
            <x-ui.table-head class="w-10">
                <input type="checkbox" x-model="allSelected" @change="toggleAll" 
                    class="rounded border-border bg-background text-primary focus:ring-primary/20">
            </x-ui.table-head>
            <x-ui.table-head>User Details</x-ui.table-head>
            <x-ui.table-head>Username</x-ui.table-head>
            <x-ui.table-head>Status & Auth</x-ui.table-head>
            <x-ui.table-head>Roles</x-ui.table-head>
            <x-ui.table-head>Team</x-ui.table-head>
            <x-ui.table-head>Last Activity</x-ui.table-head>
            <x-ui.table-head className="text-right">Actions</x-ui.table-head>
        </x-ui.table-row>
    </x-ui.table-header>
    <x-ui.table-body>
        @forelse($users as $user)
        <x-ui.table-row x-bind:class="selectedUsers.includes({{ $user->id }}) ? 'bg-primary/5' : ''">
            <x-ui.table-cell>
                <input type="checkbox" name="user_ids[]" value="{{ $user->id }}" :checked="selectedUsers.includes({{ $user->id }})" @change="toggleUser({{ $user->id }})"
                    class="rounded border-border bg-background text-primary focus:ring-primary/20">
            </x-ui.table-cell>
            <x-ui.table-cell>
                <div class="flex items-center gap-3">
                    <div class="relative">
                        <div class="size-10 rounded-xl bg-primary/10 flex items-center justify-center font-bold text-primary">
                            {{ substr($user->name, 0, 2) }}
                        </div>
                        @if($user->email_verified_at)
                            <div class="absolute -top-1 -right-1 size-4 bg-green-500 rounded-full border-2 border-background flex items-center justify-center" title="Verified">
                                <x-ui.icon name="check" size="2" class="text-white" />
                            </div>
                        @endif
                    </div>
                    <div class="flex flex-col">
                        <span class="font-bold">{{ $user->name }}</span>
                        <span class="text-[10px] text-muted-foreground">{{ $user->email }}</span>
                    </div>
                </div>
            </x-ui.table-cell>
            <x-ui.table-cell>
                <code class="text-[10px] font-mono px-1.5 py-0.5 bg-muted rounded">@ {{ $user->username }}</code>
            </x-ui.table-cell>
            <x-ui.table-cell>
                <div class="flex flex-col gap-1.5">
                    @if($user->trashed())
                        <x-ui.badge variant="destructive" className="uppercase text-[9px] tracking-widest w-fit bg-red-500/10 text-red-500">Deleted</x-ui.badge>
                    @else
                        <x-ui.badge variant="{{ $user->status === 'active' ? 'success' : 'destructive' }}" className="uppercase text-[9px] tracking-widest w-fit">
                            {{ $user->status }}
                        </x-ui.badge>
                    @endif
                    <div class="flex gap-1">
                        @if($user->two_factor_secret)
                            <x-ui.badge variant="outline" className="text-[8px] border-blue-500/30 text-blue-500 bg-blue-500/5 px-1 py-0">2FA</x-ui.badge>
                        @endif
                        @if($user->email_verified_at)
                            <x-ui.badge variant="outline" className="text-[8px] border-green-500/30 text-green-500 bg-green-500/5 px-1 py-0">V</x-ui.badge>
                        @endif
                    </div>
                </div>
            </x-ui.table-cell>
            <x-ui.table-cell>
                <div class="flex flex-wrap gap-1 max-w-[150px]">
                    @foreach($user->roles as $role)
                        <x-ui.badge variant="outline" className="text-[9px] px-1">{{ $role->name }}</x-ui.badge>
                    @endforeach
                </div>
            </x-ui.table-cell>
            <x-ui.table-cell>
                @if($user->currentTeam)
                    <x-ui.badge variant="outline" className="text-[9px] border-orange-500/30 text-orange-600 bg-orange-500/5">{{ $user->currentTeam->name }}</x-ui.badge>
                @else
                    <span class="text-[10px] text-muted-foreground italic">None</span>
                @endif
            </x-ui.table-cell>
            <x-ui.table-cell>
                <div class="flex flex-col">
                    <span class="text-xs text-muted-foreground">{{ $user->last_login_at?->diffForHumans() ?? 'Never' }}</span>
                    @if($user->last_login_ip)
                        <span class="text-[9px] font-mono text-muted-foreground/60">{{ $user->last_login_ip }}</span>
                    @endif
                </div>
            </x-ui.table-cell>
            <x-ui.table-cell className="text-right">
                <div class="flex justify-end gap-2">
                    @if($user->trashed())
                        <form action="{{ route('users.restore', $user->id) }}" method="POST">
                            @csrf
                            <x-ui.button variant="outline" size="sm" type="submit" className="h-8 px-2 text-xs font-bold text-green-600 hover:bg-green-500/10 hover:text-green-600">
                                Restore
                            </x-ui.button>
                        </form>
                        <form action="{{ route('users.force-delete', $user->id) }}" method="POST" onsubmit="return confirm('Permanently delete this user?')">
                            @csrf
                            @method('DELETE')
                            <x-ui.button variant="ghost" size="icon" type="submit" className="size-8 text-destructive">
                                <x-ui.icon name="trash-2" size="3" />
                            </x-ui.button>
                        </form>
                    @else
                        <a href="{{ route('users.edit', $user) }}">
                            <x-ui.button variant="ghost" size="icon" className="size-8">
                                <x-ui.icon name="edit" size="3" />
                            </x-ui.button>
                        </a>
                        <form action="{{ route('users.destroy', $user) }}" method="POST" onsubmit="return confirm('Are you sure you want to move this user to trash?')">
                            @csrf
                            @method('DELETE')
                            <x-ui.button variant="ghost" size="icon" type="submit" className="size-8 text-destructive">
                                <x-ui.icon name="trash" size="3" />
                            </x-ui.button>
                        </form>
                    @endif
                </div>
            </x-ui.table-cell>
        </x-ui.table-row>
        @empty
        <x-ui.table-row>
            <x-ui.table-cell colspan="7" class="h-24 text-center">
                No users found.
            </x-ui.table-cell>
        </x-ui.table-row>
        @endforelse
    </x-ui.table-body>
</x-ui.table>

@if($users->hasPages())
    <div class="p-4 border-t border-border/40">
        {{ $users->links() }}
    </div>
@endif
