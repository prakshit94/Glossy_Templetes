<x-layouts.app>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Inventory Management') }}
        </h2>
    </x-slot>

    <div class="p-6 lg:p-10" x-data="{ 
        search: '{{ request('search', '') }}',
        warehouseId: '{{ request('warehouse_id', '') }}',
        isLoading: false,

        async performSearch() {
            this.isLoading = true;
            let params = new URLSearchParams({
                search: this.search,
                warehouse_id: this.warehouseId
            });

            window.history.replaceState({}, '', `${window.location.pathname}?${params.toString()}`);

            const res = await fetch(
                `{{ route('inventory.index') }}?${params.toString()}`,
                { headers: { 'X-Requested-With': 'XMLHttpRequest' } }
            );
            
            const html = await res.text();
            document.getElementById('inventory-table-container').innerHTML = html;
            this.isLoading = false;
        }
    }">
        
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
                        <div class="text-3xl font-black tracking-tighter text-foreground" x-text="stocks.total"></div>
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
                        <div class="text-3xl font-black tracking-tighter text-red-500">{{ $stocks->where('quantity', '<=', 10)->count() }}</div>
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
                        <div class="text-3xl font-black tracking-tighter text-foreground">{{ $warehouses->count() }}</div>
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

                    <div class="flex flex-wrap items-center gap-4">
                        <select x-model="warehouseId" @change="performSearch" 
                            class="h-11 px-4 rounded-2xl border border-border/60 bg-background/50 text-[10px] font-black uppercase tracking-widest focus:ring-2 focus:ring-primary/20 transition-all text-foreground">
                            <option value="" class="bg-card">All Warehouses</option>
                            @foreach($warehouses as $warehouse)
                                <option value="{{ $warehouse->id }}" class="bg-card">{{ $warehouse->name }}</option>
                            @endforeach
                        </select>
                        
                        <div class="relative group lg:w-72">
                            <x-ui.icon name="search" size="4" class="absolute left-4 top-1/2 -translate-y-1/2 text-muted-foreground group-focus-within:text-primary transition-colors" />
                            <input type="text" x-model="search" @input.debounce.500ms="performSearch" placeholder="Search by SKU or Name..." 
                                class="pl-11 pr-4 h-11 rounded-2xl border border-border/60 bg-background/50 focus:bg-background focus:ring-2 focus:ring-primary/20 transition-all text-xs font-medium w-full shadow-inner placeholder:text-muted-foreground/40">
                        </div>
                    </div>
                </div>
            </x-ui.card-header>

            <x-ui.card-content class="p-0 relative">
                <div x-show="isLoading" x-cloak class="absolute inset-0 z-50 bg-background/50 backdrop-blur-[2px] flex items-center justify-center">
                    <x-ui.icon name="refresh-cw" class="animate-spin text-primary" size="6" />
                </div>
                <div id="inventory-table-container">
                    @include('inventory.partials.table')
                </div>
            </x-ui.card-content>
        </x-ui.card>
    </div>

    <style>
        [x-cloak] { display: none !important; }
    </style>
</x-layouts.app>
