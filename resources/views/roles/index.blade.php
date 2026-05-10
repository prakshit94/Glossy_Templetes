<x-layouts.app pageTitle="Role Management">

    <div class="p-6 lg:p-10" x-data="{ 
        selectedRoles: [], 
        allSelected: false,
        search: '',
        perPage: '{{ request('perPage', 10) }}',
        isLoading: false,
        toggleAll() {
            if (this.allSelected) {
                this.selectedRoles = Array.from(document.querySelectorAll('input[name=\'role_ids[]\']')).map(el => parseInt(el.value));
            } else {
                this.selectedRoles = [];
            }
        },
        toggleRole(id) {
            if (this.selectedRoles.includes(id)) {
                this.selectedRoles = this.selectedRoles.filter(r => r !== id);
            } else {
                this.selectedRoles.push(id);
            }
        },
        async performSearch() {
            this.isLoading = true;
            const res = await fetch(`{{ route('roles.index') }}?search=${this.search}&perPage=${this.perPage}`, {
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            });
            const html = await res.text();
            document.getElementById('roles-table-container').innerHTML = html;
            this.isLoading = false;
            this.selectedRoles = [];
            this.allSelected = false;
        }
    }">
        <!-- Role Stats Widgets -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
            <x-ui.card className="bg-background shadow-sm border-border/40">
                <x-ui.card-content className="p-4 flex items-center gap-4">
                    <div class="size-12 rounded-xl bg-primary/10 text-primary flex items-center justify-center">
                        <x-ui.icon name="shield" size="6" />
                    </div>
                    <div>
                        <p class="text-xs font-medium text-muted-foreground mb-1">Total Roles</p>
                        <div class="text-2xl font-bold">{{ $stats['total'] ?? 0 }}</div>
                    </div>
                </x-ui.card-content>
            </x-ui.card>
            <x-ui.card className="bg-background shadow-sm border-border/40">
                <x-ui.card-content className="p-4 flex items-center gap-4">
                    <div class="size-12 rounded-xl bg-purple-500/10 text-purple-500 flex items-center justify-center">
                        <x-ui.icon name="key" size="6" />
                    </div>
                    <div>
                        <p class="text-xs font-medium text-muted-foreground mb-1">Total Permissions</p>
                        <div class="text-2xl font-bold">{{ $stats['permissions'] ?? 0 }}</div>
                    </div>
                </x-ui.card-content>
            </x-ui.card>
            <x-ui.card className="bg-background shadow-sm border-border/40">
                <x-ui.card-content className="p-4 flex items-center gap-4">
                    <div class="size-12 rounded-xl bg-blue-500/10 text-blue-500 flex items-center justify-center">
                        <x-ui.icon name="plus" size="6" />
                    </div>
                    <div>
                        <p class="text-xs font-medium text-muted-foreground mb-1">New This Month</p>
                        <div class="text-2xl font-bold">{{ $stats['newThisMonth'] ?? 0 }}</div>
                    </div>
                </x-ui.card-content>
            </x-ui.card>
            <x-ui.card className="bg-background shadow-sm border-border/40">
                <x-ui.card-content className="p-4 flex items-center gap-4">
                    <div class="size-12 rounded-xl bg-orange-500/10 text-orange-500 flex items-center justify-center">
                        <x-ui.icon name="list" size="6" />
                    </div>
                    <div>
                        <p class="text-xs font-medium text-muted-foreground mb-1">Avg Perms / Role</p>
                        <div class="text-2xl font-bold">{{ $stats['avgPermissions'] ?? 0 }}</div>
                    </div>
                </x-ui.card-content>
            </x-ui.card>
        </div>

        <x-ui.card>
             <x-ui.card-header className="border-b border-border/40 bg-muted/10 p-4">
                <div class="flex flex-col gap-4">
                    
                    <!-- Top Row: Tabs & Primary Actions -->
                    <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4">
                        <!-- Left Side: Title & Bulk Actions -->
                        <div class="flex flex-wrap items-center gap-3">
                            <div class="flex bg-muted/50 px-4 py-1.5 rounded-xl border border-border/50 shadow-inner">
                                <span class="text-xs font-bold text-primary tracking-widest uppercase">System Roles</span>
                            </div>
                            
                            <!-- Bulk Actions Dropdown -->
                            <div x-show="selectedRoles.length > 0" x-cloak x-transition
                                 class="flex items-center gap-2 animate-in fade-in slide-in-from-left-4 duration-300">
                                 <x-ui.dropdown>
                                    <x-slot name="trigger">
                                        <x-ui.button variant="outline" size="sm" class="rounded-xl border-primary/20 bg-primary/5 text-primary font-bold shadow-sm whitespace-nowrap">
                                            <span x-text="selectedRoles.length"></span> Selected
                                            <x-ui.icon name="chevron-down" size="3" class="ml-2" />
                                        </x-ui.button>
                                    </x-slot>
                                    <x-slot name="content">
                                        <x-ui.dropdown-label>Bulk Actions</x-ui.dropdown-label>
                                        <form action="{{ route('roles.bulk-delete') }}" method="POST" onsubmit="return confirm('Delete selected roles?')">
                                            @csrf
                                            <input type="hidden" name="ids" :value="JSON.stringify(selectedRoles)">
                                            <button type="submit" class="w-full text-left px-2 py-1.5 text-xs hover:bg-muted rounded-md flex items-center text-destructive">
                                                <x-ui.icon name="trash" size="3" class="mr-2" /> Delete Selected
                                            </button>
                                        </form>
                                    </x-slot>
                                 </x-ui.dropdown>
                            </div>
                        </div>

                        <!-- Right Side: Action Buttons -->
                        <div class="flex flex-wrap items-center gap-2 w-full lg:w-auto">
                            <x-ui.button variant="outline" size="sm" className="flex-1 sm:flex-none rounded-xl font-bold uppercase tracking-widest text-[10px] h-9 shadow-sm" onclick="alert('Import feature coming soon!')">
                                <x-ui.icon name="activity" size="3" className="mr-2" />
                                Import
                            </x-ui.button>
                            <x-ui.button variant="outline" size="sm" className="flex-1 sm:flex-none rounded-xl font-bold uppercase tracking-widest text-[10px] h-9 shadow-sm" onclick="alert('Export feature coming soon!')">
                                <x-ui.icon name="external-link" size="3" className="mr-2" />
                                Export
                            </x-ui.button>
                            <a href="{{ route('roles.create') }}" class="w-full sm:w-auto mt-2 sm:mt-0">
                                <x-ui.button size="sm" className="w-full rounded-xl font-bold uppercase tracking-widest text-[10px] h-9 shadow-lg shadow-primary/20">
                                    <x-ui.icon name="plus" size="3" className="mr-2" />
                                    Add Role
                                </x-ui.button>
                            </a>
                        </div>
                    </div>

                    <!-- Bottom Row: Filters & Search -->
                    <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4 pt-2">
                        <!-- Left Side: Select Filters -->
                        <div class="flex flex-wrap items-center gap-2 w-full lg:w-auto">
                            <!-- Per Page Selector -->
                            <div class="flex items-center gap-2">
                                <span class="text-[10px] font-bold text-muted-foreground uppercase tracking-widest hidden sm:inline-block">Show</span>
                                <select x-model="perPage" @change="performSearch" class="h-9 px-3 py-1.5 rounded-xl border border-border bg-background/50 focus:bg-background focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all text-xs font-medium shadow-sm">
                                    <option value="5">5</option>
                                    <option value="10">10</option>
                                    <option value="15">15</option>
                                    <option value="20">20</option>
                                    <option value="50">50</option>
                                </select>
                            </div>
                        </div>

                        <!-- Right Side: Search Input -->
                        <div class="relative group w-full lg:max-w-xs shrink-0">
                            <x-ui.icon name="search" size="4" class="absolute left-3 top-1/2 -translate-y-1/2 text-muted-foreground group-focus-within:text-primary transition-colors" />
                            <input type="text" x-model="search" @input.debounce.500ms="performSearch"
                                placeholder="Search roles..." 
                                class="pl-9 pr-4 py-2 rounded-xl border border-border bg-background/50 focus:bg-background focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all w-full text-xs shadow-sm">
                            <div x-show="isLoading" class="absolute right-3 top-1/2 -translate-y-1/2">
                                <svg class="animate-spin h-3 w-3 text-primary" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                            </div>
                        </div>
                    </div>
                </div>
             </x-ui.card-header>
             <x-ui.card-content className="p-0">
                <div id="roles-table-container" class="relative" @click="
                    if ($event.target.closest('a')) {
                        let link = $event.target.closest('a');
                        if (link && link.href && link.href.includes('page=')) {
                            $event.preventDefault();
                            isLoading = true;
                            fetch(link.href, {
                                headers: { 'X-Requested-With': 'XMLHttpRequest' }
                            }).then(res => res.text()).then(html => {
                                document.getElementById('roles-table-container').innerHTML = html;
                                isLoading = false;
                            });
                        }
                    }
                ">
                    @include('roles.partials.table')
                </div>
             </x-ui.card-content>
        </x-ui.card>
    </div>
</x-layouts.app>
