<x-layouts.app pageTitle="Payments Ledger">
    @php
        $qStatus = request('status') ? explode(',', request('status')) : [];
        $qMethod = request('payment_method') ? explode(',', request('payment_method')) : [];
        $statusesList = $statusesList ?? [
            'pending' => 'Pending',
            'completed' => 'Completed',
            'failed' => 'Failed',
            'refunded' => 'Refunded'
        ];
        $methodsList = $methodsList ?? [
            'Cash' => 'Cash',
            'Card' => 'Credit / Debit Card',
            'UPI' => 'UPI / QR Code',
            'Net Banking' => 'Net Banking',
            'Wallet' => 'Wallet / Others',
        ];
        $stats = $stats ?? [
            'total_count' => 0,
            'completed_count' => 0,
            'pending_count' => 0,
            'failed_count' => 0,
            'refunded_count' => 0,
            'total_amount' => 0.0,
            'pending_amount' => 0.0,
            'refunded_amount' => 0.0,
        ];
    @endphp

    <div class="p-6 lg:p-10" x-data="{ 
        selectedItems: [], 
        allSelected: false,
        search: @js(request('search', '')),
        perPage: @js(request('perPage', 15)),
        statusFilter: @js($qStatus),
        methodFilter: @js($qMethod),
        statusesList: @js(array_keys($statusesList)),
        methodsList: @js(array_keys($methodsList)),
        stats: @js($stats),
        isLoading: false,
        isCreateOpen: false,

        toggleAll() {
            if (this.allSelected) {
                this.selectedItems = Array.from(
                    document.querySelectorAll('input[name=\'payment_ids[]\']')
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
                payment_method: this.methodFilter.join(',')
            });

            window.history.replaceState({}, '', `${window.location.pathname}?${params.toString()}`);

            const res = await fetch(
                `{{ route('payments.index') }}?${params.toString()}`,
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
            this.methodFilter = [];
            this.performSearch();
        }
    }">

        <div class="max-w-[100rem] mx-auto space-y-8">
            <!-- Stats Widgets -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
                <!-- Total Received Card -->
                <div class="group relative p-6 rounded-3xl bg-card/40 border border-border/60 backdrop-blur-xl hover:bg-emerald-500/5 transition-all duration-500 overflow-hidden shadow-2xl">
                    <div class="absolute top-0 right-0 -mr-8 -mt-8 size-32 bg-emerald-500/10 blur-[50px] rounded-full group-hover:bg-emerald-500/20 transition-all duration-500"></div>
                    <div class="flex items-center gap-5 relative z-10">
                        <div class="size-14 rounded-2xl bg-gradient-to-tr from-emerald-500/20 to-emerald-500/5 border border-emerald-500/10 text-emerald-500 flex items-center justify-center shadow-inner group-hover:scale-110 transition-transform duration-500">
                            <x-ui.icon name="check-circle" size="7" />
                        </div>
                        <div>
                            <p class="text-[10px] font-black uppercase tracking-widest text-muted-foreground/60 mb-1">Total Received Amount</p>
                            <div class="text-2xl font-black tracking-tighter text-emerald-500">₹<span x-text="Number(stats.total_amount).toLocaleString('en-IN', {minimumFractionDigits: 2, maximumFractionDigits: 2})"></span></div>
                            <p class="text-[10px] font-bold text-muted-foreground mt-1"><span x-text="stats.completed_count"></span> completed transactions</p>
                        </div>
                    </div>
                </div>

                <!-- Pending Payments Card -->
                <div class="group relative p-6 rounded-3xl bg-card/40 border border-border/60 backdrop-blur-xl hover:bg-amber-500/5 transition-all duration-500 overflow-hidden shadow-2xl">
                    <div class="absolute top-0 right-0 -mr-8 -mt-8 size-32 bg-amber-500/10 blur-[50px] rounded-full group-hover:bg-amber-500/20 transition-all duration-500"></div>
                    <div class="flex items-center gap-5 relative z-10">
                        <div class="size-14 rounded-2xl bg-gradient-to-tr from-amber-500/20 to-amber-500/5 border border-amber-500/10 text-amber-500 flex items-center justify-center shadow-inner group-hover:scale-110 transition-transform duration-500">
                            <x-ui.icon name="clock" size="7" />
                        </div>
                        <div>
                            <p class="text-[10px] font-black uppercase tracking-widest text-muted-foreground/60 mb-1">Pending Collections</p>
                            <div class="text-2xl font-black tracking-tighter text-amber-500">₹<span x-text="Number(stats.pending_amount).toLocaleString('en-IN', {minimumFractionDigits: 2, maximumFractionDigits: 2})"></span></div>
                            <p class="text-[10px] font-bold text-muted-foreground mt-1"><span x-text="stats.pending_count"></span> pending transactions</p>
                        </div>
                    </div>
                </div>

                <!-- Refunded Card -->
                <div class="group relative p-6 rounded-3xl bg-card/40 border border-border/60 backdrop-blur-xl hover:bg-orange-500/5 transition-all duration-500 overflow-hidden shadow-2xl">
                    <div class="absolute top-0 right-0 -mr-8 -mt-8 size-32 bg-orange-500/10 blur-[50px] rounded-full group-hover:bg-orange-500/20 transition-all duration-500"></div>
                    <div class="flex items-center gap-5 relative z-10">
                        <div class="size-14 rounded-2xl bg-gradient-to-tr from-orange-500/20 to-orange-500/5 border border-orange-500/10 text-orange-500 flex items-center justify-center shadow-inner group-hover:scale-110 transition-transform duration-500">
                            <x-ui.icon name="refresh-cw" size="7" />
                        </div>
                        <div>
                            <p class="text-[10px] font-black uppercase tracking-widest text-muted-foreground/60 mb-1">Refunded Amount</p>
                            <div class="text-2xl font-black tracking-tighter text-orange-500">₹<span x-text="Number(stats.refunded_amount).toLocaleString('en-IN', {minimumFractionDigits: 2, maximumFractionDigits: 2})"></span></div>
                            <p class="text-[10px] font-bold text-muted-foreground mt-1"><span x-text="stats.refunded_count"></span> refunded transactions</p>
                        </div>
                    </div>
                </div>

                <!-- Total Count Card -->
                <div class="group relative p-6 rounded-3xl bg-card/40 border border-border/60 backdrop-blur-xl hover:bg-blue-500/5 transition-all duration-500 overflow-hidden shadow-2xl">
                    <div class="absolute top-0 right-0 -mr-8 -mt-8 size-32 bg-blue-500/10 blur-[50px] rounded-full group-hover:bg-blue-500/20 transition-all duration-500"></div>
                    <div class="flex items-center gap-5 relative z-10">
                        <div class="size-14 rounded-2xl bg-gradient-to-tr from-blue-500/20 to-blue-500/5 border border-blue-500/10 text-blue-500 flex items-center justify-center shadow-inner group-hover:scale-110 transition-transform duration-500">
                            <x-ui.icon name="credit-card" size="7" />
                        </div>
                        <div>
                            <p class="text-[10px] font-black uppercase tracking-widest text-muted-foreground/60 mb-1">Total Transactions</p>
                            <div class="text-3xl font-black tracking-tighter text-foreground" x-text="stats.total_count"></div>
                            <p class="text-[10px] font-bold text-muted-foreground mt-1"><span x-text="stats.failed_count"></span> failed transactions</p>
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
                                    <x-ui.icon name="credit-card" size="6" />
                                </div>
                                <div class="min-w-0">
                                    <h2 class="text-lg sm:text-xl font-black text-foreground tracking-tight">Payments Ledger & Integration</h2>
                                    <p class="text-[10px] font-bold text-muted-foreground uppercase tracking-[0.2em] mt-1">Payment Gateways · Accounting Sync · Settlement Ledger</p>
                                </div>
                            </div>

                            <div class="flex items-center gap-3">
                                <button type="button" @click="isCreateOpen = true"
                                    class="h-10 px-5 rounded-2xl bg-primary text-primary-foreground text-xs font-black uppercase tracking-wider flex items-center gap-2 shadow-lg shadow-primary/20 hover:shadow-primary/40 hover:-translate-y-0.5 transition-all duration-300">
                                    <x-ui.icon name="plus" size="4" />
                                    <span>Record New Payment</span>
                                </button>
                            </div>
                        </div>

                        <!-- Toolbar: scope + filters -->
                        <div class="flex flex-col sm:flex-row sm:flex-wrap sm:items-center gap-3 pt-2 border-t border-border/30">
                            <div class="flex bg-muted/50 px-4 py-1.5 rounded-xl border border-border/50 shadow-inner w-fit">
                                <span class="text-xs font-bold text-primary tracking-widest uppercase">Settlement Node</span>
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
                                                    <input type="checkbox" :value="item" x-model="statusFilter" @change="performSearch" class="rounded border-border text-primary">
                                                    <span class="text-[11px] uppercase tracking-widest font-black" x-text="item"></span>
                                                </label>
                                            </template>
                                        </div>
                                    </div>
                                </div>

                                <!-- Method Filter -->
                                <div class="relative" x-data="{ open: false, filter: '' }">
                                    <button @click="open = !open" class="h-10 px-4 flex items-center rounded-xl border border-border bg-background/50 text-[11px] font-bold hover:bg-background transition-all group shadow-sm">
                                        <span class="text-muted-foreground/80 group-hover:text-primary transition-colors font-black uppercase tracking-wider">Method</span>
                                        <span class="ml-2 px-1.5 py-0.5 rounded-lg bg-primary/10 text-primary font-black text-[10px]">
                                            <span x-text="methodFilter.length"></span>/<span x-text="methodsList.length"></span>
                                        </span>
                                        <x-ui.icon name="chevron-down" size="3" class="ml-2 text-muted-foreground/40" />
                                    </button>
                                    <div x-show="open" @click.away="open = false" x-cloak class="absolute left-0 mt-2 w-64 bg-popover border border-border rounded-xl shadow-2xl z-[100] p-1">
                                        <div class="p-2 border-b border-border bg-muted/10 mb-1">
                                            <input type="text" x-model="filter" placeholder="Search..." class="w-full px-3 py-1 bg-background rounded-lg border border-border text-[11px] outline-none">
                                        </div>
                                        <div class="max-h-60 overflow-y-auto custom-scrollbar">
                                            <template x-for="item in methodsList.filter(i => i.toLowerCase().includes(filter.toLowerCase()))" :key="item">
                                                <label class="flex items-center gap-2 px-3 py-1.5 rounded-lg hover:bg-muted cursor-pointer transition-colors" x-bind:class="methodFilter.includes(item) ? 'bg-primary/5' : ''">
                                                    <input type="checkbox" :value="item" x-model="methodFilter" @change="performSearch" class="rounded border-border text-primary">
                                                    <span class="text-[11px] uppercase tracking-widest font-black" x-text="item"></span>
                                                </label>
                                            </template>
                                        </div>
                                    </div>
                                </div>
                                
                                <x-ui.button variant="ghost" size="sm" @click="clearFilters" class="rounded-xl h-10 px-4 text-[10px] font-black uppercase tracking-widest text-muted-foreground hover:text-primary transition-colors">
                                    Clear All
                                </x-ui.button>
                            </div>

                            <div class="lg:ml-auto relative group w-full lg:max-w-md shrink-0">
                                <x-ui.icon name="search" size="4" class="absolute left-3 top-1/2 -translate-y-1/2 text-muted-foreground group-focus-within:text-primary transition-colors" />
                                <input type="text" x-model="search" @input.debounce.500ms="performSearch"
                                    placeholder="Search payment #, TXN ID, invoice # or party..."
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
                            <span class="text-[10px] font-black uppercase tracking-[0.25em] text-foreground/80">Syncing payment ledger</span>
                        </div>
                    </div>
                    <div class="pointer-events-none absolute inset-x-0 top-0 h-px bg-gradient-to-r from-transparent via-primary/20 to-transparent z-10"></div>
                    <div id="table-container">
                        @include('payments.partials.table')
                    </div>
                </x-ui.card-content>
            </x-ui.card>
        </div>

        <!-- Record Payment Modal -->
        <div x-show="isCreateOpen" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="transition ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" class="fixed inset-0 z-[200] flex items-center justify-center bg-black/60 backdrop-blur-sm p-4" x-cloak>
            <div @click.away="isCreateOpen = false" class="bg-card border border-border/60 rounded-3xl shadow-2xl max-w-xl w-full overflow-hidden flex flex-col">
                <div class="p-6 border-b border-border/40 bg-muted/10 flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <div class="size-10 rounded-2xl bg-primary/10 text-primary flex items-center justify-center border border-primary/20">
                            <x-ui.icon name="credit-card" size="5" />
                        </div>
                        <div>
                            <h3 class="text-base font-black tracking-tight text-foreground">Record New Payment</h3>
                            <p class="text-[10px] text-muted-foreground font-bold uppercase tracking-widest">Gateway Settlement · Accounting Entry</p>
                        </div>
                    </div>
                    <button type="button" @click="isCreateOpen = false" class="size-8 rounded-xl bg-background border border-border/60 hover:bg-muted text-muted-foreground hover:text-foreground flex items-center justify-center transition-all">
                        <x-ui.icon name="x" size="4" />
                    </button>
                </div>
                <form action="{{ route('payments.store') }}" method="POST" class="p-6 space-y-5">
                    @csrf
                    <div>
                        <label class="block text-xs font-black uppercase tracking-widest text-muted-foreground mb-2">Select Associated Order</label>
                        <select name="order_id" required class="w-full h-11 px-4 rounded-xl border border-border bg-background text-sm font-bold focus:ring-2 focus:ring-primary/20 outline-none">
                            <option value="">-- Choose Order --</option>
                            @foreach($ordersList as $ord)
                                <option value="{{ $ord->id }}" {{ old('order_id') == $ord->id ? 'selected' : '' }}>
                                    {{ $ord->order_no }} ({{ $ord->party?->name ?? 'Internal Node' }}) — ₹{{ number_format((float) $ord->net_amount, 2) }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-black uppercase tracking-widest text-muted-foreground mb-2">Payment Amount (₹)</label>
                            <input type="number" step="0.01" name="amount" required value="{{ old('amount') }}" placeholder="0.00" class="w-full h-11 px-4 rounded-xl border border-border bg-background text-sm font-black focus:ring-2 focus:ring-primary/20 outline-none">
                        </div>
                        <div>
                            <label class="block text-xs font-black uppercase tracking-widest text-muted-foreground mb-2">Payment Method</label>
                            <select name="payment_method" required class="w-full h-11 px-4 rounded-xl border border-border bg-background text-sm font-bold focus:ring-2 focus:ring-primary/20 outline-none">
                                <option value="UPI" {{ old('payment_method') == 'UPI' ? 'selected' : '' }}>UPI / QR Code</option>
                                <option value="Cash" {{ old('payment_method') == 'Cash' ? 'selected' : '' }}>Cash</option>
                                <option value="Card" {{ old('payment_method') == 'Card' ? 'selected' : '' }}>Credit / Debit Card</option>
                                <option value="Net Banking" {{ old('payment_method') == 'Net Banking' ? 'selected' : '' }}>Net Banking</option>
                                <option value="Wallet" {{ old('payment_method') == 'Wallet' ? 'selected' : '' }}>Wallet / Others</option>
                            </select>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-black uppercase tracking-widest text-muted-foreground mb-2">Transaction ID / Reference</label>
                            <input type="text" name="transaction_id" value="{{ old('transaction_id') }}" placeholder="Auto-generated if blank" class="w-full h-11 px-4 rounded-xl border border-border bg-background text-xs font-bold focus:ring-2 focus:ring-primary/20 outline-none">
                        </div>
                        <div>
                            <label class="block text-xs font-black uppercase tracking-widest text-muted-foreground mb-2">Payment Date</label>
                            <input type="datetime-local" name="payment_date" required value="{{ old('payment_date', now()->format('Y-m-d\TH:i')) }}" class="w-full h-11 px-4 rounded-xl border border-border bg-background text-xs font-bold focus:ring-2 focus:ring-primary/20 outline-none">
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-black uppercase tracking-widest text-muted-foreground mb-2">Settlement Status</label>
                        <select name="status" required class="w-full h-11 px-4 rounded-xl border border-border bg-background text-sm font-bold focus:ring-2 focus:ring-primary/20 outline-none">
                            <option value="completed" {{ old('status') == 'completed' ? 'selected' : '' }}>Completed</option>
                            <option value="pending" {{ old('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                            <option value="failed" {{ old('status') == 'failed' ? 'selected' : '' }}>Failed</option>
                            <option value="refunded" {{ old('status') == 'refunded' ? 'selected' : '' }}>Refunded</option>
                        </select>
                    </div>

                    <div class="pt-4 border-t border-border/40 flex items-center justify-end gap-3">
                        <button type="button" @click="isCreateOpen = false" class="h-11 px-6 rounded-2xl bg-muted hover:bg-muted/80 text-xs font-black uppercase tracking-widest text-muted-foreground transition-all">
                            Cancel
                        </button>
                        <button type="submit" class="h-11 px-8 rounded-2xl bg-primary text-primary-foreground text-xs font-black uppercase tracking-widest shadow-lg shadow-primary/20 hover:shadow-primary/40 hover:-translate-y-0.5 transition-all duration-300">
                            Confirm & Record
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-layouts.app>
