{{-- ══ TAB: Order History ══ --}}
<div x-show="activeTab === 'history'" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-4" x-transition:enter-end="opacity-100 translate-y-0" x-cloak>
    @php $customer->orders?->loadMissing(['items.product', 'creator', 'updater', 'shipments', 'billingAddress', 'shippingAddress']); @endphp
    <script>
        window.customerOrders_{{ $customer->id }} = @json($customer->orders);
    </script>
    <div x-data="{ expandedOrder: null }" class="space-y-6">
        @if(isset($customer->orders) && $customer->orders->count())
            @foreach($customer->orders as $order)
                <div class="bg-card/40 backdrop-blur-3xl border border-border/50 rounded-3xl shadow-xl overflow-hidden transition-all duration-300">
                    
                    {{-- Order Summary Header (Click to expand) --}}
                    <div @click="expandedOrder = expandedOrder === {{ $order->id }} ? null : {{ $order->id }}" class="p-6 cursor-pointer hover:bg-muted/10 transition-colors flex flex-col md:flex-row md:items-center justify-between gap-4">
                        <div class="flex items-center gap-4">
                            <div class="size-12 rounded-2xl bg-primary/10 text-primary flex items-center justify-center shrink-0">
                                <x-ui.icon name="package" size="5" />
                            </div>
                            <div>
                                <div class="flex items-center gap-2 mb-1">
                                    <h3 class="text-lg font-black text-foreground">{{ $order->order_no }}</h3>
                                    @php
                                        $statusColor = match($order->status) {
                                            'delivered', 'completed' => 'emerald',
                                            'shipped' => 'blue',
                                            'processing' => 'amber',
                                            'confirmed' => 'indigo',
                                            'cancelled', 'returned' => 'red',
                                            'pending' => 'orange',
                                            default => 'primary'
                                        };
                                    @endphp
                                    <x-ui.badge variant="{{ match($order->status) { 'shipped', 'delivered', 'completed' => 'success', 'cancelled', 'returned' => 'destructive', 'pending' => 'warning', 'processing' => 'warning', default => 'default' } }}" class="rounded-lg px-2.5 py-1 text-[9px] font-black uppercase tracking-widest">
                                        {{ str_replace('_', ' ', $order->status) }}
                                    </x-ui.badge>
                                </div>
                                <div class="text-xs text-muted-foreground font-medium flex items-center gap-2 mt-1">
                                    <x-ui.icon name="calendar" size="3" /> Placed on {{ $order->created_at->format('M d, Y h:i A') }}
                                    @if($order->creator)
                                        <span class="size-1 rounded-full bg-border"></span>
                                        <x-ui.icon name="user" size="3" /> By {{ $order->creator->name }}
                                    @endif
                                    @if($order->updater && $order->updated_by !== $order->created_by)
                                        <span class="size-1 rounded-full bg-border"></span>
                                        <span class="text-amber-600 flex items-center gap-1">
                                            <x-ui.icon name="edit-3" size="3" /> Updated by {{ $order->updater->name }}
                                        </span>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <div class="flex items-center gap-6">
                            <div class="text-right">
                                <p class="text-[10px] font-black uppercase tracking-widest text-muted-foreground mb-0.5">Order Total</p>
                                <p class="text-xl font-black text-foreground">₹{{ number_format($order->net_amount, 2) }}</p>
                            </div>
                            <div class="size-8 rounded-full bg-muted/50 flex items-center justify-center text-muted-foreground group-hover:text-foreground group-hover:bg-muted transition-colors">
                                <x-ui.icon name="chevron-down" size="4" class="transform transition-transform duration-300" x-bind:class="expandedOrder === {{ $order->id }} ? 'rotate-180' : ''" />
                            </div>
                        </div>
                    </div>

                    {{-- Order Full Details (Expanded) --}}
                    <div x-show="expandedOrder === {{ $order->id }}" x-collapse x-cloak>
                        <div class="p-6 md:p-8 border-t border-border/40 bg-background/30 space-y-8">
                            
                            {{-- Actions Bar --}}
                            <div class="flex flex-wrap items-center gap-3">
                                <button type="button" @click="$dispatch('edit-order', {{ $order->id }})" class="inline-flex items-center justify-center h-9 px-4 rounded-xl bg-amber-500/10 border border-amber-500/20 text-amber-600 text-xs font-bold shadow-sm hover:bg-amber-500 hover:text-white hover:-translate-y-0.5 transition-all">
                                    <x-ui.icon name="edit-3" size="3.5" class="mr-2" /> Edit Order
                                </button>
                                <a href="{{ route('orders.receipt', $order->id) }}" class="inline-flex items-center justify-center h-9 px-4 rounded-xl bg-card border border-border text-xs font-bold text-foreground shadow-sm hover:bg-muted hover:-translate-y-0.5 transition-all">
                                    <x-ui.icon name="file-text" size="3.5" class="mr-2" /> View Receipt
                                </a>
                                <button type="button" @click="window.print()" class="inline-flex items-center justify-center h-9 px-4 rounded-xl bg-primary text-primary-foreground text-xs font-bold shadow-lg shadow-primary/25 hover:shadow-primary/40 hover:-translate-y-0.5 transition-all">
                                    <x-ui.icon name="printer" size="3.5" class="mr-2" /> Print Order
                                </button>

                                {{-- Unified Transition Dropdown --}}
                                @php
                                    $transitions = [];
                                    if ($order->status === 'pending') {
                                        $transitions[] = ['status' => 'confirmed', 'label' => 'Confirm Order', 'type' => 'upcoming', 'icon' => 'check-circle', 'color' => 'indigo'];
                                        $transitions[] = ['status' => 'cancelled', 'label' => 'Cancel Order', 'type' => 'cancel', 'icon' => 'x-circle', 'color' => 'red'];
                                    } elseif ($order->status === 'confirmed') {
                                        $transitions[] = ['status' => 'processing', 'label' => 'Mark Processing', 'type' => 'upcoming', 'icon' => 'loader', 'color' => 'amber'];
                                        $transitions[] = ['status' => 'shipped', 'label' => 'Ship Order', 'type' => 'upcoming', 'icon' => 'truck', 'color' => 'blue'];
                                        $transitions[] = ['status' => 'pending', 'label' => 'Revert to Pending', 'type' => 'revert', 'icon' => 'corner-up-left', 'color' => 'gray'];
                                        $transitions[] = ['status' => 'cancelled', 'label' => 'Cancel Order', 'type' => 'cancel', 'icon' => 'x-circle', 'color' => 'red'];
                                    } elseif ($order->status === 'processing') {
                                        $transitions[] = ['status' => 'shipped', 'label' => 'Ship Order', 'type' => 'upcoming', 'icon' => 'truck', 'color' => 'blue'];
                                        $transitions[] = ['status' => 'delivered', 'label' => 'Deliver Order', 'type' => 'upcoming', 'icon' => 'check', 'color' => 'emerald'];
                                        $transitions[] = ['status' => 'confirmed', 'label' => 'Revert to Confirmed', 'type' => 'revert', 'icon' => 'corner-up-left', 'color' => 'gray'];
                                        $transitions[] = ['status' => 'cancelled', 'label' => 'Cancel Order', 'type' => 'cancel', 'icon' => 'x-circle', 'color' => 'red'];
                                    } elseif ($order->status === 'shipped') {
                                        $transitions[] = ['status' => 'delivered', 'label' => 'Deliver Order', 'type' => 'upcoming', 'icon' => 'check', 'color' => 'emerald'];
                                        $transitions[] = ['status' => 'processing', 'label' => 'Revert to Processing', 'type' => 'revert', 'icon' => 'corner-up-left', 'color' => 'gray'];
                                    } elseif ($order->status === 'delivered') {
                                        $transitions[] = ['status' => 'shipped', 'label' => 'Revert to Shipped', 'type' => 'revert', 'icon' => 'corner-up-left', 'color' => 'gray'];
                                    } elseif ($order->status === 'cancelled') {
                                        $transitions[] = ['status' => 'pending', 'label' => 'Revert to Pending', 'type' => 'revert', 'icon' => 'corner-up-left', 'color' => 'gray'];
                                    }
                                @endphp

                                <div x-data="{ open: false }" @click.away="open = false" class="relative inline-block text-left">
                                    <button type="button" @click="open = !open" class="inline-flex items-center justify-center h-9 px-4 rounded-xl bg-primary/10 border border-primary/20 text-primary text-xs font-bold shadow-sm hover:bg-primary hover:text-white hover:-translate-y-0.5 transition-all">
                                        <x-ui.icon name="refresh-cw" size="3.5" class="mr-2 animate-spin-slow" /> Transition Status
                                    </button>

                                    <div x-show="open" x-cloak
                                        x-transition:enter="transition ease-out duration-100"
                                        x-transition:enter-start="transform opacity-0 scale-95"
                                        x-transition:enter-end="transform opacity-100 scale-100"
                                        x-transition:leave="transition ease-in duration-75"
                                        x-transition:leave-start="transform opacity-100 scale-100"
                                        x-transition:leave-end="transform opacity-0 scale-95"
                                        class="absolute left-0 mt-1.5 w-48 rounded-xl bg-card border border-border/60 shadow-xl z-50 p-1 divide-y divide-border/20">
                                        
                                        @if(count($transitions) > 0)
                                            @php
                                                $upcomming = array_filter($transitions, fn($t) => in_array($t['type'], ['upcoming', 'cancel']));
                                                $reverts = array_filter($transitions, fn($t) => $t['type'] === 'revert');
                                            @endphp

                                            @if(count($upcomming) > 0)
                                                <div class="py-1">
                                                    <div class="px-2.5 py-1 text-[9px] font-black uppercase tracking-widest text-muted-foreground/60 text-left">Fulfillment</div>
                                                    @foreach($upcomming as $t)
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
                            </div>

                            {{-- Progress Stepper --}}
                            @php
                                $steps = [
                                    ['key' => 'pending',    'label' => 'Pending',    'icon' => 'clock'],
                                    ['key' => 'confirmed',  'label' => 'Confirmed',  'icon' => 'check-circle'],
                                    ['key' => 'processing', 'label' => 'Processing', 'icon' => 'loader'],
                                    ['key' => 'shipped',    'label' => 'Shipped',    'icon' => 'truck'],
                                    ['key' => 'delivered',  'label' => 'Delivered',  'icon' => 'package-check'],
                                ];
                                $stepKeys = array_column($steps, 'key');
                                $currentIndex = array_search($order->status, $stepKeys);
                                $isCancelled = in_array($order->status, ['cancelled', 'returned']);
                            @endphp
                            <div class="bg-card/60 backdrop-blur-md rounded-2xl border border-border/50 p-6">
                                <h4 class="text-[11px] font-black uppercase tracking-[0.2em] text-muted-foreground mb-8 flex items-center gap-2">
                                    <span class="size-2 rounded-full bg-primary inline-block shadow-lg shadow-primary/50"></span> Fulfillment Tracking
                                </h4>
                                @if(!$isCancelled)
                                    <div class="relative flex items-start justify-between w-full max-w-4xl mx-auto px-2 sm:px-8 py-4">
                                        {{-- Progress connector line --}}
                                        <div class="absolute left-0 right-0 top-9 -translate-y-1/2 h-1 bg-muted/60 rounded-full overflow-hidden" style="margin: 0 2.5rem">
                                            @php
                                                $progress = $currentIndex !== false ? ($currentIndex / (count($steps) - 1)) * 100 : 0;
                                            @endphp
                                            <div class="h-full bg-{{ $statusColor }}-500 transition-all duration-1000 ease-out" style="width: {{ $progress }}%"></div>
                                        </div>
                                        
                                        @foreach($steps as $index => $step)
                                            @php
                                                $isCompleted = $currentIndex !== false && $index < $currentIndex;
                                                $isCurrent  = $currentIndex !== false && $index === $currentIndex;
                                                $isPending  = $currentIndex === false || $index > $currentIndex;
                                            @endphp
                                            <div class="relative flex flex-col items-center gap-2 z-10" style="flex: 1">
                                                {{-- Circle --}}
                                                <div class="
                                                    size-10 rounded-full flex items-center justify-center border-4 transition-all duration-500 shadow-md
                                                    {{ $isCurrent ? 'bg-'.$statusColor.'-500 border-'.$statusColor.'-200 text-white scale-110 shadow-'.$statusColor.'-500/40 ring-4 ring-'.$statusColor.'-500/20' : '' }}
                                                    {{ $isCompleted ? 'bg-'.$statusColor.'-500 border-'.$statusColor.'-200 text-white shadow-'.$statusColor.'-500/30' : '' }}
                                                    {{ $isPending ? 'bg-muted/30 border-background text-muted-foreground/40' : '' }}
                                                ">
                                                    @if($isCompleted)
                                                        <x-ui.icon name="check" size="4" />
                                                    @else
                                                        <x-ui.icon name="{{ $step['icon'] }}" size="4" />
                                                    @endif
                                                </div>

                                                {{-- Label --}}
                                                <div class="flex flex-col items-center gap-0.5">
                                                    <span class="text-[10px] font-black uppercase tracking-widest whitespace-nowrap
                                                        {{ $isCurrent ? 'text-'.$statusColor.'-600' : ($isCompleted ? 'text-foreground' : 'text-muted-foreground/40') }}
                                                    ">
                                                        {{ $step['label'] }}
                                                    </span>
                                                    @if($isCurrent)
                                                        <span class="size-1.5 rounded-full bg-{{ $statusColor }}-500 animate-pulse"></span>
                                                    @endif
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                @else
                                    <div class="flex items-center justify-center gap-3 text-destructive py-6">
                                        <x-ui.icon name="x-circle" size="6" />
                                        <div>
                                            <p class="text-sm font-black uppercase tracking-widest">Order {{ ucfirst($order->status) }}</p>
                                            <p class="text-[10px] text-muted-foreground font-bold uppercase tracking-widest">This order has been cancelled and cannot be progressed further.</p>
                                        </div>
                                    </div>
                                @endif
                                
                                {{-- Tracking Info --}}
                                @if(isset($order->shipments) && $order->shipments->isNotEmpty())
                                    @php $shipment = $order->shipments->first(); @endphp
                                    <div class="mt-8 bg-primary/5 rounded-xl border border-primary/10 p-5 flex flex-wrap gap-8 items-center justify-between">
                                        <div class="flex flex-wrap gap-8 items-center">
                                            <div class="flex items-center gap-3">
                                                <div class="p-2 bg-background rounded-lg text-primary shadow-sm">
                                                    <x-ui.icon name="truck" size="4" />
                                                </div>
                                                <div>
                                                    <p class="text-[10px] font-black text-muted-foreground uppercase tracking-widest">Carrier / Company</p>
                                                    <p class="text-sm font-bold text-foreground">{{ $shipment->carrier_name ?? 'N/A' }}</p>
                                                </div>
                                            </div>
                                            <div class="flex items-center gap-3">
                                                <div class="p-2 bg-background rounded-lg text-primary shadow-sm">
                                                    <x-ui.icon name="hash" size="4" />
                                                </div>
                                                <div>
                                                    <p class="text-[10px] font-black text-muted-foreground uppercase tracking-widest">Tracking ID</p>
                                                    <p class="text-sm font-mono font-bold text-primary">{{ $shipment->tracking_no ?? 'N/A' }}</p>
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <a href="{{ route('order.tracking.show', $shipment) }}">
                                            <x-ui.button variant="outline" size="sm" class="rounded-xl font-bold uppercase tracking-widest text-[9px] h-9 border-primary/20 hover:bg-primary/5 text-primary">
                                                <x-ui.icon name="target" size="3" class="mr-1.5" /> Track Shipment Details
                                            </x-ui.button>
                                        </a>
                                    </div>
                                @endif
                            </div>

                            {{-- Items and Summary Grid --}}
                            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                                
                                {{-- Items Table --}}
                                <div class="lg:col-span-2 bg-card/60 backdrop-blur-md rounded-2xl border border-border/50 overflow-hidden">
                                    <div class="p-5 border-b border-border/40 flex justify-between items-center bg-muted/10">
                                        <h4 class="text-sm font-black text-foreground">Order Items</h4>
                                        <span class="px-2 py-0.5 rounded-md bg-background text-[10px] font-bold text-muted-foreground shadow-sm">{{ isset($order->items) ? $order->items->count() : 0 }} items</span>
                                    </div>
                                    <div class="overflow-x-auto custom-scrollbar">
                                        <table class="w-full text-left">
                                            <thead class="bg-muted/30 text-[10px] uppercase font-black tracking-widest text-muted-foreground">
                                                <tr>
                                                    <th class="px-5 py-3">Product</th>
                                                    <th class="px-5 py-3 text-right">Price</th>
                                                    <th class="px-5 py-3 text-center">Qty</th>
                                                    <th class="px-5 py-3 text-right">Total</th>
                                                </tr>
                                            </thead>
                                            <tbody class="divide-y divide-border/30">
                                                @if(isset($order->items))
                                                    @foreach($order->items as $item)
                                                        <tr class="hover:bg-muted/10 transition-colors">
                                                            <td class="px-5 py-4">
                                                                <p class="text-sm font-bold text-foreground">{{ $item->product?->name ?? 'Unknown Product' }}</p>
                                                                <p class="text-[10px] text-muted-foreground font-mono mt-0.5">{{ $item->product?->sku ?? 'N/A' }}</p>
                                                            </td>
                                                            <td class="px-5 py-4 text-right text-muted-foreground font-medium text-xs">
                                                                ₹{{ number_format($item->unit_price ?? 0, 2) }}
                                                            </td>
                                                            <td class="px-5 py-4 text-center">
                                                                <span class="inline-flex items-center justify-center min-w-[2rem] px-2 py-1 rounded-md bg-muted/50 text-[11px] font-black text-foreground border border-border/50">{{ $item->quantity ?? 1 }}</span>
                                                            </td>
                                                            <td class="px-5 py-4 text-right font-black text-foreground text-sm">
                                                                ₹{{ number_format((($item->unit_price ?? 0) * ($item->quantity ?? 1)) - ($item->discount_amount ?? 0), 2) }}
                                                            </td>
                                                        </tr>
                                                    @endforeach
                                                @endif
                                            </tbody>
                                        </table>
                                    </div>
                                </div>

                                {{-- Payment Summary & Address --}}
                                <div class="space-y-6">
                                    <div class="bg-card/60 backdrop-blur-md rounded-2xl border border-border/50 p-6">
                                        <h4 class="text-sm font-black text-foreground mb-5 flex items-center gap-2">
                                            <x-ui.icon name="credit-card" size="4" class="text-muted-foreground" /> Payment Summary
                                        </h4>
                                        <div class="space-y-3">
                                            <div class="flex justify-between text-xs font-medium text-muted-foreground">
                                                <span>Subtotal</span>
                                                <span class="text-foreground">₹{{ number_format((float) ($order->total_amount ?? 0), 2) }}</span>
                                            </div>
                                            @if(isset($order->discount_amount) && $order->discount_amount > 0)
                                                <div class="flex justify-between text-xs font-medium text-emerald-500">
                                                    <span>Discount</span>
                                                    <span>- ₹{{ number_format((float) $order->discount_amount, 2) }}</span>
                                                </div>
                                            @endif
                                            <div class="flex justify-between text-xs font-medium text-muted-foreground">
                                                <span>Tax Total</span>
                                                <span class="text-foreground">₹{{ number_format((float) ($order->tax_amount ?? 0), 2) }}</span>
                                            </div>
                                            <div class="h-px bg-border/60 my-3"></div>
                                            <div class="flex justify-between items-end">
                                                <span class="text-sm font-black uppercase tracking-widest text-foreground">Grand Total</span>
                                                <span class="text-2xl font-black text-primary">₹{{ number_format((float) ($order->net_amount ?? 0), 2) }}</span>
                                            </div>
                                        </div>
                                    </div>

                                    {{-- Billing Address --}}
                                    @if($order->billingAddress)
                                        <div class="bg-card/60 backdrop-blur-md rounded-2xl border border-border/50 p-6 relative overflow-hidden group">
                                            <div class="absolute -right-4 -top-4 size-16 bg-primary/5 rounded-full pointer-events-none group-hover:scale-150 transition-transform duration-500"></div>
                                            <h4 class="text-sm font-black text-foreground mb-4 flex items-center gap-2 relative z-10">
                                                <x-ui.icon name="file-text" size="4" class="text-primary" /> Billing Address
                                            </h4>
                                            <div class="relative z-10">
                                                <p class="text-xs font-bold text-foreground mb-1">{{ $order->billingAddress->label ?? 'Billing' }}</p>
                                                <div class="mt-3 grid grid-cols-2 gap-x-2 gap-y-3 text-[10px]">
                                                    <div class="col-span-2">
                                                        <span class="font-black uppercase tracking-widest text-muted-foreground/60 block mb-1 text-[9px]">Street / Landmark</span>
                                                        <span class="text-foreground font-medium">{{ $order->billingAddress->address_line_1 }}</span>
                                                        @if($order->billingAddress->address_line_2)
                                                            <br><span class="text-foreground font-medium">{{ $order->billingAddress->address_line_2 }}</span>
                                                        @endif
                                                    </div>
                                                    <div>
                                                        <span class="font-black uppercase tracking-widest text-muted-foreground/60 block mb-1 text-[9px]">Village</span>
                                                        <span class="text-foreground font-medium">{{ $order->billingAddress->village?->village_name ?? $order->billingAddress->village_name ?? '—' }}</span>
                                                    </div>
                                                    <div>
                                                        <span class="font-black uppercase tracking-widest text-muted-foreground/60 block mb-1 text-[9px]">Post Office</span>
                                                        <span class="text-foreground font-medium">{{ $order->billingAddress->village?->post_so_name ?? $order->billingAddress->post_office ?? '—' }}</span>
                                                    </div>
                                                    <div>
                                                        <span class="font-black uppercase tracking-widest text-muted-foreground/60 block mb-1 text-[9px]">Taluka</span>
                                                        <span class="text-foreground font-medium">{{ $order->billingAddress->village?->taluka_name ?? $order->billingAddress->taluka ?? '—' }}</span>
                                                    </div>
                                                    <div>
                                                        <span class="font-black uppercase tracking-widest text-muted-foreground/60 block mb-1 text-[9px]">District / State / Pin</span>
                                                        <span class="text-foreground font-medium">{{ $order->billingAddress->village?->district_name ?? $order->billingAddress->city ?? '—' }}, {{ !empty($order->billingAddress->village?->state_name) ? $order->billingAddress->village->state_name : (!empty($order->billingAddress->state) ? $order->billingAddress->state : '—') }} - {{ $order->billingAddress->village?->pincode ?? $order->billingAddress->pincode ?? '—' }}</span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    @endif

                                    {{-- Shipping Address --}}
                                    @if($order->shippingAddress || $order->shipping_address)
                                        <div class="bg-card/60 backdrop-blur-md rounded-2xl border border-border/50 p-6 relative overflow-hidden group">
                                            <div class="absolute -right-4 -top-4 size-16 bg-primary/5 rounded-full pointer-events-none group-hover:scale-150 transition-transform duration-500"></div>
                                            <h4 class="text-sm font-black text-foreground mb-4 flex items-center gap-2 relative z-10">
                                                <x-ui.icon name="map-pin" size="4" class="text-primary" /> Delivery Address
                                            </h4>
                                            <div class="relative z-10">
                                                @if($order->shippingAddress)
                                                    <p class="text-xs font-bold text-foreground mb-1">{{ $order->shippingAddress->label ?? 'Shipping' }}</p>
                                                    <div class="mt-3 grid grid-cols-2 gap-x-2 gap-y-3 text-[10px]">
                                                        <div class="col-span-2">
                                                            <span class="font-black uppercase tracking-widest text-muted-foreground/60 block mb-1 text-[9px]">Street / Landmark</span>
                                                            <span class="text-foreground font-medium">{{ $order->shippingAddress->address_line_1 }}</span>
                                                            @if($order->shippingAddress->address_line_2)
                                                                <br><span class="text-foreground font-medium">{{ $order->shippingAddress->address_line_2 }}</span>
                                                            @endif
                                                        </div>
                                                        <div>
                                                            <span class="font-black uppercase tracking-widest text-muted-foreground/60 block mb-1 text-[9px]">Village</span>
                                                            <span class="text-foreground font-medium">{{ $order->shippingAddress->village?->village_name ?? $order->shippingAddress->village_name ?? '—' }}</span>
                                                        </div>
                                                        <div>
                                                            <span class="font-black uppercase tracking-widest text-muted-foreground/60 block mb-1 text-[9px]">Post Office</span>
                                                            <span class="text-foreground font-medium">{{ $order->shippingAddress->village?->post_so_name ?? $order->shippingAddress->post_office ?? '—' }}</span>
                                                        </div>
                                                        <div>
                                                            <span class="font-black uppercase tracking-widest text-muted-foreground/60 block mb-1 text-[9px]">Taluka</span>
                                                            <span class="text-foreground font-medium">{{ $order->shippingAddress->village?->taluka_name ?? $order->shippingAddress->taluka ?? '—' }}</span>
                                                        </div>
                                                        <div>
                                                            <span class="font-black uppercase tracking-widest text-muted-foreground/60 block mb-1 text-[9px]">District / State / Pin</span>
                                                            <span class="text-foreground font-medium">{{ $order->shippingAddress->village?->district_name ?? $order->shippingAddress->city ?? '—' }}, {{ !empty($order->shippingAddress->village?->state_name) ? $order->shippingAddress->village->state_name : (!empty($order->shippingAddress->state) ? $order->shippingAddress->state : '—') }} - {{ $order->shippingAddress->village?->pincode ?? $order->shippingAddress->pincode ?? '—' }}</span>
                                                        </div>
                                                    </div>
                                                @else
                                                    <p class="text-[11px] text-muted-foreground leading-relaxed font-medium">
                                                        {{ $order->shipping_address }}
                                                    </p>
                                                @endif
                                            </div>
                                        </div>
                                    @endif
                                </div>

                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        @else
            <div class="text-center py-24 px-4 rounded-3xl border-2 border-dashed border-border/60 bg-muted/5">
                <div class="size-20 rounded-3xl bg-muted flex items-center justify-center mx-auto mb-6">
                    <x-ui.icon name="clock" size="8" class="text-muted-foreground/50" />
                </div>
                <h4 class="text-lg font-black text-foreground">No Order History</h4>
                <p class="text-sm text-muted-foreground mt-2 max-w-sm mx-auto">This customer hasn't placed any orders yet. Once they do, they will appear here beautifully formatted.</p>
                <x-ui.button @click="activeTab = 'order'" class="mt-8 h-12 px-6 rounded-xl gap-2 shadow-xl shadow-primary/20 text-xs font-bold uppercase tracking-widest">
                    <x-ui.icon name="shopping-bag" size="4" /> Place an Order
                </x-ui.button>
            </div>
        @endif
    </div>
</div>
