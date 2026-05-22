<x-layouts.app pageTitle="Role Management">

    <div class="p-6 lg:p-10 max-w-[1920px] mx-auto" x-data="roleManager()">

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
            <div class="group relative p-6 rounded-3xl bg-card/40 border border-border/60 ring-1 ring-border/30 backdrop-blur-xl hover:bg-primary/5 transition-all duration-500 overflow-hidden shadow-2xl">
                <div class="absolute top-0 right-0 -mr-8 -mt-8 size-32 bg-primary/10 blur-[50px] rounded-full group-hover:bg-primary/20 transition-all duration-500"></div>
                <div class="flex items-center gap-5 relative z-10">
                    <div class="size-14 rounded-2xl bg-gradient-to-tr from-primary/20 to-primary/5 border border-primary/10 text-primary flex items-center justify-center shadow-inner group-hover:scale-110 transition-transform duration-500">
                        <x-ui.icon name="shield" size="7" />
                    </div>
                    <div>
                        <p class="text-[10px] font-black uppercase tracking-widest text-muted-foreground/60 mb-1">Total Roles</p>
                        <div class="text-3xl font-black tracking-tighter text-foreground">{{ number_format($stats['total'] ?? 0) }}</div>
                    </div>
                </div>
            </div>

            <div class="group relative p-6 rounded-3xl bg-card/40 border border-border/60 ring-1 ring-border/30 backdrop-blur-xl hover:bg-emerald-500/5 transition-all duration-500 overflow-hidden shadow-2xl">
                <div class="absolute top-0 right-0 -mr-8 -mt-8 size-32 bg-emerald-500/10 blur-[50px] rounded-full group-hover:bg-emerald-500/20 transition-all duration-500"></div>
                <div class="flex items-center gap-5 relative z-10">
                    <div class="size-14 rounded-2xl bg-gradient-to-tr from-emerald-500/20 to-emerald-500/5 border border-emerald-500/10 text-emerald-500 flex items-center justify-center shadow-inner group-hover:scale-110 transition-transform duration-500">
                        <x-ui.icon name="key" size="7" />
                    </div>
                    <div>
                        <p class="text-[10px] font-black uppercase tracking-widest text-muted-foreground/60 mb-1">Permissions</p>
                        <div class="text-3xl font-black tracking-tighter text-foreground">{{ number_format($stats['permissions'] ?? 0) }}</div>
                    </div>
                </div>
            </div>

            <div class="group relative p-6 rounded-3xl bg-card/40 border border-border/60 ring-1 ring-border/30 backdrop-blur-xl hover:bg-blue-500/5 transition-all duration-500 overflow-hidden shadow-2xl">
                <div class="absolute top-0 right-0 -mr-8 -mt-8 size-32 bg-blue-500/10 blur-[50px] rounded-full group-hover:bg-blue-500/20 transition-all duration-500"></div>
                <div class="flex items-center gap-5 relative z-10">
                    <div class="size-14 rounded-2xl bg-gradient-to-tr from-blue-500/20 to-blue-500/5 border border-blue-500/10 text-blue-500 flex items-center justify-center shadow-inner group-hover:scale-110 transition-transform duration-500">
                        <x-ui.icon name="plus" size="7" />
                    </div>
                    <div>
                        <p class="text-[10px] font-black uppercase tracking-widest text-muted-foreground/60 mb-1">New This Month</p>
                        <div class="text-3xl font-black tracking-tighter text-foreground">{{ number_format($stats['newThisMonth'] ?? 0) }}</div>
                    </div>
                </div>
            </div>

            <div class="group relative p-6 rounded-3xl bg-card/40 border border-border/60 ring-1 ring-border/30 backdrop-blur-xl hover:bg-orange-500/5 transition-all duration-500 overflow-hidden shadow-2xl">
                <div class="absolute top-0 right-0 -mr-8 -mt-8 size-32 bg-orange-500/10 blur-[50px] rounded-full group-hover:bg-orange-500/20 transition-all duration-500"></div>
                <div class="flex items-center gap-5 relative z-10">
                    <div class="size-14 rounded-2xl bg-gradient-to-tr from-orange-500/20 to-orange-500/5 border border-orange-500/10 text-orange-500 flex items-center justify-center shadow-inner group-hover:scale-110 transition-transform duration-500">
                        <x-ui.icon name="list" size="7" />
                    </div>
                    <div>
                        <p class="text-[10px] font-black uppercase tracking-widest text-muted-foreground/60 mb-1">Avg Perms / Role</p>
                        <div class="text-3xl font-black tracking-tighter text-foreground">{{ number_format($stats['avgPermissions'] ?? 0) }}</div>
                    </div>
                </div>
            </div>
        </div>

        <x-ui.card class="overflow-hidden border-border/60 shadow-2xl bg-card/30 backdrop-blur-2xl rounded-3xl ring-1 ring-border/20">
            <x-ui.card-header class="border-b border-border/40 bg-muted/10 p-6 lg:p-8">
                <div class="flex flex-col gap-6">
                    <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4">
                        <div class="flex items-start sm:items-center gap-4 min-w-0">
                            <div class="size-12 sm:size-14 shrink-0 rounded-2xl bg-gradient-to-br from-primary/25 via-primary/10 to-primary/5 border border-primary/15 text-primary flex items-center justify-center shadow-inner ring-1 ring-primary/10">
                                <x-ui.icon name="shield-check" size="6" />
                            </div>
                            <div class="min-w-0">
                                <h2 class="text-lg sm:text-xl font-black text-foreground tracking-tight">Role Management</h2>
                                <p class="text-[10px] font-bold text-muted-foreground uppercase tracking-[0.2em] mt-1">Access groups · permissions · protected roles</p>
                            </div>
                        </div>

                        <div class="flex flex-wrap items-center gap-2 w-full lg:w-auto lg:justify-end">
                            <x-ui.button variant="outline" size="sm" class="flex-1 sm:flex-none rounded-xl font-bold uppercase tracking-widest text-[10px] h-10 shadow-sm border-border/60 bg-background/40 backdrop-blur-sm" onclick="alert('Import feature coming soon!')">
                                <x-ui.icon name="upload" size="3" class="mr-2" />
                                Import
                            </x-ui.button>
                            <x-ui.button variant="outline" size="sm" class="flex-1 sm:flex-none rounded-xl font-bold uppercase tracking-widest text-[10px] h-10 shadow-sm border-border/60 bg-background/40 backdrop-blur-sm" onclick="alert('Export feature coming soon!')">
                                <x-ui.icon name="download" size="3" class="mr-2" />
                                Export
                            </x-ui.button>
                            <a href="{{ route('roles.create') }}" class="flex-1 sm:flex-none">
                                <x-ui.button size="sm" class="w-full rounded-xl font-bold uppercase tracking-widest text-[10px] h-10 shadow-lg shadow-primary/25 ring-1 ring-primary/20">
                                    <x-ui.icon name="plus" size="3" class="mr-2" />
                                    Add Role
                                </x-ui.button>
                            </a>
                        </div>
                    </div>

                    <div class="flex flex-col sm:flex-row sm:flex-wrap sm:items-center gap-3 pt-2 border-t border-border/30">
                        <div class="flex bg-muted/50 px-4 py-1.5 rounded-xl border border-border/50 shadow-inner w-fit">
                            <span class="text-xs font-bold text-primary tracking-widest uppercase">Authority Matrix</span>
                        </div>

                        <div x-show="selectedRoles.length > 0" x-cloak x-transition class="flex items-center gap-2 animate-in fade-in slide-in-from-left-4 duration-300">
                            <x-ui.dropdown>
                                <x-slot name="trigger">
                                    <x-ui.button variant="outline" size="sm" class="rounded-xl border-primary/20 bg-primary/5 text-primary font-bold shadow-sm whitespace-nowrap">
                                        <span x-text="selectedRoles.length"></span> Selected
                                        <x-ui.icon name="chevron-down" size="3" class="ml-2" />
                                    </x-ui.button>
                                </x-slot>
                                <x-slot name="content">
                                    <x-ui.dropdown-label>Bulk Actions</x-ui.dropdown-label>
                                    <div class="p-1">
                                        <form action="{{ route('roles.bulk-delete') }}" method="POST" onsubmit="return confirm('Delete selected roles?')">
                                            @csrf
                                            <input type="hidden" name="ids" :value="JSON.stringify(selectedRoles)">
                                            <button type="submit" class="w-full text-left px-3 py-2 text-[10px] font-black hover:bg-destructive/10 rounded-xl flex items-center text-destructive uppercase tracking-widest transition-colors">
                                                <x-ui.icon name="trash" size="3.5" class="mr-2" />
                                                Delete Selected
                                            </button>
                                        </form>
                                    </div>
                                </x-slot>
                            </x-ui.dropdown>
                        </div>
                    </div>

                    <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4 pt-1">
                        <div class="flex items-center gap-2">
                            <span class="text-[10px] font-bold text-muted-foreground uppercase tracking-widest hidden sm:inline-block">Show</span>
                            <select x-model="perPage" @change="performSearch()" class="h-10 px-3 rounded-xl border border-border bg-background/50 text-xs font-medium focus:ring-1 focus:ring-primary outline-none shadow-sm">
                                <option value="5">5</option>
                                <option value="10">10</option>
                                <option value="15">15</option>
                                <option value="20">20</option>
                                <option value="50">50</option>
                            </select>
                        </div>

                        <div class="relative group w-full lg:max-w-md shrink-0">
                            <x-ui.icon name="search" size="4" class="absolute left-3 top-1/2 -translate-y-1/2 text-muted-foreground group-focus-within:text-primary transition-colors" />
                            <input type="text" x-model="search" @input.debounce.500ms="performSearch()"
                                placeholder="Search role name..."
                                class="pl-9 pr-10 py-2.5 rounded-xl border border-border bg-background/50 focus:bg-background focus:ring-2 focus:ring-primary/20 transition-all w-full text-xs shadow-sm outline-none">
                            <div x-show="isLoading" x-cloak class="absolute right-3 top-1/2 -translate-y-1/2 pointer-events-none">
                                <x-ui.icon name="refresh-cw" class="animate-spin text-primary" size="4" />
                            </div>
                        </div>
                    </div>
                </div>
            </x-ui.card-header>

            <x-ui.card-content class="p-0 relative min-h-[420px] bg-gradient-to-b from-transparent via-muted/[0.03] to-muted/5">
                <div x-show="isLoading" x-cloak class="absolute inset-0 z-50 bg-background/50 backdrop-blur-md flex items-center justify-center animate-in fade-in duration-200">
                    <div class="flex flex-col items-center gap-3 rounded-2xl border border-border/50 bg-card/80 px-8 py-6 shadow-2xl">
                        <x-ui.icon name="refresh-cw" class="animate-spin text-primary" size="8" />
                        <span class="text-[10px] font-black uppercase tracking-[0.25em] text-foreground/80">Syncing roles</span>
                    </div>
                </div>
                <div class="pointer-events-none absolute inset-x-0 top-0 h-px bg-gradient-to-r from-transparent via-primary/20 to-transparent z-10"></div>
                <div id="roles-table-container" class="relative z-0" @click="handlePagination($event)">
                    @include('roles.partials.table')
                </div>
            </x-ui.card-content>
        </x-ui.card>
    </div>

    <script>
        function roleManager() {
            return {
                selectedRoles: [],
                allSelected: false,
                search: '{{ request('search', '') }}',
                perPage: '{{ request('perPage', 10) }}',
                isLoading: false,

                toggleAll() {
                    const checkboxes = document.querySelectorAll('input[name="role_ids[]"]');

                    this.selectedRoles = this.allSelected
                        ? Array.from(checkboxes).map(el => parseInt(el.value))
                        : [];
                },

                toggleRole(id) {
                    id = parseInt(id);

                    if (this.selectedRoles.includes(id)) {
                        this.selectedRoles = this.selectedRoles.filter(roleId => roleId !== id);
                    } else {
                        this.selectedRoles.push(id);
                    }
                },

                async fetchTable(url) {
                    if (this.isLoading) return;

                    this.isLoading = true;

                    try {
                        const response = await fetch(url, {
                            headers: {
                                'X-Requested-With': 'XMLHttpRequest',
                                'Accept': 'text/html'
                            }
                        });

                        if (!response.ok) {
                            throw new Error('Failed to fetch data');
                        }

                        document.getElementById('roles-table-container').innerHTML = await response.text();
                        this.selectedRoles = [];
                        this.allSelected = false;
                    } catch (error) {
                        console.error(error);
                        alert('Something went wrong while loading roles.');
                    } finally {
                        this.isLoading = false;
                    }
                },

                async performSearch() {
                    const params = new URLSearchParams({
                        search: this.search,
                        perPage: this.perPage
                    });

                    await this.fetchTable(`{{ route('roles.index') }}?${params.toString()}`);
                },

                async handlePagination(event) {
                    const link = event.target.closest('a');

                    if (!link || !link.href || !link.href.includes('page=')) {
                        return;
                    }

                    event.preventDefault();
                    await this.fetchTable(link.href);
                }
            };
        }
    </script>

    <style>
        [x-cloak] { display: none !important; }
    </style>
</x-layouts.app>
