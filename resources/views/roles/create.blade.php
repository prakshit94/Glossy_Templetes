<x-layouts.app pageTitle="Create New Role">

    <div class="p-6 lg:p-10" x-data="{ 
        permSearch: '',
        isMatch(group, permName) {
            if (!this.permSearch) return true;
            const search = this.permSearch.toLowerCase();
            return group.toLowerCase().includes(search) || permName.toLowerCase().includes(search);
        },
        hasVisiblePerms(group, perms) {
            if (!this.permSearch) return true;
            return Object.values(perms).some(p => p && this.isMatch(group, p.name));
        }
    }">
        <div class="max-w-4xl mx-auto">
            <x-ui.card>
                <form action="{{ route('roles.store') }}" method="POST">
                    @csrf
                    <x-ui.card-content class="space-y-10 pt-8 px-8">
                        <!-- Role Header Section -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-10">
                            <div class="space-y-2 group">
                                <label class="text-[10px] font-black uppercase tracking-[0.2em] text-muted-foreground/60 group-focus-within:text-primary transition-colors">Role Name</label>
                                <div class="relative">
                                    <x-ui.icon name="shield" size="4" class="absolute left-4 top-1/2 -translate-y-1/2 text-muted-foreground group-focus-within:text-primary transition-colors" />
                                    <input type="text" name="name" value="{{ old('name') }}" required 
                                        placeholder="e.g. Content Manager"
                                        class="w-full pl-12 pr-4 py-3.5 rounded-2xl bg-muted/30 border-transparent focus:bg-background focus:ring-2 focus:ring-primary/20 focus:border-primary outline-none transition-all shadow-inner font-bold text-sm">
                                </div>
                                @error('name') <p class="text-[10px] font-bold text-destructive uppercase tracking-widest mt-1">{{ $message }}</p> @enderror
                            </div>

                            <div class="p-6 rounded-[24px] bg-primary/5 border border-primary/10 flex items-center gap-4">
                                <div class="size-12 rounded-2xl bg-primary/20 flex items-center justify-center text-primary shadow-lg shadow-primary/10">
                                    <x-ui.icon name="info" size="6" />
                                </div>
                                <div>
                                    <h4 class="text-[11px] font-black uppercase tracking-widest text-primary">Role Identity</h4>
                                    <p class="text-[10px] text-muted-foreground font-bold mt-0.5 opacity-70">DEFINE UNIQUE NAME AND SCOPE</p>
                                </div>
                            </div>
                        </div>

                        <!-- Permissions Matrix Section -->
                        <div class="space-y-6 pt-6 border-t border-border/20">
                            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                                <div class="flex items-center gap-3">
                                    <div class="size-10 rounded-xl bg-primary/10 text-primary flex items-center justify-center shadow-lg shadow-primary/5">
                                        <x-ui.icon name="key" size="5" />
                                    </div>
                                    <h3 class="text-sm font-black uppercase tracking-[0.15em]">Authority Matrix</h3>
                                </div>

                                <!-- Real-time Matrix Search -->
                                <div class="relative group w-full max-w-xs">
                                    <x-ui.icon name="search" size="3.5" class="absolute left-3 top-1/2 -translate-y-1/2 text-muted-foreground group-focus-within:text-primary transition-colors" />
                                    <input type="text" x-model="permSearch"
                                        placeholder="Filter modules..." 
                                        class="pl-9 pr-4 py-2 rounded-xl border border-border/40 bg-muted/5 focus:bg-background focus:ring-4 focus:ring-primary/5 focus:border-primary transition-all w-full text-[10px] font-bold uppercase tracking-widest outline-none shadow-sm">
                                </div>
                            </div>

                            <x-ui.card class="overflow-hidden border-border/40 shadow-2xl shadow-primary/5 bg-background/50 backdrop-blur-md">
                                <x-ui.table>
                                    <x-ui.table-header class="bg-muted/30">
                                        <x-ui.table-row class="hover:bg-transparent border-b border-border/40">
                                            <x-ui.table-head class="w-[220px] py-5 px-6">
                                                <span class="text-[11px] font-black uppercase tracking-[0.2em] text-primary">Module / Feature</span>
                                            </x-ui.table-head>
                                            @foreach(['view', 'create', 'edit', 'delete', 'other'] as $action)
                                            <x-ui.table-head class="text-center py-5">
                                                <span class="text-[11px] font-black uppercase tracking-[0.2em] text-foreground">{{ $action }}</span>
                                            </x-ui.table-head>
                                            @endforeach
                                        </x-ui.table-row>
                                    </x-ui.table-header>
                                    <x-ui.table-body>
                                        @foreach($permissions->groupBy(fn($p) => explode('.', $p->name)[0]) as $group => $groupPermissions)
                                            <x-ui.table-row 
                                                x-show="hasVisiblePerms('{{ $group }}', {{ json_encode($groupPermissions->keyBy(function($p) use ($group) {
                                                    $suffix = str_replace($group . '.', '', $p->name);
                                                    if (!in_array($suffix, ['view', 'create', 'edit', 'delete', 'index', 'show'])) return 'other';
                                                    if (in_array($suffix, ['view', 'index', 'show'])) return 'view';
                                                    return $suffix;
                                                })->toArray()) }})"
                                                x-transition
                                                class="hover:bg-primary/5 transition-all duration-300 group/row border-b border-border/20 last:border-0">
                                                <x-ui.table-cell class="bg-muted/5 py-6 px-6">
                                                    <div class="flex items-center gap-4">
                                                        <div class="size-9 rounded-xl bg-primary/10 flex items-center justify-center text-primary group-hover/row:scale-110 transition-all duration-300 shadow-sm">
                                                            <x-ui.icon name="folder" size="4" />
                                                        </div>
                                                        <span class="text-[12px] font-black uppercase tracking-widest text-foreground">{{ $group }}</span>
                                                    </div>
                                                </x-ui.table-cell>
                                                
                                                @foreach(['view', 'create', 'edit', 'delete', 'other'] as $action)
                                                    <x-ui.table-cell class="text-center py-5">
                                                        @php
                                                            $currentPermission = $groupPermissions->first(function($p) use ($action, $group) {
                                                                $suffix = str_replace($group . '.', '', $p->name);
                                                                if ($action === 'other') {
                                                                    return !in_array($suffix, ['view', 'create', 'edit', 'delete', 'index', 'show']);
                                                                }
                                                                if ($action === 'view') {
                                                                    return in_array($suffix, ['view', 'index', 'show']);
                                                                }
                                                                return $suffix === $action;
                                                            });
                                                        @endphp
                                                        
                                                        @if($currentPermission)
                                                            <label class="flex flex-col items-center justify-center cursor-pointer group/check gap-1.5">
                                                                <input type="checkbox" name="permissions[]" value="{{ $currentPermission->name }}" 
                                                                    class="rounded-xl border-border bg-background text-primary focus:ring-primary/20 size-7 transition-all group-hover/check:scale-110 group-hover/check:shadow-xl group-hover/check:shadow-primary/20 cursor-pointer">
                                                                <span class="text-[9px] font-bold text-muted-foreground/60 group-hover/check:text-primary transition-colors uppercase tracking-tighter">
                                                                    {{ $currentPermission->name }}
                                                                </span>
                                                            </label>
                                                        @else
                                                            <div class="size-7 mx-auto rounded-xl bg-muted/20 border-2 border-dashed border-border/20 opacity-10"></div>
                                                        @endif
                                                    </x-ui.table-cell>
                                                @endforeach
                                            </x-ui.table-row>
                                        @endforeach
                                    </x-ui.table-body>
                                </x-ui.table>
                            </x-ui.card>
                        </div>
                    </x-ui.card-content>
                    <div class="p-6 border-t border-border/40 flex justify-end gap-3">
                        <x-ui.button variant="outline" type="button" onclick="history.back()">Cancel</x-ui.button>
                        <x-ui.button type="submit">Create Role</x-ui.button>
                    </div>
                </form>
            </x-ui.card>
        </div>
    </div>
</x-layouts.app>
