<x-layouts.app pageTitle="Inventory Management">

    <div class="p-6 lg:p-10 max-w-[1920px] mx-auto" x-data="{ 
        search: '{{ request('search', '') }}',
        warehouseId: '{{ request('warehouse_id', '') }}',
        stockStatus: '{{ request('stock_status', '') }}',
        perPage: '{{ request('perPage', 15) }}',
        stats: @js($stats),
        isLoading: false,

        async fetchTable(url) {
            this.isLoading = true;
            try {
                const res = await fetch(url, {
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json'
                    }
                });

                const data = await res.json();
                document.getElementById('inventory-table-container').innerHTML = data.table;
                this.stats = data.stats;
            } finally {
                this.isLoading = false;
            }
        },

        async performSearch() {
            let params = new URLSearchParams({
                search: this.search,
                warehouse_id: this.warehouseId,
                stock_status: this.stockStatus,
                perPage: this.perPage
            });

            const url = `{{ route('inventory.index') }}?${params.toString()}`;
            window.history.replaceState({}, '', `${window.location.pathname}?${params.toString()}`);
            await this.fetchTable(url);
        },

        async handlePagination(event) {
            const link = event.target.closest('a');
            if (!link || !link.href || !link.href.includes('page=')) {
                return;
            }

            event.preventDefault();
            await this.fetchTable(link.href);
        }
    }">
        <form id="inventory-import-form" action="{{ route('inventory.import') }}" method="POST" enctype="multipart/form-data" class="hidden">
            @csrf
            <input id="inventory-import-file" type="file" name="file" accept=".csv,text/csv" onchange="this.form.submit()">
        </form>
        
        <!-- Stats Widgets -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
            <div class="group relative p-6 rounded-3xl bg-card/40 border border-border/60 ring-1 ring-border/30 backdrop-blur-xl hover:bg-primary/5 transition-all duration-500 overflow-hidden shadow-2xl">
                <div class="absolute top-0 right-0 -mr-8 -mt-8 size-32 bg-primary/10 blur-[50px] rounded-full group-hover:bg-primary/20 transition-all duration-500"></div>
                <div class="flex items-center gap-5 relative z-10">
                    <div class="size-14 rounded-2xl bg-gradient-to-tr from-primary/20 to-primary/5 border border-primary/10 text-primary flex items-center justify-center shadow-inner group-hover:scale-110 transition-transform duration-500">
                        <x-ui.icon name="box" size="7" />
                    </div>
                    <div>
                        <p class="text-[10px] font-black uppercase tracking-widest text-muted-foreground/60 mb-1">Total Stock Items</p>
                        <div class="text-3xl font-black tracking-tighter text-foreground" x-text="stats.total"></div>
                    </div>
                </div>
            </div>

            <div class="group relative p-6 rounded-3xl bg-card/40 border border-border/60 ring-1 ring-border/30 backdrop-blur-xl hover:bg-orange-500/5 transition-all duration-500 overflow-hidden shadow-2xl">
                <div class="absolute top-0 right-0 -mr-8 -mt-8 size-32 bg-orange-500/10 blur-[50px] rounded-full group-hover:bg-orange-500/20 transition-all duration-500"></div>
                <div class="flex items-center gap-5 relative z-10">
                    <div class="size-14 rounded-2xl bg-gradient-to-tr from-orange-500/20 to-orange-500/5 border border-orange-500/10 text-orange-500 flex items-center justify-center shadow-inner group-hover:scale-110 transition-transform duration-500">
                        <x-ui.icon name="lock" size="7" />
                    </div>
                    <div>
                        <p class="text-[10px] font-black uppercase tracking-widest text-muted-foreground/60 mb-1">Total Reserved</p>
                        <div class="text-3xl font-black tracking-tighter text-foreground" x-text="parseFloat(stats.total_reserved).toLocaleString()"></div>
                    </div>
                </div>
            </div>

            <div class="group relative p-6 rounded-3xl bg-card/40 border border-border/60 ring-1 ring-border/30 backdrop-blur-xl hover:bg-emerald-500/5 transition-all duration-500 overflow-hidden shadow-2xl">
                <div class="absolute top-0 right-0 -mr-8 -mt-8 size-32 bg-emerald-500/10 blur-[50px] rounded-full group-hover:bg-emerald-500/20 transition-all duration-500"></div>
                <div class="flex items-center gap-5 relative z-10">
                    <div class="size-14 rounded-2xl bg-gradient-to-tr from-emerald-500/20 to-emerald-500/5 border border-emerald-500/10 text-emerald-500 flex items-center justify-center shadow-inner group-hover:scale-110 transition-transform duration-500">
                        <x-ui.icon name="truck" size="7" />
                    </div>
                    <div>
                        <p class="text-[10px] font-black uppercase tracking-widest text-muted-foreground/60 mb-1">Total Dispatched</p>
                        <div class="text-3xl font-black tracking-tighter text-foreground" x-text="parseFloat(stats.total_dispatched).toLocaleString()"></div>
                    </div>
                </div>
            </div>

            <div class="group relative p-6 rounded-3xl bg-card/40 border border-border/60 ring-1 ring-border/30 backdrop-blur-xl hover:bg-red-500/5 transition-all duration-500 overflow-hidden shadow-2xl">
                <div class="absolute top-0 right-0 -mr-8 -mt-8 size-32 bg-red-500/10 blur-[50px] rounded-full group-hover:bg-red-500/20 transition-all duration-500"></div>
                <div class="flex items-center gap-5 relative z-10">
                    <div class="size-14 rounded-2xl bg-gradient-to-tr from-red-500/20 to-red-500/5 border border-red-500/10 text-red-500 flex items-center justify-center shadow-inner group-hover:scale-110 transition-transform duration-500">
                        <x-ui.icon name="alert-circle" size="7" />
                    </div>
                    <div>
                        <p class="text-[10px] font-black uppercase tracking-widest text-muted-foreground/60 mb-1">Stock Alerts</p>
                        <div class="text-3xl font-black tracking-tighter text-red-500" x-text="stats.low_stock"></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Main Content Card -->
        <x-ui.card class="overflow-hidden border-border/60 shadow-2xl bg-card/30 backdrop-blur-2xl rounded-3xl ring-1 ring-border/20">
            <x-ui.card-header class="border-b border-border/40 bg-muted/10 p-6 lg:p-8">
                <div class="flex flex-col gap-6">
                    <!-- Title row -->
                    <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4">
                        <div class="flex items-start sm:items-center gap-4 min-w-0">
                            <div class="size-12 sm:size-14 shrink-0 rounded-2xl bg-gradient-to-br from-primary/25 via-primary/10 to-primary/5 border border-primary/15 text-primary flex items-center justify-center shadow-inner ring-1 ring-primary/10">
                                <x-ui.icon name="database" size="6" />
                            </div>
                            <div class="min-w-0">
                                <h2 class="text-lg sm:text-xl font-black text-foreground tracking-tight">Inventory Management</h2>
                                <p class="text-[10px] font-bold text-muted-foreground uppercase tracking-[0.2em] mt-1">Real-time tracking · reservations · dispatches</p>
                            </div>
                        </div>
                        <div class="flex flex-wrap items-center gap-2 w-full lg:w-auto lg:justify-end">
                            <x-ui.button variant="outline" size="sm" class="flex-1 sm:flex-none rounded-xl font-bold uppercase tracking-widest text-[10px] h-10 shadow-sm border-border/60 bg-background/40 backdrop-blur-sm" onclick="document.getElementById('inventory-import-file').click()">
                                <x-ui.icon name="upload" size="3" class="mr-2" />
                                Import
                            </x-ui.button>
                            <x-ui.button variant="outline" size="sm" class="flex-1 sm:flex-none rounded-xl font-bold uppercase tracking-widest text-[10px] h-10 shadow-sm border-border/60 bg-background/40 backdrop-blur-sm" @click="window.location.href = `{{ route('inventory.export') }}?${new URLSearchParams({ search: search, warehouse_id: warehouseId, stock_status: stockStatus, perPage: perPage }).toString()}`">
                                <x-ui.icon name="download" size="3" class="mr-2" />
                                Export
                            </x-ui.button>
                            <div class="flex items-center gap-2 flex-1 sm:flex-none">
                                <a href="{{ route('adjustments.create') }}" class="flex-1 sm:flex-none">
                                    <x-ui.button size="sm" class="w-full rounded-xl font-bold uppercase tracking-widest text-[10px] h-10 shadow-lg shadow-primary/25 ring-1 ring-primary/20">
                                        <x-ui.icon name="plus" size="3" class="mr-2" />
                                        Adjustment
                                    </x-ui.button>
                                </a>
                                <a href="{{ route('transfers.create') }}" class="flex-1 sm:flex-none">
                                    <x-ui.button size="sm" variant="outline" class="w-full rounded-xl font-bold uppercase tracking-widest text-[10px] h-10 shadow-sm border-border/60 bg-background/40 backdrop-blur-sm">
                                        <x-ui.icon name="repeat" size="3" class="mr-2" />
                                        Transfer
                                    </x-ui.button>
                                </a>
                            </div>
                        </div>
                    </div>

                    <!-- Filters & search -->
                    <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4 pt-4 border-t border-border/30">
                        <div class="flex flex-wrap items-center gap-2 w-full lg:w-auto">
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
                            
                            <select x-model="warehouseId" @change="performSearch()"
                                class="h-10 px-3 rounded-xl border border-border bg-background/50 text-xs font-medium focus:ring-1 focus:ring-primary outline-none shadow-sm min-w-[160px]">
                                <option value="">All Warehouses</option>
                                @foreach($warehouses as $warehouse)
                                    <option value="{{ $warehouse->id }}">{{ $warehouse->name }}</option>
                                @endforeach
                            </select>

                            <select x-model="stockStatus" @change="performSearch()"
                                class="h-10 px-3 rounded-xl border border-border bg-background/50 text-xs font-medium focus:ring-1 focus:ring-primary outline-none shadow-sm">
                                <option value="">All Stock Status</option>
                                <option value="available">Available Only</option>
                                <option value="low_stock">Low Stock Alerts</option>
                                <option value="out_of_stock">Out of Stock</option>
                            </select>
                        </div>

                        <div class="relative group w-full lg:max-w-md shrink-0">
                            <x-ui.icon name="search" size="4" class="absolute left-3 top-1/2 -translate-y-1/2 text-muted-foreground group-focus-within:text-primary transition-colors" />
                            <input type="text" x-model="search" @input.debounce.500ms="performSearch()"
                                placeholder="Search product name or SKU..."
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
                        <span class="text-[10px] font-black uppercase tracking-[0.25em] text-foreground/80">Syncing Inventory</span>
                    </div>
                </div>
                <div id="inventory-table-container" @click="handlePagination($event)" class="relative z-0">
                    @include('inventory.partials.table')
                </div>
            </x-ui.card-content>
        </x-ui.card>
    </div>

    <style>
        [x-cloak] { display: none !important; }
    </style>
</x-layouts.app>
