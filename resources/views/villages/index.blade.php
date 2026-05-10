<x-layouts.app>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Village Management') }}
        </h2>
    </x-slot>

    @php
        $qState = request('state') ? explode(',', request('state')) : [];
        $qDistrict = request('district') ? explode(',', request('district')) : [];
        $qTaluka = request('taluka') ? explode(',', request('taluka')) : [];
    @endphp

    <div class="p-6 lg:p-10" x-data="{ 
        selectedItems: [], 
        allSelected: false,
        search: '{{ request('search', '') }}',
        perPage: '{{ request('perPage', 10) }}',
        stateFilter: @js($qState),
        districtFilter: @js($qDistrict),
        talukaFilter: @js($qTaluka),
        statesList: @js($statesList),
        districtsList: @js($districtsList),
        talukasList: @js($talukasList),
        stats: @js($stats),
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
            let params = new URLSearchParams({
                search: this.search,
                perPage: this.perPage,
                state: this.stateFilter.join(','),
                district: this.districtFilter.join(','),
                taluka: this.talukaFilter.join(',')
            });

            // Persist to URL
            window.history.replaceState({}, '', `${window.location.pathname}?${params.toString()}`);

            const res = await fetch(
                `{{ route('villages.index') }}?${params.toString()}`,
                { headers: { 
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                } }
            );
            const data = await res.json();
            
            document.getElementById('table-container').innerHTML = data.table;
            this.districtsList = data.districts;
            this.talukasList = data.talukas;
            this.stats = data.stats;
            
            // Sync dependent filters
            this.districtFilter = this.districtFilter.filter(d => this.districtsList.includes(d));
            this.talukaFilter = this.talukaFilter.filter(t => this.talukasList.includes(t));

            this.isLoading = false;
            this.selectedItems = [];
            this.allSelected = false;
        },

        clearFilters() {
            this.search = '';
            this.stateFilter = [];
            this.districtFilter = [];
            this.talukaFilter = [];
            this.performSearch();
        }
    }">

        <!-- Stats Widgets -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
            <div class="group relative p-6 rounded-3xl bg-white/[0.03] dark:bg-white/[0.02] border border-white/10 backdrop-blur-xl hover:bg-primary/5 transition-all duration-500 overflow-hidden shadow-2xl">
                <div class="absolute top-0 right-0 -mr-8 -mt-8 size-32 bg-primary/10 blur-[50px] rounded-full group-hover:bg-primary/20 transition-all duration-500"></div>
                <div class="flex items-center gap-5 relative z-10">
                    <div class="size-14 rounded-2xl bg-gradient-to-tr from-primary/20 to-primary/5 border border-primary/10 text-primary flex items-center justify-center shadow-inner group-hover:scale-110 transition-transform duration-500">
                        <x-ui.icon name="map" size="7" />
                    </div>
                    <div>
                        <p class="text-[10px] font-black uppercase tracking-widest text-muted-foreground/60 mb-1">Total Villages</p>
                        <div class="text-3xl font-black tracking-tighter text-foreground" x-text="stats.total"></div>
                    </div>
                </div>
            </div>

            <div class="group relative p-6 rounded-3xl bg-white/[0.03] dark:bg-white/[0.02] border border-white/10 backdrop-blur-xl hover:bg-emerald-500/5 transition-all duration-500 overflow-hidden shadow-2xl">
                <div class="absolute top-0 right-0 -mr-8 -mt-8 size-32 bg-emerald-500/10 blur-[50px] rounded-full group-hover:bg-emerald-500/20 transition-all duration-500"></div>
                <div class="flex items-center gap-5 relative z-10">
                    <div class="size-14 rounded-2xl bg-gradient-to-tr from-emerald-500/20 to-emerald-500/5 border border-emerald-500/10 text-emerald-500 flex items-center justify-center shadow-inner group-hover:scale-110 transition-transform duration-500">
                        <x-ui.icon name="map-pin" size="7" />
                    </div>
                    <div>
                        <p class="text-[10px] font-black uppercase tracking-widest text-muted-foreground/60 mb-1">Pincodes</p>
                        <div class="text-3xl font-black tracking-tighter text-foreground" x-text="stats.pincodes"></div>
                    </div>
                </div>
            </div>

            <div class="group relative p-6 rounded-3xl bg-white/[0.03] dark:bg-white/[0.02] border border-white/10 backdrop-blur-xl hover:bg-blue-500/5 transition-all duration-500 overflow-hidden shadow-2xl">
                <div class="absolute top-0 right-0 -mr-8 -mt-8 size-32 bg-blue-500/10 blur-[50px] rounded-full group-hover:bg-blue-500/20 transition-all duration-500"></div>
                <div class="flex items-center gap-5 relative z-10">
                    <div class="size-14 rounded-2xl bg-gradient-to-tr from-blue-500/20 to-blue-500/5 border border-primary/10 text-blue-500 flex items-center justify-center shadow-inner group-hover:scale-110 transition-transform duration-500">
                        <x-ui.icon name="navigation" size="7" />
                    </div>
                    <div>
                        <p class="text-[10px] font-black uppercase tracking-widest text-muted-foreground/60 mb-1">Districts</p>
                        <div class="text-3xl font-black tracking-tighter text-foreground" x-text="stats.districts_count"></div>
                    </div>
                </div>
            </div>

            <div class="group relative p-6 rounded-3xl bg-white/[0.03] dark:bg-white/[0.02] border border-white/10 backdrop-blur-xl hover:bg-orange-500/5 transition-all duration-500 overflow-hidden shadow-2xl">
                <div class="absolute top-0 right-0 -mr-8 -mt-8 size-32 bg-orange-500/10 blur-[50px] rounded-full group-hover:bg-orange-500/20 transition-all duration-500"></div>
                <div class="flex items-center gap-5 relative z-10">
                    <div class="size-14 rounded-2xl bg-gradient-to-tr from-orange-500/20 to-orange-500/5 border border-orange-500/10 text-orange-500 flex items-center justify-center shadow-inner group-hover:scale-110 transition-transform duration-500">
                        <x-ui.icon name="layers" size="7" />
                    </div>
                    <div>
                        <p class="text-[10px] font-black uppercase tracking-widest text-muted-foreground/60 mb-1">Services</p>
                        <div class="text-3xl font-black tracking-tighter text-foreground" x-text="stats.services"></div>
                    </div>
                </div>
            </div>
        </div>

        <x-ui.card class="overflow-hidden border-border/40 shadow-2xl bg-white/[0.03] dark:bg-white/[0.02] backdrop-blur-2xl rounded-3xl">
            <x-ui.card-header class="border-b border-white/10 bg-white/[0.02] p-6">
                <div class="flex flex-col gap-6">
                    
                    <!-- Row 1: Actions -->
                    <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4">
                        <div class="flex flex-wrap items-center gap-3">
                            <div class="flex bg-muted/50 p-1 rounded-xl border border-border/50 shadow-inner">
                                <button @click="clearFilters" class="px-4 py-1.5 rounded-lg text-xs font-bold transition-all bg-background shadow-sm text-primary ring-1 ring-border/50 uppercase tracking-tight hover:bg-muted">
                                    Clear All Filters
                                </button>
                            </div>

                            @canany(['villages.edit', 'villages.delete'])
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
                                        @can('villages.edit')
                                        @foreach(\App\Models\Service::active()->get() as $service)
                                            <form action="{{ route('villages.bulk-service') }}" method="POST">
                                                @csrf
                                                <input type="hidden" name="ids" :value="JSON.stringify(selectedItems)">
                                                <input type="hidden" name="service_id" value="{{ $service->id }}">
                                                <input type="hidden" name="status" value="available">
                                                <button type="submit" class="w-full text-left px-2 py-1.5 text-xs hover:bg-muted rounded-md flex items-center">
                                                    <x-ui.icon name="check-circle" size="3" class="mr-2 text-emerald-500" />
                                                    Enable {{ $service->name }}
                                                </button>
                                            </form>
                                        @endforeach
                                        @endcan
                                        
                                        @can('villages.delete')
                                        <div class="h-px bg-border/40 my-1"></div>
                                        <form action="{{ route('villages.bulk-delete') }}" method="POST" onsubmit="return confirm('Delete records?')">
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
                            @can('villages.import')
                            <form action="{{ route('villages.import') }}" method="POST" enctype="multipart/form-data" class="hidden" id="import-form">
                                @csrf
                                <input type="file" name="file" id="import-input" onchange="document.getElementById('import-form').submit()">
                            </form>
                            <x-ui.button variant="outline" size="sm" class="rounded-xl font-bold uppercase tracking-widest text-[10px] h-9" onclick="document.getElementById('import-input').click()">
                                <x-ui.icon name="upload" size="3" class="mr-2" /> Import
                            </x-ui.button>
                            @endcan

                            @can('villages.create')
                            <a href="{{ route('villages.create') }}">
                                <x-ui.button size="sm" class="rounded-xl font-bold uppercase tracking-widest text-[10px] h-9 shadow-lg shadow-primary/20">
                                    <x-ui.icon name="plus" size="3" class="mr-2" /> Add Village
                                </x-ui.button>
                            </a>
                            @endcan
                        </div>
                    </div>

                    <!-- Row 2: Filters -->
                    <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4 pt-2 border-t border-white/5">
                        <div class="flex flex-wrap items-center gap-3">
                            
                            <div class="flex items-center gap-2">
                                <span class="text-[10px] font-bold text-muted-foreground uppercase tracking-widest">Show</span>
                                <select x-model="perPage" @change="performSearch" class="h-9 px-3 rounded-xl border border-border bg-background/50 text-xs font-medium">
                                    <option value="10">10</option>
                                    <option value="25">25</option>
                                    <option value="50">50</option>
                                </select>
                            </div>

                            <!-- Geographic Filters -->
                            @include('villages.partials.filters')
                        </div>

                        <div class="relative group w-full lg:max-w-xs">
                            <x-ui.icon name="search" size="4" class="absolute left-3 top-1/2 -translate-y-1/2 text-muted-foreground group-focus-within:text-primary transition-colors" />
                            <input type="text" x-model="search" @input.debounce.500ms="performSearch" placeholder="Search villages..." 
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
                    @include('villages.partials.table')
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
