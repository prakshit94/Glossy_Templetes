<x-layouts.app>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Product Catalog') }}
        </h2>
    </x-slot>

    <div class="p-6 lg:p-10" x-data="{ 
        selectedItems: [], 
        allSelected: false,
        search: '{{ request('search', '') }}',
        perPage: '{{ request('perPage', 10) }}',
        statusFilter: '{{ request('status', '') }}',
        categoryFilter: '{{ request('category', '') }}',
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
                status: this.statusFilter,
                category: this.categoryFilter,
                filter: this.filter
            });

            // Persist to URL
            window.history.replaceState({}, '', `${window.location.pathname}?${params.toString()}`);

            const res = await fetch(
                `{{ route('products.index') }}?${params.toString()}`,
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
            this.allSelected = false;
        },

        clearFilters() {
            this.search = '';
            this.statusFilter = '';
            this.categoryFilter = '';
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
                <div class="absolute top-0 right-0 -mr-8 -mt-8 size-32 bg-red-500/10 blur-[50px] rounded-full group-hover:bg-red-500/20 transition-all"></div>
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
                <div class="flex flex-col gap-8">
                    
                    <!-- Row 1: Title & Primary Actions -->
                    <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-6">
                        <!-- Left Side: Title & Bulk Actions -->
                        <div class="flex flex-wrap items-center gap-3">
                            <div class="flex bg-muted/50 px-4 py-1.5 rounded-xl border border-border/50 shadow-inner">
                                <span class="text-xs font-bold text-primary tracking-widest uppercase">Master Catalog</span>
                            </div>

                            <!-- View Toggle (Product Specific) -->
                            <div class="flex bg-muted/20 p-1.5 rounded-2xl border border-border/60 shadow-inner">
                                <button @click="filter = 'active'; performSearch()" 
                                    :class="filter === 'active' ? 'bg-card shadow-lg text-primary ring-1 ring-border/20' : 'text-muted-foreground/60 hover:text-foreground'" 
                                    class="px-6 py-2 rounded-xl text-[10px] font-black transition-all uppercase tracking-widest">
                                    Live
                                </button>
                                <button @click="filter = 'trashed'; performSearch()" 
                                    :class="filter === 'trashed' ? 'bg-card shadow-lg text-destructive ring-1 ring-border/20' : 'text-muted-foreground/60 hover:text-foreground'" 
                                    class="px-6 py-2 rounded-xl text-[10px] font-black transition-all uppercase tracking-widest">
                                    Archive
                                </button>
                            </div>

                            <!-- Bulk Actions Dropdown -->
                            <div x-show="selectedItems.length > 0" x-cloak class="animate-in fade-in zoom-in duration-300">
                                <x-ui.dropdown>
                                    <x-slot name="trigger">
                                        <x-ui.button variant="outline" class="h-12 rounded-2xl border-primary/30 bg-primary/5 text-primary font-black uppercase tracking-widest text-[10px] px-6">
                                            <span x-text="selectedItems.length" class="mr-1.5"></span> Selected
                                            <x-ui.icon name="chevron-down" size="3" class="ml-2 opacity-50" />
                                        </x-ui.button>
                                    </x-slot>
                                    <x-slot name="content">
                                        <x-ui.dropdown-label>Bulk Actions</x-ui.dropdown-label>
                                        <form action="{{ route('products.bulk-delete') }}" method="POST" onsubmit="return confirm('Delete selected items?')">
                                            @csrf
                                            <input type="hidden" name="ids" :value="JSON.stringify(selectedItems)">
                                            <button type="submit" class="w-full text-left px-3 py-2 text-[10px] font-black hover:bg-destructive/10 rounded-xl flex items-center text-destructive uppercase tracking-widest transition-colors">
                                                <x-ui.icon name="trash" size="3.5" class="mr-2" /> Delete Selected
                                            </button>
                                        </form>
                                    </x-slot>
                                </x-ui.dropdown>
                            </div>
                        </div>

                        <!-- Right Side: Action Buttons -->
                        <div class="flex items-center gap-3">
                            <x-ui.button variant="outline" class="h-14 px-8 rounded-2xl font-black uppercase tracking-widest text-[11px] shadow-sm hover:bg-muted/10 transition-all" onclick="alert('Import feature coming soon!')">
                                <x-ui.icon name="activity" size="4" class="mr-2" /> Import
                            </x-ui.button>
                            <x-ui.button variant="outline" class="h-14 px-8 rounded-2xl font-black uppercase tracking-widest text-[11px] shadow-sm hover:bg-muted/10 transition-all" onclick="alert('Export feature coming soon!')">
                                <x-ui.icon name="external-link" size="4" class="mr-2" /> Export
                            </x-ui.button>
                            <a href="{{ route('products.create') }}">
                                <x-ui.button class="h-14 px-8 rounded-2xl font-black uppercase tracking-widest text-[11px] shadow-lg shadow-primary/20 hover:scale-[1.02] transition-all">
                                    <x-ui.icon name="plus" size="4" class="mr-2" /> Add Product
                                </x-ui.button>
                            </a>
                        </div>
                    </div>

                    <!-- Row 2: Filters & Search -->
                    <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4 pt-6 border-t border-border/40">
                        <!-- Left Side: Select Filters -->
                        <div class="flex flex-wrap items-center gap-4">
                            <!-- Per Page Selector -->
                            <div class="flex items-center gap-2">
                                <span class="text-[10px] font-black text-muted-foreground uppercase tracking-widest hidden sm:inline-block">Show</span>
                                <select x-model="perPage" @change="performSearch" 
                                    class="h-12 px-5 rounded-2xl border border-border/60 bg-card/40 text-[10px] font-black uppercase tracking-widest focus:ring-4 focus:ring-primary/10 transition-all text-foreground cursor-pointer">
                                    <option value="10">10</option>
                                    <option value="25">25</option>
                                    <option value="50">50</option>
                                </select>
                            </div>

                            <!-- Custom Filters (Product Specific) -->
                            <select x-model="categoryFilter" @change="performSearch" 
                                class="h-12 px-5 rounded-2xl border border-border/60 bg-card/40 text-[10px] font-black uppercase tracking-widest focus:ring-4 focus:ring-primary/10 transition-all text-foreground cursor-pointer">
                                <option value="">All Categories</option>
                                @foreach(\App\Models\Category::whereNull('parent_id')->get() as $cat)
                                    <option value="{{ $cat->slug }}">{{ $cat->name }}</option>
                                @endforeach
                            </select>

                            <select x-model="statusFilter" @change="performSearch" 
                                class="h-12 px-5 rounded-2xl border border-border/60 bg-card/40 text-[10px] font-black uppercase tracking-widest focus:ring-4 focus:ring-primary/10 transition-all text-foreground cursor-pointer">
                                <option value="">Status: All</option>
                                <option value="active">Active</option>
                                <option value="draft">Draft</option>
                                <option value="out_of_stock">Out of Stock</option>
                            </select>
                        </div>

                        <!-- Right Side: Search Input -->
                        <div class="relative group w-full lg:max-w-md shrink-0">
                            <x-ui.icon name="search" size="5" class="absolute left-4 top-1/2 -translate-y-1/2 text-muted-foreground/40 group-focus-within:text-primary transition-colors" />
                            <input type="text" x-model="search" @input.debounce.500ms="performSearch" 
                                placeholder="Search catalog..."
                                class="w-full h-14 pl-12 pr-4 rounded-2xl bg-background/50 border border-border/60 focus:bg-background focus:ring-4 focus:ring-primary/10 focus:border-primary outline-none transition-all text-sm font-bold shadow-inner">
                            <div x-show="isLoading" class="absolute right-4 top-1/2 -translate-y-1/2">
                                <svg class="animate-spin h-4 w-4 text-primary" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                            </div>
                        </div>
                    </div>
                </div>
            </x-ui.card-header>

            <x-ui.card-content class="p-0 relative">
                <div x-show="isLoading" x-cloak class="absolute inset-0 z-50 bg-background/40 backdrop-blur-sm flex items-center justify-center animate-in fade-in duration-300">
                    <div class="flex flex-col items-center gap-4">
                        <x-ui.icon name="refresh-cw" class="animate-spin text-primary" size="8" />
                        <span class="text-[10px] font-black uppercase tracking-[0.2em] text-primary">Refreshing Catalog</span>
                    </div>
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
