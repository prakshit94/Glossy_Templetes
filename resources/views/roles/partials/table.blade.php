@if($roles->hasPages())
    <div class="p-4 border-b border-border/40 bg-muted/10 flex justify-end items-center">
        {{ $roles->links() }}
    </div>
@endif

<div class="relative overflow-x-auto">
    <div class="pointer-events-none absolute inset-x-8 top-0 h-px bg-gradient-to-r from-transparent via-primary/15 to-transparent hidden sm:block"></div>

    <x-ui.table>
        <x-ui.table-header class="bg-muted/30">
            <x-ui.table-row class="border-b border-border/60">
                <x-ui.table-head class="w-12 pl-5">
                    <span class="sr-only">Select row</span>
                    <input type="checkbox" x-model="allSelected" @change="toggleAll"
                        class="rounded-md border-border bg-background text-primary focus:ring-primary/25 shadow-sm">
                </x-ui.table-head>
                <x-ui.table-head class="text-[10px] font-black uppercase tracking-widest text-muted-foreground/70">Role Identity</x-ui.table-head>
                <x-ui.table-head class="text-[10px] font-black uppercase tracking-widest text-muted-foreground/70">Permission Scope</x-ui.table-head>
                <x-ui.table-head class="text-[10px] font-black uppercase tracking-widest text-muted-foreground/70 text-center">Users</x-ui.table-head>
                <x-ui.table-head class="text-right text-[10px] font-black uppercase tracking-widest text-muted-foreground/70 pr-5">Actions</x-ui.table-head>
            </x-ui.table-row>
        </x-ui.table-header>
        <x-ui.table-body>
            @forelse($roles as $role)
                @php
                    $isProtected = $role->name === 'Super Admin';
                    $permissionCount = $role->permissions->count();
                @endphp
                <x-ui.table-row
                    x-bind:class="selectedRoles.includes({{ $role->id }}) ? 'bg-primary/[0.06] ring-1 ring-inset ring-primary/15' : 'hover:bg-primary/[0.03]'"
                    class="border-b border-border/40 group/row transition-colors duration-200">
                    <x-ui.table-cell class="pl-5 align-middle">
                        @if($isProtected)
                            <span class="inline-flex size-4 items-center justify-center rounded-md border border-border/60 bg-muted/30 text-muted-foreground/50">
                                <x-ui.icon name="lock" size="2.5" />
                            </span>
                        @else
                            <input type="checkbox" name="role_ids[]" value="{{ $role->id }}" :checked="selectedRoles.includes({{ $role->id }})" @change="toggleRole({{ $role->id }})"
                                class="rounded-md border-border bg-background text-primary focus:ring-primary/25 shadow-sm">
                        @endif
                    </x-ui.table-cell>

                    <x-ui.table-cell class="align-middle">
                        <div class="flex items-center gap-4 py-1">
                            <div class="size-12 rounded-2xl bg-gradient-to-br from-primary/25 to-primary/5 border border-primary/15 flex items-center justify-center text-primary shadow-inner ring-1 ring-primary/10 group-hover/row:scale-[1.02] transition-transform duration-300">
                                <x-ui.icon name="{{ $isProtected ? 'shield-check' : 'shield' }}" size="5" />
                            </div>
                            <div class="flex flex-col min-w-0">
                                <div class="flex items-center gap-2 flex-wrap">
                                    <span class="text-sm font-black tracking-tight text-foreground truncate uppercase">{{ $role->name }}</span>
                                    <span class="text-[9px] font-mono font-bold text-muted-foreground/35 tabular-nums">#{{ sprintf('%03d', $role->id) }}</span>
                                </div>
                                <div class="flex items-center gap-2 mt-1">
                                    @if($isProtected)
                                        <x-ui.badge variant="warning" className="uppercase text-[8px] font-black tracking-[0.12em] px-2 py-0.5 rounded-md">
                                            Protected
                                        </x-ui.badge>
                                    @else
                                        <span class="text-[10px] font-bold text-muted-foreground/60 uppercase tracking-widest">Editable profile</span>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </x-ui.table-cell>

                    <x-ui.table-cell class="align-middle">
                        <div class="flex flex-col gap-2 max-w-[520px]">
                            <div class="flex items-center gap-2">
                                <div class="size-7 rounded-xl bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 border border-emerald-500/20 flex items-center justify-center">
                                    <x-ui.icon name="key" size="3.5" />
                                </div>
                                <span class="text-[10px] font-black uppercase tracking-widest text-foreground/80">{{ $permissionCount }} Permissions</span>
                            </div>

                            <div class="flex flex-wrap gap-1.5">
                                @forelse($role->permissions->take(6) as $permission)
                                    @php
                                        $permissionGroup = explode('.', $permission->name)[0];
                                        $permissionName = str_replace($permissionGroup . '.', '', $permission->name);
                                    @endphp
                                    <span class="inline-flex items-center gap-1 rounded-md border border-border/50 bg-muted/25 px-2 py-0.5 text-[8px] font-black uppercase tracking-tight text-muted-foreground">
                                        <span class="text-primary/70">{{ $permissionGroup }}</span>
                                        <span>{{ $permissionName }}</span>
                                    </span>
                                @empty
                                    <span class="text-[10px] font-bold text-muted-foreground/60 uppercase tracking-widest">No permissions assigned</span>
                                @endforelse

                                @if($permissionCount > 6)
                                    <span class="inline-flex items-center rounded-md border border-primary/15 bg-primary/10 px-2 py-0.5 text-[8px] font-black uppercase tracking-tight text-primary">
                                        +{{ $permissionCount - 6 }} More
                                    </span>
                                @endif
                            </div>
                        </div>
                    </x-ui.table-cell>

                    <x-ui.table-cell class="align-middle text-center">
                        <div class="inline-flex items-center gap-2 rounded-xl border border-border/50 bg-muted/20 px-3 py-1.5">
                            <x-ui.icon name="users" size="3.5" class="text-muted-foreground" />
                            <span class="text-xs font-black text-foreground tabular-nums">{{ $role->users->count() }}</span>
                        </div>
                    </x-ui.table-cell>

                    <x-ui.table-cell class="text-right align-middle pr-5">
                        <div class="flex justify-end gap-1">
                            <a href="{{ route('roles.edit', $role) }}">
                                <x-ui.button variant="ghost" size="icon" className="size-9 text-muted-foreground hover:text-primary hover:bg-primary/10 rounded-xl border border-transparent hover:border-primary/20 transition-all">
                                    <x-ui.icon name="edit-3" size="4" />
                                </x-ui.button>
                            </a>

                            @if(!$isProtected)
                                <form action="{{ route('roles.destroy', $role) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this role?')" class="inline">
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
                    <x-ui.table-cell colspan="5" class="h-72 text-center align-middle p-0">
                        <div class="flex flex-col items-center justify-center gap-5 py-12 px-6">
                            <div class="size-24 rounded-3xl bg-gradient-to-br from-primary/25 via-primary/8 to-transparent border border-primary/20 flex items-center justify-center text-primary shadow-inner ring-1 ring-primary/10">
                                <x-ui.icon name="shield" size="12" />
                            </div>
                            <div class="space-y-2 max-w-md text-center">
                                <p class="text-sm font-black uppercase tracking-[0.2em] text-foreground">No roles matching criteria</p>
                                <p class="text-[11px] text-muted-foreground font-medium leading-relaxed">Try another search or create a new authority profile.</p>
                            </div>
                            <a href="{{ route('roles.create') }}">
                                <x-ui.button variant="outline" size="sm" class="rounded-xl border-border/60 font-bold uppercase tracking-widest text-[10px] h-10 px-6">
                                    Add role
                                </x-ui.button>
                            </a>
                        </div>
                    </x-ui.table-cell>
                </x-ui.table-row>
            @endforelse
        </x-ui.table-body>
    </x-ui.table>
</div>

@if($roles->hasPages())
    <div class="p-4 border-t border-border/40 bg-muted/10 flex justify-end items-center rounded-b-3xl">
        {{ $roles->links() }}
    </div>
@endif
