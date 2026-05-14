{{-- ══ TAB: Order History ══ --}}
<div x-show="activeTab === 'history'" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-4" x-transition:enter-end="opacity-100 translate-y-0" x-cloak>
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
                                        $statusColor = match ($order->status) {
                                            'completed', 'delivered' => 'bg-emerald-500/10 text-emerald-500 border-emerald-500/20',
                                            'cancelled', 'returned' => 'bg-red-500/10 text-red-500 border-red-500/20',
                                            'shipped', 'in_transit' => 'bg-blue-500/10 text-blue-500 border-blue-500/20',
                                            default => 'bg-amber-500/10 text-amber-500 border-amber-500/20',
                                        };
                                    @endphp
                                    <span class="text-[10px] font-black uppercase tracking-widest px-2.5 py-1 rounded-lg border shadow-sm {{ $statusColor }}">
                                        {{ str_replace('_', ' ', $order->status) }}
                                    </span>
                                </div>
                                <p class="text-xs text-muted-foreground font-medium flex items-center gap-2">
                                    <x-ui.icon name="calendar" size="3" /> Placed on {{ $order->created_at->format('M d, Y h:i A') }}
                                </p>
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
                            <div class="flex flex-wrap gap-3">
                                <a href="{{ route('orders.receipt', $order->id) }}" class="inline-flex items-center justify-center h-9 px-4 rounded-xl bg-card border border-border text-xs font-bold text-foreground shadow-sm hover:bg-muted hover:-translate-y-0.5 transition-all">
                                    <x-ui.icon name="file-text" size="3.5" class="mr-2" /> View Receipt
                                </a>
                                <button type="button" @click="window.print()" class="inline-flex items-center justify-center h-9 px-4 rounded-xl bg-primary text-primary-foreground text-xs font-bold shadow-lg shadow-primary/25 hover:shadow-primary/40 hover:-translate-y-0.5 transition-all">
                                    <x-ui.icon name="printer" size="3.5" class="mr-2" /> Print Order
                                </button>
                            </div>

                            {{-- Progress Stepper --}}
                            <div class="bg-card/60 backdrop-blur-md rounded-2xl border border-border/50 p-6">
                                <h4 class="text-[11px] font-black uppercase tracking-[0.2em] text-muted-foreground mb-8 flex items-center gap-2">
                                    <span class="size-2 rounded-full bg-primary inline-block shadow-lg shadow-primary/50"></span> Fulfillment Tracking
                                </h4>
                                <div class="relative flex justify-between items-center w-full px-2 sm:px-8">
                                    <div class="absolute left-0 top-5 w-full h-1 bg-muted rounded-full z-0"></div>
                                    @php
                                        $progress = match ($order->status ?? '') {
                                            'confirmed' => '20%',
                                            'processing' => '40%',
                                            'ready_to_ship' => '60%',
                                            'shipped', 'in_transit' => '80%',
                                            'delivered', 'completed' => '100%',
                                            default => '0%',
                                        };
                                    @endphp
                                    <div class="absolute left-0 top-5 h-1 bg-primary rounded-full z-0 transition-all duration-1000 ease-out shadow-sm" style="width: {{ $progress }}"></div>

                                    <div class="relative z-10 flex justify-between w-full">
                                        {{-- Placed --}}
                                        <div class="flex flex-col items-center group">
                                            <div class="size-10 rounded-full flex items-center justify-center bg-primary text-primary-foreground shadow-lg shadow-primary/30 ring-4 ring-background transition-transform group-hover:scale-110">
                                                <x-ui.icon name="shopping-cart" size="4" />
                                            </div>
                                            <span class="mt-3 text-xs font-bold text-foreground">Placed</span>
                                        </div>

                                        {{-- Processing --}}
                                        @php $isProc = in_array($order->status ?? '', ['confirmed', 'processing', 'ready_to_ship', 'shipped', 'in_transit', 'delivered', 'completed']); @endphp
                                        <div class="flex flex-col items-center group">
                                            <div class="size-10 rounded-full flex items-center justify-center {{ $isProc ? 'bg-primary text-primary-foreground shadow-lg shadow-primary/30' : 'bg-card border-2 border-border text-muted-foreground' }} ring-4 ring-background transition-all duration-500 group-hover:scale-110">
                                                <x-ui.icon name="package" size="4" />
                                            </div>
                                            <span class="mt-3 text-xs font-bold {{ $isProc ? 'text-foreground' : 'text-muted-foreground' }}">Processing</span>
                                        </div>

                                        {{-- Dispatched --}}
                                        @php $isShip = in_array($order->status ?? '', ['shipped', 'in_transit', 'delivered', 'completed']); @endphp
                                        <div class="flex flex-col items-center group">
                                            <div class="size-10 rounded-full flex items-center justify-center {{ $isShip ? 'bg-primary text-primary-foreground shadow-lg shadow-primary/30' : 'bg-card border-2 border-border text-muted-foreground' }} ring-4 ring-background transition-all duration-500 group-hover:scale-110">
                                                <x-ui.icon name="truck" size="4" />
                                            </div>
                                            <span class="mt-3 text-xs font-bold {{ $isShip ? 'text-foreground' : 'text-muted-foreground' }}">Dispatched</span>
                                        </div>

                                        {{-- Delivered --}}
                                        @php $isDone = in_array($order->status ?? '', ['delivered', 'completed']); @endphp
                                        <div class="flex flex-col items-center group">
                                            <div class="size-10 rounded-full flex items-center justify-center {{ $isDone ? 'bg-primary text-primary-foreground shadow-lg shadow-primary/30' : 'bg-card border-2 border-border text-muted-foreground' }} ring-4 ring-background transition-all duration-500 group-hover:scale-110">
                                                <x-ui.icon name="check" size="4" />
                                            </div>
                                            <span class="mt-3 text-xs font-bold {{ $isDone ? 'text-foreground' : 'text-muted-foreground' }}">Delivered</span>
                                        </div>
                                    </div>
                                </div>
                                
                                {{-- Tracking Info --}}
                                @if(isset($order->shipments) && $order->shipments->isNotEmpty())
                                    <div class="mt-8 bg-primary/5 rounded-xl border border-primary/10 p-5 flex flex-wrap gap-8 items-center">
                                        <div class="flex items-center gap-3">
                                            <div class="p-2 bg-background rounded-lg text-primary shadow-sm">
                                                <x-ui.icon name="truck" size="4" />
                                            </div>
                                            <div>
                                                <p class="text-[10px] font-black text-muted-foreground uppercase tracking-widest">Carrier</p>
                                                <p class="text-sm font-bold text-foreground">{{ $order->shipments->first()->carrier ?? 'N/A' }}</p>
                                            </div>
                                        </div>
                                        <div class="flex items-center gap-3">
                                            <div class="p-2 bg-background rounded-lg text-primary shadow-sm">
                                                <x-ui.icon name="hash" size="4" />
                                            </div>
                                            <div>
                                                <p class="text-[10px] font-black text-muted-foreground uppercase tracking-widest">Tracking ID</p>
                                                <p class="text-sm font-mono font-bold text-primary">{{ $order->shipments->first()->tracking_number ?? 'N/A' }}</p>
                                            </div>
                                        </div>
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

                                    @if($order->shipping_address || $order->shippingAddress)
                                        <div class="bg-card/60 backdrop-blur-md rounded-2xl border border-border/50 p-6 relative overflow-hidden group">
                                            <div class="absolute -right-4 -top-4 size-16 bg-primary/5 rounded-full pointer-events-none group-hover:scale-150 transition-transform duration-500"></div>
                                            <h4 class="text-sm font-black text-foreground mb-4 flex items-center gap-2 relative z-10">
                                                <x-ui.icon name="map-pin" size="4" class="text-primary" /> Delivery Address
                                            </h4>
                                            <div class="relative z-10">
                                                @if($order->shipping_address)
                                                    <p class="text-[11px] text-muted-foreground leading-relaxed font-medium">
                                                        {{ $order->shipping_address }}
                                                    </p>
                                                @else
                                                    <p class="text-xs font-bold text-foreground mb-1">{{ $order->shippingAddress->label ?? 'Default Address' }}</p>
                                                    <p class="text-[11px] text-muted-foreground leading-relaxed font-medium">
                                                        {{ $order->shippingAddress->address_line_1 ?? '' }}<br>
                                                        {{ $order->shippingAddress->village?->name ?? '' }}, {{ $order->shippingAddress->village?->district ?? '' }} {{ $order->shippingAddress->village?->pincode ?? '' }}
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
