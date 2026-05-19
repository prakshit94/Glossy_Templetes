<x-layouts.app pageTitle="Refunds Management">
    @php
        $qStatus = request('status') ? explode(',', request('status')) : [];
    @endphp

    <div class="p-6 lg:p-10" x-data="{ 
        selectedItems: [], 
        selectedStatuses: {},
        allSelected: false,
        search: @js(request('search', '')),
        perPage: @js(request('perPage', 15)),
        statusFilter: @js($qStatus),
        statusesList: @js($statusesList),
        stats: @js($stats),
        isLoading: false,

        toggleAll() {
            if (this.allSelected) {
                const checkboxes = Array.from(document.querySelectorAll('input[name=\'refund_ids[]\']'));
                this.selectedItems = checkboxes.map(el => parseInt(el.value));
                this.selectedStatuses = {};
                checkboxes.forEach(el => {
                    this.selectedStatuses[parseInt(el.value)] = el.getAttribute('data-status');
                });
            } else {
                this.selectedItems = [];
                this.selectedStatuses = {};
            }
        },

        toggleItem(id, status) {
            if (this.selectedItems.includes(id)) {
                this.selectedItems = this.selectedItems.filter(i => i !== id);
                delete this.selectedStatuses[id];
            } else {
                this.selectedItems.push(id);
                this.selectedStatuses[id] = status;
            }
        },

        async performSearch() {
            this.isLoading = true;
            let params = new URLSearchParams({
                search: this.search,
                perPage: this.perPage,
                status: this.statusFilter.join(',')
            });

            window.history.replaceState({}, '', `${window.location.pathname}?${params.toString()}`);

            const res = await fetch(
                `{{ route('refunds.index') }}?${params.toString()}`,
                { headers: { 
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                } }
            );
            const data = await res.json();
            
            document.getElementById('table-container').innerHTML = data.table;
            this.stats = data.stats;
            
            this.isLoading = false;
            this.selectedItems = [];
            this.selectedStatuses = {};
            this.allSelected = false;
        },

        clearFilters() {
            this.search = '';
            this.statusFilter = [];
            this.performSearch();
        }
    }">

        <div class="max-w-[100rem] mx-auto space-y-8">
            <!-- Stats Widgets -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
                <div class="group relative p-6 rounded-3xl bg-card/40 border border-border/60 backdrop-blur-xl hover:bg-primary/5 transition-all duration-500 overflow-hidden shadow-2xl">
                    <div class="absolute top-0 right-0 -mr-8 -mt-8 size-32 bg-primary/10 blur-[50px] rounded-full group-hover:bg-primary/20 transition-all duration-500"></div>
                    <div class="flex items-center gap-5 relative z-10">
                        <div class="size-14 rounded-2xl bg-gradient-to-tr from-primary/20 to-primary/5 border border-primary/10 text-primary flex items-center justify-center shadow-inner group-hover:scale-110 transition-transform duration-500">
                            <x-ui.icon name="refresh-cw" size="7" />
                        </div>
                        <div>
                            <p class="text-[10px] font-black uppercase tracking-widest text-muted-foreground/60 mb-1">Total Refunds</p>
                            <div class="text-3xl font-black tracking-tighter text-foreground" x-text="stats.total"></div>
                        </div>
                    </div>
                </div>

                <div class="group relative p-6 rounded-3xl bg-card/40 border border-border/60 backdrop-blur-xl hover:bg-amber-500/5 transition-all duration-500 overflow-hidden shadow-2xl">
                    <div class="absolute top-0 right-0 -mr-8 -mt-8 size-32 bg-amber-500/10 blur-[50px] rounded-full group-hover:bg-amber-500/20 transition-all duration-500"></div>
                    <div class="flex items-center gap-5 relative z-10">
                        <div class="size-14 rounded-2xl bg-gradient-to-tr from-amber-500/20 to-amber-500/5 border border-amber-500/10 text-amber-500 flex items-center justify-center shadow-inner group-hover:scale-110 transition-transform duration-500">
                            <x-ui.icon name="clock" size="7" />
                        </div>
                        <div>
                            <p class="text-[10px] font-black uppercase tracking-widest text-muted-foreground/60 mb-1">Pending</p>
                            <div class="text-3xl font-black tracking-tighter text-amber-500" x-text="stats.pending"></div>
                        </div>
                    </div>
                </div>

                <div class="group relative p-6 rounded-3xl bg-card/40 border border-border/60 backdrop-blur-xl hover:bg-emerald-500/5 transition-all duration-500 overflow-hidden shadow-2xl">
                    <div class="absolute top-0 right-0 -mr-8 -mt-8 size-32 bg-emerald-500/10 blur-[50px] rounded-full group-hover:bg-emerald-500/20 transition-all duration-500"></div>
                    <div class="flex items-center gap-5 relative z-10">
                        <div class="size-14 rounded-2xl bg-gradient-to-tr from-emerald-500/20 to-emerald-500/5 border border-emerald-500/10 text-emerald-500 flex items-center justify-center shadow-inner group-hover:scale-110 transition-transform duration-500">
                            <x-ui.icon name="check-circle" size="7" />
                        </div>
                        <div>
                            <p class="text-[10px] font-black uppercase tracking-widest text-muted-foreground/60 mb-1">Processed</p>
                            <div class="text-3xl font-black tracking-tighter text-emerald-500" x-text="stats.processed"></div>
                        </div>
                    </div>
                </div>

                <div class="group relative p-6 rounded-3xl bg-card/40 border border-border/60 backdrop-blur-xl hover:bg-red-500/5 transition-all duration-500 overflow-hidden shadow-2xl">
                    <div class="absolute top-0 right-0 -mr-8 -mt-8 size-32 bg-red-500/10 blur-[50px] rounded-full group-hover:bg-red-500/20 transition-all duration-500"></div>
                    <div class="flex items-center gap-5 relative z-10">
                        <div class="size-14 rounded-2xl bg-gradient-to-tr from-red-500/20 to-red-500/5 border border-red-500/10 text-red-500 flex items-center justify-center shadow-inner group-hover:scale-110 transition-transform duration-500">
                            <x-ui.icon name="x-circle" size="7" />
                        </div>
                        <div>
                            <p class="text-[10px] font-black uppercase tracking-widest text-muted-foreground/60 mb-1">Failed</p>
                            <div class="text-3xl font-black tracking-tighter text-red-500" x-text="stats.failed"></div>
                        </div>
                    </div>
                </div>
            </div>

            <x-ui.card class="overflow-hidden border-border/60 shadow-2xl bg-card/30 backdrop-blur-2xl rounded-3xl">
                <x-ui.card-header class="border-b border-border/40 bg-muted/10 p-6 lg:p-8">
                    <div class="flex flex-col gap-6">
                        <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4">
                            <div class="flex items-start sm:items-center gap-4 min-w-0">
                                <div class="size-12 sm:size-14 shrink-0 rounded-2xl bg-gradient-to-br from-primary/25 via-primary/10 to-primary/5 border border-primary/15 text-primary flex items-center justify-center shadow-inner ring-1 ring-primary/10">
                                    <x-ui.icon name="refresh-cw" size="6" />
                                </div>
                                <div class="min-w-0">
                                    <h2 class="text-lg sm:text-xl font-black text-foreground tracking-tight">Refunds Management</h2>
                                    <p class="text-[10px] font-bold text-muted-foreground uppercase tracking-[0.2em] mt-1">Payment Reversals & Refunds</p>
                                </div>
                            </div>
                            <div class="flex flex-wrap items-center gap-2 w-full lg:w-auto lg:justify-end">
                                <a href="{{ route('refunds.create') }}" class="flex-1 sm:flex-none">
                                    <x-ui.button size="sm" class="w-full rounded-xl font-bold uppercase tracking-widest text-[10px] h-10 shadow-lg shadow-primary/25 ring-1 ring-primary/20">
                                        <x-ui.icon name="plus" size="3" class="mr-2" />
                                        New Refund
                                    </x-ui.button>
                                </a>
                            </div>
                        </div>

                        <div class="flex flex-col sm:flex-row sm:flex-wrap sm:items-center gap-3 pt-2 border-t border-border/30">
                            <div class="flex bg-muted/50 px-4 py-1.5 rounded-xl border border-border/50 shadow-inner w-fit">
                                <span class="text-xs font-bold text-primary tracking-widest uppercase">Refunds Ledger</span>
                            </div>
                            
                            <div class="flex items-center gap-2">
                                <span class="text-[10px] font-bold text-muted-foreground uppercase tracking-widest">Show</span>
                                <select x-model="perPage" @change="performSearch()" class="h-10 px-3 rounded-xl border border-border bg-background/50 text-xs font-medium focus:ring-1 focus:ring-primary outline-none shadow-sm">
                                    <option value="15">15</option>
                                    <option value="25">25</option>
                                    <option value="50">50</option>
                                    <option value="100">100</option>
                                </select>
                            </div>

                            <div class="flex flex-wrap items-center gap-2">
                                <x-ui.dropdown>
                                    <x-slot name="trigger">
                                        <x-ui.button variant="outline" size="sm" class="rounded-xl border-border bg-background/50 h-10 px-3 shadow-sm hover:bg-background text-xs font-medium">
                                            <x-ui.icon name="filter" size="3.5" class="mr-2 opacity-70" />
                                            Status 
                                            <span x-show="statusFilter.length > 0" x-cloak class="ml-1.5 inline-flex items-center justify-center bg-primary text-primary-foreground text-[10px] font-bold rounded-md px-1.5 min-w-[20px] h-5" x-text="statusFilter.length"></span>
                                        </x-ui.button>
                                    </x-slot>
                                    <x-slot name="content" class="w-56 p-2">
                                        <div class="space-y-1 max-h-[300px] overflow-y-auto custom-scrollbar p-1">
                                            <template x-for="status in statusesList" :key="status">
                                                <label class="flex items-center gap-3 px-2 py-1.5 hover:bg-accent rounded-lg cursor-pointer transition-colors">
                                                    <input type="checkbox" :value="status" x-model="statusFilter" @change="performSearch()" class="rounded-md border-border bg-background text-primary focus:ring-primary/25 shadow-sm">
                                                    <span class="text-xs font-medium capitalize" x-text="status"></span>
                                                </label>
                                            </template>
                                        </div>
                                    </x-slot>
                                </x-ui.dropdown>
                                
                                <x-ui.button variant="ghost" size="sm" @click="clearFilters()" class="rounded-xl h-10 px-4 text-[10px] font-black uppercase tracking-widest text-muted-foreground hover:text-primary transition-colors">
                                    Clear All
                                </x-ui.button>
                            </div>

                            <div class="lg:ml-auto relative group w-full lg:max-w-md shrink-0">
                                <x-ui.icon name="search" size="4" class="absolute left-3 top-1/2 -translate-y-1/2 text-muted-foreground group-focus-within:text-primary transition-colors" />
                                <input type="text" x-model="search" @input.debounce.500ms="performSearch()"
                                    placeholder="Search payment number or order..."
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
                            <span class="text-[10px] font-black uppercase tracking-[0.25em] text-foreground/80">Syncing ledger</span>
                        </div>
                    </div>
                    <div class="pointer-events-none absolute inset-x-0 top-0 h-px bg-gradient-to-r from-transparent via-primary/20 to-transparent z-10"></div>
                    <div id="table-container">
                        @include('refunds.partials.table')
                    </div>
                </x-ui.card-content>
            </x-ui.card>
        </div>
    </div>

    <style>
        [x-cloak] { display: none !important; }
        .custom-scrollbar::-webkit-scrollbar { height: 6px; width: 6px; }
        .custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
        .custom-scrollbar::-webkit-scrollbar-thumb { background: rgba(var(--border), 0.1); border-radius: 10px; }
    </style>
</x-layouts.app>
