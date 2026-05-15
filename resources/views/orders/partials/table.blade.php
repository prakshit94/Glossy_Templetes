@if($orders->hasPages())
    <div class="p-4 border-b border-border/40 bg-muted/10 flex justify-end items-center">
        {{ $orders->links() }}
    </div>
@endif

<div class="relative">
    <div class="pointer-events-none absolute inset-x-8 top-0 h-px bg-gradient-to-r from-transparent via-primary/15 to-transparent hidden sm:block"></div>

    <x-ui.table>
        <x-ui.table-header class="bg-muted/30">
            <x-ui.table-row class="border-b border-border/60">
                <x-ui.table-head class="w-12 pl-5">
                    <input type="checkbox" x-model="allSelected" @change="toggleAll"
                        class="rounded-md border-border bg-background text-primary focus:ring-primary/25 shadow-sm">
                </x-ui.table-head>
                <x-ui.table-head class="text-[10px] font-black uppercase tracking-widest text-muted-foreground/70 whitespace-nowrap">Order Identity</x-ui.table-head>
                <x-ui.table-head class="text-[10px] font-black uppercase tracking-widest text-muted-foreground/70 whitespace-nowrap">Transaction Type</x-ui.table-head>
                <x-ui.table-head class="text-[10px] font-black uppercase tracking-widest text-muted-foreground/70 whitespace-nowrap">Associated Party</x-ui.table-head>
                <x-ui.table-head class="text-[10px] font-black uppercase tracking-widest text-muted-foreground/70 whitespace-nowrap">Fulfillment Node</x-ui.table-head>
                <x-ui.table-head class="text-[10px] font-black uppercase tracking-widest text-muted-foreground/70 whitespace-nowrap text-center">Lifecycle Status</x-ui.table-head>
                <x-ui.table-head class="text-[10px] font-black uppercase tracking-widest text-muted-foreground/70 text-right whitespace-nowrap">Financial Total</x-ui.table-head>
                <x-ui.table-head class="text-right text-[10px] font-black uppercase tracking-widest text-muted-foreground/70 pr-5">Actions</x-ui.table-head>
            </x-ui.table-row>
        </x-ui.table-header>
        <x-ui.table-body>
            @forelse($orders as $order)
                <x-ui.table-row
                    x-bind:class="selectedItems.includes({{ $order->id }}) ? 'bg-primary/[0.06] ring-1 ring-inset ring-primary/15' : 'hover:bg-primary/[0.03]'"
                    class="border-b border-border/40 group/row transition-colors duration-200">
                    
                    <x-ui.table-cell class="pl-5 align-middle">
                        <input type="checkbox" name="order_ids[]" value="{{ $order->id }}" 
                            :checked="selectedItems.includes({{ $order->id }})" 
                            @change="toggleItem({{ $order->id }})"
                            class="rounded-md border-border bg-background text-primary focus:ring-primary/25 shadow-sm">
                    </x-ui.table-cell>

                    <x-ui.table-cell class="align-middle">
                        <div class="flex items-center gap-4 py-0.5">
                            <div class="shrink-0">
                                <div class="size-11 rounded-2xl bg-gradient-to-br from-primary/25 to-primary/5 border border-primary/15 flex items-center justify-center text-primary shadow-inner ring-1 ring-primary/10 group-hover/row:scale-[1.02] transition-transform duration-300">
                                    <x-ui.icon name="package" size="4.5" />
                                </div>
                            </div>
                            <div class="flex flex-col min-w-0">
                                <div class="flex items-center gap-2">
                                    <span class="text-sm font-black tracking-tight text-foreground uppercase truncate">{{ $order->order_no }}</span>
                                </div>
                                <span class="text-[10px] font-bold text-muted-foreground/65 tabular-nums">
                                    {{ optional($order->order_date)->format('M d, Y') }} at {{ optional($order->order_date)->format('h:i A') }}
                                </span>
                            </div>
                        </div>
                    </x-ui.table-cell>

                    <x-ui.table-cell class="align-middle">
                        @php
                            $typeVariant = $order->type === 'sale' ? 'default' : 'outline';
                            $typeColor = $order->type === 'sale' ? 'text-blue-500 bg-blue-500/5' : 'text-purple-500 bg-purple-500/5';
                        @endphp
                        <div class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-xl border border-border/50 {{ $typeColor }}">
                            <x-ui.icon :name="$order->type === 'sale' ? 'arrow-up-right' : 'arrow-down-left'" size="3" />
                            <span class="text-[9px] font-black uppercase tracking-widest">{{ $order->type }}</span>
                        </div>
                    </x-ui.table-cell>

                    <x-ui.table-cell class="align-middle">
                        <div class="flex items-center gap-2">
                            <div class="size-7 rounded-lg bg-muted/40 flex items-center justify-center text-muted-foreground">
                                <x-ui.icon name="user" size="3" />
                            </div>
                            <span class="text-[11px] font-bold text-foreground/80 truncate max-w-[140px]">{{ $order->party?->name ?? 'Internal Node' }}</span>
                        </div>
                    </x-ui.table-cell>

                    <x-ui.table-cell class="align-middle">
                        <div class="flex items-center gap-2">
                            <div class="size-7 rounded-lg bg-emerald-500/10 flex items-center justify-center text-emerald-600">
                                <x-ui.icon name="database" size="3" />
                            </div>
                            <span class="text-[11px] font-bold text-foreground/80 truncate max-w-[120px]">{{ $order->warehouse?->name ?? 'Main Hub' }}</span>
                        </div>
                    </x-ui.table-cell>

                    <x-ui.table-cell class="align-middle text-center">
                            @php
                                $statusVariant = match($order->status) {
                                    'shipped', 'delivered', 'completed' => 'success',
                                    'cancelled', 'returned' => 'destructive',
                                    'pending' => 'warning',
                                    'processing', 'in_transit' => 'default',
                                    default => 'outline'
                                };
                            @endphp
                            <x-ui.badge :variant="$statusVariant" className="uppercase text-[9px] font-black tracking-[0.12em] px-2.5 py-1 rounded-lg shadow-sm ring-1 ring-black/5 dark:ring-white/10">
                                {{ str_replace('_', ' ', $order->status) }}
                            </x-ui.badge>
                        </x-ui.table-cell>

                    <x-ui.table-cell class="text-right align-middle">
                        <div class="flex flex-col items-end">
                            <span class="text-sm font-black text-foreground tracking-tight">₹{{ number_format((float) $order->net_amount, 2) }}</span>
                            <span class="text-[9px] font-bold text-muted-foreground/60">{{ $order->items_count ?? 0 }} items itemized</span>
                        </div>
                    </x-ui.table-cell>

                    <x-ui.table-cell class="text-right align-middle pr-5">
                        <div class="flex justify-end gap-1 opacity-100 lg:opacity-0 lg:group-hover/row:opacity-100 transition-opacity duration-200">
                            <a href="{{ route('orders.show', $order) }}" title="Visual Dossier">
                                <x-ui.button variant="ghost" size="icon" className="size-9 text-muted-foreground hover:text-primary hover:bg-primary/10 rounded-xl border border-transparent hover:border-primary/20 transition-all">
                                    <x-ui.icon name="eye" size="4" />
                                </x-ui.button>
                            </a>
                            <a href="{{ route('orders.edit', $order) }}" title="Modify Structure">
                                <x-ui.button variant="ghost" size="icon" className="size-9 text-muted-foreground hover:text-amber-500 hover:bg-amber-500/10 rounded-xl border border-transparent hover:border-amber-500/20 transition-all">
                                    <x-ui.icon name="edit-3" size="4" />
                                </x-ui.button>
                            </a>
                        </div>
                    </x-ui.table-cell>
                </x-ui.table-row>
            @empty
                <x-ui.table-row>
                    <x-ui.table-cell colspan="8" class="h-72 text-center align-middle p-0">
                        <div class="flex flex-col items-center justify-center gap-5 py-12 px-6">
                            <div class="size-24 rounded-3xl bg-gradient-to-br from-primary/25 via-primary/8 to-transparent border border-primary/20 flex items-center justify-center text-primary shadow-inner ring-1 ring-primary/10">
                                <x-ui.icon name="package" size="12" />
                            </div>
                            <div class="space-y-2 max-w-md text-center">
                                <p class="text-sm font-black uppercase tracking-[0.2em] text-foreground">No orders in ledger</p>
                                <p class="text-[11px] text-muted-foreground font-medium leading-relaxed">Adjust your filters, search queries, or geographical parameters to locate orders.</p>
                            </div>
                            <x-ui.button variant="outline" size="sm" onclick="location.reload()" class="rounded-xl border-border/60 font-bold uppercase tracking-widest text-[10px] h-10 px-6">
                                Refresh Ledger
                            </x-ui.button>
                        </div>
                    </x-ui.table-cell>
                </x-ui.table-row>
            @endforelse
        </x-ui.table-body>
    </x-ui.table>
</div>

@if($orders->hasPages())
    <div class="p-4 border-t border-border/40 bg-muted/10 flex justify-end items-center rounded-b-3xl">
        {{ $orders->links() }}
    </div>
@endif
