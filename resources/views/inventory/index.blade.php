<x-layouts.app>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-foreground leading-tight">
            {{ __('Inventory Management') }}
        </h2>
    </x-slot>

    <div class="p-6 lg:p-10" x-data="{ 
        search: '{{ request('search', '') }}',
        warehouseId: '{{ request('warehouse_id', '') }}',
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
        @if(session('success'))
            <div class="mb-6 rounded-2xl border border-emerald-500/20 bg-emerald-500/10 px-4 py-3 text-xs font-bold text-emerald-700 dark:text-emerald-300">
                {{ session('success') }}
            </div>
        @endif

        @if(session('error'))
            <div class="mb-6 rounded-2xl border border-destructive/20 bg-destructive/10 px-4 py-3 text-xs font-bold text-destructive">
                {{ session('error') }}
            </div>
        @endif

        @error('file')
            <div class="mb-6 rounded-2xl border border-destructive/20 bg-destructive/10 px-4 py-3 text-xs font-bold text-destructive">
                {{ $message }}
            </div>
        @enderror

        <form id="inventory-import-form" action="{{ route('inventory.import') }}" method="POST" enctype="multipart/form-data" class="hidden">
            @csrf
            <input id="inventory-import-file" type="file" name="file" accept=".csv,text/csv" onchange="this.form.submit()">
        </form>
        
        <!-- Stats Widgets -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
            <div class="group relative p-6 rounded-3xl bg-card/40 border border-border/60 backdrop-blur-xl hover:bg-orange-500/5 transition-all duration-500 overflow-hidden shadow-2xl">
                <div class="absolute top-0 right-0 -mr-8 -mt-8 size-32 bg-orange-500/10 blur-[50px] rounded-full group-hover:bg-orange-500/20 transition-all"></div>
                <div class="flex items-center gap-5 relative z-10">
                    <div class="size-14 rounded-2xl bg-gradient-to-tr from-orange-500/20 to-orange-500/5 border border-orange-500/10 text-orange-500 flex items-center justify-center shadow-inner group-hover:scale-110 transition-transform duration-500">
                        <x-ui.icon name="box" size="7" />
                    </div>
                    <div>
                        <p class="text-[10px] font-black uppercase tracking-widest text-muted-foreground/60 mb-1">Total Stock Items</p>
                        <div class="text-3xl font-black tracking-tighter text-foreground" x-text="stats.total"></div>
                    </div>
                </div>
            </div>

            <div class="group relative p-6 rounded-3xl bg-card/40 border border-border/60 backdrop-blur-xl hover:bg-red-500/5 transition-all duration-500 overflow-hidden shadow-2xl">
                <div class="absolute top-0 right-0 -mr-8 -mt-8 size-32 bg-red-500/10 blur-[50px] rounded-full group-hover:bg-red-500/20 transition-all"></div>
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

            <div class="group relative p-6 rounded-3xl bg-card/40 border border-border/60 backdrop-blur-xl hover:bg-blue-500/5 transition-all duration-500 overflow-hidden shadow-2xl">
                <div class="absolute top-0 right-0 -mr-8 -mt-8 size-32 bg-blue-500/10 blur-[50px] rounded-full group-hover:bg-blue-500/20 transition-all"></div>
                <div class="flex items-center gap-5 relative z-10">
                    <div class="size-14 rounded-2xl bg-gradient-to-tr from-blue-500/20 to-blue-500/5 border border-blue-500/10 text-blue-500 flex items-center justify-center shadow-inner group-hover:scale-110 transition-transform duration-500">
                        <x-ui.icon name="home" size="7" />
                    </div>
                    <div>
                        <p class="text-[10px] font-black uppercase tracking-widest text-muted-foreground/60 mb-1">Warehouses</p>
                        <div class="text-3xl font-black tracking-tighter text-foreground" x-text="stats.warehouses_count"></div>
                    </div>
                </div>
            </div>
        </div>

        <x-ui.card class="overflow-hidden border-border/60 shadow-2xl bg-card/30 backdrop-blur-2xl rounded-3xl">
            <x-ui.card-header class="border-b border-border/40 bg-muted/10 p-6">
                <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-6">
                    <div class="flex items-center gap-3">
                        <div class="size-12 rounded-2xl bg-primary/10 border border-primary/20 text-primary flex items-center justify-center shadow-inner">
                            <x-ui.icon name="database" size="6" />
                        </div>
                        <div>
                            <h3 class="text-lg font-black text-foreground tracking-tight">Stock Movement</h3>
                            <p class="text-[10px] uppercase font-bold text-muted-foreground tracking-widest">Real-time inventory tracking</p>
                        </div>
                    </div>

                    <div class="flex flex-wrap items-center gap-2 w-full lg:w-auto">
                        <x-ui.button type="button" variant="outline" size="sm" class="flex-1 sm:flex-none rounded-xl font-bold uppercase tracking-widest text-[10px] h-11 shadow-sm" onclick="document.getElementById('inventory-import-file').click()">
                            <x-ui.icon name="upload" size="3" class="mr-2" />
                            Import
                        </x-ui.button>
                        <x-ui.button type="button" variant="outline" size="sm" class="flex-1 sm:flex-none rounded-xl font-bold uppercase tracking-widest text-[10px] h-11 shadow-sm" @click="window.location.href = `{{ route('inventory.export') }}?${new URLSearchParams({ search: search, warehouse_id: warehouseId, perPage: perPage }).toString()}`">
                            <x-ui.icon name="download" size="3" class="mr-2" />
                            Export
                        </x-ui.button>
                    </div>
                </div>

                <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4 pt-2">
                    <div class="flex flex-wrap items-center gap-2 w-full lg:w-auto">
                        <div class="flex items-center gap-2">
                            <span class="text-[10px] font-bold text-muted-foreground uppercase tracking-widest hidden sm:inline-block">Show</span>
                            <select x-model="perPage" @change="performSearch" 
                                class="h-11 px-4 rounded-2xl border border-border/60 bg-background/50 text-[10px] font-black uppercase tracking-widest focus:ring-2 focus:ring-primary/20 transition-all text-foreground">
                                <option value="5" class="bg-card">5</option>
                                <option value="10" class="bg-card">10</option>
                                <option value="15" class="bg-card">15</option>
                                <option value="20" class="bg-card">20</option>
                                <option value="50" class="bg-card">50</option>
                            </select>
                        </div>

                        <select x-model="warehouseId" @change="performSearch" 
                            class="h-11 px-4 rounded-2xl border border-border/60 bg-background/50 text-[10px] font-black uppercase tracking-widest focus:ring-2 focus:ring-primary/20 transition-all text-foreground">
                            <option value="" class="bg-card">All Warehouses</option>
                            @foreach($warehouses as $warehouse)
                                <option value="{{ $warehouse->id }}" class="bg-card">{{ $warehouse->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="relative group w-full lg:max-w-xs shrink-0">
                            <x-ui.icon name="search" size="4" class="absolute left-4 top-1/2 -translate-y-1/2 text-muted-foreground group-focus-within:text-primary transition-colors" />
                            <input type="text" x-model="search" @input.debounce.500ms="performSearch" placeholder="Search by SKU or Name..." 
                                class="pl-11 pr-4 h-11 rounded-2xl border border-border/60 bg-background/50 focus:bg-background focus:ring-2 focus:ring-primary/20 transition-all text-xs font-medium w-full shadow-inner placeholder:text-muted-foreground/40">
                    </div>
                </div>
            </x-ui.card-header>

            <x-ui.card-content class="p-0 relative">
                <div x-show="isLoading" x-cloak class="absolute inset-0 z-50 bg-background/50 backdrop-blur-[2px] flex items-center justify-center">
                    <x-ui.icon name="refresh-cw" class="animate-spin text-primary" size="6" />
                </div>
                <div id="inventory-table-container" @click="handlePagination($event)">
                    @include('inventory.partials.table')
                </div>
            </x-ui.card-content>
        </x-ui.card>
    </div>

    <style>
        [x-cloak] { display: none !important; }
    </style>
</x-layouts.app>
