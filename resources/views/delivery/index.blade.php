<x-layouts.app pageTitle="Logistics & Delivery Operations">

    @php $deliveryBaseUrl = url('delivery'); @endphp

    <div class="p-6 lg:p-10" x-data="{ 
        search: '{{ request('search', '') }}',
        perPage: '{{ request('perPage', 10) }}',
        stats: @js($stats),
        verificationData: @js($verificationPayloads ?? []),
        verificationFormUrl: '',
        deliveryId: null,
        deliveryNo: '',
        shipmentNo: '',
        orderNo: '',
        deliveryStatus: '',
        driverName: '',
        vehicle: '',
        partyName: '',
        phones: [],
        emails: [],
        orderAmount: '',
        orderDate: '',
        shipping: {},
        billing: {},
        legacyShipping: '',
        history: [],
        outcome: '',
        remark: '',
        followUpAt: '',
        isLoading: false,
        selectedRecords: [],
        allSelected: false,

        openDeliveryVerification(id) {
            const d = this.verificationData[id];
            if (!d) return;
            this.deliveryId = d.id;
            this.verificationFormUrl = `{{ $deliveryBaseUrl }}/${id}/verification`;
            this.deliveryNo = d.delivery_no;
            this.shipmentNo = d.shipment_no;
            this.orderNo = d.order_no;
            this.deliveryStatus = d.status;
            this.driverName = d.driver_name;
            this.vehicle = d.vehicle;
            this.partyName = d.party_name;
            this.phones = d.phones || [];
            this.emails = d.emails || [];
            this.orderAmount = d.order_amount;
            this.orderDate = d.order_date;
            this.shipping = d.shipping || {};
            this.billing = d.billing || {};
            this.legacyShipping = d.legacy_shipping || '';
            this.history = d.history || [];
            this.outcome = '';
            this.remark = '';
            this.followUpAt = '';
            this.$dispatch('open-modal', { name: 'delivery-verification-modal' });
        },

        toggleAll() {
            const checkboxes = document.querySelectorAll('input[name=\'ids[]\']');
            if (this.allSelected) {
                this.selectedRecords = Array.from(checkboxes).map(el => parseInt(el.value));
            } else {
                this.selectedRecords = [];
            }
        },

        toggleRecord(id) {
            id = parseInt(id);
            if (this.selectedRecords.includes(id)) {
                this.selectedRecords = this.selectedRecords.filter(rId => rId !== id);
            } else {
                this.selectedRecords.push(id);
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
                const tableRoot = document.getElementById('table-container');
                tableRoot.innerHTML = data.table;
                if (window.Alpine?.initTree) {
                    window.Alpine.initTree(tableRoot);
                }
                this.stats = data.stats;
                if (data.verificationPayloads) {
                    this.verificationData = data.verificationPayloads;
                }
                this.selectedRecords = [];
                this.allSelected = false;
            } catch (error) {
                console.error('Fetch failed:', error);
            } finally {
                this.isLoading = false;
            }
        },

        async performSearch() {
            const params = new URLSearchParams({ search: this.search, perPage: this.perPage });
            const url = `{{ route('delivery.index') }}?${params.toString()}`;
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

        openAssignModal() {
            $dispatch('open-modal', { name: 'assign-modal' });
        }
    }">

        @if(session('success'))
            <div class="mb-6 p-4 rounded-2xl bg-emerald-500/10 border border-emerald-500/20 text-emerald-600 text-xs font-bold uppercase tracking-widest">
                {{ session('success') }}
            </div>
        @endif
        @if(session('error'))
            <div class="mb-6 p-4 rounded-2xl bg-red-500/10 border border-red-500/20 text-red-600 text-xs font-bold uppercase tracking-widest">
                {{ session('error') }}
            </div>
        @endif

        <!-- Stats Widgets -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
            <x-ui.card class="p-6 bg-card/40 border-border/60 backdrop-blur-xl hover:bg-primary/5 transition-all duration-500 rounded-3xl group relative overflow-hidden">
                <div class="absolute top-0 right-0 -mr-8 -mt-8 size-32 bg-primary/10 blur-[50px] rounded-full group-hover:bg-primary/20 transition-all"></div>
                <div class="flex items-center gap-5 relative z-10">
                    <div class="size-14 rounded-2xl bg-gradient-to-tr from-primary/20 to-primary/5 border border-primary/10 text-primary flex items-center justify-center shadow-inner group-hover:scale-110 transition-transform duration-500">
                        <x-ui.icon name="truck-2" size="7" />
                    </div>
                    <div>
                        <p class="text-[10px] font-black uppercase tracking-widest text-muted-foreground/60 mb-1">Total Deliveries</p>
                        <div class="text-3xl font-black tracking-tighter text-foreground" x-text="stats.total"></div>
                    </div>
                </div>
            </x-ui.card>

            <x-ui.card class="p-6 bg-card/40 border-border/60 backdrop-blur-xl hover:bg-blue-500/5 transition-all duration-500 rounded-3xl group relative overflow-hidden">
                <div class="absolute top-0 right-0 -mr-8 -mt-8 size-32 bg-blue-500/10 blur-[50px] rounded-full group-hover:bg-blue-500/20 transition-all"></div>
                <div class="flex items-center gap-5 relative z-10">
                    <div class="size-14 rounded-2xl bg-gradient-to-tr from-blue-500/20 to-blue-500/5 border border-blue-500/10 text-blue-500 flex items-center justify-center shadow-inner group-hover:scale-110 transition-transform duration-500">
                        <x-ui.icon name="navigation" size="7" />
                    </div>
                    <div>
                        <p class="text-[10px] font-black uppercase tracking-widest text-muted-foreground/60 mb-1">Out For Delivery</p>
                        <div class="text-3xl font-black tracking-tighter text-foreground" x-text="stats.out_for_delivery"></div>
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
                        <p class="text-[10px] font-black uppercase tracking-widest text-muted-foreground/60 mb-1">Delivered</p>
                        <div class="text-3xl font-black tracking-tighter text-foreground" x-text="stats.delivered"></div>
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
                        <p class="text-[10px] font-black uppercase tracking-widest text-muted-foreground/60 mb-1">Failed</p>
                        <div class="text-3xl font-black tracking-tighter text-foreground" x-text="stats.failed"></div>
                    </div>
                </div>
            </x-ui.card>
        </div>

        <x-ui.card class="overflow-hidden border-border/60 shadow-2xl bg-card/30 backdrop-blur-2xl rounded-3xl">
            <x-ui.card-header class="border-b border-border/40 bg-muted/10 p-8">
                <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-6">
                    <div class="flex flex-wrap items-center gap-3">
                        <div class="flex bg-muted/50 px-4 py-1.5 rounded-xl border border-border/50 shadow-inner">
                            <span class="text-xs font-bold text-primary tracking-widest uppercase">Deliveries Control</span>
                        </div>
                        
                        <!-- Bulk Actions -->
                        <div x-show="selectedRecords.length > 0" x-cloak x-transition class="flex items-center gap-2">
                            <x-ui.dropdown>
                                <x-slot name="trigger">
                                    <x-ui.button variant="outline" size="sm" class="rounded-xl border-primary/20 bg-primary/5 text-primary font-bold shadow-sm whitespace-nowrap">
                                        <span x-text="selectedRecords.length"></span> Selected
                                        <x-ui.icon name="chevron-down" size="3" class="ml-2" />
                                    </x-ui.button>
                                </x-slot>
                                <x-slot name="content">
                                    <x-ui.dropdown-label>Bulk Actions</x-ui.dropdown-label>
                                    <form action="{{ route('delivery.bulk-delete') }}" method="POST" onsubmit="return confirm('Delete selected delivery assignments?')">
                                        @csrf
                                        <input type="hidden" name="ids" :value="JSON.stringify(selectedRecords)">
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
                                <option value="10">10</option>
                                <option value="20">20</option>
                                <option value="50">50</option>
                            </select>
                        </div>
                        <div class="relative group w-full lg:w-64 shrink-0">
                            <x-ui.icon name="search" size="4" class="absolute left-3 top-1/2 -translate-y-1/2 text-muted-foreground group-focus-within:text-primary transition-colors" />
                            <input type="text" x-model="search" @input.debounce.500ms="performSearch()" placeholder="Search deliveries..." 
                                class="pl-9 pr-4 py-2.5 rounded-xl border border-border bg-background/50 focus:bg-background focus:ring-2 focus:ring-primary/20 transition-all w-full text-xs shadow-sm outline-none">
                        </div>
                        <x-ui.button @click.stop="openAssignModal" class="rounded-xl font-black uppercase tracking-widest text-[10px] h-11 px-6 shadow-lg shadow-primary/20 w-full lg:w-auto">
                            <x-ui.icon name="plus" size="3" class="mr-2" /> Assign Shipment
                        </x-ui.button>
                    </div>
                </div>
            </x-ui.card-header>

            <x-ui.card-content class="p-0 relative">
                <div x-show="isLoading" x-cloak class="absolute inset-0 z-50 bg-background/50 backdrop-blur-[2px] flex items-center justify-center">
                    <x-ui.icon name="refresh-cw" class="animate-spin text-primary" size="6" />
                </div>
                <div id="table-container" @click="handlePagination($event)">
                    @include('delivery.partials.table')
                </div>
            </x-ui.card-content>
        </x-ui.card>

        <!-- Assignment Modal -->
        <x-ui.modal id="assign-modal" maxWidth="md">
            <div class="p-8">
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

                    <div class="space-y-2" x-data="{ 
                        open: false,
                        search: '',
                        selectedShipments: [],
                        shipments: [
                            @foreach($availableShipments as $shp)
                                { 
                                    id: {{ $shp->id }}, 
                                    no: '{{ addslashes($shp->shipment_no) }}', 
                                    order: '{{ $shp->order ? addslashes($shp->order->order_no) : '—' }}',
                                    party: '{{ $shp->order && $shp->order->party ? addslashes($shp->order->party->company_name) : 'No Party' }}'
                                },
                            @endforeach
                        ],
                        get filteredShipments() {
                            if (!this.search) return this.shipments;
                            return this.shipments.filter(s => 
                                s.no.toLowerCase().includes(this.search.toLowerCase()) || 
                                s.order.toLowerCase().includes(this.search.toLowerCase()) ||
                                s.party.toLowerCase().includes(this.search.toLowerCase())
                            );
                        },
                        toggleShipment(shp) {
                            if (this.selectedShipments.some(s => s.id === shp.id)) {
                                this.selectedShipments = this.selectedShipments.filter(s => s.id !== shp.id);
                            } else {
                                this.selectedShipments.push(shp);
                            }
                        },
                        isSelected(id) {
                            return this.selectedShipments.some(s => s.id === id);
                        }
                    }">
                        <label class="text-[10px] font-black uppercase tracking-widest text-muted-foreground/80 ml-1">Select Ready Shipment(s)</label>
                        
                        <!-- Hidden inputs for each selected shipment id -->
                        <template x-for="s in selectedShipments" :key="s.id">
                            <input type="hidden" name="shipment_ids[]" :value="s.id">
                        </template>

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
                                
                                <!-- Search Input Box -->
                                <div class="relative group">
                                    <x-ui.icon name="search" size="3" class="absolute left-2.5 top-1/2 -translate-y-1/2 text-muted-foreground group-focus-within:text-primary transition-colors" />
                                    <input type="text" x-model="search" placeholder="Type shipment no, order no, or company name..." @click.stop
                                        class="pl-8 pr-3 py-1.5 rounded-lg border border-border bg-background/30 focus:bg-background focus:ring-2 focus:ring-primary/20 transition-all w-full text-[11px] outline-none h-8">
                                </div>

                                <!-- Checkbox Options List -->
                                <div class="space-y-1 max-h-40 overflow-y-auto">
                                    <template x-for="s in filteredShipments" :key="s.id">
                                        <button type="button" @click="toggleShipment(s)"
                                            class="w-full text-left px-3 py-2 rounded-xl text-[11px] font-semibold flex items-center justify-between hover:bg-primary/10 hover:text-primary transition-all border border-transparent"
                                            :class="isSelected(s.id) ? 'bg-primary/5 text-primary border-primary/20' : 'text-foreground/80'">
                                            <div class="flex flex-col">
                                                <span x-text="s.no"></span>
                                                <span class="text-[9px] text-muted-foreground font-medium" x-text="`Order #${s.order} — ${s.party}`"></span>
                                            </div>
                                            <!-- Checkbox Element -->
                                            <input type="checkbox" :checked="isSelected(s.id)" class="rounded border-border text-primary focus:ring-primary/20 pointer-events-none">
                                        </button>
                                    </template>
                                    <div x-show="filteredShipments.length === 0" class="text-center py-4 text-[10px] text-muted-foreground font-medium uppercase tracking-widest">
                                        No shipments found
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="space-y-2">
                            <label for="driver_id" class="text-[10px] font-black uppercase tracking-widest text-muted-foreground/80 ml-1">Select Driver</label>
                            <select id="driver_id" name="driver_id" required class="w-full h-11 px-4 rounded-xl border border-border bg-background/50 text-[10px] font-black uppercase tracking-widest focus:bg-background focus:ring-2 focus:ring-primary/20 outline-none transition-all">
                                <option value="" disabled selected>Select a Driver</option>
                                @forelse($drivers as $drv)
                                    <option value="{{ $drv->id }}">{{ $drv->name }}</option>
                                @empty
                                    <option value="" disabled>No drivers available</option>
                                @endforelse
                            </select>
                        </div>

                        <div class="space-y-2">
                            <label for="transport_id" class="text-[10px] font-black uppercase tracking-widest text-muted-foreground/80 ml-1">Select Vehicle</label>
                            <select id="transport_id" name="transport_id" required class="w-full h-11 px-4 rounded-xl border border-border bg-background/50 text-[10px] font-black uppercase tracking-widest focus:bg-background focus:ring-2 focus:ring-primary/20 outline-none transition-all">
                                <option value="" disabled selected>Select a Vehicle</option>
                                @forelse($transports as $tr)
                                    <option value="{{ $tr->id }}">{{ $tr->name }} ({{ $tr->vehicle_number }})</option>
                                @empty
                                    <option value="" disabled>No vehicles available</option>
                                @endforelse
                            </select>
                        </div>
                    </div>

                    <div class="mt-8 flex justify-end gap-3 border-t border-border/30 pt-6">
                        <x-ui.button type="button" variant="outline" @click="$dispatch('close-modal', { name: 'assign-modal' })" class="rounded-xl font-black uppercase tracking-widest text-[10px]">Cancel</x-ui.button>
                        <x-ui.button type="submit" class="rounded-xl font-black uppercase tracking-widest text-[10px] shadow-lg shadow-primary/25">Dispatch Order</x-ui.button>
                    </div>
                </form>
            </div>
        </x-ui.modal>

        @include('delivery.partials.verification-modal')

    </div>

    <style>
        [x-cloak] { display: none !important; }
        .custom-scrollbar::-webkit-scrollbar { height: 6px; width: 6px; }
        .custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
        .custom-scrollbar::-webkit-scrollbar-thumb { background: rgba(var(--border), 0.1); border-radius: 10px; }
    </style>
</x-layouts.app>
