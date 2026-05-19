<x-layouts.app pageTitle="Product Catalog">

    @php
        $qCategory = request('category') ? explode(',', request('category')) : [];
        $qStatus = request('status') ? explode(',', request('status')) : [];
    @endphp

    <div class="p-6 lg:p-10" x-data="{ 
        selectedItems: [], 
        allSelected: false,
        search: '{{ request('search', '') }}',
        perPage: '{{ request('perPage', 10) }}',
        categoryFilter: @js($qCategory),
        statusFilter: @js($qStatus),
        categoriesList: @js($categoriesList ?? []),
        statusList: @js($statusList ?? []),
        filter: '{{ request('filter', 'active') }}',
        stats: @js($stats),
        isLoading: false,

        toggleAll() {
            if (this.allSelected) {
                this.selectedItems = Array.from(
                    document.querySelectorAll('input[name=\'product_ids[]\']')
                ).map(el => parseInt(el.value));
            } else {
                this.selectedItems = [];
            }
        },

        async performSearch() {
            this.isLoading = true;
            let params = new URLSearchParams({
                search: this.search,
                perPage: this.perPage,
                category: this.categoryFilter.join(','),
                status: this.statusFilter.join(','),
                filter: this.filter
            });

            // Persist to URL
            window.history.replaceState({}, '', `${window.location.pathname}?${params.toString()}`);

            try {
                const res = await fetch(
                    `{{ route('products.index') }}?${params.toString()}`,
                    { headers: { 
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json'
                    } }
                );
                
                const data = await res.json();
                document.getElementById('table-container').innerHTML = data.table;
                this.categoriesList = data.categoriesList;
                this.statusList = data.statusList;
                this.stats = data.stats;
            } catch (error) {
                console.error('Search failed:', error);
            } finally {
                this.isLoading = false;
                this.selectedItems = [];
                this.allSelected = false;
            }
        },

        clearFilters() {
            this.search = '';
            this.categoryFilter = [];
            this.statusFilter = [];
            this.filter = 'active';
            this.performSearch();
        }
    }">

        <!-- Stats Widgets -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
            <div class="group relative p-6 rounded-3xl bg-card/40 border border-border/60 backdrop-blur-xl hover:bg-primary/5 transition-all duration-500 overflow-hidden shadow-2xl">
                <div class="absolute top-0 right-0 -mr-8 -mt-8 size-32 bg-primary/10 blur-[50px] rounded-full group-hover:bg-primary/20 transition-all"></div>
                <div class="flex items-center gap-5 relative z-10">
                    <div class="size-14 rounded-2xl bg-gradient-to-tr from-primary/20 to-primary/5 border border-primary/10 text-primary flex items-center justify-center shadow-inner group-hover:scale-110 transition-transform duration-500">
                        <x-ui.icon name="package" size="7" />
                    </div>
                    <div>
                        <p class="text-[10px] font-black uppercase tracking-widest text-muted-foreground/60 mb-1">Total Products</p>
                        <div class="text-3xl font-black tracking-tighter text-foreground" x-text="stats.total"></div>
                    </div>
                </div>
            </div>

            <div class="group relative p-6 rounded-3xl bg-card/40 border border-border/60 backdrop-blur-xl hover:bg-emerald-500/5 transition-all duration-500 overflow-hidden shadow-2xl">
                <div class="absolute top-0 right-0 -mr-8 -mt-8 size-32 bg-emerald-500/10 blur-[50px] rounded-full group-hover:bg-emerald-500/20 transition-all"></div>
                <div class="flex items-center gap-5 relative z-10">
                    <div class="size-14 rounded-2xl bg-gradient-to-tr from-emerald-500/20 to-emerald-500/5 border border-emerald-500/10 text-emerald-500 flex items-center justify-center shadow-inner group-hover:scale-110 transition-transform duration-500">
                        <x-ui.icon name="check-circle" size="7" />
                    </div>
                    <div>
                        <p class="text-[10px] font-black uppercase tracking-widest text-muted-foreground/60 mb-1">Active Items</p>
                        <div class="text-3xl font-black tracking-tighter text-foreground" x-text="stats.active"></div>
                    </div>
                </div>
            </div>

            <div class="group relative p-6 rounded-3xl bg-card/40 border border-border/60 backdrop-blur-xl hover:bg-orange-500/5 transition-all duration-500 overflow-hidden shadow-2xl">
                <div class="absolute top-0 right-0 -mr-8 -mt-8 size-32 bg-orange-500/10 blur-[50px] rounded-full group-hover:bg-orange-500/20 transition-all"></div>
                <div class="flex items-center gap-5 relative z-10">
                    <div class="size-14 rounded-2xl bg-gradient-to-tr from-orange-500/20 to-orange-500/5 border border-orange-500/10 text-orange-500 flex items-center justify-center shadow-inner group-hover:scale-110 transition-transform duration-500">
                        <x-ui.icon name="alert-triangle" size="7" />
                    </div>
                    <div>
                        <p class="text-[10px] font-black uppercase tracking-widest text-muted-foreground/60 mb-1">Low Stock Alerts</p>
                        <div class="text-3xl font-black tracking-tighter text-orange-500" x-text="stats.low_stock"></div>
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
                        <p class="text-[10px] font-black uppercase tracking-widest text-muted-foreground/60 mb-1">Out of Stock</p>
                        <div class="text-3xl font-black tracking-tighter text-red-500" x-text="stats.out_of_stock"></div>
                    </div>
                </div>
            </div>
        </div>

        <x-ui.card class="overflow-hidden border-border/60 shadow-2xl bg-card/30 backdrop-blur-2xl rounded-3xl">
            <x-ui.card-header class="border-b border-border/40 bg-muted/10 p-8">
                <div class="flex flex-col gap-6">
                    
                    <!-- Row 1: Actions -->
                    <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4">
                        <div class="flex flex-wrap items-center gap-3">
                            <div class="flex bg-muted/50 p-1 rounded-xl border border-border/50 shadow-inner">
                                <button @click="clearFilters()" class="px-4 py-1.5 rounded-lg text-xs font-bold transition-all bg-background shadow-sm text-primary ring-1 ring-border/50 uppercase tracking-tight hover:bg-muted">
                                    Clear All Filters
                                </button>
                            </div>

                            <!-- View Toggle -->
                            <div class="flex bg-muted/20 p-1 rounded-xl border border-border/60 shadow-inner">
                                <button @click="filter = 'active'; performSearch()" 
                                    :class="filter === 'active' ? 'bg-card shadow-sm text-primary ring-1 ring-border/20' : 'text-muted-foreground/60 hover:text-foreground'" 
                                    class="px-4 py-1.5 rounded-lg text-[10px] font-bold transition-all uppercase tracking-widest">
                                    Live
                                </button>
                                    <button @click="filter = 'trashed'; performSearch()" 
                                        :class="filter === 'trashed' ? 'bg-card shadow-sm text-destructive ring-1 ring-border/20' : 'text-muted-foreground/60 hover:text-foreground'" 
                                        class="px-4 py-1.5 rounded-lg text-[10px] font-bold transition-all uppercase tracking-widest">
                                        Disabled Items
                                    </button>
                            </div>

                            <div x-show="selectedItems.length > 0" x-cloak class="flex items-center gap-2">
                                <x-ui.dropdown>
                                    <x-slot name="trigger">
                                        <x-ui.button variant="outline" size="sm" class="rounded-xl border-primary/20 bg-primary/5 text-primary font-bold h-9">
                                            <span x-text="selectedItems.length"></span> Selected
                                            <x-ui.icon name="chevron-down" size="3" class="ml-2" />
                                        </x-ui.button>
                                    </x-slot>
                                    <x-slot name="content">
                                        <x-ui.dropdown-label>Bulk Actions</x-ui.dropdown-label>
                                        <form action="{{ route('products.bulk-delete') }}" method="POST" onsubmit="return confirm('Delete selected items?')">
                                            @csrf
                                            <input type="hidden" name="ids" :value="JSON.stringify(selectedItems)">
                                            <button type="submit" class="w-full text-left px-2 py-1.5 text-xs hover:bg-muted rounded-md flex items-center text-destructive">
                                                <x-ui.icon name="trash" size="3" class="mr-2" />
                                                Delete Selected
                                            </button>
                                        </form>
                                    </x-slot>
                                </x-ui.dropdown>
                            </div>
                        </div>

                        <div class="flex flex-wrap items-center gap-2">
                            <x-ui.button variant="outline" size="sm" class="rounded-xl font-bold uppercase tracking-widest text-[10px] h-9" onclick="alert('Import feature coming soon!')">
                                <x-ui.icon name="upload" size="3" class="mr-2" /> Import
                            </x-ui.button>
                            <x-ui.button variant="outline" size="sm" class="rounded-xl font-bold uppercase tracking-widest text-[10px] h-9" onclick="alert('Export feature coming soon!')">
                                <x-ui.icon name="download" size="3" class="mr-2" /> Export
                            </x-ui.button>
                            <a href="{{ route('products.create') }}">
                                <x-ui.button size="sm" class="rounded-xl font-bold uppercase tracking-widest text-[10px] h-9 shadow-lg shadow-primary/20">
                                    <x-ui.icon name="plus" size="3" class="mr-2" /> Add Product
                                </x-ui.button>
                            </a>
                        </div>
                    </div>

                    <!-- Row 2: Filters -->
                    <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4 pt-2 border-t border-border/30">
                        <div class="flex flex-wrap items-center gap-3">
                            
                            <div class="flex items-center gap-2">
                                <span class="text-[10px] font-bold text-muted-foreground uppercase tracking-widest">Show</span>
                                <select x-model="perPage" @change="performSearch()" class="h-9 px-3 rounded-xl border border-border bg-background/50 text-xs font-medium focus:ring-1 focus:ring-primary outline-none">
                                    <option value="10">10</option>
                                    <option value="25">25</option>
                                    <option value="50">50</option>
                                </select>
                            </div>

                            <!-- Product Filters -->
                            @include('products.partials.filters')
                        </div>

                        <div class="relative group w-full lg:max-w-xs">
                            <x-ui.icon name="search" size="4" class="absolute left-3 top-1/2 -translate-y-1/2 text-muted-foreground group-focus-within:text-primary transition-colors" />
                            <input type="text" x-model="search" @input.debounce.500ms="performSearch()" placeholder="Search catalog..." 
                                class="pl-9 pr-4 py-2 rounded-xl border border-border bg-background/50 focus:bg-background focus:ring-2 focus:ring-primary/20 transition-all w-full text-xs shadow-sm outline-none">
                        </div>
                    </div>
                </div>
            </x-ui.card-header>

            <x-ui.card-content class="p-0 relative">
                <div x-show="isLoading" x-cloak class="absolute inset-0 z-50 bg-background/50 backdrop-blur-[2px] flex items-center justify-center animate-in fade-in duration-300">
                    <x-ui.icon name="refresh-cw" class="animate-spin text-primary" size="6" />
                </div>
                <div id="table-container">
                    @include('products.partials.table')
                </div>
            </x-ui.card-content>
        </x-ui.card>
    </div>

    <style>
        [x-cloak] { display: none !important; }
    </style>
</x-layouts.app>