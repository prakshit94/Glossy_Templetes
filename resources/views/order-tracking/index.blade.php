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
        importRows: [],
        async handleImportFileSelect(event) {
            const file = event.target.files[0];
            if (!file) return;
            const formData = new FormData();
            formData.append('file', file);
            formData.append('preview', '1');
            formData.append('_token', document.querySelector('meta[name=csrf-token]')?.getAttribute('content') || '{{ csrf_token() }}');
            this.importRows = [];
            try {
                const response = await fetch('{{ route('orders.import') }}', {
                    method: 'POST',
                    body: formData,
                    headers: { 'Accept': 'application/json' }
                });
                const data = await response.json();
                if (data.preview) {
                    this.importRows = data.preview;
                    this.$dispatch('open-modal', { name: 'import-preview-modal' });
                } else if (data.error) {
                    alert('Error: ' + data.error);
                }
            } catch (err) {
                alert('Error generating preview.');
            }
        },
        confirmImport() {
            document.getElementById('import-form').submit();
        },
        cancelImport() {
            document.getElementById('import-form').reset();
            this.importRows = [];
            this.$dispatch('close-modal', { name: 'import-preview-modal' });
        },
        openAssignModal(preShipments) {
            if (preShipments && preShipments.length > 0) {
                window._orderAssignShipments = preShipments;
            } else {
                const checkboxes = document.querySelectorAll('.shipment-checkbox:checked');
                const shipments = [];
                checkboxes.forEach(cb => {
                    const shipId = cb.value;
                    const shipNo = cb.getAttribute('data-shipment-no');
                    const orderNo = cb.getAttribute('data-order-no');
                    const party = cb.getAttribute('data-party');
                    const driverId = cb.getAttribute('data-driver-id') || '';
                    const transportId = cb.getAttribute('data-transport-id') || '';
                    if (shipId && shipId !== '' && shipId !== 'null') {
                        shipments.push({ id: parseInt(shipId), no: shipNo, order: orderNo, party: party, driver_id: driverId, transport_id: transportId });
                    }
                });
                window._orderAssignShipments = shipments;
            }
            this.$dispatch('open-modal', { name: 'assign-modal' });
        },

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
                            <form id="import-form" action="{{ route('orders.import') }}" method="POST" enctype="multipart/form-data" class="hidden">
                                @csrf
                                <input type="file" name="file" id="import-file" accept=".csv,.txt" @change="handleImportFileSelect($event)">
                            </form>
                            <x-ui.dropdown>
                                <x-slot name="trigger">
                                    <x-ui.button variant="outline" size="sm" class="flex-1 sm:flex-none rounded-xl font-bold uppercase tracking-widest text-[10px] h-11 px-6 shadow-sm border-border/60 bg-background/40 backdrop-blur-sm hover:bg-background/80 transition-all">
                                        <x-ui.icon name="upload" size="3" class="mr-2" />
                                        Import
                                        <x-ui.icon name="chevron-down" size="3" class="ml-2 opacity-50" />
                                    </x-ui.button>
                                </x-slot>
                                <x-slot name="content">
                                    <div class="p-1 space-y-1">
                                        <a href="{{ route('orders.import-template') }}" class="w-full text-left px-3 py-2 text-[10px] font-bold hover:bg-primary/5 hover:text-primary rounded-xl flex items-center text-foreground/80 uppercase tracking-wider transition-colors">
                                            <x-ui.icon name="file-text" size="3.5" class="mr-2 text-muted-foreground" />
                                            Download Template
                                        </a>
                                        <button type="button" onclick="document.getElementById('import-file').click()" class="w-full text-left px-3 py-2 text-[10px] font-bold hover:bg-primary/5 hover:text-primary rounded-xl flex items-center text-foreground/80 uppercase tracking-wider transition-colors">
                                            <x-ui.icon name="upload-cloud" size="3.5" class="mr-2 text-muted-foreground" />
                                            Upload CSV
                                        </button>
                                    </div>
                                </x-slot>
                            </x-ui.dropdown>

                            <x-ui.button variant="outline" size="sm" class="flex-1 sm:flex-none rounded-xl font-bold uppercase tracking-widest text-[10px] h-11 px-6 shadow-sm border-border/60 bg-background/40 backdrop-blur-sm hover:bg-background/80 transition-all" onclick="window.location.href = '{{ route('orders.export') }}' + window.location.search">
                                <x-ui.icon name="download" size="3" class="mr-2" />
                                Export Ledger
                            </x-ui.button>
                            <x-ui.button variant="outline" size="sm" class="flex-1 sm:flex-none rounded-xl font-bold uppercase tracking-widest text-[10px] h-11 px-6 shadow-sm border-primary/30 bg-primary/5 text-primary hover:bg-primary/10 transition-all" onclick="window.location.href = '{{ route('delivery.performance.index') }}'">
                                <x-ui.icon name="bar-chart" size="3" class="mr-2" />
                                Performance
                            </x-ui.button>
                        </div>
                    </div>

                    <!-- Filters & Search Bar -->
                    <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4 pt-6 border-t border-border/30">
                        <div class="flex flex-wrap items-center gap-4">
                            <div class="flex items-center gap-3">
                                <span class="text-[10px] font-black text-muted-foreground uppercase tracking-widest">Show</span>
                                <select x-model="perPage" @change="performSearch()" 
                                    class="h-11 px-4 rounded-xl border border-border bg-background/50 text-[10px] font-black uppercase tracking-widest focus:ring-2 focus:ring-primary/20 transition-all outline-none shadow-sm cursor-pointer hover:bg-background">
                                    <option value="15">15 Entries</option>
                                    <option value="30">30 Entries</option>
                                    <option value="50">50 Entries</option>
                                    <option value="100">100 Entries</option>
                                </select>
                            </div>

                            <!-- Bulk actions trigger -->
                            <div x-show="selectedShipments.length > 0" x-cloak x-transition
                                class="flex items-center gap-2 shrink-0 animate-in fade-in slide-in-from-left-4 duration-300">
                                <x-ui.dropdown>
                                    <x-slot name="trigger">
                                        <x-ui.button variant="outline" size="sm" class="rounded-xl border-primary/20 bg-primary/5 text-primary font-bold shadow-sm whitespace-nowrap h-11 px-4 text-[10px] uppercase tracking-widest">
                                            <span x-text="selectedShipments.length"></span> Selected
                                            <x-ui.icon name="chevron-down" size="3" class="ml-2" />
                                        </x-ui.button>
                                    </x-slot>
                                    <x-slot name="content">
                                        <div class="p-1 space-y-1 w-56 divide-y divide-border/20">
                                            <div class="py-1">
                                                <div class="px-3 py-1 text-[9px] font-black uppercase tracking-widest text-muted-foreground/60">Logistics Action</div>
                                                <button type="button" @click="openAssignModal(null)"
                                                    class="w-full text-left px-3 py-2 text-[10px] font-bold hover:bg-primary/5 hover:text-primary rounded-xl flex items-center text-foreground/80 uppercase tracking-wider transition-colors">
                                                    <span class="size-2 rounded-full bg-blue-500 mr-2"></span>
                                                    Assign Shipment
                                                </button>
                                            </div>
                                        </div>
                                    </x-slot>
                                </x-ui.dropdown>
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

    <!-- Assign Shipment Modal -->
    <x-ui.modal id="assign-modal" maxWidth="md">
        <div class="p-8" x-data="{
            open: false,
            search: '',
            selectedDriver: '',
            selectedTransport: '',
            selectedShipments: [],
            shipments: [],
            get filteredShipments() {
                if (!this.search) return this.shipments;
                return this.shipments.filter(s =>
                    s.no.toLowerCase().includes(this.search.toLowerCase()) ||
                    s.order.toLowerCase().includes(this.search.toLowerCase()) ||
                    (s.party && s.party.toLowerCase().includes(this.search.toLowerCase()))
                );
            },
            toggleShipment(shp) {
                if (this.selectedShipments.some(s => s.id === shp.id)) {
                    this.selectedShipments = this.selectedShipments.filter(s => s.id !== shp.id);
                } else {
                    this.selectedShipments.push(shp);
                    if (shp.driver_id && !this.selectedDriver) {
                        this.selectedDriver = shp.driver_id;
                    }
                    if (shp.transport_id && !this.selectedTransport) {
                        this.selectedTransport = shp.transport_id;
                    }
                }
            },
            isSelected(id) {
                return this.selectedShipments.some(s => s.id === id);
            },
            init() {
                this.$watch('$el', () => {});
                window.addEventListener('open-modal', (e) => {
                    if (e.detail && e.detail.name === 'assign-modal') {
                        this.shipments = window._orderAssignShipments || [];
                        this.selectedShipments = [...this.shipments];
                        this.search = '';
                    }
                });
            }
        }">
            <div class="flex items-center justify-between mb-6">
                <div class="flex items-center gap-3">
                    <div class="size-10 rounded-2xl bg-primary/10 border border-primary/20 text-primary flex items-center justify-center shadow-inner">
                        <x-ui.icon name="truck-2" size="5" />
                    </div>
                    <div>
                        <h3 class="text-sm font-black text-foreground uppercase tracking-widest">Assign Shipment</h3>
                        <p class="text-[10px] text-muted-foreground font-bold tracking-tight">Configure delivery dispatch parameters</p>
                    </div>
                </div>
                <button type="button" @click="$dispatch('close-modal', { name: 'assign-modal' })" class="size-8 rounded-lg hover:bg-muted flex items-center justify-center transition-colors">
                    <x-ui.icon name="x" size="4" />
                </button>
            </div>

            <form action="{{ route('delivery.assign') }}" method="POST" class="space-y-5">
                @csrf

                <!-- Hidden inputs for each selected shipment -->
                <template x-for="s in selectedShipments" :key="s.id">
                    <input type="hidden" name="shipment_ids[]" :value="s.id">
                </template>

                <div class="space-y-2">
                    <label class="text-[10px] font-black uppercase tracking-widest text-muted-foreground/80 ml-1">Select Shipment(s)</label>

                    <!-- Custom Searchable Multi-Select Trigger -->
                    <div class="relative" @click.outside="open = false">
                        <button type="button" @click="open = !open"
                            class="w-full min-h-11 px-4 py-2 rounded-xl border border-border bg-background/50 focus:bg-background focus:ring-2 focus:ring-primary/20 text-left text-xs font-semibold flex items-center justify-between outline-none transition-all">
                            <div class="flex flex-wrap gap-1 max-w-[90%]">
                                <template x-if="selectedShipments.length === 0">
                                    <span class="text-muted-foreground">Select Shipment(s)...</span>
                                </template>
                                <template x-for="s in selectedShipments" :key="s.id">
                                    <span class="px-2 py-0.5 rounded-lg bg-primary/10 border border-primary/20 text-primary text-[10px] font-bold flex items-center gap-1">
                                        <span x-text="s.no"></span>
                                        <span class="cursor-pointer font-black text-[9px] hover:text-primary/75" @click.stop="toggleShipment(s)">×</span>
                                    </span>
                                </template>
                            </div>
                            <x-ui.icon name="chevron-down" size="3.5" class="text-muted-foreground transition-transform shrink-0" ::class="open ? 'rotate-180' : ''" />
                        </button>

                        <!-- Dropdown Box -->
                        <div x-show="open" x-cloak
                            class="absolute left-0 right-0 mt-2 p-3 bg-card/95 border border-border/80 backdrop-blur-2xl rounded-2xl shadow-2xl z-[100] max-h-60 overflow-y-auto space-y-2">

                            <!-- Search -->
                            <div class="relative group">
                                <x-ui.icon name="search" size="3" class="absolute left-2.5 top-1/2 -translate-y-1/2 text-muted-foreground group-focus-within:text-primary transition-colors" />
                                <input type="text" x-model="search" placeholder="Type shipment no, order no..." @click.stop
                                    class="pl-8 pr-3 py-1.5 rounded-lg border border-border bg-background/30 focus:bg-background focus:ring-2 focus:ring-primary/20 transition-all w-full text-[11px] outline-none h-8">
                            </div>

                            <!-- Checkbox Options -->
                            <div class="space-y-1 max-h-40 overflow-y-auto">
                                <template x-for="s in filteredShipments" :key="s.id">
                                    <button type="button" @click="toggleShipment(s)"
                                        class="w-full text-left px-3 py-2 rounded-xl text-[11px] font-semibold flex items-center justify-between hover:bg-primary/10 hover:text-primary transition-all border border-transparent"
                                        :class="isSelected(s.id) ? 'bg-primary/5 text-primary border-primary/20' : 'text-foreground/80'">
                                        <div class="flex flex-col">
                                            <span x-text="s.no"></span>
                                            <span class="text-[9px] text-muted-foreground font-medium" x-text="`Order #${s.order}${s.party ? ' — ' + s.party : ''}`"></span>
                                        </div>
                                        <input type="checkbox" :checked="isSelected(s.id)" class="rounded border-border text-primary focus:ring-primary/20 pointer-events-none">
                                    </button>
                                </template>
                                <template x-if="filteredShipments.length === 0">
                                    <div class="px-3 py-4 text-center text-xs text-muted-foreground">No shipments match your search.</div>
                                </template>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="space-y-2">
                    <label for="driver_id" class="text-[10px] font-black uppercase tracking-widest text-muted-foreground/80 ml-1">Driver Allocation</label>
                    <select id="driver_id" name="driver_id" x-model="selectedDriver" required class="w-full h-11 px-4 rounded-xl border border-border bg-background/50 focus:bg-background focus:ring-2 focus:ring-primary/20 text-sm font-semibold outline-none transition-all appearance-none cursor-pointer">
                        <option value="">-- Select Driver --</option>
                        @foreach($drivers as $driver)
                            <option value="{{ $driver->id }}">{{ $driver->user->name }} ({{ str_replace('_', ' ', $driver->status) }})</option>
                        @endforeach
                    </select>
                </div>

                <div class="space-y-2">
                    <label for="transport_id" class="text-[10px] font-black uppercase tracking-widest text-muted-foreground/80 ml-1">Transport Vehicle</label>
                    <select id="transport_id" name="transport_id" x-model="selectedTransport" required class="w-full h-11 px-4 rounded-xl border border-border bg-background/50 focus:bg-background focus:ring-2 focus:ring-primary/20 text-sm font-semibold outline-none transition-all appearance-none cursor-pointer">
                        <option value="">-- Select Vehicle --</option>
                        @foreach($transports as $vehicle)
                            <option value="{{ $vehicle->id }}">{{ $vehicle->name }} - {{ $vehicle->vehicle_number }} ({{ str_replace('_', ' ', $vehicle->status) }})</option>
                        @endforeach
                    </select>
                </div>

                <div class="flex items-center justify-end gap-3 pt-4 border-t border-border/40">
                    <x-ui.button type="button" variant="outline" @click="$dispatch('close-modal', { name: 'assign-modal' })" class="rounded-xl font-bold text-xs h-10 px-6">
                        Cancel
                    </x-ui.button>
                    <x-ui.button type="submit" x-bind:disabled="selectedShipments.length === 0" class="rounded-xl font-bold text-xs h-10 px-6 shadow-lg shadow-primary/20">
                        Dispatch Deliveries
                    </x-ui.button>
                </div>
            </form>
        </div>
    </x-ui.modal>

    <x-ui.modal id="import-preview-modal" maxWidth="4xl">
        <div class="p-6 space-y-4 max-h-[85vh] flex flex-col">
            <div>
                <h3 class="text-lg font-black text-foreground mb-1">Preview Import</h3>
                <p class="text-xs text-muted-foreground font-semibold uppercase tracking-wider">
                    <span x-text="importRows.length"></span> records found
                </p>
            </div>
            
            <div class="h-px bg-border/60 w-full shrink-0"></div>
            
            <div class="flex-1 overflow-auto rounded-2xl border border-border/60 custom-scrollbar">
                <table class="w-full text-left border-collapse min-w-[700px]">
                    <thead class="sticky top-0 bg-muted/90 backdrop-blur-md z-10 shadow-sm">
                        <tr class="border-b border-border/40">
                            <th class="px-4 py-3 text-[10px] font-black uppercase tracking-widest text-muted-foreground whitespace-nowrap">Order No</th>
                            <th class="px-4 py-3 text-[10px] font-black uppercase tracking-widest text-muted-foreground whitespace-nowrap">Customer</th>
                            <th class="px-4 py-3 text-[10px] font-black uppercase tracking-widest text-muted-foreground whitespace-nowrap">Current Status</th>
                            <th class="px-4 py-3 text-[10px] font-black uppercase tracking-widest text-muted-foreground whitespace-nowrap">Upcoming Status</th>
                            <th class="px-4 py-3 text-[10px] font-black uppercase tracking-widest text-muted-foreground whitespace-nowrap">CSV Tracking</th>
                            <th class="px-4 py-3 text-[10px] font-black uppercase tracking-widest text-muted-foreground whitespace-nowrap">Existing Tracking</th>
                            <th class="px-4 py-3 text-[10px] font-black uppercase tracking-widest text-muted-foreground whitespace-nowrap">Valid?</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-border/40">
                        <template x-for="(row, rowIndex) in importRows" :key="rowIndex">
                            <tr class="hover:bg-muted/10 transition-colors">
                                <td class="px-4 py-2.5 text-xs font-bold text-foreground/80" x-text="row.order_no"></td>
                                <td class="px-4 py-2.5 text-xs font-semibold text-foreground/80" x-text="row.customer"></td>
                                <td class="px-4 py-2.5 text-xs font-semibold uppercase tracking-wider text-muted-foreground" x-text="(row.current_status || '').replace(/_/g, ' ')"></td>
                                <td class="px-4 py-2.5 text-xs font-bold uppercase tracking-wider text-blue-500" x-text="(row.upcoming_status || '').replace(/_/g, ' ')"></td>
                                <td class="px-4 py-2.5 text-xs font-semibold text-foreground/80" x-text="row.csv_carrier + ' - ' + row.csv_tracking"></td>
                                <td class="px-4 py-2.5 text-xs font-semibold text-muted-foreground" x-text="row.existing_carrier + ' - ' + row.existing_tracking"></td>
                                <td class="px-4 py-2.5 text-xs font-semibold">
                                    <span x-show="row.is_valid" class="text-emerald-500 font-bold"><x-ui.icon name="check-circle" size="3" class="inline" /> Yes</span>
                                    <span x-show="!row.is_valid" class="text-red-500 font-bold"><x-ui.icon name="x-circle" size="3" class="inline" /> No</span>
                                </td>
                            </tr>
                        </template>
                    </tbody>
                </table>
                <div x-show="importRows.length === 0" class="p-8 text-center text-xs font-bold text-muted-foreground uppercase tracking-widest">
                    No data rows found in CSV
                </div>
            </div>
            
            <div class="flex items-center justify-end gap-3 pt-4 border-t border-border/40 shrink-0">
                <x-ui.button type="button" variant="outline" size="sm" @click="cancelImport()" class="rounded-xl font-bold uppercase tracking-widest text-[10px] h-10">
                    Cancel
                </x-ui.button>
                <x-ui.button type="button" size="sm" @click="confirmImport()" x-bind:disabled="importRows.filter(r => r.is_valid).length === 0" class="rounded-xl font-bold uppercase tracking-widest text-[10px] h-10 shadow-lg shadow-primary/20">
                    <x-ui.icon name="check-circle" size="3" class="mr-2" /> Confirm & Process (<span x-text="importRows.filter(r => r.is_valid).length"></span>)
                </x-ui.button>
            </div>
        </div>
    </x-ui.modal>

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
