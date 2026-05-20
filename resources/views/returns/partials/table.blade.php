@if($returns->hasPages())
    <div class="p-4 border-b border-border/40 bg-muted/10 flex justify-end items-center">
        {{ $returns->links() }}
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
                <x-ui.table-head class="text-[10px] font-black uppercase tracking-widest text-muted-foreground/70 whitespace-nowrap">Return Identity</x-ui.table-head>
                <x-ui.table-head class="text-[10px] font-black uppercase tracking-widest text-muted-foreground/70 whitespace-nowrap">Order Info</x-ui.table-head>
                <x-ui.table-head class="text-[10px] font-black uppercase tracking-widest text-muted-foreground/70 whitespace-nowrap">Associated Party</x-ui.table-head>
                <x-ui.table-head class="text-[10px] font-black uppercase tracking-widest text-muted-foreground/70 whitespace-nowrap text-center">Lifecycle Status</x-ui.table-head>
                <x-ui.table-head class="text-[10px] font-black uppercase tracking-widest text-muted-foreground/70 whitespace-nowrap">Returned Products</x-ui.table-head>
                <x-ui.table-head class="text-[10px] font-black uppercase tracking-widest text-muted-foreground/70 text-right whitespace-nowrap">Refund Amount</x-ui.table-head>
                <x-ui.table-head class="text-right text-[10px] font-black uppercase tracking-widest text-muted-foreground/70 pr-5">Actions</x-ui.table-head>
            </x-ui.table-row>
        </x-ui.table-header>
        <x-ui.table-body>
            @forelse($returns as $return)
                <x-ui.table-row
                    x-bind:class="selectedItems.includes({{ $return->id }}) ? 'bg-primary/[0.06] ring-1 ring-inset ring-primary/15' : 'hover:bg-primary/[0.03]'"
                    class="border-b border-border/40 group/row transition-colors duration-200">
                    
                    <x-ui.table-cell class="pl-5 align-middle">
                        <input type="checkbox" name="return_ids[]" value="{{ $return->id }}" 
                            :checked="selectedItems.includes({{ $return->id }})" 
                            @change="toggleItem({{ $return->id }}, '{{ $return->status }}')"
                            data-status="{{ $return->status }}"
                            class="rounded-md border-border bg-background text-primary focus:ring-primary/25 shadow-sm">
                    </x-ui.table-cell>

                    <x-ui.table-cell class="align-middle">
                        <div class="flex items-center gap-4 py-0.5">
                            <div class="shrink-0">
                                <div class="size-11 rounded-2xl bg-gradient-to-br from-primary/25 to-primary/5 border border-primary/15 flex items-center justify-center text-primary shadow-inner ring-1 ring-primary/10 group-hover/row:scale-[1.02] transition-transform duration-300">
                                    <x-ui.icon name="corner-down-left" size="4.5" />
                                </div>
                            </div>
                            <div class="flex flex-col min-w-0">
                                <div class="flex items-center gap-2">
                                    <span x-data="{ copied: false }" @click.prevent.stop="navigator.clipboard.writeText('{{ $return->return_no }}'); copied = true; setTimeout(() => copied = false, 2000)" class="cursor-pointer text-sm font-black tracking-tight text-foreground uppercase truncate hover:text-primary transition-colors flex items-center gap-1.5 relative group/copy w-max">
                                        {{ $return->return_no }}
                                        <x-ui.icon name="copy" size="3" class="opacity-0 group-hover/copy:opacity-100 transition-opacity text-primary" />
                                        <span x-show="copied" x-cloak class="absolute -top-6 left-0 bg-foreground text-background text-[9px] font-bold px-2 py-0.5 rounded shadow-lg pointer-events-none normal-case tracking-normal">Copied!</span>
                                    </span>
                                    <span class="text-[9px] font-black uppercase px-1.5 py-0.5 rounded bg-muted text-muted-foreground border border-border/40 whitespace-nowrap">ID: {{ $return->id }}</span>
                                </div>
                                <span class="text-[10px] font-bold text-muted-foreground/65 tabular-nums">
                                    {{ optional($return->created_at)->format('M d, Y') }} at {{ optional($return->created_at)->format('h:i A') }}
                                </span>
                            </div>
                        </div>
                    </x-ui.table-cell>

                    <x-ui.table-cell class="align-middle">
                        <div class="flex flex-col">
                            <a href="{{ route('orders.show', $return->order_id) }}" class="text-[11px] font-bold text-foreground/80 hover:text-primary transition-colors truncate max-w-[140px]">Order #{{ $return->order->order_no }}</a>
                        </div>
                    </x-ui.table-cell>

                    <x-ui.table-cell class="align-middle">
                        <div class="flex items-center gap-2">
                            <div class="size-7 rounded-lg bg-muted/40 flex items-center justify-center text-muted-foreground">
                                <x-ui.icon name="user" size="3" />
                            </div>
                            <span class="text-[11px] font-bold text-foreground/80 truncate max-w-[140px]">{{ $return->order->party->company_name ?? ($return->order->party->firstname . ' ' . $return->order->party->lastname) }}</span>
                        </div>
                    </x-ui.table-cell>

                    <x-ui.table-cell class="align-middle text-center">
                        @php
                            $statusVariant = match($return->status) {
                                'completed' => 'success',
                                'rejected' => 'destructive',
                                'requested' => 'warning',
                                'received', 'inspected' => 'default',
                                default => 'outline'
                            };
                            $returnFinalized = in_array($return->status, ['completed', 'rejected'], true);
                            $statusChip = match($statusVariant) {
                                'success' => 'bg-emerald-500/10 text-emerald-600 border border-emerald-500/20',
                                'destructive' => 'bg-red-500/10 text-red-600 border border-red-500/20',
                                'warning' => 'bg-amber-500/10 text-amber-600 border border-amber-500/20',
                                'default' => 'bg-blue-500/10 text-blue-600 border border-blue-500/20',
                                default => 'bg-muted/40 text-muted-foreground border border-border/50'
                            };
                        @endphp

                        @if($returnFinalized)
                        <div class="relative inline-block text-left">
                            <div class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg shadow-sm ring-1 ring-black/5 dark:ring-white/10 {{ $statusChip }}">
                                <span class="uppercase text-[9px] font-black tracking-[0.12em]">{{ str_replace('_', ' ', $return->status) }}</span>
                            </div>
                        </div>
                        @else
                        <button type="button"
                            title="Update status"
                            @click="openReturnStatusModal({{ $return->id }}, @js($return->return_no), @js($return->status), false)"
                            class="relative inline-block text-left group/status transition-transform hover:scale-[1.02] active:scale-[0.98]">
                            <div class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg shadow-sm ring-1 ring-black/5 dark:ring-white/10 {{ $statusChip }} group-hover/status:ring-primary/30">
                                <span class="uppercase text-[9px] font-black tracking-[0.12em]">{{ str_replace('_', ' ', $return->status) }}</span>
                                <x-ui.icon name="chevron-down" size="3" class="opacity-40 group-hover/status:opacity-90 transition-opacity" />
                            </div>
                        </button>
                        @endif
                    </x-ui.table-cell>

                    <x-ui.table-cell class="align-middle py-3">
                        <div class="flex flex-wrap gap-1.5 max-w-xs sm:max-w-md">
                            @foreach($return->items as $item)
                                @php
                                    $prod = $item->orderItem->product ?? null;
                                    $fullName = $prod ? $prod->name : 'Item #'.$item->order_item_id;
                                    $qtyStr = (int) $item->quantity;
                                    $tooltipText = $fullName . ' (Qty: ' . $qtyStr . ')';
                                @endphp
                                <div class="group/item relative inline-flex items-center gap-1.5 px-2.5 py-1 rounded-xl text-[11px] font-medium border transition-all cursor-default bg-background/80 text-foreground/80 border-border/60 shadow-2xs hover:border-primary/40"
                                     title="{{ $tooltipText }}">
                                    <span class="truncate max-w-[120px] font-bold">{{ $fullName }}</span>
                                    <span class="px-1.5 py-0.5 rounded-md text-[9px] font-black tabular-nums bg-muted text-muted-foreground">{{ $qtyStr }}</span>
                                    
                                    <!-- Gorgeous CSS hover popup -->
                                    <div class="pointer-events-none absolute bottom-full left-1/2 -translate-x-1/2 mb-2 opacity-0 group-hover/item:opacity-100 group-hover/item:translate-y-0 translate-y-1 transition-all duration-200 z-50 w-max max-w-xs bg-popover text-popover-foreground text-[11px] font-bold py-2 px-3 rounded-xl border border-border shadow-2xl flex flex-col gap-1 ring-1 ring-black/5">
                                        <div class="flex items-center justify-between gap-4 border-b border-border/40 pb-1">
                                            <span class="text-primary tracking-wide font-black uppercase text-[9px]">Return Item</span>
                                            <span class="text-[10px] font-black tabular-nums px-1.5 py-0.2 rounded bg-muted text-muted-foreground">Qty: {{ $qtyStr }}</span>
                                        </div>
                                        <span class="whitespace-normal leading-relaxed text-foreground font-semibold text-left">{{ $fullName }}</span>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </x-ui.table-cell>

                    <x-ui.table-cell class="text-right align-middle">
                        <div class="flex flex-col items-end">
                            <span class="text-sm font-black text-foreground tracking-tight">₹{{ number_format((float) $return->refund_amount, 2) }}</span>
                            <span class="text-[9px] font-bold text-muted-foreground/60">{{ $return->items_count ?? 0 }} items to return</span>
                        </div>
                    </x-ui.table-cell>

                    <x-ui.table-cell class="text-right align-middle pr-5">
                        @php
                            $__actionFinal = in_array($return->status, ['completed', 'rejected'], true);
                        @endphp
                        <div class="flex justify-end gap-1">
                            @if(!$__actionFinal)
                                <button type="button"
                                    title="Update status"
                                    class="inline-flex items-center justify-center size-9 rounded-xl border border-transparent text-muted-foreground hover:text-primary hover:bg-primary/10 transition-all"
                                    @click="openReturnStatusModal({{ $return->id }}, @js($return->return_no), @js($return->status), false)">
                                    <x-ui.icon name="refresh-cw" size="4" />
                                </button>
                            @endif
                            <a href="{{ route('returns.show', $return) }}" title="Visual Dossier">
                                <x-ui.button variant="ghost" size="icon" className="size-9 text-muted-foreground hover:text-primary hover:bg-primary/10 rounded-xl border border-transparent hover:border-primary/20 transition-all">
                                    <x-ui.icon name="eye" size="4" />
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
                                <x-ui.icon name="corner-down-left" size="12" />
                            </div>
                            <div class="space-y-2 max-w-md text-center">
                                <p class="text-sm font-black uppercase tracking-[0.2em] text-foreground">No returns in ledger</p>
                                <p class="text-[11px] text-muted-foreground font-medium leading-relaxed">Adjust your filters, search queries, or geographical parameters to locate returns.</p>
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

@if($returns->hasPages())
    <div class="p-4 border-t border-border/40 bg-muted/10 flex justify-end items-center rounded-b-3xl">
        {{ $returns->links() }}
    </div>
@endif
