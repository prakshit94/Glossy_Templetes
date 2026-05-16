<x-layouts.app pageTitle="Stock Transfers">

    <div class="p-6 lg:p-10 max-w-[1920px] mx-auto" x-data="{ 
        search: '{{ request('search', '') }}',
        status: '{{ request('status', '') }}',
        stats: @js($stats),
        isLoading: false,

        async performSearch() {
            this.isLoading = true;
            let params = new URLSearchParams({
                search: this.search,
                status: this.status
            });

            window.history.replaceState({}, '', `${window.location.pathname}?${params.toString()}`);

            const res = await fetch(
                `{{ route('transfers.index') }}?${params.toString()}`,
                { headers: { 
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                } }
            );
            
            const data = await res.json();
            document.getElementById('transfers-table-container').innerHTML = data.table;
            this.stats = data.stats;
            this.isLoading = false;
        },

        clearFilters() {
            this.search = '';
            this.status = '';
            this.performSearch();
        }
    }">
        @if(session('success'))
            <div class="mb-6 animate-in fade-in slide-in-from-top-4 duration-500 rounded-2xl border border-emerald-500/20 bg-emerald-500/10 px-4 py-3 text-xs font-bold text-emerald-700 dark:text-emerald-300 backdrop-blur-md shadow-lg shadow-emerald-500/5">
                <div class="flex items-center gap-2">
                    <x-ui.icon name="check-circle" size="4" />
                    {{ session('success') }}
                </div>
            </div>
        @endif

        @if(session('error'))
            <div class="mb-6 animate-in fade-in slide-in-from-top-4 duration-500 rounded-2xl border border-destructive/20 bg-destructive/10 px-4 py-3 text-xs font-bold text-destructive backdrop-blur-md shadow-lg shadow-destructive/5">
                <div class="flex items-center gap-2">
                    <x-ui.icon name="alert-circle" size="4" />
                    {{ session('error') }}
                </div>
            </div>
        @endif
        
        <!-- Stats Widgets -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
            <div class="group relative p-6 rounded-3xl bg-card/40 border border-border/60 ring-1 ring-border/30 backdrop-blur-xl hover:bg-primary/5 transition-all duration-500 overflow-hidden shadow-2xl">
                <div class="absolute top-0 right-0 -mr-8 -mt-8 size-32 bg-primary/10 blur-[50px] rounded-full group-hover:bg-primary/20 transition-all duration-500"></div>
                <div class="flex items-center gap-5 relative z-10">
                    <div class="size-14 rounded-2xl bg-gradient-to-tr from-primary/20 to-primary/5 border border-primary/10 text-primary flex items-center justify-center shadow-inner group-hover:scale-110 transition-transform duration-500">
                        <x-ui.icon name="repeat" size="7" />
                    </div>
                    <div>
                        <p class="text-[10px] font-black uppercase tracking-widest text-muted-foreground/60 mb-1">Total Transfers</p>
                        <div class="text-3xl font-black tracking-tighter text-foreground" x-text="stats.total"></div>
                    </div>
                </div>
            </div>

            <div class="group relative p-6 rounded-3xl bg-card/40 border border-border/60 ring-1 ring-border/30 backdrop-blur-xl hover:bg-orange-500/5 transition-all duration-500 overflow-hidden shadow-2xl">
                <div class="absolute top-0 right-0 -mr-8 -mt-8 size-32 bg-orange-500/10 blur-[50px] rounded-full group-hover:bg-orange-500/20 transition-all duration-500"></div>
                <div class="flex items-center gap-5 relative z-10">
                    <div class="size-14 rounded-2xl bg-gradient-to-tr from-orange-500/20 to-orange-500/5 border border-orange-500/10 text-orange-500 flex items-center justify-center shadow-inner group-hover:scale-110 transition-transform duration-500">
                        <x-ui.icon name="send" size="7" />
                    </div>
                    <div>
                        <p class="text-[10px] font-black uppercase tracking-widest text-muted-foreground/60 mb-1">In-Transit (Pending)</p>
                        <div class="text-3xl font-black tracking-tighter text-orange-500" x-text="stats.pending"></div>
                    </div>
                </div>
            </div>

            <div class="group relative p-6 rounded-3xl bg-card/40 border border-border/60 ring-1 ring-border/30 backdrop-blur-xl hover:bg-emerald-500/5 transition-all duration-500 overflow-hidden shadow-2xl">
                <div class="absolute top-0 right-0 -mr-8 -mt-8 size-32 bg-emerald-500/10 blur-[50px] rounded-full group-hover:bg-emerald-500/20 transition-all duration-500"></div>
                <div class="flex items-center gap-5 relative z-10">
                    <div class="size-14 rounded-2xl bg-gradient-to-tr from-emerald-500/20 to-emerald-500/5 border border-emerald-500/10 text-emerald-500 flex items-center justify-center shadow-inner group-hover:scale-110 transition-transform duration-500">
                        <x-ui.icon name="check-circle" size="7" />
                    </div>
                    <div>
                        <p class="text-[10px] font-black uppercase tracking-widest text-muted-foreground/60 mb-1">Successfully Received</p>
                        <div class="text-3xl font-black tracking-tighter text-emerald-500" x-text="stats.received"></div>
                    </div>
                </div>
            </div>

            <div class="group relative p-6 rounded-3xl bg-card/40 border border-border/60 ring-1 ring-border/30 backdrop-blur-xl hover:bg-muted/10 transition-all duration-500 overflow-hidden shadow-2xl">
                <div class="absolute top-0 right-0 -mr-8 -mt-8 size-32 bg-muted/10 blur-[50px] rounded-full group-hover:bg-muted/20 transition-all duration-500"></div>
                <div class="flex items-center gap-5 relative z-10">
                    <div class="size-14 rounded-2xl bg-gradient-to-tr from-muted/20 to-muted/5 border border-border/10 text-muted-foreground flex items-center justify-center shadow-inner group-hover:scale-110 transition-transform duration-500">
                        <x-ui.icon name="file-text" size="7" />
                    </div>
                    <div>
                        <p class="text-[10px] font-black uppercase tracking-widest text-muted-foreground/60 mb-1">Draft Transfers</p>
                        <div class="text-3xl font-black tracking-tighter text-foreground" x-text="stats.draft"></div>
                    </div>
                </div>
            </div>
        </div>

        <x-ui.card class="overflow-hidden border-border/60 shadow-2xl bg-card/30 backdrop-blur-2xl rounded-3xl ring-1 ring-border/20">
            <x-ui.card-header class="border-b border-border/40 bg-muted/10 p-6 lg:p-8">
                <div class="flex flex-col gap-6">
                    <!-- Title Row -->
                    <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4">
                        <div class="flex items-start sm:items-center gap-4 min-w-0">
                            <div class="size-12 sm:size-14 shrink-0 rounded-2xl bg-gradient-to-br from-primary/25 via-primary/10 to-primary/5 border border-primary/15 text-primary flex items-center justify-center shadow-inner ring-1 ring-primary/10">
                                <x-ui.icon name="repeat" size="6" />
                            </div>
                            <div class="min-w-0">
                                <h2 class="text-lg sm:text-xl font-black text-foreground tracking-tight">Stock Transfers</h2>
                                <p class="text-[10px] font-bold text-muted-foreground uppercase tracking-[0.2em] mt-1">Inter-warehouse movement · logistics · rebalancing</p>
                            </div>
                        </div>
                        <div class="flex flex-wrap items-center gap-2 w-full lg:w-auto lg:justify-end">
                            <x-ui.button variant="outline" size="sm" class="flex-1 sm:flex-none rounded-xl font-bold uppercase tracking-widest text-[10px] h-10 shadow-sm border-border/60 bg-background/40 backdrop-blur-sm">
                                <x-ui.icon name="download" size="3" class="mr-2" />
                                Export CSV
                            </x-ui.button>
                            <a href="{{ route('transfers.create') }}" class="flex-1 sm:flex-none">
                                <x-ui.button size="sm" class="w-full rounded-xl font-bold uppercase tracking-widest text-[10px] h-10 shadow-lg shadow-primary/25 ring-1 ring-primary/20">
                                    <x-ui.icon name="plus" size="3" class="mr-2" />
                                    New Transfer
                                </x-ui.button>
                            </a>
                        </div>
                    </div>

                    <!-- Filters & Search Row -->
                    <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4 pt-4 border-t border-border/30">
                        <div class="flex flex-wrap items-center gap-3">
                            <div class="flex items-center gap-2">
                                <span class="text-[10px] font-bold text-muted-foreground uppercase tracking-widest">Status</span>
                                <select x-model="status" @change="performSearch" 
                                    class="h-10 px-4 rounded-xl border border-border bg-background/50 text-[10px] font-black uppercase tracking-widest focus:ring-2 focus:ring-primary/20 transition-all">
                                    <option value="">All Statuses</option>
                                    <option value="draft">Draft (Unsent)</option>
                                    <option value="sent">Sent (In-Transit)</option>
                                    <option value="received">Received (Completed)</option>
                                    <option value="cancelled">Cancelled</option>
                                </select>
                            </div>
                            <button @click="clearFilters" class="h-10 px-4 rounded-xl text-[10px] font-black uppercase tracking-widest border border-border/60 bg-muted/20 hover:bg-muted/40 transition-colors">
                                Reset Filters
                            </button>
                        </div>

                        <div class="relative group w-full lg:max-w-md shrink-0">
                            <x-ui.icon name="search" size="4" class="absolute left-3 top-1/2 -translate-y-1/2 text-muted-foreground group-focus-within:text-primary transition-colors" />
                            <input type="text" x-model="search" @input.debounce.500ms="performSearch"
                                placeholder="Search by transfer no..."
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
                        <span class="text-[10px] font-black uppercase tracking-[0.25em] text-foreground/80">Syncing Transfers</span>
                    </div>
                </div>
                <div id="transfers-table-container" class="relative z-0">
                    @include('inventory.transfers.partials.table')
                </div>
            </x-ui.card-content>
        </x-ui.card>
    </div>

    <style>
        [x-cloak] { display: none !important; }
    </style>
</x-layouts.app>
