<x-layouts.app pageTitle="Return Details">
    <div class="p-6 lg:p-10 space-y-6"
        x-data="{
            statusUpdateUrl: @js(route('returns.status', $return)),
            statusReturnNo: @js($return->return_no),
            statusCurrent: @js($return->status),
            statusPending: @js($return->status),
            statusLocked: @js(in_array($return->status, ['completed', 'rejected'], true)),
            openReturnDetailStatusModal() {
                if (this.statusLocked) return;
                this.statusPending = this.statusCurrent;
                this.$dispatch('open-modal', { name: 'return-status-modal' });
            }
        }">
        <!-- Header -->
        <x-ui.card class="overflow-hidden border-border/60 shadow-2xl bg-card/30 backdrop-blur-2xl rounded-3xl">
            <div class="p-8 flex flex-col md:flex-row md:items-center justify-between gap-6 relative">
                <!-- Status Background Glow -->
                @php
                    $statusColor = match($return->status) {
                        'completed' => 'emerald',
                        'rejected' => 'red',
                        'inspected', 'received' => 'blue',
                        'requested' => 'amber',
                        default => 'primary'
                    };
                @endphp
                <div class="absolute top-0 right-0 -mr-16 -mt-16 size-64 bg-{{ $statusColor }}-500/10 blur-[60px] rounded-full pointer-events-none"></div>
                
                <div class="flex items-center gap-5 relative z-10">
                    <div class="size-16 rounded-2xl bg-{{ $statusColor }}-500/10 border border-{{ $statusColor }}-500/20 text-{{ $statusColor }}-500 flex items-center justify-center shadow-inner">
                        <x-ui.icon name="{{ match($return->status) { 'completed' => 'check-circle', 'rejected' => 'x-circle', 'requested' => 'clock', default => 'corner-down-left' } }}" size="8" />
                    </div>
                    <div>
                        <div class="flex items-center gap-3 mb-1">
                            <h3 class="text-2xl font-black text-foreground tracking-tight">{{ $return->return_no }}</h3>
                            <x-ui.badge variant="{{ match($return->status) { 'completed' => 'success', 'rejected' => 'destructive', 'requested' => 'warning', default => 'default' } }}" class="rounded-lg px-2 py-0.5 text-[10px] font-black uppercase tracking-widest">
                                {{ str_replace('_', ' ', $return->status) }}
                            </x-ui.badge>
                        </div>
                        <p class="text-xs text-muted-foreground font-medium flex items-center gap-2">
                            <span class="font-bold uppercase tracking-wider text-foreground">RETURN REQUEST</span> 
                            <span class="size-1 rounded-full bg-border"></span>
                            {{ optional($return->created_at)->format('M d, Y • h:i A') }}
                        </p>
                    </div>
                </div>

                <div class="flex flex-wrap items-center gap-3 relative z-10">
                    <a href="{{ route('returns.index') }}">
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
            <!-- Left Column: Details & Items -->
            <div class="md:col-span-2 space-y-6">
                
                <!-- Party Info -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                    <x-ui.card class="overflow-hidden border-border/60 shadow-xl bg-card/30 backdrop-blur-xl rounded-3xl p-6">
                        <h4 class="text-[10px] font-black uppercase tracking-[0.2em] text-primary mb-4 flex items-center gap-2">
                            <x-ui.icon name="user" size="3" /> Customer Information
                        </h4>
                        @if($return->order->party)
                            <p class="text-lg font-black text-foreground mb-1">{{ $return->order->party->company_name ?? ($return->order->party->firstname . ' ' . $return->order->party->lastname) }}</p>
                            <div class="flex items-center gap-2 mb-3">
                                <span class="text-[9px] uppercase tracking-widest font-bold px-2 py-0.5 rounded-md bg-muted text-muted-foreground">{{ $return->order->party->type }}</span>
                                <span class="text-[10px] font-medium text-muted-foreground flex items-center gap-1"><x-ui.icon name="phone" size="3" /> {{ $return->order->party->phone ?? 'No Phone' }}</span>
                            </div>
                        @else
                            <p class="text-sm font-medium text-muted-foreground">No party assigned</p>
                        @endif
                    </x-ui.card>

                    <x-ui.card class="overflow-hidden border-border/60 shadow-xl bg-card/30 backdrop-blur-xl rounded-3xl p-6">
                        <h4 class="text-[10px] font-black uppercase tracking-[0.2em] text-primary mb-4 flex items-center gap-2">
                            <x-ui.icon name="hash" size="3" /> Associated Order
                        </h4>
                        <div>
                            <p class="text-[9px] font-black uppercase tracking-widest text-muted-foreground mb-1">Order Number</p>
                            <p class="text-sm font-bold text-foreground mb-3 flex items-center gap-2">
                                {{ $return->order->order_no }}
                                <a href="{{ route('orders.show', $return->order_id) }}" class="text-primary hover:text-primary/80 transition-colors">
                                    <x-ui.icon name="external-link" size="3" />
                                </a>
                            </p>
                            <p class="text-[9px] font-black uppercase tracking-widest text-muted-foreground mb-1">Order Date</p>
                            <p class="text-xs font-bold text-foreground">
                                {{ $return->order->order_date->format('M d, Y h:i A') }}
                            </p>
                        </div>
                    </x-ui.card>
                </div>
                
                <!-- Items Table -->
                <x-ui.card class="overflow-hidden border-border/60 shadow-xl bg-card/30 backdrop-blur-xl rounded-3xl">
                    <div class="p-6 border-b border-border/40 bg-muted/5 flex items-center gap-2">
                        <h4 class="text-[10px] font-black uppercase tracking-[0.2em] text-primary">Returned Items ({{ $return->items->count() }})</h4>
                    </div>
                    <div class="overflow-x-auto custom-scrollbar">
                        <table class="w-full text-left border-collapse">
                            <thead>
                                <tr class="border-b border-border/40 bg-muted/5">
                                    <th class="px-6 py-4 text-[10px] font-black uppercase tracking-widest text-muted-foreground">Product</th>
                                    <th class="px-6 py-4 text-[10px] font-black uppercase tracking-widest text-muted-foreground text-center">Qty</th>
                                    <th class="px-6 py-4 text-[10px] font-black uppercase tracking-widest text-muted-foreground text-right">Unit Price</th>
                                    <th class="px-6 py-4 text-[10px] font-black uppercase tracking-widest text-muted-foreground text-right">Total Refund</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-border/40">
                                @foreach($return->items as $item)
                                    <tr class="hover:bg-muted/10 transition-colors">
                                        <td class="px-6 py-4">
                                            <div class="flex items-center gap-3">
                                                <div class="size-10 rounded-xl bg-background border border-border flex items-center justify-center overflow-hidden shrink-0">
                                                    @if($item->orderItem->product?->image_url)
                                                        <img src="{{ $item->orderItem->product->image_url }}" class="size-full object-cover">
                                                    @else
                                                        <x-ui.icon name="package" size="4" class="text-muted-foreground/30" />
                                                    @endif
                                                </div>
                                                <div>
                                                    <p class="text-sm font-bold text-foreground">{{ $item->orderItem->product?->name ?? 'Unknown Product' }}</p>
                                                    <p class="text-[10px] text-muted-foreground font-mono">{{ $item->orderItem->product?->sku ?? 'N/A' }}</p>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 text-center text-sm font-black text-foreground">{{ $item->quantity }}</td>
                                        <td class="px-6 py-4 text-right text-xs font-semibold text-muted-foreground">₹{{ number_format((float) $item->orderItem->unit_price, 2) }}</td>
                                        <td class="px-6 py-4 text-right text-sm font-black text-primary">₹{{ number_format((float) ($item->quantity * $item->orderItem->unit_price), 2) }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </x-ui.card>
            </div>

            <!-- Right Column: Status & Financial Summary -->
            <div class="space-y-6">
                <!-- Status Update Card — opens same modal as returns ledger -->
                <x-ui.card class="overflow-hidden border-border/60 shadow-2xl bg-card/50 backdrop-blur-3xl rounded-3xl sticky top-6">
                    <div class="p-6 border-b border-border/40 bg-muted/10">
                        <h4 class="text-[10px] font-black uppercase tracking-[0.2em] text-primary flex items-center gap-2">
                            <x-ui.icon name="refresh-cw" size="3" /> Action Status
                        </h4>
                    </div>
                    <div class="p-6 space-y-4">
                        @if(!in_array($return->status, ['completed', 'rejected']))
                            <p class="text-xs text-muted-foreground leading-relaxed">
                                Move this return through <strong class="text-foreground">requested → received → inspected</strong>, then <strong class="text-emerald-600">complete</strong> to restock and trigger refunds, or <strong class="text-destructive">reject</strong> to close without inventory impact.
                            </p>
                            <x-ui.button type="button" class="w-full h-12 rounded-2xl font-black uppercase tracking-[0.15em] text-xs shadow-xl shadow-primary/20" @click="openReturnDetailStatusModal()">
                                <x-ui.icon name="sliders" size="4" class="mr-2" />
                                Update status
                            </x-ui.button>
                            <p class="text-[9px] font-bold text-muted-foreground text-center">Same workflow as the returns list — opens an improved confirmation panel.</p>
                        @else
                            <div class="p-4 bg-muted/30 rounded-2xl border border-border text-center">
                                <x-ui.icon name="lock" size="6" class="text-muted-foreground/60 mx-auto mb-2" />
                                <p class="text-[10px] font-black uppercase tracking-widest text-muted-foreground">Return is finalized</p>
                                <p class="text-sm font-bold text-foreground mt-1">{{ ucfirst($return->status) }}</p>
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
                                <span class="text-[10px] font-black uppercase tracking-widest text-muted-foreground block mb-1">Total Refund Amount</span>
                            </div>
                            <span class="text-3xl font-black text-primary tracking-tighter">₹{{ number_format((float) $return->refund_amount, 2) }}</span>
                        </div>
                    </div>
                    <div class="p-6 bg-primary/5 border-t border-primary/10">
                        <p class="text-xs text-primary/80 font-semibold flex items-center gap-2 justify-center text-center">
                            <x-ui.icon name="shield-check" size="4" /> Validated return ledger
                        </p>
                    </div>
                </x-ui.card>
                
                <!-- Return Reason -->
                <x-ui.card class="overflow-hidden border-border/60 shadow-xl bg-card/30 backdrop-blur-3xl rounded-3xl">
                    <div class="p-6 border-b border-border/40 bg-muted/10">
                        <h4 class="text-[10px] font-black uppercase tracking-[0.2em] text-primary flex items-center gap-2">
                            <x-ui.icon name="file-text" size="3" /> Return Reason
                        </h4>
                    </div>
                    <div class="p-6">
                        <p class="text-sm text-foreground font-medium leading-relaxed whitespace-pre-line">{{ $return->reason ?: 'No reason provided.' }}</p>
                    </div>
                </x-ui.card>
            </div>
        </div>

        @include('returns.partials.update-status-modal')
    </div>
</x-layouts.app>
