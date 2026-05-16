<x-layouts.app pageTitle="Brand Management">

    <div class="p-6 lg:p-10" x-data="{ 
        search: '{{ request('search', '') }}',
        perPage: '{{ request('perPage', 12) }}',
        stats: @js($stats),
        isLoading: false,
        editingBrand: null,
        selectedBrands: [],
        allSelected: false,

        toggleAll() {
            const checkboxes = document.querySelectorAll('input[name=\'brand_ids[]\']');
            if (this.allSelected) {
                this.selectedBrands = Array.from(checkboxes).map(el => parseInt(el.value));
            } else {
                this.selectedBrands = [];
            }
        },

        toggleBrand(id) {
            id = parseInt(id);
            if (this.selectedBrands.includes(id)) {
                this.selectedBrands = this.selectedBrands.filter(bId => bId !== id);
            } else {
                this.selectedBrands.push(id);
            }
        },

        async fetchTable(url) {
            if (this.isLoading) return;
            this.isLoading = true;
            try {
                const res = await fetch(url, {
                    headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
                });
                const data = await res.json();
                document.getElementById('table-container').innerHTML = data.table;
                this.stats = data.stats;
                this.selectedBrands = [];
                this.allSelected = false;
            } catch (error) {
                console.error('Fetch failed:', error);
            } finally {
                this.isLoading = false;
            }
        },

        async performSearch() {
            const params = new URLSearchParams({ search: this.search, perPage: this.perPage });
            const url = `{{ route('brands.index') }}?${params.toString()}`;
            window.history.replaceState({}, '', url);
            await this.fetchTable(url);
        },

        async handlePagination(event) {
            const link = event.target.closest('a');
            if (!link || !link.href || !link.href.includes('page=')) return;
            event.preventDefault();
            window.history.replaceState({}, '', link.href);
            await this.fetchTable(link.href);
        },

        openAddModal() {
            this.editingBrand = null;
            $dispatch('open-modal', { name: 'brand-modal' });
        },

        openEditModal(brand) {
            this.editingBrand = brand;
            $dispatch('open-modal', { name: 'brand-modal' });
        }
    }">

        <!-- Stats Widgets -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
            <x-ui.card class="p-6 bg-card/40 border-border/60 backdrop-blur-xl hover:bg-primary/5 transition-all duration-500 rounded-3xl group relative overflow-hidden">
                <div class="absolute top-0 right-0 -mr-8 -mt-8 size-32 bg-primary/10 blur-[50px] rounded-full group-hover:bg-primary/20 transition-all"></div>
                <div class="flex items-center gap-5 relative z-10">
                    <div class="size-14 rounded-2xl bg-gradient-to-tr from-primary/20 to-primary/5 border border-primary/10 text-primary flex items-center justify-center shadow-inner group-hover:scale-110 transition-transform duration-500">
                        <x-ui.icon name="award" size="7" />
                    </div>
                    <div>
                        <p class="text-[10px] font-black uppercase tracking-widest text-muted-foreground/60 mb-1">Total Brands</p>
                        <div class="text-3xl font-black tracking-tighter text-foreground" x-text="stats.total"></div>
                    </div>
                </div>
            </x-ui.card>

            <x-ui.card class="p-6 bg-card/40 border-border/60 backdrop-blur-xl hover:bg-emerald-500/5 transition-all duration-500 rounded-3xl group relative overflow-hidden">
                <div class="absolute top-0 right-0 -mr-8 -mt-8 size-32 bg-emerald-500/10 blur-[50px] rounded-full group-hover:bg-emerald-500/20 transition-all"></div>
                <div class="flex items-center gap-5 relative z-10">
                    <div class="size-14 rounded-2xl bg-gradient-to-tr from-emerald-500/20 to-emerald-500/5 border border-emerald-500/10 text-emerald-500 flex items-center justify-center shadow-inner group-hover:scale-110 transition-transform duration-500">
                        <x-ui.icon name="check-circle" size="7" />
                    </div>
                    <div>
                        <p class="text-[10px] font-black uppercase tracking-widest text-muted-foreground/60 mb-1">Active</p>
                        <div class="text-3xl font-black tracking-tighter text-foreground" x-text="stats.active"></div>
                    </div>
                </div>
            </x-ui.card>

            <x-ui.card class="p-6 bg-card/40 border-border/60 backdrop-blur-xl hover:bg-red-500/5 transition-all duration-500 rounded-3xl group relative overflow-hidden">
                <div class="absolute top-0 right-0 -mr-8 -mt-8 size-32 bg-red-500/10 blur-[50px] rounded-full group-hover:bg-red-500/20 transition-all"></div>
                <div class="flex items-center gap-5 relative z-10">
                    <div class="size-14 rounded-2xl bg-gradient-to-tr from-red-500/20 to-red-500/5 border border-red-500/10 text-red-500 flex items-center justify-center shadow-inner group-hover:scale-110 transition-transform duration-500">
                        <x-ui.icon name="x-circle" size="7" />
                    </div>
                    <div>
                        <p class="text-[10px] font-black uppercase tracking-widest text-muted-foreground/60 mb-1">Inactive</p>
                        <div class="text-3xl font-black tracking-tighter text-foreground" x-text="stats.inactive"></div>
                    </div>
                </div>
            </x-ui.card>
        </div>

        <x-ui.card class="overflow-hidden border-border/60 shadow-2xl bg-card/30 backdrop-blur-2xl rounded-3xl">
            <x-ui.card-header class="border-b border-border/40 bg-muted/10 p-8">
                <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-6">
                    <div class="flex flex-wrap items-center gap-3">
                        <div class="flex bg-muted/50 px-4 py-1.5 rounded-xl border border-border/50 shadow-inner">
                            <span class="text-xs font-bold text-primary tracking-widest uppercase">Brands List</span>
                        </div>
                        
                        <!-- Bulk Actions -->
                        <div x-show="selectedBrands.length > 0" x-cloak x-transition class="flex items-center gap-2">
                            <x-ui.dropdown>
                                <x-slot name="trigger">
                                    <x-ui.button variant="outline" size="sm" class="rounded-xl border-primary/20 bg-primary/5 text-primary font-bold shadow-sm whitespace-nowrap">
                                        <span x-text="selectedBrands.length"></span> Selected
                                        <x-ui.icon name="chevron-down" size="3" class="ml-2" />
                                    </x-ui.button>
                                </x-slot>
                                <x-slot name="content">
                                    <x-ui.dropdown-label>Bulk Actions</x-ui.dropdown-label>
                                    <form action="{{ route('brands.bulk-delete') }}" method="POST" onsubmit="return confirm('Delete selected brands?')">
                                        @csrf
                                        <input type="hidden" name="ids" :value="JSON.stringify(selectedBrands)">
                                        <button type="submit" class="w-full text-left px-2 py-1.5 text-xs hover:bg-muted rounded-md flex items-center text-destructive">
                                            <x-ui.icon name="trash" size="3" class="mr-2" /> Delete Selected
                                        </button>
                                    </form>
                                </x-slot>
                            </x-ui.dropdown>
                        </div>
                    </div>

                    <div class="flex flex-wrap items-center gap-3 w-full lg:w-auto">
                        <div class="flex items-center gap-2">
                            <span class="text-[10px] font-bold text-muted-foreground uppercase tracking-widest hidden sm:inline-block">Show</span>
                            <select x-model="perPage" @change="performSearch()" class="h-11 px-3 py-1.5 rounded-xl border border-border bg-background/50 focus:bg-background focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all text-xs font-medium shadow-sm">
                                <option value="5">5</option>
                                <option value="12">12</option>
                                <option value="24">24</option>
                                <option value="50">50</option>
                            </select>
                        </div>
                        <div class="relative group w-full lg:w-64 shrink-0">
                            <x-ui.icon name="search" size="4" class="absolute left-3 top-1/2 -translate-y-1/2 text-muted-foreground group-focus-within:text-primary transition-colors" />
                            <input type="text" x-model="search" @input.debounce.500ms="performSearch" placeholder="Search brands..." 
                                class="pl-9 pr-4 py-2.5 rounded-xl border border-border bg-background/50 focus:bg-background focus:ring-2 focus:ring-primary/20 transition-all w-full text-xs shadow-sm outline-none">
                        </div>
                        <x-ui.button @click.stop="openAddModal" class="rounded-xl font-black uppercase tracking-widest text-[10px] h-11 px-6 shadow-lg shadow-primary/20 w-full lg:w-auto">
                            <x-ui.icon name="plus" size="3" class="mr-2" /> Add Brand
                        </x-ui.button>
                    </div>
                </div>
            </x-ui.card-header>

            <x-ui.card-content class="p-0 relative">
                <div x-show="isLoading" x-cloak class="absolute inset-0 z-50 bg-background/50 backdrop-blur-[2px] flex items-center justify-center">
                    <x-ui.icon name="refresh-cw" class="animate-spin text-primary" size="6" />
                </div>
                <div id="table-container" @click="handlePagination($event)">
                    @include('brands.partials.table')
                </div>
            </x-ui.card-content>
        </x-ui.card>

        @include('brands.partials.modal')
    </div>
</x-layouts.app>
