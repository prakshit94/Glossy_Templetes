<x-layouts.app>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-foreground leading-tight">
            {{ __('Warehouse Details') }}
        </h2>
    </x-slot>

    <div class="p-6 lg:p-10" x-data="{ 
        search: '{{ request('search', '') }}',
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
                stock_status: this.stockStatus,
                perPage: this.perPage
            });

            const url = `{{ route('warehouses.show', $warehouse->id) }}?${params.toString()}`;
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
        <div class="max-w-7xl mx-auto space-y-8">
            <!-- Header Card -->
            <x-ui.card class="overflow-hidden border-border/60 shadow-2xl bg-card/30 backdrop-blur-2xl rounded-3xl">
                <div class="p-8 flex flex-col md:flex-row md:items-center justify-between gap-6">
                    <div class="flex items-center gap-6">
                        <div class="size-20 rounded-3xl bg-primary/10 border border-primary/20 text-primary flex items-center justify-center shadow-inner">
                            <x-ui.icon name="warehouse" size="10" />
                        </div>
                        <div>
                            <div class="flex items-center gap-3 mb-2">
                                <h3 class="text-3xl font-black text-foreground tracking-tighter">{{ $warehouse->name }}</h3>
                                <x-ui.badge variant="{{ $warehouse->status === 'active' ? 'success' : 'outline' }}" class="rounded-lg px-2 py-0.5 text-[10px] font-black uppercase tracking-widest">
                                    {{ $warehouse->status }}
                                </x-ui.badge>
                            </div>
                            <div class="flex items-center gap-4 text-sm text-muted-foreground font-medium">
                                <span class="flex items-center gap-1.5"><x-ui.icon name="hash" size="3.5" /> {{ $warehouse->code }}</span>
                                <span class="flex items-center gap-1.5"><x-ui.icon name="map-pin" size="3.5" /> {{ $warehouse->address_line_1 ?? $warehouse->address ?? 'N/A' }}{{ !empty($warehouse->village?->state_name ?? $warehouse->state) ? ', ' . ($warehouse->village?->state_name ?? $warehouse->state) : '' }}</span>
                            </div>
                        </div>
                    </div>

                    <div class="flex flex-wrap items-center gap-3">
                        <a href="{{ route('warehouses.index') }}">
                            <x-ui.button variant="outline" size="sm" class="rounded-xl font-bold uppercase tracking-widest text-[10px]">
                                <x-ui.icon name="arrow-left" size="3" class="mr-2" /> Back
                            </x-ui.button>
                        </a>
                        <a href="{{ route('warehouses.edit', $warehouse) }}">
                            <x-ui.button size="sm" class="rounded-xl font-bold uppercase tracking-widest text-[10px] shadow-lg shadow-primary/20">
                                <x-ui.icon name="edit-2" size="3" class="mr-2" /> Edit Details
                            </x-ui.button>
                        </a>
                    </div>
                </div>
            </x-ui.card>

            <div class="grid grid-cols-1 lg:grid-cols-4 gap-8">
                <!-- Sidebar Info -->
                <div class="lg:col-span-1 space-y-6">
                    <x-ui.card class="overflow-hidden border-border/60 shadow-xl bg-card/30 backdrop-blur-xl rounded-3xl p-6">
                        <h4 class="text-[10px] font-black uppercase tracking-[0.2em] text-primary mb-6">Default Status</h4>
                        <div class="flex items-center gap-4">
                            <div class="size-12 rounded-2xl bg-muted flex items-center justify-center border border-border shadow-sm">
                                <x-ui.icon name="{{ $warehouse->is_default ? 'star' : 'star-off' }}" size="6" class="text-muted-foreground" />
                            </div>
                            <div>
                                <p class="text-sm font-bold text-foreground">{{ $warehouse->is_default ? 'Default Warehouse' : 'Standard Warehouse' }}</p>
                                <p class="text-[10px] text-muted-foreground font-medium uppercase tracking-tight">{{ $warehouse->is_default ? 'Used as default' : 'Not default' }}</p>
                            </div>
                        </div>
                    </x-ui.card>

                    <x-ui.card class="overflow-hidden border-border/60 shadow-xl bg-card/30 backdrop-blur-xl rounded-3xl p-6">
                        <h4 class="text-[10px] font-black uppercase tracking-[0.2em] text-primary mb-6">Inventory Summary</h4>
                        <div class="space-y-4">
                            <div class="flex justify-between items-center">
                                <span class="text-xs text-muted-foreground">Unique Products</span>
                                <span class="text-sm font-black text-foreground" x-text="stats.total"></span>
                            </div>
                            <div class="flex justify-between items-center">
                                <span class="text-xs text-muted-foreground">Total Stock Qty</span>
                                <span class="text-sm font-black text-foreground" x-text="parseFloat(stats.total_qty || 0).toLocaleString()"></span>
                            </div>
                            <div class="flex justify-between items-center">
                                <span class="text-xs text-muted-foreground">Total Reserved</span>
                                <span class="text-sm font-black text-orange-500" x-text="parseFloat(stats.total_reserved || 0).toLocaleString()"></span>
                            </div>
                            <div class="flex justify-between items-center">
                                <span class="text-xs text-muted-foreground">Low Stock Alerts</span>
                                <span class="text-sm font-black text-destructive" x-text="stats.low_stock"></span>
                            </div>
                        </div>
                    </x-ui.card>

                    <x-ui.card class="overflow-hidden border-border/60 shadow-xl bg-card/30 backdrop-blur-xl rounded-3xl p-6">
                        <div class="flex items-center gap-2 mb-6">
                            <x-ui.icon name="map-pin" size="4" class="text-primary" />
                            <h4 class="text-[10px] font-black uppercase tracking-[0.2em] text-primary">Location Details</h4>
                        </div>
                        <div class="space-y-3">
                            <p class="text-sm font-bold text-foreground mb-1">{{ $warehouse->address_line_1 ?? $warehouse->address ?? '—' }}</p>
                            @if($warehouse->address_line_2)
                                <p class="text-xs text-muted-foreground mb-4">{{ $warehouse->address_line_2 }}</p>
                            @endif
                            <div class="pt-3 border-t border-border/40 space-y-2">
                                <div class="flex justify-between">
                                    <span class="text-[10px] uppercase tracking-widest text-muted-foreground font-bold">Village</span>
                                    <span class="text-xs font-bold text-foreground">{{ $warehouse->village?->village_name ?? $warehouse->village_name ?? '—' }}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-[10px] uppercase tracking-widest text-muted-foreground font-bold">Post Office</span>
                                    <span class="text-xs font-bold text-foreground">{{ $warehouse->village?->post_so_name ?? $warehouse->post_office ?? '—' }}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-[10px] uppercase tracking-widest text-muted-foreground font-bold">Taluka</span>
                                    <span class="text-xs font-bold text-foreground">{{ $warehouse->village?->taluka_name ?? $warehouse->taluka ?? '—' }}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-[10px] uppercase tracking-widest text-muted-foreground font-bold">District</span>
                                    <span class="text-xs font-bold text-foreground">{{ $warehouse->village?->district_name ?? $warehouse->city ?? '—' }}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-[10px] uppercase tracking-widest text-muted-foreground font-bold">State</span>
                                    <span class="text-xs font-bold text-foreground">{{ !empty($warehouse->village?->state_name) ? $warehouse->village->state_name : (!empty($warehouse->state) ? $warehouse->state : '—') }}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-[10px] uppercase tracking-widest text-muted-foreground font-bold">Pincode</span>
                                    <span class="text-xs font-bold font-mono text-foreground">{{ $warehouse->village?->pincode ?? $warehouse->pincode ?? '—' }}</span>
                                </div>
                            </div>
                        </div>
                    </x-ui.card>
                </div>

                <!-- Stock Table Card -->
                <div class="lg:col-span-3">
                    <x-ui.card class="overflow-hidden border-border/60 shadow-2xl bg-card/30 backdrop-blur-2xl rounded-3xl ring-1 ring-border/20">
                        <x-ui.card-header class="border-b border-border/40 bg-muted/10 p-6 lg:p-8">
                            <div class="flex flex-col gap-6">
                                <!-- Title row -->
                                <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4">
                                    <div class="flex items-center gap-4 min-w-0">
                                        <div class="size-14 shrink-0 rounded-2xl bg-gradient-to-br from-primary/25 via-primary/10 to-primary/5 border border-primary/15 text-primary flex items-center justify-center shadow-inner ring-1 ring-primary/10">
                                            <x-ui.icon name="database" size="6" />
                                        </div>
                                        <div class="min-w-0">
                                            <h2 class="text-xl font-black text-foreground tracking-tight">Warehouse Inventory Levels</h2>
                                            <p class="text-[10px] font-bold text-muted-foreground uppercase tracking-[0.2em] mt-1">Physical stock, reservations and availability</p>
                                        </div>
                                    </div>
                                    <div class="flex items-center gap-2">
                                        <x-ui.button variant="outline" size="sm" class="rounded-xl font-bold uppercase tracking-widest text-[10px] h-10 shadow-sm border-border/60 bg-background/40 backdrop-blur-sm" @click="window.location.href = `{{ route('inventory.export') }}?${new URLSearchParams({ warehouse_id: '{{ $warehouse->id }}', search: search, stock_status: stockStatus }).toString()}`">
                                            <x-ui.icon name="download" size="3" class="mr-2" />
                                            Export Stock
                                        </x-ui.button>
                                    </div>
                                </div>

                                <!-- Filters & search -->
                                <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4 pt-4 border-t border-border/30">
                                    <div class="flex flex-wrap items-center gap-2">
                                        <div class="flex items-center gap-2">
                                            <span class="text-[10px] font-bold text-muted-foreground uppercase tracking-widest hidden sm:inline-block">Show</span>
                                            <select x-model="perPage" @change="performSearch" class="h-10 px-3 rounded-xl border border-border bg-background/50 text-xs font-medium focus:ring-1 focus:ring-primary outline-none shadow-sm">
                                                <option value="5">5</option>
                                                <option value="10">10</option>
                                                <option value="15">15</option>
                                                <option value="20">20</option>
                                                <option value="50">50</option>
                                            </select>
                                        </div>

                                        <select x-model="stockStatus" @change="performSearch"
                                            class="h-10 px-3 rounded-xl border border-border bg-background/50 text-xs font-medium focus:ring-1 focus:ring-primary outline-none shadow-sm">
                                            <option value="">All Stock Status</option>
                                            <option value="available">Available Only</option>
                                            <option value="low_stock">Low Stock Alerts</option>
                                            <option value="out_of_stock">Out of Stock</option>
                                        </select>
                                    </div>

                                    <div class="relative group w-full lg:max-w-md shrink-0">
                                        <x-ui.icon name="search" size="4" class="absolute left-3 top-1/2 -translate-y-1/2 text-muted-foreground group-focus-within:text-primary transition-colors" />
                                        <input type="text" x-model="search" @input.debounce.500ms="performSearch"
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
            </div>
        </div>
    </div>

    <style>
        [x-cloak] { display: none !important; }
    </style>
</x-layouts.app>
