<x-layouts.app pageTitle="Orders Management">
    @php
        $qStatus = request('status') ? explode(',', request('status')) : [];
        $qProduct = request('product') ? explode(',', request('product')) : [];
        $qState = request('state') ? explode(',', request('state')) : [];
        $qDistrict = request('district') ? explode(',', request('district')) : [];
        $qTaluka = request('taluka') ? explode(',', request('taluka')) : [];
        $qVillage = request('village') ? explode(',', request('village')) : [];
    @endphp

    <div class="p-6 lg:p-10" x-data="{ 
        selectedItems: [], 
        selectedStatuses: {},
        allSelected: false,
        visibleColumns: (() => {
            const defaults = {
                order_identity: true,
                transaction_type: true,
                associated_party: true,
                fulfillment_node: true,
                created_by: true,
                lifecycle_status: true,
                carrier_details: true,
                ordered_products: true,
                financial_total: true,
                actions: true
            };
            const stored = localStorage.getItem('order_table_visible_columns');
            return stored ? { ...defaults, ...JSON.parse(stored) } : defaults;
        })(),
        toggleColumn(col) {
            this.visibleColumns[col] = !this.visibleColumns[col];
            localStorage.setItem('order_table_visible_columns', JSON.stringify(this.visibleColumns));
        },
        search: @js(request('search', '')),
        perPage: @js(request('perPage', 15)),
        sortDate: @js(request('sort_date', 'desc')),
        fromDate: @js(request('from_date', '')),
        toDate: @js(request('to_date', '')),
        statusFilter: @js($qStatus),
        productFilter: @js($qProduct),
        fulfillmentFilter: @js(request('fulfillment', '')),
        stateFilter: @js($qState),
        districtFilter: @js($qDistrict),
        talukaFilter: @js($qTaluka),
        villageFilter: @js($qVillage),
        carrierFilter: @js(request('carrier') ? explode(',', request('carrier')) : []),
        statusesList: @js($statusesList),
        productsList: @js($productsList),
        statesList: @js($statesList),
        districtsList: @js($districtsList),
        talukasList: @js($talukasList),
        villagesList: @js($villagesList ?? []),
        carriersList: @js($carriersList),
        stats: @js($stats),
        isLoading: false,
        shipOrderNo: '',
        shipOrderId: '',
        shipCarrierName: '',
        shipActionUrl: '',
        returnOrderId: null,
        returnOrderNo: '',
        returnItems: [],
        verifyOrderId: '',
        verifyOrderNo: '',
        verifyOutcome: '',
        verifyActionUrl: '',
        bulkVerifyStatus: '',
        bulkVerifyOutcome: '',
        get returnRefundTotal() {
            return this.returnItems.reduce((sum, item) => sum + (Number(item.qty) * Number(item.price)), 0);
        },
        openVerificationModal(orderId, orderNo, outcome) {
            this.verifyOrderId = orderId;
            this.verifyOrderNo = orderNo;
            this.verifyOutcome = outcome;
            this.verifyActionUrl = `{{ route('orders.verification.store', ':id') }}`.replace(':id', orderId);
            this.$dispatch('open-modal', { name: 'index-verification-modal' });
        },
        openBulkVerificationModal(status, outcome) {
            this.bulkVerifyStatus = status;
            this.bulkVerifyOutcome = outcome;
            this.$dispatch('open-modal', { name: 'bulk-verification-modal' });
        },
        openShipModal(orderId, orderNo, carrierName = '') {
            this.shipOrderId = orderId;
            this.shipOrderNo = orderNo;
            this.shipCarrierName = carrierName;
            this.shipActionUrl = `{{ route('orders.ship', ':id') }}`.replace(':id', orderId);
            this.$dispatch('open-modal', { name: 'create-shipment-modal' });
        },
        openAssignModal(preShipments) {
            if (preShipments && preShipments.length > 0) {
                window._orderAssignShipments = preShipments.filter(s => s.id);
            } else {
                const checkboxes = document.querySelectorAll('input[name=\'order_ids[]\']:checked');
                const shipments = [];
                checkboxes.forEach(cb => {
                    const shipId = cb.getAttribute('data-shipment-id');
                    const orderNo = cb.getAttribute('data-order-no');
                    const driverId = cb.getAttribute('data-driver-id') || '';
                    const transportId = cb.getAttribute('data-transport-id') || '';
                    if (shipId && shipId !== '' && shipId !== 'null') {
                        shipments.push({ id: parseInt(shipId), no: orderNo, order: orderNo, party: '', driver_id: driverId, transport_id: transportId });
                    }
                });
                window._orderAssignShipments = shipments;
            }
            this.$dispatch('open-modal', { name: 'assign-modal' });
        },
        openReturnModal(payload) {
            this.returnOrderId = payload.id;
            this.returnOrderNo = payload.orderNo;
            this.returnItems = (payload.items || []).map((i) => ({
                id: i.id,
                name: i.name,
                sku: i.sku,
                price: i.price,
                max: i.max,
                qty: i.max,
            }));
            this.$dispatch('open-modal', { name: 'create-return-modal' });
        },
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

        toggleAll() {
            if (this.allSelected) {
                const checkboxes = Array.from(document.querySelectorAll('input[name=\'order_ids[]\']'));
                this.selectedItems = checkboxes.map(el => parseInt(el.value));
                this.selectedStatuses = {};
                checkboxes.forEach(el => {
                    this.selectedStatuses[parseInt(el.value)] = el.getAttribute('data-status');
                });
            } else {
                this.selectedItems = [];
                this.selectedStatuses = {};
            }
        },

        toggleItem(id, status) {
            if (this.selectedItems.includes(id)) {
                this.selectedItems = this.selectedItems.filter(i => i !== id);
                delete this.selectedStatuses[id];
            } else {
                this.selectedItems.push(id);
                this.selectedStatuses[id] = status;
            }
        },

        canBulkFulfill(status) {
            const selectedValues = Object.values(this.selectedStatuses);
            if (selectedValues.length === 0) return false;
            return selectedValues.some(current => {
                if (status === 'confirmed') return current === 'pending';
                if (status === 'processing') return current === 'confirmed';
                if (status === 'ready_to_ship') return current === 'confirmed' || current === 'processing';
                if (status === 'dispatched') return current === 'ready_to_ship';
                if (status === 'delivered') return current === 'dispatched' || current === 'shipped';
                if (status === 'cancelled') return ['pending', 'confirmed', 'processing', 'ready_to_ship'].includes(current);
                return false;
            });
        },

        canBulkRevert(status) {
            const selectedValues = Object.values(this.selectedStatuses);
            if (selectedValues.length === 0) return false;
            return selectedValues.some(current => {
                if (status === 'pending') return ['confirmed', 'processing', 'cancelled', 'ready_to_ship'].includes(current);
                if (status === 'confirmed') return current === 'processing';
                if (status === 'ready_to_ship') return current === 'dispatched' || current === 'delivered';
                if (status === 'processing') return current === 'ready_to_ship';
                if (status === 'dispatched') return current === 'delivered';
                return false;
            });
        },

        hasBulkFulfillOptions() {
            return ['confirmed', 'processing', 'ready_to_ship', 'dispatched', 'delivered', 'cancelled'].some(s => this.canBulkFulfill(s));
        },

        hasBulkRevertOptions() {
            return ['pending', 'confirmed', 'processing', 'ready_to_ship', 'dispatched'].some(s => this.canBulkRevert(s));
        },

        async performSearch() {
            this.isLoading = true;
            let params = new URLSearchParams({
                search: this.search,
                perPage: this.perPage,
                status: this.statusFilter.join(','),
                product: this.productFilter.join(','),
                fulfillment: this.fulfillmentFilter,
                state: this.stateFilter.join(','),
                district: this.districtFilter.join(','),
                taluka: this.talukaFilter.join(','),
                village: this.villageFilter.join(','),
                carrier: this.carrierFilter.join(','),
                sort_date: this.sortDate,
                from_date: this.fromDate,
                to_date: this.toDate
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
            const tableRoot = document.getElementById('table-container');
            if (window.Alpine?.initTree && tableRoot) {
                window.Alpine.initTree(tableRoot);
            }
            this.districtsList = data.districts;
            this.talukasList = data.talukas;
            this.villagesList = data.villages || [];
            this.stats = data.stats;
            if (data.carriers) this.carriersList = data.carriers;
            
            // Sync dependent filters
            this.districtFilter = this.districtFilter.filter(d => this.districtsList.includes(d));
            this.talukaFilter = this.talukaFilter.filter(t => this.talukasList.includes(t));
            this.villageFilter = this.villageFilter.filter(v => this.villagesList.includes(v));

            this.isLoading = false;
            this.selectedItems = [];
            this.selectedStatuses = {};
            this.allSelected = false;
        },

        clearFilters() {
            this.search = '';
            this.statusFilter = [];
            this.productFilter = [];
            this.fulfillmentFilter = '';
            this.stateFilter = [];
            this.districtFilter = [];
            this.talukaFilter = [];
            this.villageFilter = [];
            this.carrierFilter = [];
            this.sortDate = 'desc';
            this.fromDate = '';
            this.toDate = '';
            this.performSearch();
        }
    }">

        <div class="max-w-[100rem] mx-auto space-y-8">
            <!-- Stats Widgets -->
            <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-8 gap-3 mb-6">

                <!-- Total -->
                <div class="group relative p-4 rounded-2xl bg-card/40 border border-border/60 backdrop-blur-xl hover:bg-primary/5 transition-all duration-300 overflow-hidden shadow-xl col-span-2 md:col-span-1">
                    <div class="absolute top-0 right-0 -mr-6 -mt-6 size-20 bg-primary/10 blur-[40px] rounded-full"></div>
                    <div class="flex items-center gap-3 relative z-10">
                        <div class="size-10 rounded-xl bg-gradient-to-tr from-primary/20 to-primary/5 border border-primary/10 text-primary flex items-center justify-center shadow-inner group-hover:scale-110 transition-transform duration-300">
                            <x-ui.icon name="shopping-cart" size="5" />
                        </div>
                        <div>
                            <p class="text-[9px] font-black uppercase tracking-widest text-muted-foreground/60 mb-0.5">Total</p>
                            <div class="text-2xl font-black tracking-tighter text-foreground" x-text="stats.total"></div>
                        </div>
                    </div>
                </div>

                <!-- Future Order -->
                <div class="group relative p-4 rounded-2xl bg-card/40 border border-border/60 backdrop-blur-xl hover:bg-purple-500/5 transition-all duration-300 overflow-hidden shadow-xl">
                    <div class="absolute top-0 right-0 -mr-6 -mt-6 size-20 bg-purple-500/10 blur-[40px] rounded-full"></div>
                    <div class="flex items-center gap-3 relative z-10">
                        <div class="size-10 rounded-xl bg-gradient-to-tr from-purple-500/20 to-purple-500/5 border border-purple-500/10 text-purple-500 flex items-center justify-center shadow-inner group-hover:scale-110 transition-transform duration-300">
                            <x-ui.icon name="calendar" size="5" />
                        </div>
                        <div>
                            <p class="text-[9px] font-black uppercase tracking-widest text-muted-foreground/60 mb-0.5">Future</p>
                            <div class="text-2xl font-black tracking-tighter text-purple-500" x-text="stats.future_order"></div>
                        </div>
                    </div>
                </div>

                <!-- Pending -->
                <div class="group relative p-4 rounded-2xl bg-card/40 border border-border/60 backdrop-blur-xl hover:bg-orange-500/5 transition-all duration-300 overflow-hidden shadow-xl">
                    <div class="absolute top-0 right-0 -mr-6 -mt-6 size-20 bg-orange-500/10 blur-[40px] rounded-full"></div>
                    <div class="flex items-center gap-3 relative z-10">
                        <div class="size-10 rounded-xl bg-gradient-to-tr from-orange-500/20 to-orange-500/5 border border-orange-500/10 text-orange-500 flex items-center justify-center shadow-inner group-hover:scale-110 transition-transform duration-300">
                            <x-ui.icon name="clock" size="5" />
                        </div>
                        <div>
                            <p class="text-[9px] font-black uppercase tracking-widest text-muted-foreground/60 mb-0.5">Pending</p>
                            <div class="text-2xl font-black tracking-tighter text-orange-500" x-text="stats.pending"></div>
                        </div>
                    </div>
                </div>

                <!-- Confirmed -->
                <div class="group relative p-4 rounded-2xl bg-card/40 border border-border/60 backdrop-blur-xl hover:bg-sky-500/5 transition-all duration-300 overflow-hidden shadow-xl">
                    <div class="absolute top-0 right-0 -mr-6 -mt-6 size-20 bg-sky-500/10 blur-[40px] rounded-full"></div>
                    <div class="flex items-center gap-3 relative z-10">
                        <div class="size-10 rounded-xl bg-gradient-to-tr from-sky-500/20 to-sky-500/5 border border-sky-500/10 text-sky-500 flex items-center justify-center shadow-inner group-hover:scale-110 transition-transform duration-300">
                            <x-ui.icon name="check-circle" size="5" />
                        </div>
                        <div>
                            <p class="text-[9px] font-black uppercase tracking-widest text-muted-foreground/60 mb-0.5">Confirmed</p>
                            <div class="text-2xl font-black tracking-tighter text-sky-500" x-text="stats.confirmed"></div>
                        </div>
                    </div>
                </div>

                <!-- Processing -->
                <div class="group relative p-4 rounded-2xl bg-card/40 border border-border/60 backdrop-blur-xl hover:bg-blue-500/5 transition-all duration-300 overflow-hidden shadow-xl">
                    <div class="absolute top-0 right-0 -mr-6 -mt-6 size-20 bg-blue-500/10 blur-[40px] rounded-full"></div>
                    <div class="flex items-center gap-3 relative z-10">
                        <div class="size-10 rounded-xl bg-gradient-to-tr from-blue-500/20 to-blue-500/5 border border-blue-500/10 text-blue-500 flex items-center justify-center shadow-inner group-hover:scale-110 transition-transform duration-300">
                            <x-ui.icon name="settings" size="5" />
                        </div>
                        <div>
                            <p class="text-[9px] font-black uppercase tracking-widest text-muted-foreground/60 mb-0.5">Processing</p>
                            <div class="text-2xl font-black tracking-tighter text-blue-500" x-text="stats.processing"></div>
                        </div>
                    </div>
                </div>

                <!-- Ready to Ship -->
                <div class="group relative p-4 rounded-2xl bg-card/40 border border-border/60 backdrop-blur-xl hover:bg-indigo-500/5 transition-all duration-300 overflow-hidden shadow-xl">
                    <div class="absolute top-0 right-0 -mr-6 -mt-6 size-20 bg-indigo-500/10 blur-[40px] rounded-full"></div>
                    <div class="flex items-center gap-3 relative z-10">
                        <div class="size-10 rounded-xl bg-gradient-to-tr from-indigo-500/20 to-indigo-500/5 border border-indigo-500/10 text-indigo-500 flex items-center justify-center shadow-inner group-hover:scale-110 transition-transform duration-300">
                            <x-ui.icon name="package" size="5" />
                        </div>
                        <div>
                            <p class="text-[9px] font-black uppercase tracking-widest text-muted-foreground/60 mb-0.5">Ready</p>
                            <div class="text-2xl font-black tracking-tighter text-indigo-500" x-text="stats.ready_to_ship"></div>
                        </div>
                    </div>
                </div>

                <!-- Dispatched -->
                <div class="group relative p-4 rounded-2xl bg-card/40 border border-border/60 backdrop-blur-xl hover:bg-teal-500/5 transition-all duration-300 overflow-hidden shadow-xl">
                    <div class="absolute top-0 right-0 -mr-6 -mt-6 size-20 bg-teal-500/10 blur-[40px] rounded-full"></div>
                    <div class="flex items-center gap-3 relative z-10">
                        <div class="size-10 rounded-xl bg-gradient-to-tr from-teal-500/20 to-teal-500/5 border border-teal-500/10 text-teal-500 flex items-center justify-center shadow-inner group-hover:scale-110 transition-transform duration-300">
                            <x-ui.icon name="truck" size="5" />
                        </div>
                        <div>
                            <p class="text-[9px] font-black uppercase tracking-widest text-muted-foreground/60 mb-0.5">Dispatched</p>
                            <div class="text-2xl font-black tracking-tighter text-teal-500" x-text="stats.dispatched"></div>
                        </div>
                    </div>
                </div>

                <!-- Delivered -->
                <div class="group relative p-4 rounded-2xl bg-card/40 border border-border/60 backdrop-blur-xl hover:bg-emerald-500/5 transition-all duration-300 overflow-hidden shadow-xl">
                    <div class="absolute top-0 right-0 -mr-6 -mt-6 size-20 bg-emerald-500/10 blur-[40px] rounded-full"></div>
                    <div class="flex items-center gap-3 relative z-10">
                        <div class="size-10 rounded-xl bg-gradient-to-tr from-emerald-500/20 to-emerald-500/5 border border-emerald-500/10 text-emerald-500 flex items-center justify-center shadow-inner group-hover:scale-110 transition-transform duration-300">
                            <x-ui.icon name="check" size="5" />
                        </div>
                        <div>
                            <p class="text-[9px] font-black uppercase tracking-widest text-muted-foreground/60 mb-0.5">Delivered</p>
                            <div class="text-2xl font-black tracking-tighter text-emerald-500" x-text="stats.delivered"></div>
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
                                @can('orders.create')
                                    <form id="import-form" action="{{ route('orders.import') }}" method="POST" enctype="multipart/form-data" class="hidden">
                                        @csrf
                                        <input type="file" name="file" id="import-file" accept=".csv,.txt" @change="handleImportFileSelect($event)">
                                    </form>
                                    <x-ui.dropdown>
                                        <x-slot name="trigger">
                                            <x-ui.button variant="outline" size="sm" class="flex-1 sm:flex-none rounded-xl font-bold uppercase tracking-widest text-[10px] h-10 shadow-sm border-border/60 bg-background/40 backdrop-blur-sm">
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
                                @endcan
                                <x-ui.dropdown>
                                    <x-slot name="trigger">
                                        <x-ui.button variant="outline" size="sm" class="flex-1 sm:flex-none rounded-xl font-bold uppercase tracking-widest text-[10px] h-10 shadow-sm border-border/60 bg-background/40 backdrop-blur-sm">
                                            <x-ui.icon name="sliders" size="3" class="mr-2 text-cyan-500" />
                                            Columns
                                            <x-ui.icon name="chevron-down" size="3" class="ml-2 opacity-50" />
                                        </x-ui.button>
                                    </x-slot>
                                    <x-slot name="content">
                                        <div class="p-2 space-y-1.5 w-56" @click.stop>
                                            <div class="px-2 py-1 text-[9px] font-black uppercase tracking-widest text-muted-foreground/60 border-b border-border/20">
                                                Toggle Columns
                                            </div>
                                            <div class="space-y-0.5">
                                                <template x-for="(visible, col) in visibleColumns" :key="col">
                                                    <label class="flex items-center gap-2 px-2.5 py-1.5 rounded-lg hover:bg-primary/5 cursor-pointer transition-colors" x-bind:class="visible ? 'bg-primary/5' : ''">
                                                        <input type="checkbox" :checked="visible" @change="toggleColumn(col)" class="rounded border-border text-primary focus:ring-primary/25">
                                                        <span class="text-[10px] font-black uppercase tracking-wider text-foreground/80" x-text="col.replace(/_/g, ' ')"></span>
                                                    </label>
                                                </template>
                                            </div>
                                        </div>
                                    </x-slot>
                                </x-ui.dropdown>

                                @can('orders.view')
                                    <x-ui.button variant="outline" size="sm" class="flex-1 sm:flex-none rounded-xl font-bold uppercase tracking-widest text-[10px] h-10 shadow-sm border-border/60 bg-background/40 backdrop-blur-sm" onclick="window.location.href = '{{ route('orders.export') }}' + window.location.search">
                                        <x-ui.icon name="download" size="3" class="mr-2" />
                                        Export
                                    </x-ui.button>
                                @endcan
                            </div>
                        </div>                        <!-- Toolbar: filters and search controls -->
                        <div class="flex flex-col xl:flex-row xl:items-center justify-between gap-4 pt-4 border-t border-border/30">
                            <!-- Left: All filters & controls -->
                            <div class="flex flex-wrap items-center gap-2.5 flex-1 min-w-0">
                                
                                <!-- Show Limit Select -->
                                <div class="flex items-center gap-2 shrink-0">
                                    <span class="text-[10px] font-black text-muted-foreground/60 uppercase tracking-widest">Show</span>
                                    <select x-model="perPage" @change="performSearch()" class="h-9 px-3 rounded-xl border border-border bg-background/50 text-[11px] font-bold text-muted-foreground focus:text-foreground outline-none shadow-sm cursor-pointer hover:bg-background transition-all">
                                        <option value="15">15</option>
                                        <option value="25">25</option>
                                        <option value="50">50</option>
                                        <option value="100">100</option>
                                    </select>
                                </div>

                                <!-- Bulk actions trigger -->
                                <div x-show="selectedItems.length > 0" x-cloak x-transition
                                    class="flex items-center gap-2 shrink-0 animate-in fade-in slide-in-from-left-4 duration-300">
                                    @canany(['orders.bulk_status', 'orders.bulk_print'])
                                        <x-ui.dropdown>
                                            <x-slot name="trigger">
                                                <x-ui.button variant="outline" size="sm" class="rounded-xl border-primary/20 bg-primary/5 text-primary font-bold shadow-sm whitespace-nowrap h-9 px-4 text-[10px] uppercase tracking-widest">
                                                    <span x-text="selectedItems.length"></span> Selected
                                                    <x-ui.icon name="chevron-down" size="3" class="ml-2" />
                                                </x-ui.button>
                                            </x-slot>
                                            <x-slot name="content">
                                                <x-ui.dropdown-label>Mass Lifecycle Update</x-ui.dropdown-label>
                                                <div class="p-1 space-y-1 divide-y divide-border/20">
                                                    @can('orders.bulk_status')
                                                        <div class="py-1" x-show="hasBulkFulfillOptions()">
                                                            <div class="px-3 py-1 text-[9px] font-black uppercase tracking-widest text-muted-foreground/60">Fulfillment States</div>
                                                            {{-- confirmed → verification modal (same as Lifecycle dropdown) --}}
                                                            <button type="button" x-show="canBulkFulfill('confirmed')"
                                                                @click="openBulkVerificationModal('confirmed', 'customer_confirmed')"
                                                                class="w-full text-left px-3 py-2 text-[10px] font-bold hover:bg-primary/5 hover:text-primary rounded-xl flex items-center text-foreground/80 uppercase tracking-wider transition-colors">
                                                                <span class="size-2 rounded-full bg-indigo-500 mr-2"></span>
                                                                Confirm Orders
                                                            </button>

                                                            {{-- processing → verification modal --}}
                                                            <button type="button" x-show="canBulkFulfill('processing')"
                                                                @click="openBulkVerificationModal('processing', 'mark_processing')"
                                                                class="w-full text-left px-3 py-2 text-[10px] font-bold hover:bg-primary/5 hover:text-primary rounded-xl flex items-center text-foreground/80 uppercase tracking-wider transition-colors">
                                                                <span class="size-2 rounded-full bg-amber-500 mr-2"></span>
                                                                Mark Processing
                                                            </button>

                                                            {{-- ready_to_ship → direct form (no dedicated verification outcome) --}}
                                                            <form action="{{ route('orders.bulk-status') }}" method="POST" x-show="canBulkFulfill('ready_to_ship')">
                                                                @csrf
                                                                <input type="hidden" name="ids" :value="JSON.stringify(selectedItems)">
                                                                <input type="hidden" name="status" value="ready_to_ship">
                                                                <button type="submit" class="w-full text-left px-3 py-2 text-[10px] font-bold hover:bg-primary/5 hover:text-primary rounded-xl flex items-center text-foreground/80 uppercase tracking-wider transition-colors">
                                                                    <span class="size-2 rounded-full bg-indigo-500 mr-2"></span>
                                                                    Mark Ready to Ship
                                                                </button>
                                                            </form>

                                                            {{-- dispatched → verification modal --}}
                                                            <button type="button" x-show="canBulkFulfill('dispatched')"
                                                                @click="openBulkVerificationModal('dispatched', 'dispatch_order')"
                                                                class="w-full text-left px-3 py-2 text-[10px] font-bold hover:bg-primary/5 hover:text-primary rounded-xl flex items-center text-foreground/80 uppercase tracking-wider transition-colors">
                                                                <span class="size-2 rounded-full bg-blue-500 mr-2"></span>
                                                                Dispatch Orders
                                                            </button>

                                                            {{-- Assign Shipment (unchanged) --}}
                                                            <button type="button" @click="openAssignModal(null)" x-show="canBulkFulfill('dispatched')"
                                                                class="w-full text-left px-3 py-2 text-[10px] font-bold hover:bg-primary/5 hover:text-primary rounded-xl flex items-center text-foreground/80 uppercase tracking-wider transition-colors">
                                                                <span class="size-2 rounded-full bg-blue-500 mr-2"></span>
                                                                Assign Shipment
                                                            </button>

                                                            {{-- delivered → verification modal --}}
                                                            <button type="button" x-show="canBulkFulfill('delivered')"
                                                                @click="openBulkVerificationModal('delivered', 'mark_delivered')"
                                                                class="w-full text-left px-3 py-2 text-[10px] font-bold hover:bg-primary/5 hover:text-primary rounded-xl flex items-center text-foreground/80 uppercase tracking-wider transition-colors">
                                                                <span class="size-2 rounded-full bg-emerald-500 mr-2"></span>
                                                                Deliver Orders
                                                            </button>

                                                            {{-- cancelled → verification modal --}}
                                                            <button type="button" x-show="canBulkFulfill('cancelled')"
                                                                @click="openBulkVerificationModal('cancelled', 'cancel_order')"
                                                                class="w-full text-left px-3 py-2 text-[10px] font-bold hover:bg-primary/5 hover:text-primary rounded-xl flex items-center text-foreground/80 uppercase tracking-wider transition-colors">
                                                                <span class="size-2 rounded-full bg-red-500 mr-2"></span>
                                                                Cancel Orders
                                                            </button>
                                                        </div>
                                                        <div class="py-1" x-show="hasBulkRevertOptions()">
                                                            <div class="px-3 py-1 text-[9px] font-black uppercase tracking-widest text-amber-600/70">Revert / Undo States</div>
                                                            @foreach(['pending' => 'Revert to Pending', 'confirmed' => 'Revert to Confirmed', 'processing' => 'Revert to Processing', 'ready_to_ship' => 'Revert to Ready to Ship', 'dispatched' => 'Revert to Dispatched'] as $status => $label)
                                                                <form action="{{ route('orders.bulk-status') }}" method="POST" x-show="canBulkRevert('{{ $status }}')">
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
                                                    @endcan
                                                    @can('orders.bulk_print')
                                                        <div class="py-1">
                                                            <div class="px-3 py-1 text-[9px] font-black uppercase tracking-widest text-blue-600/70">Bulk PDF Downloads</div>
                                                            <form action="{{ route('orders.bulk-print') }}" method="GET" target="_blank">
                                                                <input type="hidden" name="type" value="invoice">
                                                                <template x-for="id in selectedItems">
                                                                    <input type="hidden" name="ids[]" :value="id">
                                                                </template>
                                                                <button type="submit" class="w-full text-left px-3 py-2 text-[10px] font-bold hover:bg-blue-500/5 hover:text-blue-600 rounded-xl flex items-center text-foreground/85 uppercase tracking-wider transition-colors">
                                                                    <x-ui.icon name="file-text" size="3.5" class="mr-2 text-blue-500" />
                                                                    Bulk Invoice PDF
                                                                </button>
                                                            </form>
                                                            <form action="{{ route('orders.bulk-print') }}" method="GET" target="_blank">
                                                                <input type="hidden" name="type" value="cod">
                                                                <template x-for="id in selectedItems">
                                                                    <input type="hidden" name="ids[]" :value="id">
                                                                </template>
                                                                <button type="submit" class="w-full text-left px-3 py-2 text-[10px] font-bold hover:bg-emerald-500/5 hover:text-emerald-600 rounded-xl flex items-center text-foreground/85 uppercase tracking-wider transition-colors">
                                                                    <x-ui.icon name="printer" size="3.5" class="mr-2 text-emerald-500" />
                                                                    Bulk COD PDF
                                                                </button>
                                                            </form>
                                                        </div>
                                                    @endcan
                                                </div>
                                            </x-slot>
                                        </x-ui.dropdown>
                                    @endcanany
                                </div>

                                <!-- Filter dropdowns list (Product, Fulfillment Node, Status, Carrier, Sort, Date range) -->
                                @include('orders.partials.filters')

                                <x-ui.button variant="ghost" size="sm" @click="clearFilters()" class="rounded-xl h-9 px-4 text-[10px] font-black uppercase tracking-widest text-muted-foreground hover:text-primary transition-colors">
                                    Clear All
                                </x-ui.button>
                            </div>

                            <!-- Right: Search input -->
                            <div class="relative group w-full xl:max-w-xs shrink-0">
                                <x-ui.icon name="search" size="4" class="absolute left-3 top-1/2 -translate-y-1/2 text-muted-foreground group-focus-within:text-primary transition-colors" />
                                <input type="text" x-model="search" @input.debounce.500ms="performSearch()"
                                    placeholder="Search order number or party name..."
                                    class="pl-9 pr-10 py-2.5 rounded-xl border border-border bg-background/50 focus:bg-background focus:ring-2 focus:ring-primary/20 transition-all w-full text-xs shadow-sm outline-none">
                                <div x-show="isLoading" x-cloak class="absolute right-3 top-1/2 -translate-y-1/2 pointer-events-none">
                                    <x-ui.icon name="refresh-cw" class="animate-spin text-primary" size="4" />
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

    <!-- Ready to Ship Modal -->
    <x-ui.modal id="create-shipment-modal" maxWidth="md">
        <form :action="shipActionUrl" method="POST" class="p-6 space-y-4">
            @csrf
            <div>
                <h3 class="text-lg font-black text-foreground mb-1">Ready to Ship Details</h3>
                <p class="text-xs text-muted-foreground font-semibold uppercase tracking-wider">Order <span x-text="shipOrderNo"></span></p>
            </div>
            
            <div class="h-px bg-border/60 w-full my-2"></div>
            
            <div class="space-y-4">
                <div class="space-y-2">
                    <label for="carrier_name" class="text-[10px] font-black uppercase tracking-widest text-muted-foreground/80">Shipping Company / Carrier</label>
                    <select id="carrier_name" name="carrier_name" x-model="shipCarrierName" class="h-11 w-full rounded-xl border border-input bg-background/50 px-3 py-2 text-sm text-foreground focus:outline-none focus-visible:ring-2 focus-visible:ring-primary/20 appearance-none cursor-pointer">
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
                <x-ui.button type="submit" size="sm" class="rounded-xl font-bold uppercase tracking-widest text-[10px] h-10 bg-indigo-500 hover:bg-indigo-600 text-white shadow-lg shadow-indigo-500/20">
                    <x-ui.icon name="package" size="3" class="mr-2" /> Mark Ready to Ship
                </x-ui.button>
            </div>
        </form>
    </x-ui.modal>

    <!-- Create Return (same payload/rules as /returns/create) -->
    <x-ui.modal id="create-return-modal" maxWidth="2xl">
        <form action="{{ route('returns.store') }}" method="POST" class="p-6 space-y-4 max-h-[85vh] overflow-y-auto custom-scrollbar">
            @csrf
            <input type="hidden" name="return_to" value="orders">
            <input type="hidden" name="order_id" x-bind:value="returnOrderId">

            <div>
                <h3 class="text-lg font-black text-foreground mb-1">Create Return</h3>
                <p class="text-xs text-muted-foreground font-semibold uppercase tracking-wider">Order <span x-text="returnOrderNo"></span></p>
            </div>

            <div class="h-px bg-border/60 w-full"></div>

            <div class="space-y-4 overflow-x-auto rounded-2xl border border-border/60">
                <table class="w-full text-left border-collapse min-w-[520px]">
                    <thead>
                        <tr class="border-b border-border/40 bg-muted/5">
                            <th class="px-3 py-2 text-[10px] font-black uppercase tracking-widest text-muted-foreground">Product</th>
                            <th class="px-3 py-2 text-[10px] font-black uppercase tracking-widest text-muted-foreground text-right">Unit</th>
                            <th class="px-3 py-2 text-[10px] font-black uppercase tracking-widest text-muted-foreground text-right">Ordered</th>
                            <th class="px-3 py-2 text-[10px] font-black uppercase tracking-widest text-primary text-right">Return Qty</th>
                            <th class="px-3 py-2 text-[10px] font-black uppercase tracking-widest text-muted-foreground text-right">Refund</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-border/40">
                        <template x-for="(item, index) in returnItems" :key="item.id">
                            <tr class="hover:bg-muted/10 transition-colors">
                                <td class="px-3 py-2">
                                    <div class="font-bold text-sm text-foreground" x-text="item.name"></div>
                                    <div class="text-[10px] font-bold tracking-widest text-muted-foreground uppercase mt-0.5" x-text="'SKU: ' + item.sku"></div>
                                    <input type="hidden" :name="'items[' + index + '][order_item_id]'" :value="item.id" x-bind:disabled="Number(item.qty) <= 0">
                                </td>
                                <td class="px-3 py-2 text-right text-sm font-bold text-muted-foreground" x-text="'₹' + Number(item.price).toFixed(2)"></td>
                                <td class="px-3 py-2 text-right text-sm font-black tabular-nums" x-text="item.max"></td>
                                <td class="px-3 py-2 text-right">
                                    <input type="number" :name="'items[' + index + '][quantity]'" x-model.number="item.qty" min="0" :max="item.max" step="0.01"
                                        class="w-24 h-9 px-2 rounded-xl border border-border bg-background/50 text-sm font-semibold text-right"
                                        x-bind:disabled="Number(item.qty) <= 0">
                                </td>
                                <td class="px-3 py-2 text-right text-sm font-black tabular-nums" x-text="'₹' + (Number(item.qty) * Number(item.price)).toFixed(2)"></td>
                            </tr>
                        </template>
                    </tbody>
                    <tfoot>
                        <tr class="bg-muted/10 border-t border-border/40">
                            <td colspan="4" class="px-3 py-3 text-right text-[10px] font-black uppercase tracking-widest text-muted-foreground">Estimated refund</td>
                            <td class="px-3 py-3 text-right text-base font-black text-primary tabular-nums" x-text="'₹' + returnRefundTotal.toFixed(2)"></td>
                        </tr>
                    </tfoot>
                </table>
            </div>

            <div class="space-y-2">
                <label class="text-[10px] font-black uppercase tracking-widest text-muted-foreground/80">Return reason</label>
                <textarea name="reason" rows="3" required
                    class="w-full px-4 py-3 rounded-2xl border border-border bg-background/50 focus:bg-background focus:ring-2 focus:ring-primary/20 transition-all text-sm font-medium"></textarea>
            </div>

            <div class="flex items-center justify-end gap-3 pt-2 border-t border-border/40">
                <x-ui.button type="button" variant="outline" size="sm" @click="$dispatch('close-modal', { name: 'create-return-modal' })" class="rounded-xl font-bold uppercase tracking-widest text-[10px] h-10">
                    Cancel
                </x-ui.button>
                <x-ui.button type="submit" size="sm" x-bind:disabled="returnRefundTotal <= 0" class="rounded-xl font-bold uppercase tracking-widest text-[10px] h-10 bg-amber-600 hover:bg-amber-700 text-white">
                    <x-ui.icon name="corner-down-left" size="3" class="mr-2" /> Submit Return
                </x-ui.button>
            </div>
        </form>
    </x-ui.modal>

    <!-- Index Verification Modal -->
    @php
        $verificationOutcomes = \App\Models\OrderVerificationLog::OUTCOMES ?? [
            'customer_confirmed' => 'Customer Confirmed',
            'customer_cancelled' => 'Customer Cancelled',
            'no_response' => 'No Response',
            'call_back' => 'Requested Call Back',
            'number_busy' => 'Number Busy',
            'number_unreachable' => 'Number Unreachable',
            'wrong_number' => 'Wrong Number',
            'mark_processing' => 'Marked Processing',
            'dispatch_order' => 'Dispatched Order',
            'mark_delivered' => 'Marked Delivered',
            'cancel_order' => 'Cancelled by Staff'
        ];
    @endphp
    <x-ui.modal id="index-verification-modal" maxWidth="lg">
        <div class="p-6 lg:p-8 max-h-[90vh] overflow-y-auto custom-scrollbar">
            <div class="flex items-center justify-between mb-6 sticky top-0 bg-card/95 backdrop-blur-md z-10 pb-4 border-b border-border/40">
                <div class="flex items-center gap-3">
                    <div class="size-10 rounded-2xl bg-primary/10 border border-primary/20 text-primary flex items-center justify-center shadow-inner">
                        <x-ui.icon name="phone" size="5" />
                    </div>
                    <div>
                        <h3 class="text-sm font-black text-foreground uppercase tracking-widest">Order Verification</h3>
                        <p class="text-[10px] text-muted-foreground font-bold tracking-tight">
                            Order <span x-text="verifyOrderNo"></span>
                        </p>
                    </div>
                </div>
                <button type="button" @click="$dispatch('close-modal', { name: 'index-verification-modal' })" class="size-8 rounded-lg hover:bg-muted flex items-center justify-center transition-colors">
                    <x-ui.icon name="x" size="4" />
                </button>
            </div>

            <form method="POST" :action="verifyActionUrl" class="space-y-4 mb-4 p-5 rounded-2xl border border-primary/20 bg-primary/5">
                @csrf
                <h4 class="text-[10px] font-black uppercase tracking-widest text-primary flex items-center gap-2">
                    <x-ui.icon name="phone" size="3.5" /> Log Verification Call
                </h4>
                <div class="grid grid-cols-1 gap-4">
                    <div class="space-y-2">
                        <label class="text-[10px] font-black uppercase tracking-widest text-muted-foreground/80 ml-1">Call Outcome</label>
                        <select name="outcome" x-model="verifyOutcome" required class="w-full h-11 px-4 rounded-xl border border-border bg-background/80 text-xs font-bold focus:ring-2 focus:ring-primary/20 outline-none">
                            <option value="" disabled selected>Select outcome...</option>
                            @foreach($verificationOutcomes as $value => $label)
                                <option value="{{ $value }}">{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    
                    <div class="space-y-2">
                        <label class="text-[10px] font-black uppercase tracking-widest text-muted-foreground/80 ml-1">Remarks</label>
                        <textarea name="remark" rows="3" placeholder="Add call notes, customer response..."
                            class="w-full px-4 py-3 rounded-xl border border-border bg-background/80 text-xs font-medium focus:ring-2 focus:ring-primary/20 outline-none resize-none"></textarea>
                    </div>
                    
                    <div class="space-y-2">
                        <label class="text-[10px] font-black uppercase tracking-widest text-muted-foreground/80 ml-1">Next Follow-up (optional)</label>
                        <input type="datetime-local" name="follow_up_at"
                            class="w-full h-11 px-4 rounded-xl border border-border bg-background/80 text-xs font-medium focus:ring-2 focus:ring-primary/20 outline-none">
                    </div>
                </div>
                
                <div class="flex justify-end gap-3 pt-2">
                    <x-ui.button type="button" variant="outline" @click="$dispatch('close-modal', { name: 'index-verification-modal' })" class="rounded-xl font-black uppercase tracking-widest text-[10px]">Close</x-ui.button>
                    <x-ui.button type="submit" class="rounded-xl font-black uppercase tracking-widest text-[10px] shadow-lg shadow-primary/25" x-bind:disabled="!verifyOutcome">
                        <x-ui.icon name="check" size="3" class="mr-1.5" /> Save Verification
                    </x-ui.button>
                </div>
            </form>
        </div>
    </x-ui.modal>

    <!-- Bulk Verification Modal (mirrors index-verification-modal for bulk actions) -->
    <x-ui.modal id="bulk-verification-modal" maxWidth="lg">
        <div class="p-6 lg:p-8 max-h-[90vh] overflow-y-auto custom-scrollbar">
            <div class="flex items-center justify-between mb-6 sticky top-0 bg-card/95 backdrop-blur-md z-10 pb-4 border-b border-border/40">
                <div class="flex items-center gap-3">
                    <div class="size-10 rounded-2xl bg-primary/10 border border-primary/20 text-primary flex items-center justify-center shadow-inner">
                        <x-ui.icon name="phone" size="5" />
                    </div>
                    <div>
                        <h3 class="text-sm font-black text-foreground uppercase tracking-widest">Bulk Order Verification</h3>
                        <p class="text-[10px] text-muted-foreground font-bold tracking-tight">
                            Logging call for <span x-text="selectedItems.length"></span> selected order(s)
                        </p>
                    </div>
                </div>
                <button type="button" @click="$dispatch('close-modal', { name: 'bulk-verification-modal' })" class="size-8 rounded-lg hover:bg-muted flex items-center justify-center transition-colors">
                    <x-ui.icon name="x" size="4" />
                </button>
            </div>

            <form method="POST" action="{{ route('orders.bulk-verification') }}" class="space-y-4 p-5 rounded-2xl border border-primary/20 bg-primary/5">
                @csrf
                {{-- Pass the selected IDs and target status into the form --}}
                <input type="hidden" name="ids" :value="JSON.stringify(selectedItems)">
                <input type="hidden" name="status" :value="bulkVerifyStatus">

                <h4 class="text-[10px] font-black uppercase tracking-widest text-primary flex items-center gap-2">
                    <x-ui.icon name="phone" size="3.5" /> Log Verification Call
                </h4>

                <div class="grid grid-cols-1 gap-4">
                    <div class="space-y-2">
                        <label class="text-[10px] font-black uppercase tracking-widest text-muted-foreground/80 ml-1">Call Outcome</label>
                        <select name="outcome" x-model="bulkVerifyOutcome" required
                            class="w-full h-11 px-4 rounded-xl border border-border bg-background/80 text-xs font-bold focus:ring-2 focus:ring-primary/20 outline-none">
                            <option value="" disabled selected>Select outcome...</option>
                            @foreach($verificationOutcomes as $value => $label)
                                <option value="{{ $value }}">{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="space-y-2">
                        <label class="text-[10px] font-black uppercase tracking-widest text-muted-foreground/80 ml-1">Remarks</label>
                        <textarea name="remark" rows="3" placeholder="Add call notes, customer response..."
                            class="w-full px-4 py-3 rounded-xl border border-border bg-background/80 text-xs font-medium focus:ring-2 focus:ring-primary/20 outline-none resize-none"></textarea>
                    </div>

                    <div class="space-y-2">
                        <label class="text-[10px] font-black uppercase tracking-widest text-muted-foreground/80 ml-1">Next Follow-up (optional)</label>
                        <input type="datetime-local" name="follow_up_at"
                            class="w-full h-11 px-4 rounded-xl border border-border bg-background/80 text-xs font-medium focus:ring-2 focus:ring-primary/20 outline-none">
                    </div>
                </div>

                <div class="flex justify-end gap-3 pt-2">
                    <x-ui.button type="button" variant="outline" @click="$dispatch('close-modal', { name: 'bulk-verification-modal' })" class="rounded-xl font-black uppercase tracking-widest text-[10px]">Close</x-ui.button>
                    <x-ui.button type="submit" class="rounded-xl font-black uppercase tracking-widest text-[10px] shadow-lg shadow-primary/25" x-bind:disabled="!bulkVerifyOutcome">
                        <x-ui.icon name="check" size="3" class="mr-1.5" /> Save & Update Status
                    </x-ui.button>
                </div>
            </form>
        </div>
    </x-ui.modal>

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
                    s.party.toLowerCase().includes(this.search.toLowerCase())
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
                                <div x-show="filteredShipments.length === 0" class="text-center py-4 text-[10px] text-muted-foreground font-medium uppercase tracking-widest">
                                    No shipments found
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="space-y-2">
                        <label for="assign_driver_id" class="text-[10px] font-black uppercase tracking-widest text-muted-foreground/80 ml-1">Select Driver</label>
                        <select id="assign_driver_id" name="driver_id" x-model="selectedDriver" required class="w-full h-11 px-4 rounded-xl border border-border bg-background/50 text-[10px] font-black uppercase tracking-widest focus:bg-background focus:ring-2 focus:ring-primary/20 outline-none transition-all">
                            <option value="" disabled selected>Select a Driver</option>
                            @forelse($drivers as $drv)
                                <option value="{{ $drv->id }}">{{ $drv->name }}</option>
                            @empty
                                <option value="" disabled>No drivers available</option>
                            @endforelse
                        </select>
                    </div>

                    <div class="space-y-2">
                        <label for="assign_transport_id" class="text-[10px] font-black uppercase tracking-widest text-muted-foreground/80 ml-1">Select Vehicle</label>
                        <select id="assign_transport_id" name="transport_id" x-model="selectedTransport" required class="w-full h-11 px-4 rounded-xl border border-border bg-background/50 text-[10px] font-black uppercase tracking-widest focus:bg-background focus:ring-2 focus:ring-primary/20 outline-none transition-all">
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

    <!-- Import Preview Modal -->
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

    </div>

    <style>
        [x-cloak] { display: none !important; }
        .custom-scrollbar::-webkit-scrollbar { height: 6px; width: 6px; }
        .custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
        .custom-scrollbar::-webkit-scrollbar-thumb { background: rgba(var(--border), 0.1); border-radius: 10px; }
    </style>
</x-layouts.app>

