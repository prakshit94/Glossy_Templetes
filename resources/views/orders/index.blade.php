<x-layouts.app pageTitle="Orders Management">
    @php
        $qStatus = request('status') ? explode(',', request('status')) : [];
        $qState = request('state') ? explode(',', request('state')) : [];
        $qDistrict = request('district') ? explode(',', request('district')) : [];
        $qTaluka = request('taluka') ? explode(',', request('taluka')) : [];
    @endphp

    <div class="p-6 lg:p-10" x-data="{ 
        selectedItems: [], 
        allSelected: false,
        search: @js(request('search', '')),
        perPage: @js(request('perPage', 15)),
        statusFilter: @js($qStatus),
        stateFilter: @js($qState),
        districtFilter: @js($qDistrict),
        talukaFilter: @js($qTaluka),
        statusesList: @js($statusesList),
        statesList: @js($statesList),
        districtsList: @js($districtsList),
        talukasList: @js($talukasList),
        stats: @js($stats),
        isLoading: false,
        shipOrderNo: '',
        shipOrderId: '',
        shipActionUrl: '',
        openShipModal(orderId, orderNo) {
            this.shipOrderId = orderId;
            this.shipOrderNo = orderNo;
            this.shipActionUrl = `{{ route('orders.ship', ':id') }}`.replace(':id', orderId);
            this.$dispatch('open-modal', { name: 'create-shipment-modal' });
        },

        toggleAll() {
            if (this.allSelected) {
                this.selectedItems = Array.from(
                    document.querySelectorAll('input[name=\'order_ids[]\']')
                ).map(el => parseInt(el.value));
            } else {
                this.selectedItems = [];
            }
        },

        toggleItem(id) {
            if (this.selectedItems.includes(id)) {
                this.selectedItems = this.selectedItems.filter(i => i !== id);
            } else {
                this.selectedItems.push(id);
            }
        },

        async performSearch() {
            this.isLoading = true;
            let params = new URLSearchParams({
                search: this.search,
                perPage: this.perPage,
                status: this.statusFilter.join(','),
                state: this.stateFilter.join(','),
                district: this.districtFilter.join(','),
                taluka: this.talukaFilter.join(',')
            });

            // Persist to URL
            window.history.replaceState({}, '', `${window.location.pathname}?${params.toString()}`);

            const res = await fetch(
                `{{ route('orders.index') }}?${params.toString()}`,
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
            this.statusFilter = [];
            this.stateFilter = [];
            this.districtFilter = [];
            this.talukaFilter = [];
            this.performSearch();
        }
    }">

        <div class="max-w-[100rem] mx-auto space-y-8">
            <!-- Stats Widgets -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
                <div class="group relative p-6 rounded-3xl bg-card/40 border border-border/60 backdrop-blur-xl hover:bg-primary/5 transition-all duration-500 overflow-hidden shadow-2xl">
                    <div class="absolute top-0 right-0 -mr-8 -mt-8 size-32 bg-primary/10 blur-[50px] rounded-full group-hover:bg-primary/20 transition-all duration-500"></div>
                    <div class="flex items-center gap-5 relative z-10">
                        <div class="size-14 rounded-2xl bg-gradient-to-tr from-primary/20 to-primary/5 border border-primary/10 text-primary flex items-center justify-center shadow-inner group-hover:scale-110 transition-transform duration-500">
                            <x-ui.icon name="shopping-cart" size="7" />
                        </div>
                        <div>
                            <p class="text-[10px] font-black uppercase tracking-widest text-muted-foreground/60 mb-1">Total Orders</p>
                            <div class="text-3xl font-black tracking-tighter text-foreground" x-text="stats.total"></div>
                        </div>
                    </div>
                </div>

                <div class="group relative p-6 rounded-3xl bg-card/40 border border-border/60 backdrop-blur-xl hover:bg-orange-500/5 transition-all duration-500 overflow-hidden shadow-2xl">
                    <div class="absolute top-0 right-0 -mr-8 -mt-8 size-32 bg-orange-500/10 blur-[50px] rounded-full group-hover:bg-orange-500/20 transition-all duration-500"></div>
                    <div class="flex items-center gap-5 relative z-10">
                        <div class="size-14 rounded-2xl bg-gradient-to-tr from-orange-500/20 to-orange-500/5 border border-orange-500/10 text-orange-500 flex items-center justify-center shadow-inner group-hover:scale-110 transition-transform duration-500">
                            <x-ui.icon name="clock" size="7" />
                        </div>
                        <div>
                            <p class="text-[10px] font-black uppercase tracking-widest text-muted-foreground/60 mb-1">Pending</p>
                            <div class="text-3xl font-black tracking-tighter text-orange-500" x-text="stats.pending"></div>
                        </div>
                    </div>
                </div>

                <div class="group relative p-6 rounded-3xl bg-card/40 border border-border/60 backdrop-blur-xl hover:bg-blue-500/5 transition-all duration-500 overflow-hidden shadow-2xl">
                    <div class="absolute top-0 right-0 -mr-8 -mt-8 size-32 bg-blue-500/10 blur-[50px] rounded-full group-hover:bg-blue-500/20 transition-all duration-500"></div>
                    <div class="flex items-center gap-5 relative z-10">
                        <div class="size-14 rounded-2xl bg-gradient-to-tr from-blue-500/20 to-blue-500/5 border border-blue-500/10 text-blue-500 flex items-center justify-center shadow-inner group-hover:scale-110 transition-transform duration-500">
                            <x-ui.icon name="settings" size="7" />
                        </div>
                        <div>
                            <p class="text-[10px] font-black uppercase tracking-widest text-muted-foreground/60 mb-1">Processing</p>
                            <div class="text-3xl font-black tracking-tighter text-blue-500" x-text="stats.processing"></div>
                        </div>
                    </div>
                </div>

                <div class="group relative p-6 rounded-3xl bg-card/40 border border-border/60 backdrop-blur-xl hover:bg-emerald-500/5 transition-all duration-500 overflow-hidden shadow-2xl">
                    <div class="absolute top-0 right-0 -mr-8 -mt-8 size-32 bg-emerald-500/10 blur-[50px] rounded-full group-hover:bg-emerald-500/20 transition-all duration-500"></div>
                    <div class="flex items-center gap-5 relative z-10">
                        <div class="size-14 rounded-2xl bg-gradient-to-tr from-emerald-500/20 to-emerald-500/5 border border-emerald-500/10 text-emerald-500 flex items-center justify-center shadow-inner group-hover:scale-110 transition-transform duration-500">
                            <x-ui.icon name="truck" size="7" />
                        </div>
                        <div>
                            <p class="text-[10px] font-black uppercase tracking-widest text-muted-foreground/60 mb-1">Shipped</p>
                            <div class="text-3xl font-black tracking-tighter text-emerald-500" x-text="stats.shipped"></div>
                        </div>
                    </div>
                </div>
            </div>

            <x-ui.card class="overflow-hidden border-border/60 shadow-2xl bg-card/30 backdrop-blur-2xl rounded-3xl">
                <x-ui.card-header class="border-b border-border/40 bg-muted/10 p-6 lg:p-8">
                    <div class="flex flex-col gap-6">
                        <!-- Title row: brand block + primary CTAs -->
                        <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4">
                            <div class="flex items-start sm:items-center gap-4 min-w-0">
                                <div class="size-12 sm:size-14 shrink-0 rounded-2xl bg-gradient-to-br from-primary/25 via-primary/10 to-primary/5 border border-primary/15 text-primary flex items-center justify-center shadow-inner ring-1 ring-primary/10">
                                    <x-ui.icon name="shopping-cart" size="6" />
                                </div>
                                <div class="min-w-0">
                                    <h2 class="text-lg sm:text-xl font-black text-foreground tracking-tight">Orders Management</h2>
                                    <p class="text-[10px] font-bold text-muted-foreground uppercase tracking-[0.2em] mt-1">Lifecycle Tracking · Fulfillment · Logistics</p>
                                </div>
                            </div>
                            <div class="flex flex-wrap items-center gap-2 w-full lg:w-auto lg:justify-end">
                                <x-ui.button variant="outline" size="sm" class="flex-1 sm:flex-none rounded-xl font-bold uppercase tracking-widest text-[10px] h-10 shadow-sm border-border/60 bg-background/40 backdrop-blur-sm" onclick="alert('Import feature coming soon!')">
                                    <x-ui.icon name="upload" size="3" class="mr-2" />
                                    Import
                                </x-ui.button>
                                <x-ui.button variant="outline" size="sm" class="flex-1 sm:flex-none rounded-xl font-bold uppercase tracking-widest text-[10px] h-10 shadow-sm border-border/60 bg-background/40 backdrop-blur-sm" onclick="alert('Export feature coming soon!')">
                                    <x-ui.icon name="download" size="3" class="mr-2" />
                                    Export
                                </x-ui.button>
                                <a href="{{ route('orders.create') }}" class="flex-1 sm:flex-none">
                                    <x-ui.button size="sm" class="w-full rounded-xl font-bold uppercase tracking-widest text-[10px] h-10 shadow-lg shadow-primary/25 ring-1 ring-primary/20">
                                        <x-ui.icon name="plus" size="3" class="mr-2" />
                                        New Order
                                    </x-ui.button>
                                </a>
                            </div>
                        </div>

                        <!-- Toolbar: scope + filters -->
                        <div class="flex flex-col sm:flex-row sm:flex-wrap sm:items-center gap-3 pt-2 border-t border-border/30">
                            <div class="flex bg-muted/50 px-4 py-1.5 rounded-xl border border-border/50 shadow-inner w-fit">
                                <span class="text-xs font-bold text-primary tracking-widest uppercase">Order Ledger</span>
                            </div>

                            <div x-show="selectedItems.length > 0" x-cloak x-transition
                                class="flex items-center gap-2 animate-in fade-in slide-in-from-left-4 duration-300">
                                <x-ui.dropdown>
                                    <x-slot name="trigger">
                                        <x-ui.button variant="outline" size="sm" class="rounded-xl border-primary/20 bg-primary/5 text-primary font-bold shadow-sm whitespace-nowrap h-10 px-4">
                                            <span x-text="selectedItems.length"></span> Selected
                                            <x-ui.icon name="chevron-down" size="3" class="ml-2" />
                                        </x-ui.button>
                                    </x-slot>
                                    <x-slot name="content">
                                        <x-ui.dropdown-label>Mass Lifecycle Update</x-ui.dropdown-label>
                                        <div class="p-1 space-y-1 divide-y divide-border/20">
                                            <div class="py-1">
                                                <div class="px-3 py-1 text-[9px] font-black uppercase tracking-widest text-muted-foreground/60">Fulfillment States</div>
                                                @foreach(['confirmed' => 'Confirm Orders', 'processing' => 'Mark Processing', 'shipped' => 'Ship Orders', 'delivered' => 'Deliver Orders', 'cancelled' => 'Cancel Orders'] as $status => $label)
                                                    <form action="{{ route('orders.bulk-status') }}" method="POST">
                                                        @csrf
                                                        <input type="hidden" name="ids" :value="JSON.stringify(selectedItems)">
                                                        <input type="hidden" name="status" value="{{ $status }}">
                                                        <button type="submit" class="w-full text-left px-3 py-2 text-[10px] font-bold hover:bg-primary/5 hover:text-primary rounded-xl flex items-center text-foreground/80 uppercase tracking-wider transition-colors">
                                                            <span class="size-2 rounded-full bg-{{ match($status) { 'confirmed' => 'indigo', 'processing' => 'amber', 'shipped' => 'blue', 'delivered' => 'emerald', 'cancelled' => 'red' } }}-500 mr-2"></span>
                                                            {{ $label }}
                                                        </button>
                                                    </form>
                                                @endforeach
                                            </div>
                                            <div class="py-1">
                                                <div class="px-3 py-1 text-[9px] font-black uppercase tracking-widest text-amber-600/70">Revert / Undo States</div>
                                                @foreach(['pending' => 'Revert to Pending', 'confirmed' => 'Revert to Confirmed', 'processing' => 'Revert to Processing', 'shipped' => 'Revert to Shipped'] as $status => $label)
                                                    <form action="{{ route('orders.bulk-status') }}" method="POST">
                                                        @csrf
                                                        <input type="hidden" name="ids" :value="JSON.stringify(selectedItems)">
                                                        <input type="hidden" name="status" value="{{ $status }}">
                                                        <button type="submit" class="w-full text-left px-3 py-2 text-[10px] font-bold hover:bg-amber-500/5 hover:text-amber-600 rounded-xl flex items-center text-foreground/85 uppercase tracking-wider transition-colors">
                                                            <x-ui.icon name="corner-up-left" size="3.5" class="mr-2 text-amber-500" />
                                                            {{ $label }}
                                                        </button>
                                                    </form>
                                                @endforeach
                                            </div>
                                        </div>
                                    </x-slot>
                                </x-ui.dropdown>
                            </div>
                            
                            <div class="flex items-center gap-2">
                                <span class="text-[10px] font-bold text-muted-foreground uppercase tracking-widest">Show</span>
                                <select x-model="perPage" @change="performSearch" class="h-10 px-3 rounded-xl border border-border bg-background/50 text-xs font-medium focus:ring-1 focus:ring-primary outline-none shadow-sm">
                                    <option value="15">15</option>
                                    <option value="25">25</option>
                                    <option value="50">50</option>
                                    <option value="100">100</option>
                                </select>
                            </div>

                            <div class="flex flex-wrap items-center gap-2">
                                @include('orders.partials.filters')
                                
                                <x-ui.button variant="ghost" size="sm" @click="clearFilters" class="rounded-xl h-10 px-4 text-[10px] font-black uppercase tracking-widest text-muted-foreground hover:text-primary transition-colors">
                                    Clear All
                                </x-ui.button>
                            </div>

                            <div class="lg:ml-auto relative group w-full lg:max-w-md shrink-0">
                                <x-ui.icon name="search" size="4" class="absolute left-3 top-1/2 -translate-y-1/2 text-muted-foreground group-focus-within:text-primary transition-colors" />
                                <input type="text" x-model="search" @input.debounce.500ms="performSearch"
                                    placeholder="Search order number or party name..."
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
                            <span class="text-[10px] font-black uppercase tracking-[0.25em] text-foreground/80">Syncing ledger</span>
                        </div>
                    </div>
                    <div class="pointer-events-none absolute inset-x-0 top-0 h-px bg-gradient-to-r from-transparent via-primary/20 to-transparent z-10"></div>
                    <div id="table-container">
                        @include('orders.partials.table')
                    </div>
                </x-ui.card-content>
            </x-ui.card>
        </div>

    <!-- Create Shipment Modal -->
    <x-ui.modal id="create-shipment-modal" maxWidth="md">
        <form :action="shipActionUrl" method="POST" class="p-6 space-y-4">
            @csrf
            <div>
                <h3 class="text-lg font-black text-foreground mb-1">Create Shipment</h3>
                <p class="text-xs text-muted-foreground font-semibold uppercase tracking-wider">Order <span x-text="shipOrderNo"></span></p>
            </div>
            
            <div class="h-px bg-border/60 w-full my-2"></div>
            
            <div class="space-y-4">
                <div class="space-y-2">
                    <label for="carrier_name" class="text-[10px] font-black uppercase tracking-widest text-muted-foreground/80">Shipping Company / Carrier</label>
                    <select id="carrier_name" name="carrier_name" class="h-11 w-full rounded-xl border border-input bg-background/50 px-3 py-2 text-sm text-foreground focus:outline-none focus-visible:ring-2 focus-visible:ring-primary/20 appearance-none cursor-pointer">
                        <option value="">-- Select Shipping Option --</option>
                        @foreach($services as $svc)
                            <option value="{{ $svc->name }}">{{ $svc->name }} ({{ $svc->code }})</option>
                        @endforeach
                    </select>
                </div>
                
                <div class="space-y-2">
                    <label for="tracking_no" class="text-[10px] font-black uppercase tracking-widest text-muted-foreground/80">Tracking ID</label>
                    <input type="text" id="tracking_no" name="tracking_no" placeholder="e.g. TRK123456789" class="h-11 w-full rounded-xl border border-input bg-background/50 px-3 py-2 text-sm text-foreground focus:outline-none focus-visible:ring-2 focus-visible:ring-primary/20">
                </div>
            </div>
            
            <div class="flex items-center justify-end gap-3 pt-4 border-t border-border/40">
                <x-ui.button type="button" variant="outline" size="sm" @click="$dispatch('close-modal', { name: 'create-shipment-modal' })" class="rounded-xl font-bold uppercase tracking-widest text-[10px] h-10">
                    Cancel
                </x-ui.button>
                <x-ui.button type="submit" size="sm" class="rounded-xl font-bold uppercase tracking-widest text-[10px] h-10 bg-blue-500 hover:bg-blue-600 text-white shadow-lg shadow-blue-500/20">
                    <x-ui.icon name="truck" size="3" class="mr-2" /> Confirm & Ship
                </x-ui.button>
            </div>
        </form>
    </x-ui.modal>
    </div>

    <style>
        [x-cloak] { display: none !important; }
        .custom-scrollbar::-webkit-scrollbar { height: 6px; width: 6px; }
        .custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
        .custom-scrollbar::-webkit-scrollbar-thumb { background: rgba(var(--border), 0.1); border-radius: 10px; }
    </style>
</x-layouts.app>

