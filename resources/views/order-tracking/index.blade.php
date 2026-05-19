<x-layouts.app pageTitle="Order Tracking & Logistics">

    <div class="p-6 lg:p-10 max-w-[1920px] mx-auto" x-data="{ 
        search: '{{ request('search', '') }}',
        statusFilter: '{{ request('status', '') }}' ? '{{ request('status') }}'.split(',') : [],
        carrierFilter: '{{ request('carrier', '') }}' ? '{{ request('carrier') }}'.split(',') : [],
        perPage: '{{ request('perPage', 15) }}',
        stats: @js($stats),
        availableCarriers: @js($availableCarriers ?? []),
        statusesList: ['pending', 'shipped', 'in_transit', 'delivered', 'failed'],
        isLoading: false,
        selectedShipments: [],
        allSelected: false,

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
                document.getElementById('tracking-table-container').innerHTML = data.table;
                this.stats = data.stats;
                this.selectedShipments = [];
                this.allSelected = false;
            } catch (error) {
                console.error('Fetch failed:', error);
            } finally {
                this.isLoading = false;
            }
        },

        async performSearch() {
            let params = new URLSearchParams({
                search: this.search,
                status: this.statusFilter.join(','),
                carrier: this.carrierFilter.join(','),
                perPage: this.perPage
            });
            const url = `{{ route('order.tracking.index') }}?${params.toString()}`;
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

        clearFilters() {
            this.search = '';
            this.statusFilter = [];
            this.carrierFilter = [];
            this.perPage = '15';
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
        
        <!-- Stats Row -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-6 mb-8">
            @php
                $statItems = [
                    'total' => ['label' => 'Total Shipments', 'color' => 'primary', 'icon' => 'package'],
                    'in_transit' => ['label' => 'In Transit', 'color' => 'blue', 'icon' => 'truck'],
                    'delivered' => ['label' => 'Delivered', 'color' => 'emerald', 'icon' => 'check-circle'],
                    'shipped' => ['label' => 'Awaiting Pickup', 'color' => 'amber', 'icon' => 'clock'],
                    'failed' => ['label' => 'Exceptions', 'color' => 'destructive', 'icon' => 'alert-circle'],
                ];
            @endphp
            @foreach($statItems as $key => $item)
                <div class="group relative p-6 rounded-3xl bg-card/40 border border-border/60 ring-1 ring-border/30 backdrop-blur-xl hover:bg-card/60 transition-all duration-500 overflow-hidden shadow-2xl">
                    <div class="absolute top-0 right-0 -mr-8 -mt-8 size-32 bg-{{ $item['color'] }}-500/10 blur-[50px] rounded-full group-hover:bg-{{ $item['color'] }}-500/20 transition-all duration-500"></div>
                    <div class="flex items-center gap-5 relative z-10">
                        <div class="size-14 rounded-2xl bg-gradient-to-tr from-{{ $item['color'] }}-500/20 to-{{ $item['color'] }}-500/5 border border-{{ $item['color'] }}-500/10 text-{{ $item['color'] }}-500 flex items-center justify-center shadow-inner group-hover:scale-110 transition-transform duration-500">
                            <x-ui.icon name="{{ $item['icon'] }}" size="7" />
                        </div>
                        <div>
                            <p class="text-[10px] font-black uppercase tracking-widest text-muted-foreground/60 mb-1">{{ $item['label'] }}</p>
                            <div class="text-3xl font-black tracking-tighter text-{{ $item['color'] === 'primary' ? 'foreground' : ($item['color'] . '-500') }}" x-text="stats.{{ $key }}"></div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        <x-ui.card class="overflow-hidden border-border/60 shadow-2xl bg-card/30 backdrop-blur-2xl rounded-3xl ring-1 ring-border/20">
            <x-ui.card-header class="border-b border-border/40 bg-muted/10 p-6 lg:p-8">
                <div class="flex flex-col gap-6">
                    <!-- Title & Actions Row -->
                    <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4">
                        <div class="flex items-start sm:items-center gap-4 min-w-0">
                            <div class="size-12 sm:size-14 shrink-0 rounded-2xl bg-gradient-to-br from-primary/25 via-primary/10 to-primary/5 border border-primary/15 text-primary flex items-center justify-center shadow-inner ring-1 ring-primary/10">
                                <x-ui.icon name="target" size="6" />
                            </div>
                            <div class="min-w-0">
                                <h2 class="text-lg sm:text-xl font-black text-foreground tracking-tight">Order Tracking & Logistics</h2>
                                <p class="text-[10px] font-bold text-muted-foreground uppercase tracking-[0.2em] mt-1 italic opacity-80">Real-time shipment monitoring · carrier updates · delivery milestones</p>
                            </div>
                        </div>
                        <div class="flex flex-wrap items-center gap-3 w-full lg:w-auto lg:justify-end">
                            <x-ui.button variant="outline" size="sm" class="flex-1 sm:flex-none rounded-xl font-bold uppercase tracking-widest text-[10px] h-11 px-6 shadow-sm border-border/60 bg-background/40 backdrop-blur-sm hover:bg-background/80 transition-all">
                                <x-ui.icon name="download" size="3" class="mr-2" />
                                Export Ledger
                            </x-ui.button>
                        </div>
                    </div>

                    <!-- Filters & Search Bar -->
                    <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4 pt-6 border-t border-border/30">
                        <div class="flex flex-wrap items-center gap-4">
                            <div class="flex items-center gap-3">
                                <span class="text-[10px] font-black text-muted-foreground uppercase tracking-widest">Show</span>
                                <select x-model="perPage" @change="performSearch()" 
                                    class="h-11 px-4 rounded-xl border border-border bg-background/50 text-[10px] font-black uppercase tracking-widest focus:ring-2 focus:ring-primary/20 transition-all outline-none">
                                    <option value="15">15 Entries</option>
                                    <option value="30">30 Entries</option>
                                    <option value="50">50 Entries</option>
                                    <option value="100">100 Entries</option>
                                </select>
                            </div>
                            <!-- Status Multi-Select -->
                            <div class="relative" x-data="{ open: false, filter: '' }">
                                <button @click="open = !open" class="h-11 px-4 flex items-center rounded-xl border border-border bg-background/50 text-[11px] font-bold hover:bg-background transition-all group shadow-sm">
                                    <span class="text-muted-foreground/80 group-hover:text-primary transition-colors uppercase tracking-widest text-[10px] font-black">Status</span>
                                    <span class="ml-2 px-1.5 py-0.5 rounded-lg bg-primary/10 text-primary font-black text-[10px]">
                                        <span x-text="statusFilter.length"></span>/<span x-text="statusesList.length"></span>
                                    </span>
                                    <x-ui.icon name="chevron-down" size="3" class="ml-2 text-muted-foreground/40" />
                                </button>
                                <div x-show="open" @click.away="open = false" x-cloak class="absolute left-0 mt-2 w-64 bg-popover border border-border rounded-xl shadow-2xl z-[100] p-1">
                                    <div class="p-2 border-b border-border bg-muted/10 mb-1">
                                        <input type="text" x-model="filter" placeholder="Search status..." class="w-full px-3 py-1.5 bg-background rounded-lg border border-border text-[11px] outline-none">
                                    </div>
                                    <div class="max-h-60 overflow-y-auto custom-scrollbar">
                                        <template x-for="item in statusesList.filter(i => i.toLowerCase().includes(filter.toLowerCase()))" :key="item">
                                            <label class="flex items-center gap-2 px-3 py-2 rounded-lg hover:bg-muted cursor-pointer transition-colors" x-bind:class="statusFilter.includes(item) ? 'bg-primary/5' : ''">
                                                <input type="checkbox" :value="item" x-model="statusFilter" @change="performSearch()" class="rounded border-border text-primary">
                                                <span class="text-[11px] uppercase tracking-widest font-bold" x-text="item.replace('_', ' ')"></span>
                                            </label>
                                        </template>
                                    </div>
                                </div>
                            </div>

                            <!-- Carrier Multi-Select -->
                            <div class="relative" x-data="{ open: false, filter: '' }">
                                <button @click="open = !open" class="h-11 px-4 flex items-center rounded-xl border border-border bg-background/50 text-[11px] font-bold hover:bg-background transition-all group shadow-sm">
                                    <span class="text-muted-foreground/80 group-hover:text-blue-500 transition-colors uppercase tracking-widest text-[10px] font-black">Carrier</span>
                                    <span class="ml-2 px-1.5 py-0.5 rounded-lg bg-blue-500/10 text-blue-500 font-black text-[10px]">
                                        <span x-text="carrierFilter.length"></span>/<span x-text="availableCarriers.length"></span>
                                    </span>
                                    <x-ui.icon name="chevron-down" size="3" class="ml-2 text-muted-foreground/40" />
                                </button>
                                <div x-show="open" @click.away="open = false" x-cloak class="absolute left-0 mt-2 w-64 bg-popover border border-border rounded-xl shadow-2xl z-[100] p-1">
                                    <div class="p-2 border-b border-border bg-muted/10 mb-1">
                                        <input type="text" x-model="filter" placeholder="Search carrier..." class="w-full px-3 py-1.5 bg-background rounded-lg border border-border text-[11px] outline-none">
                                    </div>
                                    <div class="max-h-60 overflow-y-auto custom-scrollbar">
                                        <template x-show="availableCarriers.length === 0">
                                            <div class="px-3 py-3 text-center text-[10px] text-muted-foreground font-bold">No carriers recorded</div>
                                        </template>
                                        <template x-for="item in availableCarriers.filter(i => i.toLowerCase().includes(filter.toLowerCase()))" :key="item">
                                            <label class="flex items-center gap-2 px-3 py-2 rounded-lg hover:bg-muted cursor-pointer transition-colors" x-bind:class="carrierFilter.includes(item) ? 'bg-blue-500/5' : ''">
                                                <input type="checkbox" :value="item" x-model="carrierFilter" @change="performSearch()" class="rounded border-border text-blue-500">
                                                <span class="text-[11px] font-bold" x-text="item"></span>
                                            </label>
                                        </template>
                                    </div>
                                </div>
                            </div>
                            <button @click="clearFilters()" class="h-11 px-5 rounded-xl text-[10px] font-black uppercase tracking-widest border border-border/60 bg-muted/20 hover:bg-muted/40 transition-colors flex items-center gap-2 group">
                                <x-ui.icon name="rotate-ccw" size="3" class="group-hover:rotate-[-45deg] transition-transform" />
                                Reset
                            </button>
                        </div>

                        <div class="relative group w-full lg:max-w-md shrink-0">
                            <x-ui.icon name="search" size="4" class="absolute left-4 top-1/2 -translate-y-1/2 text-muted-foreground group-focus-within:text-primary transition-colors" />
                            <input type="text" x-model="search" @input.debounce.500ms="performSearch()"
                                placeholder="Search Shipment #, Order #, Tracking #..."
                                class="pl-10 pr-12 py-3 rounded-xl border border-border bg-background/50 focus:bg-background focus:ring-2 focus:ring-primary/20 transition-all w-full text-xs shadow-sm outline-none font-medium">
                            <div x-show="isLoading" x-cloak class="absolute right-4 top-1/2 -translate-y-1/2 pointer-events-none">
                                <x-ui.icon name="refresh-cw" class="animate-spin text-primary" size="4" />
                            </div>
                        </div>
                    </div>
                </div>
            </x-ui.card-header>

            <x-ui.card-content class="p-0 relative min-h-[420px] bg-gradient-to-b from-transparent via-muted/[0.03] to-muted/5">
                <div x-show="isLoading" x-cloak class="absolute inset-0 z-50 bg-background/50 backdrop-blur-md flex items-center justify-center animate-in fade-in duration-200">
                    <div class="flex flex-col items-center gap-3 rounded-2xl border border-border/50 bg-card/80 px-10 py-8 shadow-2xl">
                        <div class="relative">
                            <x-ui.icon name="refresh-cw" class="animate-spin text-primary" size="10" />
                            <div class="absolute inset-0 flex items-center justify-center">
                                <x-ui.icon name="target" size="4" class="text-primary/40" />
                            </div>
                        </div>
                        <span class="text-[10px] font-black uppercase tracking-[0.3em] text-foreground/80 mt-2">Syncing Logistics</span>
                    </div>
                </div>
                <div id="tracking-table-container" class="relative z-0" @click="handlePagination($event)">
                    @include('order-tracking.partials.table')
                </div>
            </x-ui.card-content>
        </x-ui.card>
    </div>

    <style>
        [x-cloak] { display: none !important; }
        /* Custom scrollbar for better theme integration */
        .custom-scrollbar::-webkit-scrollbar {
            width: 5px;
            height: 5px;
        }
        .custom-scrollbar::-webkit-scrollbar-track {
            background: transparent;
        }
        .custom-scrollbar::-webkit-scrollbar-thumb {
            background: rgba(var(--primary), 0.1);
            border-radius: 10px;
        }
        .custom-scrollbar::-webkit-scrollbar-thumb:hover {
            background: rgba(var(--primary), 0.2);
        }
    </style>
</x-layouts.app>
