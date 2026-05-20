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
                <x-ui.table-head class="text-[10px] font-black uppercase tracking-widest text-muted-foreground/70 whitespace-nowrap">Ordered Products</x-ui.table-head>
                <x-ui.table-head class="text-[10px] font-black uppercase tracking-widest text-muted-foreground/70 text-right whitespace-nowrap">Financial Total</x-ui.table-head>
                <x-ui.table-head class="text-right text-[10px] font-black uppercase tracking-widest text-muted-foreground/70 pr-5">Actions</x-ui.table-head>
            </x-ui.table-row>
        </x-ui.table-header>
        <x-ui.table-body>
            @forelse($orders as $order)
                @php
                    $hasOutOfStock = false;
                    if ($order->status === 'pending') {
                        foreach ($order->items as $item) {
                            $prod = $item->product;
                            if ($prod && $item->quantity > $prod->available_stock) {
                                $hasOutOfStock = true;
                                break;
                            }
                        }
                    }
                @endphp
                <x-ui.table-row
                    x-bind:class="selectedItems.includes({{ $order->id }}) ? 'bg-primary/[0.06] ring-1 ring-inset ring-primary/15 relative z-40' : 'hover:bg-primary/[0.03] hover:z-50 relative'"
                    class="border-b border-border/40 group/row transition-colors duration-200 {{ $hasOutOfStock ? 'border-l-4 border-l-red-500 bg-red-500/[0.04] hover:bg-red-500/[0.06]' : '' }}">
                    
                    <x-ui.table-cell class="pl-5 align-middle">
                        <input type="checkbox" name="order_ids[]" value="{{ $order->id }}" 
                            :checked="selectedItems.includes({{ $order->id }})" 
                            @change="toggleItem({{ $order->id }}, '{{ $order->status }}')"
                            data-status="{{ $order->status }}"
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
                                    <span x-data="{ copied: false }" @click.prevent.stop="navigator.clipboard.writeText('{{ $order->order_no }}'); copied = true; setTimeout(() => copied = false, 2000)" class="cursor-pointer text-sm font-black tracking-tight text-foreground uppercase truncate hover:text-primary transition-colors flex items-center gap-1.5 relative group/copy w-max">
                                        {{ $order->order_no }}
                                        <x-ui.icon name="copy" size="3" class="opacity-0 group-hover/copy:opacity-100 transition-opacity text-primary" />
                                        <span x-show="copied" x-cloak class="absolute -top-6 left-0 bg-foreground text-background text-[9px] font-bold px-2 py-0.5 rounded shadow-lg pointer-events-none normal-case tracking-normal">Copied!</span>
                                    </span>
                                    <span class="text-[9px] font-black uppercase px-1.5 py-0.5 rounded bg-muted text-muted-foreground border border-border/40 whitespace-nowrap">ID: {{ $order->id }}</span>
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
                            $transitions = [];
                            if ($order->status === 'pending') {
                                $transitions[] = ['status' => 'confirmed', 'label' => 'Confirm Order', 'type' => 'upcoming', 'icon' => 'check-circle', 'color' => 'indigo'];
                                $transitions[] = ['status' => 'cancelled', 'label' => 'Cancel Order', 'type' => 'cancel', 'icon' => 'x-circle', 'color' => 'red'];
                            } elseif ($order->status === 'confirmed') {
                                $transitions[] = ['status' => 'processing', 'label' => 'Mark Processing', 'type' => 'upcoming', 'icon' => 'loader', 'color' => 'amber'];
                                $transitions[] = ['status' => 'ready_to_ship', 'label' => 'Mark Ready to Ship', 'type' => 'upcoming', 'icon' => 'package', 'color' => 'indigo'];
                                $transitions[] = ['status' => 'pending', 'label' => 'Revert to Pending', 'type' => 'revert', 'icon' => 'corner-up-left', 'color' => 'gray'];
                                $transitions[] = ['status' => 'cancelled', 'label' => 'Cancel Order', 'type' => 'cancel', 'icon' => 'x-circle', 'color' => 'red'];
                            } elseif ($order->status === 'processing') {
                                $transitions[] = ['status' => 'ready_to_ship', 'label' => 'Mark Ready to Ship', 'type' => 'upcoming', 'icon' => 'package', 'color' => 'indigo'];
                                $transitions[] = ['status' => 'confirmed', 'label' => 'Revert to Confirmed', 'type' => 'revert', 'icon' => 'corner-up-left', 'color' => 'gray'];
                                $transitions[] = ['status' => 'cancelled', 'label' => 'Cancel Order', 'type' => 'cancel', 'icon' => 'x-circle', 'color' => 'red'];
                            } elseif ($order->status === 'ready_to_ship') {
                                $transitions[] = ['status' => 'dispatched', 'label' => 'Dispatch Order', 'type' => 'upcoming', 'icon' => 'truck', 'color' => 'blue'];
                                $transitions[] = ['status' => 'processing', 'label' => 'Revert to Processing', 'type' => 'revert', 'icon' => 'corner-up-left', 'color' => 'gray'];
                            } elseif ($order->status === 'dispatched' || $order->status === 'shipped') {
                                $transitions[] = ['status' => 'delivered', 'label' => 'Deliver Order', 'type' => 'upcoming', 'icon' => 'check', 'color' => 'emerald'];
                                $transitions[] = ['status' => 'processing', 'label' => 'Revert to Processing', 'type' => 'revert', 'icon' => 'corner-up-left', 'color' => 'gray'];
                            } elseif ($order->status === 'delivered') {
                                $transitions[] = ['status' => $order->shipments->isNotEmpty() ? 'ready_to_ship' : 'processing', 'label' => 'Revert to Shipped', 'type' => 'revert', 'icon' => 'corner-up-left', 'color' => 'gray'];
                            } elseif ($order->status === 'cancelled') {
                                $transitions[] = ['status' => 'pending', 'label' => 'Revert to Pending', 'type' => 'revert', 'icon' => 'corner-up-left', 'color' => 'gray'];
                            }

                            $statusVariant = match($order->status) {
                                'shipped', 'dispatched', 'delivered', 'completed' => 'success',
                                'cancelled', 'returned' => 'destructive',
                                'pending' => 'warning',
                                'processing', 'in_transit' => 'default',
                                'ready_to_ship' => 'warning',
                                default => 'outline'
                            };
                        @endphp
                        
                        <div x-data="{ open: false }" @click.away="open = false" class="relative inline-block text-left">
                            <button type="button" @click="open = !open" 
                                class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg shadow-sm ring-1 ring-black/5 dark:ring-white/10 transition-all hover:scale-[1.03] active:scale-[0.97]
                                {{ match($statusVariant) {
                                    'success' => 'bg-emerald-500/10 text-emerald-600 border border-emerald-500/20',
                                    'destructive' => 'bg-red-500/10 text-red-600 border border-red-500/20',
                                    'warning' => 'bg-amber-500/10 text-amber-600 border border-amber-500/20',
                                    'default' => 'bg-blue-500/10 text-blue-600 border border-blue-500/20',
                                    default => 'bg-muted/40 text-muted-foreground border border-border/50'
                                } }}">
                                <span class="uppercase text-[9px] font-black tracking-[0.12em]">{{ str_replace('_', ' ', $order->status) }}</span>
                                <x-ui.icon name="chevron-down" size="2.5" class="opacity-60" />
                            </button>

                            <div x-show="open" x-cloak
                                x-transition:enter="transition ease-out duration-100"
                                x-transition:enter-start="transform opacity-0 scale-95"
                                x-transition:enter-end="transform opacity-100 scale-100"
                                x-transition:leave="transition ease-in duration-75"
                                x-transition:leave-start="transform opacity-100 scale-100"
                                x-transition:leave-end="transform opacity-0 scale-95"
                                class="absolute right-0 mt-1.5 w-48 rounded-xl bg-card border border-border/60 shadow-xl z-50 p-1 divide-y divide-border/20">
                                
                                @if(count($transitions) > 0)
                                    @php
                                        $upcomming = array_filter($transitions, fn($t) => in_array($t['type'], ['upcoming', 'cancel']));
                                        $reverts = array_filter($transitions, fn($t) => $t['type'] === 'revert');
                                    @endphp

                                    @if(count($upcomming) > 0)
                                        <div class="py-1">
                                            <div class="px-2.5 py-1 text-[9px] font-black uppercase tracking-widest text-muted-foreground/60 text-left">Fulfillment</div>
                                            @foreach($upcomming as $t)
                                                @if($t['status'] === 'ready_to_ship')
                                                    <button type="button" @click.prevent="openShipModal({{ $order->id }}, '{{ $order->order_no }}')"
                                                        class="w-full flex items-center gap-2 px-2.5 py-1.5 text-left text-[10px] font-bold uppercase tracking-wider text-foreground hover:bg-primary/5 hover:text-primary rounded-lg transition-colors">
                                                        <span class="size-2 rounded-full bg-{{ $t['color'] }}-500"></span>
                                                        {{ $t['label'] }}
                                                    </button>
                                                @else
                                                    <form action="{{ route('orders.bulk-status') }}" method="POST" class="m-0">
                                                        @csrf
                                                        <input type="hidden" name="ids" value="[{{ $order->id }}]">
                                                        <input type="hidden" name="status" value="{{ $t['status'] }}">
                                                        <button type="submit" 
                                                            class="w-full flex items-center gap-2 px-2.5 py-1.5 text-left text-[10px] font-bold uppercase tracking-wider text-foreground hover:bg-primary/5 hover:text-primary rounded-lg transition-colors">
                                                            <span class="size-2 rounded-full bg-{{ $t['color'] }}-500"></span>
                                                            {{ $t['label'] }}
                                                        </button>
                                                    </form>
                                                @endif
                                            @endforeach
                                        </div>
                                    @endif

                                    @if(count($reverts) > 0)
                                        <div class="py-1">
                                            <div class="px-2.5 py-1 text-[9px] font-black uppercase tracking-widest text-amber-600/70 text-left">Revert Change</div>
                                            @foreach($reverts as $t)
                                                <form action="{{ route('orders.bulk-status') }}" method="POST" class="m-0">
                                                    @csrf
                                                    <input type="hidden" name="ids" value="[{{ $order->id }}]">
                                                    <input type="hidden" name="status" value="{{ $t['status'] }}">
                                                    <button type="submit" 
                                                        class="w-full flex items-center gap-2 px-2.5 py-1.5 text-left text-[10px] font-bold uppercase tracking-wider text-amber-600 hover:bg-amber-500/5 rounded-lg transition-colors">
                                                        <x-ui.icon name="corner-up-left" size="3" class="text-amber-500" />
                                                        {{ $t['label'] }}
                                                    </button>
                                                </form>
                                            @endforeach
                                        </div>
                                    @endif
                                @else
                                    <div class="px-2.5 py-2 text-[10px] font-bold text-muted-foreground text-center">No actions available</div>
                                @endif
                            </div>
                        </div>
                    </x-ui.table-cell>

                    <x-ui.table-cell class="align-middle py-3">
                        <div x-data="{ show: false, mouseX: 0, mouseY: 0 }"
                             @mouseenter="show = true; mouseX = $event.clientX; mouseY = $event.clientY;"
                             @mousemove="mouseX = $event.clientX; mouseY = $event.clientY;"
                             @mouseleave="show = false"
                             class="relative inline-flex items-center justify-center size-10 rounded-xl bg-primary/10 text-primary border border-primary/20 hover:bg-primary/20 hover:scale-105 transition-all cursor-help shadow-sm ring-1 ring-black/5">
                            
                            <x-ui.icon name="shopping-bag" size="4.5" />
                            <span class="absolute -top-1.5 -right-1.5 flex h-4 min-w-4 items-center justify-center rounded-full bg-primary text-[9px] font-black text-primary-foreground px-1 ring-2 ring-background shadow-sm">
                                {{ $order->items->count() }}
                            </span>
                            
                            <!-- Teleported Tooltip for ALL items -->
                            <template x-teleport="body">
                                <div x-show="show" x-cloak
                                     x-transition:enter="transition ease-out duration-200"
                                     x-transition:enter-start="opacity-0 translate-y-2 scale-95"
                                     x-transition:enter-end="opacity-100 translate-y-0 scale-100"
                                     x-transition:leave="transition ease-in duration-100"
                                     x-transition:leave-start="opacity-100"
                                     x-transition:leave-end="opacity-0"
                                     :style="'left: ' + (mouseX - 160 > 10 ? mouseX - 160 : 10) + 'px; top: ' + (mouseY + 20) + 'px;'"
                                     class="fixed z-[99999] w-max max-w-xs sm:max-w-sm bg-popover/95 backdrop-blur-xl text-popover-foreground py-3 px-4 rounded-2xl border border-border/70 shadow-[0_20px_50px_-12px_rgba(0,0,0,0.5)] ring-1 ring-black/5 pointer-events-none">
                                    
                                    <div class="flex items-center justify-between gap-6 border-b border-border/50 pb-2 mb-2">
                                        <span class="text-primary tracking-widest font-black uppercase text-[10px]">Ordered Items</span>
                                        <span class="text-[9px] font-black tabular-nums px-2 py-0.5 rounded-md bg-muted text-muted-foreground">{{ $order->items->count() }} Types</span>
                                    </div>
                                    <div class="flex flex-col gap-2 max-h-[250px] overflow-y-auto pr-1 custom-scrollbar">
                                        @foreach($order->items as $item)
                                            @php
                                                $prod = $item->product;
                                                $isItemOOS = false;
                                                if ($order->status === 'pending' && $prod && $item->quantity > $prod->available_stock) {
                                                    $isItemOOS = true;
                                                }
                                                $fullName = $prod ? $prod->name : 'Item #'.$item->product_id;
                                                $qtyStr = (int) $item->quantity;
                                            @endphp
                                            <div class="flex items-start justify-between gap-4 p-2 rounded-lg {{ $isItemOOS ? 'bg-red-500/10 border border-red-500/20' : 'bg-muted/30' }}">
                                                <div class="flex flex-col min-w-0 text-left">
                                                    <span class="text-[11px] font-bold text-foreground leading-tight break-words">{{ $fullName }}</span>
                                                    @if($isItemOOS)
                                                        <span class="text-red-500 text-[9px] font-black uppercase tracking-wider flex items-center gap-1 mt-1">
                                                            <x-ui.icon name="alert-triangle" size="2.5" /> Out of Stock (Available: {{ $prod ? $prod->available_stock : 0 }})
                                                        </span>
                                                    @endif
                                                </div>
                                                <div class="shrink-0 flex items-center gap-1">
                                                    <span class="text-muted-foreground text-[9px] font-bold uppercase">Qty</span>
                                                    <span class="text-[11px] font-black tabular-nums text-foreground bg-background border border-border/50 px-1.5 py-0.5 rounded-md">{{ $qtyStr }}</span>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            </template>
                        </div>
                    </x-ui.table-cell>

                    <x-ui.table-cell class="text-right align-middle">
                        <div class="flex flex-col items-end">
                            <span class="text-sm font-black text-foreground tracking-tight">₹{{ number_format((float) $order->net_amount, 2) }}</span>
                            <span class="text-[9px] font-bold text-muted-foreground/60">{{ $order->items_count ?? 0 }} items itemized</span>
                        </div>
                    </x-ui.table-cell>

                    <x-ui.table-cell class="text-right align-middle pr-5">
                        <div class="flex justify-end gap-1">
                            <a href="{{ route('orders.show', $order) }}" title="Visual Dossier">
                                <x-ui.button variant="ghost" size="icon" className="size-9 text-muted-foreground hover:text-primary hover:bg-primary/10 rounded-xl border border-transparent hover:border-primary/20 transition-all">
                                    <x-ui.icon name="eye" size="4" />
                                </x-ui.button>
                            </a>
                            @if($order->invoice)
                                <a href="{{ route('orders.invoice-pdf', $order) }}" target="_blank" title="Download Invoice">
                                    <x-ui.button variant="ghost" size="icon" className="size-9 text-muted-foreground hover:text-blue-500 hover:bg-blue-500/10 rounded-xl border border-transparent hover:border-blue-500/20 transition-all">
                                        <x-ui.icon name="file-text" size="4" />
                                    </x-ui.button>
                                </a>
                            @else
                                <form action="{{ route('orders.generate-invoice', $order) }}" method="POST" class="inline">
                                    @csrf
                                    <x-ui.button type="submit" variant="ghost" size="icon" className="size-9 text-muted-foreground hover:text-indigo-500 hover:bg-indigo-500/10 rounded-xl border border-transparent hover:border-indigo-500/20 transition-all" title="Generate Invoice">
                                        <x-ui.icon name="file-plus" size="4" />
                                    </x-ui.button>
                                </form>
                            @endif
                            <a href="{{ route('orders.cod-pdf', $order) }}" target="_blank" title="Download COD PDF">
                                <x-ui.button variant="ghost" size="icon" className="size-9 text-muted-foreground hover:text-emerald-500 hover:bg-emerald-500/10 rounded-xl border border-transparent hover:border-emerald-500/20 transition-all">
                                    <x-ui.icon name="printer" size="4" />
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
                    <x-ui.table-cell colspan="9" class="h-72 text-center align-middle p-0">
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
