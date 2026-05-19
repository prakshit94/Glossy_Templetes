@if($refunds->hasPages())
    <div class="p-4 border-b border-border/40 bg-muted/10 flex justify-end items-center">
        {{ $refunds->links() }}
    </div>
@endif

<div class="relative">
    <div class="pointer-events-none absolute inset-x-8 top-0 h-px bg-gradient-to-r from-transparent via-primary/15 to-transparent hidden sm:block"></div>

    <x-ui.table>
        <x-ui.table-header class="bg-muted/30">
            <x-ui.table-row class="border-b border-border/60">
                <x-ui.table-head class="text-[10px] font-black uppercase tracking-widest text-muted-foreground/70 whitespace-nowrap pl-5">Refund ID</x-ui.table-head>
                <x-ui.table-head class="text-[10px] font-black uppercase tracking-widest text-muted-foreground/70 whitespace-nowrap">Payment Info</x-ui.table-head>
                <x-ui.table-head class="text-[10px] font-black uppercase tracking-widest text-muted-foreground/70 whitespace-nowrap">Associated Order</x-ui.table-head>
                <x-ui.table-head class="text-[10px] font-black uppercase tracking-widest text-muted-foreground/70 whitespace-nowrap text-center">Lifecycle Status</x-ui.table-head>
                <x-ui.table-head class="text-[10px] font-black uppercase tracking-widest text-muted-foreground/70 text-right whitespace-nowrap">Refund Amount</x-ui.table-head>
                <x-ui.table-head class="text-right text-[10px] font-black uppercase tracking-widest text-muted-foreground/70 pr-5">Actions</x-ui.table-head>
            </x-ui.table-row>
        </x-ui.table-header>
        <x-ui.table-body>
            @forelse($refunds as $refund)
                <x-ui.table-row
                    class="border-b border-border/40 group/row transition-colors duration-200 hover:bg-primary/[0.03]">
                    
                    <x-ui.table-cell class="align-middle pl-5">
                        <div class="flex items-center gap-4 py-0.5">
                            <div class="shrink-0">
                                <div class="size-11 rounded-2xl bg-gradient-to-br from-primary/25 to-primary/5 border border-primary/15 flex items-center justify-center text-primary shadow-inner ring-1 ring-primary/10 group-hover/row:scale-[1.02] transition-transform duration-300">
                                    <x-ui.icon name="refresh-cw" size="4.5" />
                                </div>
                            </div>
                            <div class="flex flex-col min-w-0">
                                <div class="flex items-center gap-2">
                                    <span class="text-sm font-black tracking-tight text-foreground uppercase">REF-{{ $refund->id }}</span>
                                </div>
                                <span class="text-[10px] font-bold text-muted-foreground/65 tabular-nums">
                                    {{ optional($refund->created_at)->format('M d, Y') }}
                                </span>
                            </div>
                        </div>
                    </x-ui.table-cell>

                    <x-ui.table-cell class="align-middle">
                        <div class="flex flex-col">
                            <span class="text-[11px] font-bold text-foreground/80 truncate">{{ $refund->payment->payment_no }}</span>
                            <span class="text-[9px] font-bold text-muted-foreground/60 uppercase tracking-widest">
                                via {{ $refund->payment->payment_method }}
                            </span>
                        </div>
                    </x-ui.table-cell>

                    <x-ui.table-cell class="align-middle">
                        <div class="flex flex-col">
                            @if($refund->payment->order)
                            <a href="{{ route('orders.show', $refund->payment->order_id) }}" class="text-[11px] font-bold text-primary hover:underline transition-colors truncate">Order #{{ $refund->payment->order->order_no }}</a>
                            <span class="text-[10px] font-bold text-muted-foreground/80 truncate">{{ $refund->payment->order->party->company_name ?? ($refund->payment->order->party->firstname . ' ' . $refund->payment->order->party->lastname) }}</span>
                            @else
                                <span class="text-[11px] text-muted-foreground">Standalone Payment</span>
                            @endif
                        </div>
                    </x-ui.table-cell>

                    <x-ui.table-cell class="align-middle text-center">
                        @php
                            $statusVariant = match($refund->status) {
                                'processed' => 'success',
                                'failed' => 'destructive',
                                'pending' => 'warning',
                                default => 'outline'
                            };
                        @endphp
                        
                        <div class="relative inline-block text-left">
                            <div class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg shadow-sm ring-1 ring-black/5 dark:ring-white/10
                                {{ match($statusVariant) {
                                    'success' => 'bg-emerald-500/10 text-emerald-600 border border-emerald-500/20',
                                    'destructive' => 'bg-red-500/10 text-red-600 border border-red-500/20',
                                    'warning' => 'bg-amber-500/10 text-amber-600 border border-amber-500/20',
                                    default => 'bg-muted/40 text-muted-foreground border border-border/50'
                                } }}">
                                <span class="uppercase text-[9px] font-black tracking-[0.12em]">{{ str_replace('_', ' ', $refund->status) }}</span>
                            </div>
                        </div>
                    </x-ui.table-cell>

                    <x-ui.table-cell class="text-right align-middle">
                        <div class="flex flex-col items-end">
                            <span class="text-sm font-black text-foreground tracking-tight">₹{{ number_format((float) $refund->amount, 2) }}</span>
                        </div>
                    </x-ui.table-cell>

                    <x-ui.table-cell class="text-right align-middle pr-5">
                        <div class="flex justify-end gap-1">
                            <a href="{{ route('refunds.show', $refund) }}" title="Visual Dossier">
                                <x-ui.button variant="ghost" size="icon" className="size-9 text-muted-foreground hover:text-primary hover:bg-primary/10 rounded-xl border border-transparent hover:border-primary/20 transition-all">
                                    <x-ui.icon name="eye" size="4" />
                                </x-ui.button>
                            </a>
                        </div>
                    </x-ui.table-cell>
                </x-ui.table-row>
            @empty
                <x-ui.table-row>
                    <x-ui.table-cell colspan="6" class="h-72 text-center align-middle p-0">
                        <div class="flex flex-col items-center justify-center gap-5 py-12 px-6">
                            <div class="size-24 rounded-3xl bg-gradient-to-br from-primary/25 via-primary/8 to-transparent border border-primary/20 flex items-center justify-center text-primary shadow-inner ring-1 ring-primary/10">
                                <x-ui.icon name="refresh-cw" size="12" />
                            </div>
                            <div class="space-y-2 max-w-md text-center">
                                <p class="text-sm font-black uppercase tracking-[0.2em] text-foreground">No refunds in ledger</p>
                                <p class="text-[11px] text-muted-foreground font-medium leading-relaxed">Adjust your filters or search queries to locate refunds.</p>
                            </div>
                        </div>
                    </x-ui.table-cell>
                </x-ui.table-row>
            @endforelse
        </x-ui.table-body>
    </x-ui.table>
</div>

@if($refunds->hasPages())
    <div class="p-4 border-t border-border/40 bg-muted/10 flex justify-end items-center rounded-b-3xl">
        {{ $refunds->links() }}
    </div>
@endif
