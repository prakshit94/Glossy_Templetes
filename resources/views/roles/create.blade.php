<x-layouts.app pageTitle="Create New Role">

    <div class="p-6 lg:p-10" x-data="{ 
        permSearch: '',
        allChecked: false,
        groupStates: {},

        isMatch(group, permName) {
            if (!this.permSearch) return true;
            const search = this.permSearch.toLowerCase();
            return group.toLowerCase().includes(search) || permName.toLowerCase().includes(search);
        },

        hasVisiblePerms(group, perms) {
            if (!this.permSearch) return true;
            return Object.values(perms).some(p => p && this.isMatch(group, p.name));
        },

        toggleAll() {
            const checkboxes = document.querySelectorAll('input[name=\'permissions[]\']');
            checkboxes.forEach(cb => cb.checked = this.allChecked);
            this.syncAllGroupCheckboxes();
        },

        toggleGroup(group, state) {
            const checkboxes = document.querySelectorAll(`input[data-group='${group}']`);
            checkboxes.forEach(cb => cb.checked = state);
            this.syncGlobalCheckbox();
        },

        syncGroupCheckbox(group) {
            const checkboxes = document.querySelectorAll(`input[data-group='${group}']`);
            const allChecked = Array.from(checkboxes).every(cb => cb.checked);
            this.groupStates[group] = allChecked;
            this.syncGlobalCheckbox();
        },

        syncAllGroupCheckboxes() {
            document.querySelectorAll('input[data-group-toggle]').forEach(toggle => {
                const group = toggle.getAttribute('data-group-toggle');
                const checkboxes = document.querySelectorAll(`input[data-group='${group}']`);
                this.groupStates[group] = checkboxes.length > 0 && Array.from(checkboxes).every(cb => cb.checked);
            });
            this.syncGlobalCheckbox();
        },

        syncGlobalCheckbox() {
            const checkboxes = document.querySelectorAll('input[name=\'permissions[]\']');
            this.allChecked = checkboxes.length > 0 && Array.from(checkboxes).every(cb => cb.checked);
        },

        init() {
            this.syncAllGroupCheckboxes();
        }
    }">
        <div class="max-w-5xl mx-auto space-y-6">
            <x-ui.card class="overflow-hidden border-border/60 shadow-2xl bg-card/30 backdrop-blur-2xl rounded-[24px]">
                <x-ui.card-header class="border-b border-border/40 bg-muted/10 p-6">
                    <div class="flex flex-col md:flex-row md:items-center justify-between gap-6">
                        <div class="flex items-center gap-4">
                            <div class="size-12 rounded-xl bg-primary/10 text-primary flex items-center justify-center shadow-inner">
                                <x-ui.icon name="shield" size="6" />
                            </div>
                            <div>
                                <h2 class="text-xl font-bold text-foreground tracking-tight">Create New Role</h2>
                                <p class="text-[10px] font-bold text-muted-foreground uppercase tracking-[0.2em] mt-0.5">Configuration Engine</p>
                            </div>
                        </div>

                        <div class="relative group w-full max-w-xs">
                            <x-ui.icon name="search" size="4" class="absolute left-3.5 top-1/2 -translate-y-1/2 text-muted-foreground group-focus-within:text-primary transition-colors" />
                            <input type="text" x-model="permSearch" placeholder="Search Permissions..." 
                                class="pl-11 pr-5 py-2.5 rounded-xl border border-border/60 bg-background/50 focus:bg-background focus:ring-2 focus:ring-primary/20 transition-all w-full text-xs shadow-sm uppercase font-bold tracking-widest text-foreground placeholder:text-muted-foreground/40">
                        </div>
                    </div>
                </x-ui.card-header>

                <form action="{{ route('roles.store') }}" method="POST">
                    @csrf
                    <x-ui.card-content class="p-0">
                        <div class="p-8 border-b border-border/40 bg-muted/5">
                            <div class="max-w-xl space-y-2">
                                <label class="text-[10px] font-black uppercase tracking-[0.25em] text-muted-foreground/80">Role Name</label>
                                <input type="text" name="name" value="{{ old('name') }}" required 
                                    placeholder="e.g. Finance Manager"
                                    class="w-full px-5 py-3.5 rounded-xl bg-background/50 border border-border/60 focus:bg-background focus:ring-2 focus:ring-primary/20 focus:border-primary outline-none transition-all font-bold text-base tracking-tight shadow-inner text-foreground placeholder:text-muted-foreground/40">
                                @error('name') <p class="text-[10px] font-bold text-red-500 uppercase tracking-widest mt-1">{{ $message }}</p> @enderror
                            </div>
                        </div>

                        <x-ui.table>
                            <x-ui.table-header class="bg-muted/10">
                                <x-ui.table-row class="border-b border-border/40 hover:bg-transparent">
                                    <x-ui.table-head class="py-5 px-8 w-1/3">
                                        <label class="flex items-center gap-3 cursor-pointer group/global">
                                            <input type="checkbox" x-model="allChecked" @change="toggleAll" 
                                                class="rounded-lg border-border/60 bg-background/50 text-primary focus:ring-primary/20 size-5 cursor-pointer">
                                            <span class="text-[10px] font-black uppercase tracking-[0.25em] text-primary group-hover/global:translate-x-1 transition-transform">Master Select</span>
                                        </label>
                                    </x-ui.table-head>
                                    <x-ui.table-head class="py-5 px-8">
                                        <span class="text-[10px] font-black uppercase tracking-[0.25em] text-foreground/80">Available Permissions</span>
                                    </x-ui.table-head>
                                </x-ui.table-row>
                            </x-ui.table-header>
                            <x-ui.table-body>
                                @foreach($permissions->groupBy(fn($p) => explode('.', $p->name)[0]) as $group => $groupPermissions)
                                    <x-ui.table-row 
                                        x-show="hasVisiblePerms('{{ $group }}', {{ json_encode($groupPermissions->keyBy('name')->toArray()) }})"
                                        x-transition
                                        class="hover:bg-primary/[0.03] transition-colors border-b border-border/40 last:border-0 group/row">
                                        <x-ui.table-cell class="py-6 px-8 bg-muted/5">
                                            <div class="flex items-center justify-between">
                                                <div class="flex items-center gap-4">
                                                    <div class="size-8 rounded-lg bg-primary/10 flex items-center justify-center text-primary group-hover/row:scale-110 transition-transform">
                                                        <x-ui.icon name="folder" size="4" />
                                                    </div>
                                                    <span class="text-sm font-black uppercase tracking-widest text-foreground">{{ $group }}</span>
                                                </div>
                                                <label class="flex items-center gap-2 cursor-pointer group/group">
                                                    <input type="checkbox" 
                                                        x-model="groupStates['{{ $group }}']"
                                                        data-group-toggle="{{ $group }}"
                                                        @change="toggleGroup('{{ $group }}', $event.target.checked)"
                                                        class="rounded border-border/60 bg-background/50 text-primary focus:ring-primary/20 size-4">
                                                    <span class="text-[8px] font-black uppercase tracking-widest text-primary/40 group-hover/group:text-primary transition-colors">All</span>
                                                </label>
                                            </div>
                                        </x-ui.table-cell>
                                        <x-ui.table-cell class="py-6 px-8">
                                            <div class="flex flex-wrap gap-3">
                                                @foreach($groupPermissions as $permission)
                                                    <label x-show="isMatch('{{ $group }}', '{{ $permission->name }}')" 
                                                        class="flex items-center gap-2.5 px-3 py-2 rounded-xl bg-muted/10 border border-border/40 hover:border-primary/30 hover:bg-primary/5 transition-all cursor-pointer group/perm">
                                                        <input type="checkbox" name="permissions[]" value="{{ $permission->name }}" 
                                                            data-group="{{ $group }}"
                                                            @change="syncGroupCheckbox('{{ $group }}')"
                                                            class="rounded-lg border-border/60 bg-background/50 text-primary focus:ring-primary/20 size-5 transition-all">
                                                        <span class="text-[11px] font-bold text-muted-foreground group-hover/perm:text-foreground transition-colors uppercase tracking-tight">
                                                            {{ str_replace($group . '.', '', $permission->name) }}
                                                        </span>
                                                    </label>
                                                @endforeach
                                            </div>
                                        </x-ui.table-cell>
                                    </x-ui.table-row>
                                @endforeach
                            </x-ui.table-body>
                        </x-ui.table>
                    </x-ui.card-content>

                    <div class="p-6 bg-muted/10 border-t border-border/40 flex justify-end gap-3 rounded-b-[24px]">
                        <x-ui.button variant="outline" type="button" onclick="history.back()" class="rounded-xl h-11 px-6 font-bold uppercase tracking-widest text-[10px] border-border text-muted-foreground hover:bg-muted">
                            Discard
                        </x-ui.button>
                        <x-ui.button type="submit" class="rounded-xl h-11 px-8 font-bold uppercase tracking-widest text-[10px] shadow-lg shadow-primary/20 hover:scale-[1.02] active:scale-[0.98] transition-all">
                            Create Role
                        </x-ui.button>
                    </div>
                </form>
            </x-ui.card>
        </div>
    </div>

    <style>
        [x-cloak] { display: none !important; }
        .custom-scrollbar::-webkit-scrollbar { height: 4px; width: 4px; }
        .custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
        .custom-scrollbar::-webkit-scrollbar-thumb { background: rgba(255, 255, 255, 0.1); border-radius: 10px; }
    </style>
</x-layouts.app>
