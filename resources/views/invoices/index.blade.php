<x-layouts.app pageTitle="Invoices Management">
    @php
        $qStatus = request('status') ? explode(',', request('status')) : [];
        $statusesList = $statusesList ?? [
            'unpaid' => 'Unpaid',
            'partially_paid' => 'Partially Paid',
            'paid' => 'Paid',
            'cancelled' => 'Cancelled'
        ];
        $stats = $stats ?? [
            'total' => 0,
            'paid' => 0,
            'partially_paid' => 0,
            'unpaid' => 0,
            'cancelled' => 0,
            'total_amount' => 0.0,
            'paid_amount' => 0.0,
            'unpaid_amount' => 0.0,
        ];
    @endphp

    <div class="p-6 lg:p-10" x-data="{ 
        selectedItems: [], 
        allSelected: false,
        search: @js(request('search', '')),
        perPage: @js(request('perPage', 15)),
        statusFilter: @js($qStatus),
        statusesList: @js(array_keys($statusesList)),
        stats: @js($stats),
        isLoading: false,

        toggleAll() {
            if (this.allSelected) {
                this.selectedItems = Array.from(
                    document.querySelectorAll('input[name=\'invoice_ids[]\']')
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
                status: this.statusFilter.join(',')
            });

            // Persist parameters to browser URL history
            window.history.replaceState({}, '', `${window.location.pathname}?${params.toString()}`);

            const res = await fetch(
                `{{ route('invoices.index') }}?${params.toString()}`,
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
            this.statusFilter = [];
            this.performSearch();
        }
    }">

        <div class="max-w-[100rem] mx-auto space-y-8">
            <!-- Stats Widgets -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
                <!-- Total Invoices Card -->
                <div class="group relative p-6 rounded-3xl bg-card/40 border border-border/60 backdrop-blur-xl hover:bg-primary/5 transition-all duration-500 overflow-hidden shadow-2xl">
                    <div class="absolute top-0 right-0 -mr-8 -mt-8 size-32 bg-primary/10 blur-[50px] rounded-full group-hover:bg-primary/20 transition-all duration-500"></div>
                    <div class="flex items-center gap-5 relative z-10">
                        <div class="size-14 rounded-2xl bg-gradient-to-tr from-primary/20 to-primary/5 border border-primary/10 text-primary flex items-center justify-center shadow-inner group-hover:scale-110 transition-transform duration-500">
                            <x-ui.icon name="file-text" size="7" />
                        </div>
                        <div>
                            <p class="text-[10px] font-black uppercase tracking-widest text-muted-foreground/60 mb-1">Total Invoices</p>
                            <div class="text-3xl font-black tracking-tighter text-foreground" x-text="stats.total"></div>
                        </div>
                    </div>
                </div>

                <!-- Paid Card -->
                <div class="group relative p-6 rounded-3xl bg-card/40 border border-border/60 backdrop-blur-xl hover:bg-emerald-500/5 transition-all duration-500 overflow-hidden shadow-2xl">
                    <div class="absolute top-0 right-0 -mr-8 -mt-8 size-32 bg-emerald-500/10 blur-[50px] rounded-full group-hover:bg-emerald-500/20 transition-all duration-500"></div>
                    <div class="flex items-center gap-5 relative z-10">
                        <div class="size-14 rounded-2xl bg-gradient-to-tr from-emerald-500/20 to-emerald-500/5 border border-emerald-500/10 text-emerald-500 flex items-center justify-center shadow-inner group-hover:scale-110 transition-transform duration-500">
                            <x-ui.icon name="check-circle" size="7" />
                        </div>
                        <div>
                            <p class="text-[10px] font-black uppercase tracking-widest text-muted-foreground/60 mb-1">Total Received Amount</p>
                            <div class="text-2xl font-black tracking-tighter text-emerald-500">₹<span x-text="Number(stats.paid_amount).toLocaleString('en-IN', {minimumFractionDigits: 2, maximumFractionDigits: 2})"></span></div>
                            <p class="text-[10px] font-bold text-muted-foreground mt-1"><span x-text="stats.paid"></span> fully paid</p>
                        </div>
                    </div>
                </div>

                <!-- Unpaid Card -->
                <div class="group relative p-6 rounded-3xl bg-card/40 border border-border/60 backdrop-blur-xl hover:bg-orange-500/5 transition-all duration-500 overflow-hidden shadow-2xl">
                    <div class="absolute top-0 right-0 -mr-8 -mt-8 size-32 bg-orange-500/10 blur-[50px] rounded-full group-hover:bg-orange-500/20 transition-all duration-500"></div>
                    <div class="flex items-center gap-5 relative z-10">
                        <div class="size-14 rounded-2xl bg-gradient-to-tr from-orange-500/20 to-orange-500/5 border border-orange-500/10 text-orange-500 flex items-center justify-center shadow-inner group-hover:scale-110 transition-transform duration-500">
                            <x-ui.icon name="alert-triangle" size="7" />
                        </div>
                        <div>
                            <p class="text-[10px] font-black uppercase tracking-widest text-muted-foreground/60 mb-1">Pending Due</p>
                            <div class="text-2xl font-black tracking-tighter text-orange-500">₹<span x-text="Number(stats.unpaid_amount).toLocaleString('en-IN', {minimumFractionDigits: 2, maximumFractionDigits: 2})"></span></div>
                            <p class="text-[10px] font-bold text-muted-foreground mt-1"><span x-text="stats.unpaid + stats.partially_paid"></span> invoices pending</p>
                        </div>
                    </div>
                </div>

                <!-- Total Amount Card -->
                <div class="group relative p-6 rounded-3xl bg-card/40 border border-border/60 backdrop-blur-xl hover:bg-blue-500/5 transition-all duration-500 overflow-hidden shadow-2xl">
                    <div class="absolute top-0 right-0 -mr-8 -mt-8 size-32 bg-blue-500/10 blur-[50px] rounded-full group-hover:bg-blue-500/20 transition-all duration-500"></div>
                    <div class="flex items-center gap-5 relative z-10">
                        <div class="size-14 rounded-2xl bg-gradient-to-tr from-blue-500/20 to-blue-500/5 border border-blue-500/10 text-blue-500 flex items-center justify-center shadow-inner group-hover:scale-110 transition-transform duration-500">
                            <x-ui.icon name="finance" size="7" />
                        </div>
                        <div>
                            <p class="text-[10px] font-black uppercase tracking-widest text-muted-foreground/60 mb-1">Total Invoiced Amount</p>
                            <div class="text-2xl font-black tracking-tighter text-foreground">₹<span x-text="Number(stats.total_amount).toLocaleString('en-IN', {minimumFractionDigits: 2, maximumFractionDigits: 2})"></span></div>
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
                                <div class="size-12 sm:size-14 shrink-0 rounded-2xl bg-gradient-to-br from-blue-500/25 via-blue-500/10 to-blue-500/5 border border-blue-500/15 text-blue-500 flex items-center justify-center shadow-inner ring-1 ring-blue-500/10">
                                    <x-ui.icon name="file-text" size="6" />
                                </div>
                                <div class="min-w-0">
                                    <h2 class="text-lg sm:text-xl font-black text-foreground tracking-tight">Invoices Management</h2>
                                    <p class="text-[10px] font-bold text-muted-foreground uppercase tracking-[0.2em] mt-1">Invoice Ledgers · Financial Audit · Billing</p>
                                </div>
                            </div>
                        </div>

                        <!-- Toolbar: scope + filters -->
                        <div class="flex flex-col sm:flex-row sm:flex-wrap sm:items-center gap-3 pt-2 border-t border-border/30">
                            <div class="flex bg-muted/50 px-4 py-1.5 rounded-xl border border-border/50 shadow-inner w-fit">
                                <span class="text-xs font-bold text-primary tracking-widest uppercase">Invoices Ledger</span>
                            </div>

                            <div class="flex items-center gap-2">
                                <span class="text-[10px] font-bold text-muted-foreground uppercase tracking-widest">Show</span>
                                <select x-model="perPage" @change="performSearch()" class="h-10 px-3 rounded-xl border border-border bg-background/50 text-xs font-medium focus:ring-1 focus:ring-primary outline-none shadow-sm">
                                    <option value="15">15</option>
                                    <option value="25">25</option>
                                    <option value="50">50</option>
                                    <option value="100">100</option>
                                </select>
                            </div>

                            <div class="flex flex-wrap items-center gap-2">
                                <!-- Status Filter -->
                                <div class="relative" x-data="{ open: false, filter: '' }">
                                    <button @click="open = !open" class="h-10 px-4 flex items-center rounded-xl border border-border bg-background/50 text-[11px] font-bold hover:bg-background transition-all group shadow-sm">
                                        <span class="text-muted-foreground/80 group-hover:text-primary transition-colors font-black uppercase tracking-wider">Status</span>
                                        <span class="ml-2 px-1.5 py-0.5 rounded-lg bg-primary/10 text-primary font-black text-[10px]">
                                            <span x-text="statusFilter.length"></span>/<span x-text="statusesList.length"></span>
                                        </span>
                                        <x-ui.icon name="chevron-down" size="3" class="ml-2 text-muted-foreground/40" />
                                    </button>
                                    <div x-show="open" @click.away="open = false" x-cloak class="absolute left-0 mt-2 w-64 bg-popover border border-border rounded-xl shadow-2xl z-[100] p-1">
                                        <div class="p-2 border-b border-border bg-muted/10 mb-1">
                                            <input type="text" x-model="filter" placeholder="Search..." class="w-full px-3 py-1 bg-background rounded-lg border border-border text-[11px] outline-none">
                                        </div>
                                        <div class="max-h-60 overflow-y-auto custom-scrollbar">
                                            <template x-for="item in statusesList.filter(i => i.toLowerCase().includes(filter.toLowerCase()))" :key="item">
                                                <label class="flex items-center gap-2 px-3 py-1.5 rounded-lg hover:bg-muted cursor-pointer transition-colors" x-bind:class="statusFilter.includes(item) ? 'bg-primary/5' : ''">
                                                    <input type="checkbox" :value="item" x-model="statusFilter" @change="performSearch()" class="rounded border-border text-primary">
                                                    <span class="text-[11px] uppercase tracking-widest font-black" x-text="item.replace('_', ' ')"></span>
                                                </label>
                                            </template>
                                        </div>
                                    </div>
                                </div>
                                
                                <x-ui.button variant="ghost" size="sm" @click="clearFilters()" class="rounded-xl h-10 px-4 text-[10px] font-black uppercase tracking-widest text-muted-foreground hover:text-primary transition-colors">
                                    Clear All
                                </x-ui.button>
                            </div>

                            <div class="lg:ml-auto relative group w-full lg:max-w-md shrink-0">
                                <x-ui.icon name="search" size="4" class="absolute left-3 top-1/2 -translate-y-1/2 text-muted-foreground group-focus-within:text-primary transition-colors" />
                                <input type="text" x-model="search" @input.debounce.500ms="performSearch()"
                                    placeholder="Search invoice number, order number or party..."
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
                        @include('invoices.partials.table')
                    </div>
                </x-ui.card-content>
            </x-ui.card>
        </div>
    </div>

    <!-- Record Payment Modal -->
    <x-ui.modal id="record-payment-modal" maxWidth="md">
        <div x-data="{
            orderId: '',
            orderNo: '',
            invoiceNo: '',
            totalAmount: 0,
            paidAmount: 0,
            dueAmount: 0
        }"
        x-on:open-modal.window="if ($event.detail.name == 'record-payment-modal' && $event.detail.data) {
            orderId = $event.detail.data.order_id;
            orderNo = $event.detail.data.order_no;
            invoiceNo = $event.detail.data.invoice_no;
            totalAmount = $event.detail.data.total_amount;
            paidAmount = $event.detail.data.paid_amount;
            dueAmount = $event.detail.data.due_amount;
            $nextTick(() => {
                document.getElementById('amount').value = dueAmount;
            });
        }">
            <form action="{{ route('payments.store') }}" method="POST" class="p-6 space-y-4">
                @csrf
                <input type="hidden" name="order_id" :value="orderId">
                <div>
                    <h3 class="text-lg font-black text-foreground mb-1">Record New Payment</h3>
                    <p class="text-xs text-muted-foreground font-semibold uppercase tracking-wider">Log a transaction against Invoice <span class="text-primary font-black" x-text="invoiceNo"></span></p>
                </div>
                
                <div class="h-px bg-border/60 w-full my-2"></div>
                
                <div class="space-y-4">
                    <!-- Info display for selected invoice -->
                    <div class="p-4 rounded-xl border border-border bg-muted/30">
                        <div class="flex justify-between items-center mb-3">
                            <span class="text-[10px] font-black uppercase tracking-widest text-muted-foreground">Invoice breakdown</span>
                        </div>
                        <div class="grid grid-cols-3 gap-2 text-center divide-x divide-border">
                            <div class="flex flex-col">
                                <span class="text-[10px] font-bold text-muted-foreground uppercase">Total Amount</span>
                                <span class="text-sm font-black text-foreground" x-text="'₹' + Number(totalAmount).toLocaleString('en-IN', {minimumFractionDigits: 2})"></span>
                            </div>
                            <div class="flex flex-col pl-2">
                                <span class="text-[10px] font-bold text-muted-foreground uppercase">Already Paid</span>
                                <span class="text-sm font-black text-emerald-500" x-text="'₹' + Number(paidAmount).toLocaleString('en-IN', {minimumFractionDigits: 2})"></span>
                            </div>
                            <div class="flex flex-col pl-2">
                                <span class="text-[10px] font-bold text-muted-foreground uppercase">Pending Due</span>
                                <span class="text-sm font-black text-orange-500" x-text="'₹' + Number(dueAmount).toLocaleString('en-IN', {minimumFractionDigits: 2})"></span>
                            </div>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div class="space-y-2">
                            <label for="amount" class="text-[10px] font-black uppercase tracking-widest text-muted-foreground/80">Payment Amount (₹)</label>
                            <input type="number" id="amount" name="amount" step="0.01" min="0.01" required placeholder="e.g. 500.00" class="h-11 w-full rounded-xl border border-input bg-background/50 px-3 py-2 text-sm text-foreground focus:outline-none focus-visible:ring-2 focus-visible:ring-primary/20">
                        </div>
                        
                        <div class="space-y-2">
                            <label for="payment_method" class="text-[10px] font-black uppercase tracking-widest text-muted-foreground/80">Payment Method</label>
                            <select id="payment_method" name="payment_method" required class="h-11 w-full rounded-xl border border-input bg-background/50 px-3 py-2 text-sm text-foreground focus:outline-none focus-visible:ring-2 focus-visible:ring-primary/20 appearance-none cursor-pointer">
                                <option value="UPI">UPI</option>
                                <option value="Cash">Cash</option>
                                <option value="Bank Transfer">Bank Transfer</option>
                                <option value="Card">Card / POS</option>
                                <option value="COD">Cash On Delivery (COD)</option>
                            </select>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div class="space-y-2">
                            <label for="transaction_id" class="text-[10px] font-black uppercase tracking-widest text-muted-foreground/80">Transaction ID / Ref (Optional)</label>
                            <input type="text" id="transaction_id" name="transaction_id" placeholder="e.g. TXN123456789" class="h-11 w-full rounded-xl border border-input bg-background/50 px-3 py-2 text-sm text-foreground focus:outline-none focus-visible:ring-2 focus-visible:ring-primary/20">
                        </div>

                        <div class="space-y-2">
                            <label for="payment_date" class="text-[10px] font-black uppercase tracking-widest text-muted-foreground/80">Payment Date</label>
                            <input type="datetime-local" id="payment_date" name="payment_date" required value="{{ now()->format('Y-m-d\TH:i') }}" class="h-11 w-full rounded-xl border border-input bg-background/50 px-3 py-2 text-xs font-bold text-foreground focus:outline-none focus-visible:ring-2 focus-visible:ring-primary/20">
                        </div>
                    </div>

                    <div class="space-y-2">
                        <label for="status" class="text-[10px] font-black uppercase tracking-widest text-muted-foreground/80">Settlement Status</label>
                        <select id="status" name="status" required class="h-11 w-full rounded-xl border border-input bg-background/50 px-3 py-2 text-sm text-foreground focus:outline-none focus-visible:ring-2 focus-visible:ring-primary/20 appearance-none cursor-pointer">
                            <option value="completed" selected>Completed</option>
                            <option value="pending">Pending</option>
                            <option value="failed">Failed</option>
                            <option value="refunded">Refunded</option>
                        </select>
                    </div>

                    <div class="space-y-2">
                        <label for="notes" class="text-[10px] font-black uppercase tracking-widest text-muted-foreground/80">Notes / Remarks</label>
                        <textarea id="notes" name="notes" placeholder="Add any payment reference remarks..." class="w-full rounded-xl border border-input bg-background/50 px-3 py-2 text-sm text-foreground focus:outline-none focus-visible:ring-2 focus-visible:ring-primary/20" rows="2"></textarea>
                    </div>
                </div>
                
                <div class="flex items-center justify-end gap-3 pt-4 border-t border-border/40">
                    <x-ui.button type="button" variant="outline" size="sm" @click="$dispatch('close-modal', { name: 'record-payment-modal' })" class="rounded-xl font-bold uppercase tracking-widest text-[10px] h-10">
                        Cancel
                    </x-ui.button>
                    <x-ui.button type="submit" size="sm" class="rounded-xl font-bold uppercase tracking-widest text-[10px] h-10 bg-primary hover:bg-primary/90 text-primary-foreground shadow-lg shadow-primary/20">
                        <x-ui.icon name="credit-card" size="3" class="mr-2" /> Record Transaction
                    </x-ui.button>
                </div>
            </form>
        </div>
    </x-ui.modal>
</x-layouts.app>
