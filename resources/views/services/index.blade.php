<x-layouts.app>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-foreground leading-tight">
            {{ __('Service Catalog') }}
        </h2>
    </x-slot>

    <div class="p-6 lg:p-10" x-data="{ 
        selectedItems: [], 
        allSelected: false,
        search: '',
        isLoading: false,

        toggleAll() {
            if (this.allSelected) {
                this.selectedItems = Array.from(
                    document.querySelectorAll('input[name=\'item_ids[]\']')
                ).map(el => parseInt(el.value));
            } else {
                this.selectedItems = [];
            }
        },

        async performSearch() {
            this.isLoading = true;
            const res = await fetch(
                `{{ route('services.index') }}?search=${this.search}`,
                { headers: { 'X-Requested-With': 'XMLHttpRequest' } }
            );
            const html = await res.text();
            document.getElementById('table-container').innerHTML = html;
            this.isLoading = false;
            this.selectedItems = [];
            this.allSelected = false;
        }
    }">

        <!-- Stats Widgets -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
            <div class="group relative p-6 rounded-3xl bg-card/40 border border-border/60 backdrop-blur-xl hover:bg-primary/5 transition-all duration-500 overflow-hidden shadow-2xl">
                <div class="absolute top-0 right-0 -mr-8 -mt-8 size-32 bg-primary/10 blur-[50px] rounded-full group-hover:bg-primary/20 transition-all duration-500"></div>
                <div class="flex items-center gap-5 relative z-10">
                    <div class="size-14 rounded-2xl bg-gradient-to-tr from-primary/20 to-primary/5 border border-primary/10 text-primary flex items-center justify-center shadow-inner group-hover:scale-110 transition-transform duration-500">
                        <x-ui.icon name="layers" size="7" />
                    </div>
                    <div>
                        <p class="text-[10px] font-black uppercase tracking-widest text-muted-foreground/60 mb-1">Total Services</p>
                        <div class="text-3xl font-black tracking-tighter text-foreground">{{ number_format($stats['total'] ?? 0) }}</div>
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
                        <p class="text-[10px] font-black uppercase tracking-widest text-muted-foreground/60 mb-1">Active Now</p>
                        <div class="text-3xl font-black tracking-tighter text-foreground">{{ number_format($stats['active'] ?? 0) }}</div>
                    </div>
                </div>
            </div>

            <div class="group relative p-6 rounded-3xl bg-card/40 border border-border/60 backdrop-blur-xl hover:bg-blue-500/5 transition-all duration-500 overflow-hidden shadow-2xl">
                <div class="absolute top-0 right-0 -mr-8 -mt-8 size-32 bg-blue-500/10 blur-[50px] rounded-full group-hover:bg-blue-500/20 transition-all duration-500"></div>
                <div class="flex items-center gap-5 relative z-10">
                    <div class="size-14 rounded-2xl bg-gradient-to-tr from-blue-500/20 to-blue-500/5 border border-primary/10 text-blue-500 flex items-center justify-center shadow-inner group-hover:scale-110 transition-transform duration-500">
                        <x-ui.icon name="map-pin" size="7" />
                    </div>
                    <div>
                        <p class="text-[10px] font-black uppercase tracking-widest text-muted-foreground/60 mb-1">Mappings</p>
                        <div class="text-3xl font-black tracking-tighter text-foreground">{{ number_format($stats['mappings'] ?? 0) }}</div>
                    </div>
                </div>
            </div>
        </div>

        <x-ui.card class="overflow-hidden border-border/60 shadow-2xl bg-card/30 backdrop-blur-2xl rounded-3xl">
            <x-ui.card-header class="border-b border-border/40 bg-muted/10 p-6">
                <div class="flex flex-col gap-6">
                    
                    <!-- Row 1: Actions -->
                    <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4">
                        <div class="flex flex-wrap items-center gap-3">
                            <div class="flex bg-muted/50 p-1 rounded-xl border border-border/50 shadow-inner">
                                <a href="{{ route('services.index') }}" class="px-4 py-1.5 rounded-lg text-xs font-bold transition-all bg-background shadow-sm text-primary ring-1 ring-border/50 uppercase tracking-tight">
                                    All Services
                                </a>
                            </div>

                            @canany(['services.edit', 'services.delete'])
                            <div x-show="selectedItems.length > 0" x-cloak x-transition class="flex items-center gap-2">
                                <x-ui.dropdown>
                                    <x-slot name="trigger">
                                        <x-ui.button variant="outline" size="sm" class="rounded-xl border-primary/20 bg-primary/5 text-primary font-bold h-9">
                                            <span x-text="selectedItems.length"></span> Selected
                                            <x-ui.icon name="chevron-down" size="3" class="ml-2" />
                                        </x-ui.button>
                                    </x-slot>
                                    <x-slot name="content">
                                        <x-ui.dropdown-label>Bulk Actions</x-ui.dropdown-label>
                                        
                                        @can('services.edit')
                                        <form action="{{ route('services.bulk-status') }}" method="POST">
                                            @csrf
                                            <input type="hidden" name="ids" :value="JSON.stringify(selectedItems)">
                                            <input type="hidden" name="status" value="active">
                                            <button type="submit" class="w-full text-left px-2 py-1.5 text-xs hover:bg-muted rounded-md flex items-center">
                                                <x-ui.icon name="check-circle" size="3" class="mr-2 text-emerald-500" />
                                                Set as Active
                                            </button>
                                        </form>
                                        <form action="{{ route('services.bulk-status') }}" method="POST">
                                            @csrf
                                            <input type="hidden" name="ids" :value="JSON.stringify(selectedItems)">
                                            <input type="hidden" name="status" value="inactive">
                                            <button type="submit" class="w-full text-left px-2 py-1.5 text-xs hover:bg-muted rounded-md flex items-center text-orange-500">
                                                <x-ui.icon name="slash" size="3" class="mr-2" />
                                                Set as Inactive
                                            </button>
                                        </form>
                                        @endcan

                                        @can('services.delete')
                                        <x-ui.separator class="my-1 opacity-50" />
                                        <form action="{{ route('services.bulk-delete') }}" method="POST" onsubmit="return confirm('Delete records?')">
                                            @csrf
                                            <input type="hidden" name="ids" :value="JSON.stringify(selectedItems)">
                                            <button type="submit" class="w-full text-left px-2 py-1.5 text-xs hover:bg-muted rounded-md flex items-center text-destructive">
                                                <x-ui.icon name="trash" size="3" class="mr-2" />
                                                Delete Selected
                                            </button>
                                        </form>
                                        @endcan
                                    </x-slot>
                                </x-ui.dropdown>
                            </div>
                            @endcanany
                        </div>

                        <div class="flex flex-wrap items-center gap-2">
                            @can('services.create')
                            <a href="{{ route('services.create') }}">
                                <x-ui.button size="sm" class="rounded-xl font-bold uppercase tracking-widest text-[10px] h-9 shadow-lg shadow-primary/20">
                                    <x-ui.icon name="plus" size="3" class="mr-2" /> Add Service
                                </x-ui.button>
                            </a>
                            @endcan
                        </div>
                    </div>

                    <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4 pt-2 border-t border-border/40">
                        <div class="flex flex-wrap items-center gap-2">
                            <div class="flex items-center gap-2">
                                <span class="text-[10px] font-bold text-muted-foreground uppercase tracking-widest">Show</span>
                                <select class="h-9 px-3 rounded-xl border border-border/60 bg-background/50 text-xs font-medium text-foreground">
                                    <option class="bg-card">10</option>
                                    <option class="bg-card">25</option>
                                    <option class="bg-card">50</option>
                                </select>
                            </div>
                        </div>

                        <div class="relative group w-full lg:max-w-xs">
                            <x-ui.icon name="search" size="4" class="absolute left-3 top-1/2 -translate-y-1/2 text-muted-foreground group-focus-within:text-primary transition-colors" />
                            <input type="text" x-model="search" @input.debounce.500ms="performSearch" placeholder="Search services..." 
                                class="pl-9 pr-4 py-2 rounded-xl border border-border bg-background/50 focus:bg-background focus:ring-2 focus:ring-primary/20 transition-all w-full text-xs shadow-sm">
                        </div>
                    </div>
                </div>
            </x-ui.card-header>

            <x-ui.card-content class="p-0 relative">
                <div x-show="isLoading" x-cloak class="absolute inset-0 z-50 bg-background/50 backdrop-blur-[2px] flex items-center justify-center">
                    <x-ui.icon name="refresh-cw" class="animate-spin text-primary" size="6" />
                </div>
                <div id="table-container">
                    <div class="overflow-x-auto custom-scrollbar">
                        @include('services.partials.table')
                    </div>
                </div>
            </x-ui.card-content>
        </x-ui.card>
    </div>

    <style>
        [x-cloak] { display: none !important; }
        .custom-scrollbar::-webkit-scrollbar { height: 6px; width: 6px; }
        .custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
        .custom-scrollbar::-webkit-scrollbar-thumb { background: rgba(var(--border), 0.1); border-radius: 10px; }
    </style>
</x-layouts.app>
