<x-layouts.app pageTitle="Settlement Dossier">
    @php
        $statusColor = match($payment->status) {
            'completed' => 'emerald',
            'pending' => 'amber',
            'failed' => 'red',
            'refunded' => 'orange',
            default => 'primary'
        };
    @endphp

    <div class="p-6 lg:p-10 space-y-8" x-data="{ isPrintMode: false }">
        <!-- Header -->
        <x-ui.card class="overflow-hidden border-border/60 shadow-2xl bg-card/30 backdrop-blur-2xl rounded-3xl">
            <div class="p-8 flex flex-col md:flex-row md:items-center justify-between gap-6 relative">
                <!-- Status Background Glow -->
                <div class="absolute top-0 right-0 -mr-16 -mt-16 size-64 bg-{{ $statusColor }}-500/10 blur-[60px] rounded-full pointer-events-none"></div>
                
                <div class="flex items-center gap-5 relative z-10">
                    <div class="size-16 rounded-2xl bg-{{ $statusColor }}-500/10 border border-{{ $statusColor }}-500/20 text-{{ $statusColor }}-500 flex items-center justify-center shadow-inner shrink-0">
                        <x-ui.icon name="{{ match($payment->status) { 'completed' => 'check-circle', 'pending' => 'clock', 'failed' => 'x-circle', 'refunded' => 'refresh-cw', default => 'credit-card' } }}" size="8" />
                    </div>
                    <div>
                        <div class="flex items-center gap-3 mb-1">
                            <h3 class="text-2xl font-black text-foreground tracking-tight">{{ $payment->payment_no }}</h3>
                            <span class="px-2.5 py-1 rounded-xl bg-{{ $statusColor }}-500/10 border border-{{ $statusColor }}-500/20 text-{{ $statusColor }}-600 text-[10px] font-black uppercase tracking-widest">
                                {{ $payment->status }}
                            </span>
                        </div>
                        <p class="text-xs text-muted-foreground font-medium flex items-center gap-2">
                            <span class="font-bold uppercase tracking-wider text-foreground">{{ $payment->payment_method }} PAYMENT</span> 
                            <span class="size-1 rounded-full bg-border"></span>
                            {{ optional($payment->payment_date)->format('M d, Y • h:i A') }}
                        </p>
                    </div>
                </div>

                <div class="flex flex-wrap items-center gap-3 relative z-10">
                    <a href="{{ route('payments.index') }}">
                        <x-ui.button variant="outline" size="sm" class="rounded-xl font-bold uppercase tracking-widest text-[10px]">
                            <x-ui.icon name="arrow-left" size="3" class="mr-2" /> Back
                        </x-ui.button>
                    </a>
                    
                    <button onclick="window.print()" class="h-9 px-4 rounded-xl bg-primary text-primary-foreground text-xs font-black uppercase tracking-wider flex items-center gap-2 shadow-lg shadow-primary/20 hover:shadow-primary/40 hover:-translate-y-0.5 transition-all duration-300">
                        <x-ui.icon name="printer" size="3.5" />
                        <span>Print Receipt</span>
                    </button>
                </div>
            </div>
        </x-ui.card>

        <!-- Main Details Grid -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- Left & Center: Financial Breakdown & Entity Details -->
            <div class="lg:col-span-2 space-y-8">
                <!-- Transaction Summary Card -->
                <x-ui.card class="border-border/60 shadow-xl bg-card/40 backdrop-blur-xl rounded-3xl overflow-hidden">
                    <div class="p-6 border-b border-border/40 bg-muted/10 flex items-center gap-3">
                        <div class="size-8 rounded-xl bg-primary/10 text-primary flex items-center justify-center border border-primary/20">
                            <x-ui.icon name="finance" size="4" />
                        </div>
                        <h4 class="text-sm font-black uppercase tracking-widest text-foreground">Settlement Overview</h4>
                    </div>
                    <div class="p-6 md:p-8 space-y-6">
                        <div class="flex items-center justify-between border-b border-border/40 pb-6">
                            <div>
                                <p class="text-xs font-bold text-muted-foreground uppercase tracking-widest">Settled Amount</p>
                                <h2 class="text-4xl font-black text-foreground tracking-tight mt-1">₹{{ number_format((float) $payment->amount, 2) }}</h2>
                            </div>
                            <div class="text-right">
                                <p class="text-xs font-bold text-muted-foreground uppercase tracking-widest">Gateway Reference</p>
                                <span class="text-xs font-mono font-bold bg-muted/80 px-3 py-1 rounded-xl text-foreground mt-1 inline-block">
                                    {{ $payment->transaction_id ?: 'N/A' }}
                                </span>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-6 pt-2">
                            <div class="p-4 rounded-2xl bg-muted/30 border border-border/40 space-y-1">
                                <span class="text-[10px] font-black uppercase tracking-widest text-muted-foreground">Payment Gateway Method</span>
                                <p class="text-sm font-black text-foreground">{{ $payment->payment_method ?: 'Standard Settlement' }}</p>
                            </div>
                            <div class="p-4 rounded-2xl bg-muted/30 border border-border/40 space-y-1">
                                <span class="text-[10px] font-black uppercase tracking-widest text-muted-foreground">Timestamp</span>
                                <p class="text-sm font-bold text-foreground">{{ optional($payment->payment_date)->format('l, F j, Y • h:i A') }}</p>
                            </div>
                        </div>
                    </div>
                </x-ui.card>

                <!-- Associated Entity Information -->
                <x-ui.card class="border-border/60 shadow-xl bg-card/40 backdrop-blur-xl rounded-3xl overflow-hidden">
                    <div class="p-6 border-b border-border/40 bg-muted/10 flex items-center gap-3">
                        <div class="size-8 rounded-xl bg-blue-500/10 text-blue-500 flex items-center justify-center border border-blue-500/20">
                            <x-ui.icon name="link" size="4" />
                        </div>
                        <h4 class="text-sm font-black uppercase tracking-widest text-foreground">Associated Commercial Entities</h4>
                    </div>
                    <div class="p-6 md:p-8 space-y-6">
                        @if($payment->order)
                            <div class="flex items-center justify-between p-5 rounded-2xl bg-background border border-border/60 hover:border-primary/40 transition-all">
                                <div class="flex items-center gap-4">
                                    <div class="size-12 rounded-2xl bg-primary/10 text-primary flex items-center justify-center border border-primary/20 shrink-0">
                                        <x-ui.icon name="package" size="6" />
                                    </div>
                                    <div>
                                        <div class="flex items-center gap-2">
                                            <span class="text-[10px] font-black uppercase px-2 py-0.5 rounded bg-primary/10 text-primary">Order</span>
                                            <span class="text-sm font-black text-foreground">{{ $payment->order->order_no }}</span>
                                        </div>
                                        <p class="text-xs font-bold text-muted-foreground mt-0.5">Customer: {{ $payment->order->party?->name ?? 'Internal Node' }} ({{ $payment->order->party?->phone ?? 'N/A' }})</p>
                                    </div>
                                </div>
                                <a href="{{ route('orders.show', $payment->order->id) }}">
                                    <x-ui.button size="sm" variant="outline" class="rounded-xl text-xs font-black uppercase tracking-wider">
                                        View Order
                                    </x-ui.button>
                                </a>
                            </div>
                        @else
                            <p class="text-xs text-muted-foreground italic">No order linked to this payment record.</p>
                        @endif

                        @if($payment->invoice)
                            <div class="flex items-center justify-between p-5 rounded-2xl bg-background border border-border/60 hover:border-purple-500/40 transition-all">
                                <div class="flex items-center gap-4">
                                    <div class="size-12 rounded-2xl bg-purple-500/10 text-purple-600 flex items-center justify-center border border-purple-500/20 shrink-0">
                                        <x-ui.icon name="file-text" size="6" />
                                    </div>
                                    <div>
                                        <div class="flex items-center gap-2">
                                            <span class="text-[10px] font-black uppercase px-2 py-0.5 rounded bg-purple-500/10 text-purple-600">Invoice</span>
                                            <span class="text-sm font-black text-foreground">{{ $payment->invoice->invoice_no }}</span>
                                        </div>
                                        <p class="text-xs font-bold text-muted-foreground mt-0.5">Net Amount: ₹{{ number_format((float) $payment->invoice->net_amount, 2) }}</p>
                                    </div>
                                </div>
                            </div>
                        @endif
                    </div>
                </x-ui.card>
            </div>

            <!-- Right Column: Audit Timeline & Accounting -->
            <div class="space-y-8">
                <!-- Audit Timeline Card -->
                <x-ui.card class="border-border/60 shadow-xl bg-card/40 backdrop-blur-xl rounded-3xl overflow-hidden">
                    <div class="p-6 border-b border-border/40 bg-muted/10 flex items-center gap-3">
                        <div class="size-8 rounded-xl bg-amber-500/10 text-amber-500 flex items-center justify-center border border-amber-500/20">
                            <x-ui.icon name="clock" size="4" />
                        </div>
                        <h4 class="text-sm font-black uppercase tracking-widest text-foreground">Ledger Audit Timeline</h4>
                    </div>
                    <div class="p-6 space-y-6">
                        <div class="relative pl-6 space-y-6 before:absolute before:left-2.5 before:top-2 before:bottom-2 before:w-0.5 before:bg-border">
                            <!-- Created -->
                            <div class="relative flex items-start gap-4">
                                <div class="absolute -left-6 top-1.5 size-3 rounded-full bg-primary ring-4 ring-background"></div>
                                <div>
                                    <p class="text-xs font-black text-foreground uppercase tracking-widest">Payment Recorded</p>
                                    <span class="text-[11px] font-bold text-muted-foreground">{{ $payment->created_at->format('M d, Y • h:i A') }}</span>
                                </div>
                            </div>
                            <!-- Last Updated -->
                            <div class="relative flex items-start gap-4">
                                <div class="absolute -left-6 top-1.5 size-3 rounded-full bg-blue-500 ring-4 ring-background"></div>
                                <div>
                                    <p class="text-xs font-black text-foreground uppercase tracking-widest">Last Modified</p>
                                    <span class="text-[11px] font-bold text-muted-foreground">{{ $payment->updated_at->format('M d, Y • h:i A') }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </x-ui.card>

                <!-- Accounting Integration -->
                <x-ui.card class="border-border/60 shadow-xl bg-card/40 backdrop-blur-xl rounded-3xl overflow-hidden">
                    <div class="p-6 border-b border-border/40 bg-muted/10 flex items-center gap-3">
                        <div class="size-8 rounded-xl bg-emerald-500/10 text-emerald-500 flex items-center justify-center border border-emerald-500/20">
                            <x-ui.icon name="check-circle" size="4" />
                        </div>
                        <h4 class="text-sm font-black uppercase tracking-widest text-foreground">Accounting Integration</h4>
                    </div>
                    <div class="p-6 space-y-4">
                        @if($payment->status === 'completed')
                            <div class="flex items-center gap-3 p-4 rounded-2xl bg-emerald-500/10 border border-emerald-500/20 text-emerald-600">
                                <x-ui.icon name="check-circle" size="6" class="shrink-0" />
                                <div>
                                    <p class="text-xs font-black uppercase tracking-wider">Synced to Core Accounting</p>
                                    <p class="text-[11px] font-medium mt-0.5">Ledger entries posted to Cash in Hand and Sales Revenue.</p>
                                </div>
                            </div>
                        @else
                            <div class="flex items-center gap-3 p-4 rounded-2xl bg-amber-500/10 border border-amber-500/20 text-amber-600">
                                <x-ui.icon name="alert-triangle" size="6" class="shrink-0" />
                                <div>
                                    <p class="text-xs font-black uppercase tracking-wider">Pending Settlement Sync</p>
                                    <p class="text-[11px] font-medium mt-0.5">Accounting entries will post automatically upon successful completion.</p>
                                </div>
                            </div>
                        @endif
                    </div>
                </x-ui.card>
            </div>
        </div>
    </div>
</x-layouts.app>
