<x-layouts.app pageTitle="Refund Details">
    <div class="p-6 lg:p-10 space-y-6">
        <!-- Header -->
        <x-ui.card class="overflow-hidden border-border/60 shadow-2xl bg-card/30 backdrop-blur-2xl rounded-3xl">
            <div class="p-8 flex flex-col md:flex-row md:items-center justify-between gap-6 relative">
                <!-- Status Background Glow -->
                @php
                    $statusColor = match($refund->status) {
                        'processed' => 'emerald',
                        'failed' => 'red',
                        'pending' => 'amber',
                        default => 'primary'
                    };
                @endphp
                <div class="absolute top-0 right-0 -mr-16 -mt-16 size-64 bg-{{ $statusColor }}-500/10 blur-[60px] rounded-full pointer-events-none"></div>
                
                <div class="flex items-center gap-5 relative z-10">
                    <div class="size-16 rounded-2xl bg-{{ $statusColor }}-500/10 border border-{{ $statusColor }}-500/20 text-{{ $statusColor }}-500 flex items-center justify-center shadow-inner">
                        <x-ui.icon name="{{ match($refund->status) { 'processed' => 'check-circle', 'failed' => 'x-circle', 'pending' => 'clock', default => 'refresh-cw' } }}" size="8" />
                    </div>
                    <div>
                        <div class="flex items-center gap-3 mb-1">
                            <h3 class="text-2xl font-black text-foreground tracking-tight">REF-{{ $refund->id }}</h3>
                            <x-ui.badge variant="{{ match($refund->status) { 'processed' => 'success', 'failed' => 'destructive', 'pending' => 'warning', default => 'default' } }}" class="rounded-lg px-2 py-0.5 text-[10px] font-black uppercase tracking-widest">
                                {{ str_replace('_', ' ', $refund->status) }}
                            </x-ui.badge>
                        </div>
                        <p class="text-xs text-muted-foreground font-medium flex items-center gap-2">
                            <span class="font-bold uppercase tracking-wider text-foreground">REFUND TRANSACTION</span> 
                            <span class="size-1 rounded-full bg-border"></span>
                            {{ optional($refund->created_at)->format('M d, Y • h:i A') }}
                        </p>
                    </div>
                </div>

                <div class="flex flex-wrap items-center gap-3 relative z-10">
                    <a href="{{ route('refunds.index') }}">
                        <x-ui.button variant="outline" size="sm" class="rounded-xl font-bold uppercase tracking-widest text-[10px]">
                            <x-ui.icon name="arrow-left" size="3" class="mr-2" /> Back
                        </x-ui.button>
                    </a>
                </div>
            </div>
        </x-ui.card>

        @if(session('success'))
            <div class="rounded-3xl border border-emerald-500/20 bg-emerald-500/10 px-6 py-4 text-sm font-semibold text-emerald-600 flex items-center gap-3 shadow-sm">
                <x-ui.icon name="check-circle" size="5" />
                {{ session('success') }}
            </div>
        @endif
        @if(session('error'))
            <div class="rounded-3xl border border-destructive/20 bg-destructive/10 px-6 py-4 text-sm font-semibold text-destructive flex items-center gap-3 shadow-sm">
                <x-ui.icon name="alert-circle" size="5" />
                {{ session('error') }}
            </div>
        @endif

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <!-- Left Column: Details -->
            <div class="md:col-span-2 space-y-6">
                
                <!-- Payment & Order Info -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                    <x-ui.card class="overflow-hidden border-border/60 shadow-xl bg-card/30 backdrop-blur-xl rounded-3xl p-6">
                        <h4 class="text-[10px] font-black uppercase tracking-[0.2em] text-primary mb-4 flex items-center gap-2">
                            <x-ui.icon name="credit-card" size="3" /> Original Payment
                        </h4>
                        <div>
                            <p class="text-[9px] font-black uppercase tracking-widest text-muted-foreground mb-1">Payment Reference</p>
                            <p class="text-sm font-bold text-foreground mb-3 flex items-center gap-2">
                                {{ $refund->payment->payment_no }}
                            </p>
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <p class="text-[9px] font-black uppercase tracking-widest text-muted-foreground mb-1">Original Amount</p>
                                    <p class="text-xs font-bold text-foreground">₹{{ number_format($refund->payment->amount, 2) }}</p>
                                </div>
                                <div>
                                    <p class="text-[9px] font-black uppercase tracking-widest text-muted-foreground mb-1">Payment Method</p>
                                    <p class="text-xs font-bold text-foreground uppercase">{{ $refund->payment->payment_method }}</p>
                                </div>
                            </div>
                        </div>
                    </x-ui.card>

                    <x-ui.card class="overflow-hidden border-border/60 shadow-xl bg-card/30 backdrop-blur-xl rounded-3xl p-6">
                        <h4 class="text-[10px] font-black uppercase tracking-[0.2em] text-primary mb-4 flex items-center gap-2">
                            <x-ui.icon name="hash" size="3" /> Associated Order
                        </h4>
                        @if($refund->payment->order)
                            <div>
                                <p class="text-[9px] font-black uppercase tracking-widest text-muted-foreground mb-1">Order Number</p>
                                <p class="text-sm font-bold text-foreground mb-3 flex items-center gap-2">
                                    {{ $refund->payment->order->order_no }}
                                    <a href="{{ route('orders.show', $refund->payment->order_id) }}" class="text-primary hover:text-primary/80 transition-colors">
                                        <x-ui.icon name="external-link" size="3" />
                                    </a>
                                </p>
                                <p class="text-[9px] font-black uppercase tracking-widest text-muted-foreground mb-1">Customer / Party</p>
                                <p class="text-xs font-bold text-foreground">
                                    {{ $refund->payment->order->party->company_name ?? ($refund->payment->order->party->firstname . ' ' . $refund->payment->order->party->lastname) }}
                                </p>
                            </div>
                        @else
                            <p class="text-sm font-medium text-muted-foreground">Standalone payment (No order attached)</p>
                        @endif
                    </x-ui.card>
                </div>
                
                <!-- Refund Reason -->
                <x-ui.card class="overflow-hidden border-border/60 shadow-xl bg-card/30 backdrop-blur-3xl rounded-3xl">
                    <div class="p-6 border-b border-border/40 bg-muted/10">
                        <h4 class="text-[10px] font-black uppercase tracking-[0.2em] text-primary flex items-center gap-2">
                            <x-ui.icon name="file-text" size="3" /> Reason for Refund
                        </h4>
                    </div>
                    <div class="p-6">
                        <p class="text-sm text-foreground font-medium leading-relaxed whitespace-pre-line">{{ $refund->reason ?: 'No reason provided.' }}</p>
                    </div>
                </x-ui.card>
            </div>

            <!-- Right Column: Status & Financial Summary -->
            <div class="space-y-6">
                <!-- Status Update Card -->
                <x-ui.card class="overflow-hidden border-border/60 shadow-2xl bg-card/50 backdrop-blur-3xl rounded-3xl sticky top-6">
                    <div class="p-6 border-b border-border/40 bg-muted/10">
                        <h4 class="text-[10px] font-black uppercase tracking-[0.2em] text-primary flex items-center gap-2">
                            <x-ui.icon name="refresh-cw" size="3" /> Action Status
                        </h4>
                    </div>
                    <div class="p-6">
                        @if($refund->status === 'pending')
                            <form action="{{ route('refunds.status', $refund) }}" method="POST" class="space-y-4">
                                @csrf
                                <div class="space-y-2">
                                    <label class="text-[10px] font-black uppercase tracking-widest text-muted-foreground/80 ml-1">Update Status To</label>
                                    <select name="status" class="w-full h-12 px-4 rounded-2xl border border-border bg-background/50 focus:bg-background focus:ring-2 focus:ring-primary/20 transition-all text-sm font-medium">
                                        @foreach(['pending', 'processed', 'failed'] as $statusOption)
                                            <option value="{{ $statusOption }}" {{ $refund->status === $statusOption ? 'selected' : '' }}>
                                                {{ ucfirst($statusOption) }}
                                            </option>
                                        @endforeach
                                    </select>
                                    <p class="mt-2 text-[9px] font-bold text-muted-foreground text-center">Once marked <strong class="text-emerald-500">Processed</strong> or <strong class="text-red-500">Failed</strong>, this refund is finalized.</p>
                                </div>
                                <x-ui.button type="submit" class="w-full h-12 rounded-2xl font-black uppercase tracking-[0.2em] text-xs shadow-xl shadow-primary/20">
                                    Update Status
                                </x-ui.button>
                            </form>
                        @else
                            <div class="p-4 bg-muted/30 rounded-2xl border border-border text-center">
                                <x-ui.icon name="lock" size="6" class="text-muted-foreground/60 mx-auto mb-2" />
                                <p class="text-[10px] font-black uppercase tracking-widest text-muted-foreground">Refund is finalized</p>
                                <p class="text-sm font-bold text-foreground mt-1">{{ ucfirst($refund->status) }}</p>
                                @if($refund->processed_at)
                                    <p class="text-[9px] font-black uppercase tracking-widest text-muted-foreground mt-1">on {{ $refund->processed_at->format('M d, Y h:i A') }}</p>
                                @endif
                            </div>
                        @endif
                    </div>
                </x-ui.card>

                <!-- Financial Summary -->
                <x-ui.card class="overflow-hidden border-border/60 shadow-2xl bg-card/50 backdrop-blur-3xl rounded-3xl">
                    <div class="p-6 border-b border-border/40 bg-muted/10">
                        <h4 class="text-[10px] font-black uppercase tracking-[0.2em] text-primary flex items-center gap-2">
                            <x-ui.icon name="credit-card" size="3" /> Refund Summary
                        </h4>
                    </div>
                    <div class="p-6 space-y-4">
                        <div class="flex justify-between items-end">
                            <div>
                                <span class="text-[10px] font-black uppercase tracking-widest text-muted-foreground block mb-1">Refund Amount</span>
                            </div>
                            <span class="text-3xl font-black text-primary tracking-tighter">₹{{ number_format((float) $refund->amount, 2) }}</span>
                        </div>
                    </div>
                    <div class="p-6 bg-primary/5 border-t border-primary/10">
                        <p class="text-xs text-primary/80 font-semibold flex items-center gap-2 justify-center text-center">
                            <x-ui.icon name="shield-check" size="4" /> Validated refund ledger
                        </p>
                    </div>
                </x-ui.card>
            </div>
        </div>
    </div>
</x-layouts.app>
