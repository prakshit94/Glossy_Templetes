<x-layouts.app pageTitle="Order Details">

    <div class="p-6 lg:p-10 space-y-6">
        <!-- Header -->
        <x-ui.card class="overflow-hidden border-border/60 shadow-2xl bg-card/30 backdrop-blur-2xl rounded-3xl">
            <div class="p-8 flex flex-col md:flex-row md:items-center justify-between gap-6 relative">
                <!-- Status Background Glow -->
                @php
                    $statusColor = match($order->status) {
                        'delivered', 'completed' => 'emerald',
                        'shipped' => 'blue',
                        'confirmed' => 'indigo',
                        'cancelled', 'returned' => 'red',
                        'pending' => 'orange',
                        default => 'primary'
                    };
                @endphp
                <div class="absolute top-0 right-0 -mr-16 -mt-16 size-64 bg-{{ $statusColor }}-500/10 blur-[60px] rounded-full pointer-events-none"></div>
                
                <div class="flex items-center gap-5 relative z-10">
                    <div class="size-16 rounded-2xl bg-{{ $statusColor }}-500/10 border border-{{ $statusColor }}-500/20 text-{{ $statusColor }}-500 flex items-center justify-center shadow-inner">
                        <x-ui.icon name="{{ match($order->status) { 'delivered' => 'check-circle', 'shipped' => 'truck', 'cancelled' => 'x-circle', default => 'package' } }}" size="8" />
                    </div>
                    <div>
                        <div class="flex items-center gap-3 mb-1">
                            <h3 class="text-2xl font-black text-foreground tracking-tight">{{ $order->order_no }}</h3>
                            <x-ui.badge variant="{{ match($order->status) { 'shipped', 'delivered', 'completed' => 'success', 'cancelled', 'returned' => 'destructive', 'pending' => 'warning', default => 'default' } }}" class="rounded-lg px-2 py-0.5 text-[10px] font-black uppercase tracking-widest">
                                {{ str_replace('_', ' ', $order->status) }}
                            </x-ui.badge>
                        </div>
                        <p class="text-xs text-muted-foreground font-medium flex items-center gap-2">
                            <span class="font-bold uppercase tracking-wider text-foreground">{{ $order->type }} ORDER</span> 
                            <span class="size-1 rounded-full bg-border"></span>
                            {{ optional($order->order_date)->format('M d, Y • h:i A') }}
                        </p>
                    </div>
                </div>

                <div class="flex flex-wrap items-center gap-3 relative z-10">
                    <a href="{{ route('orders.index') }}">
                        <x-ui.button variant="outline" size="sm" class="rounded-xl font-bold uppercase tracking-widest text-[10px]">
                            <x-ui.icon name="arrow-left" size="3" class="mr-2" /> Back
                        </x-ui.button>
                    </a>
                    
                    @if($order->type === 'sale' && $order->party && $order->party->type === 'customer')
                        <a href="{{ route('customers.show', ['customer' => $order->party_id, 'edit_order' => $order->id]) }}">
                            <x-ui.button variant="outline" size="sm" class="rounded-xl font-bold uppercase tracking-widest text-[10px] text-amber-600 border-amber-600/30 hover:bg-amber-600/10">
                                <x-ui.icon name="edit-3" size="3" class="mr-2" /> Edit Cart
                            </x-ui.button>
                        </a>
                    @endif

                    @if($order->status === 'pending')
                        <form action="{{ route('orders.confirm', $order) }}" method="POST">
                            @csrf
                            <x-ui.button size="sm" class="rounded-xl font-bold uppercase tracking-widest text-[10px] bg-primary shadow-lg shadow-primary/20">
                                <x-ui.icon name="check-circle" size="3" class="mr-2" /> Confirm
                            </x-ui.button>
                        </form>
                    @endif
                    @if($order->status === 'confirmed')
                        <form action="{{ route('orders.ship', $order) }}" method="POST">
                            @csrf
                            <x-ui.button size="sm" class="rounded-xl font-bold uppercase tracking-widest text-[10px] bg-emerald-500 hover:bg-emerald-600 text-white shadow-lg shadow-emerald-500/20">
                                <x-ui.icon name="truck" size="3" class="mr-2" /> Ship
                            </x-ui.button>
                        </form>
                    @endif
                </div>
            </div>
        </x-ui.card>

        <!-- Lifecycle Steps -->
        @php
            $steps = ['pending', 'confirmed', 'processing', 'shipped', 'delivered'];
            $currentIndex = array_search($order->status, $steps);
            $isCancelled = in_array($order->status, ['cancelled', 'returned']);
        @endphp
        @if(!$isCancelled && $currentIndex !== false)
        <x-ui.card class="overflow-hidden border-border/60 shadow-xl bg-card/30 backdrop-blur-xl rounded-3xl p-8">
            <div class="relative flex items-center justify-between w-full max-w-4xl mx-auto">
                <div class="absolute left-0 top-1/2 -translate-y-1/2 w-full h-1 bg-muted rounded-full overflow-hidden">
                    <div class="h-full bg-{{ $statusColor }}-500 transition-all duration-1000" style="width: {{ ($currentIndex / (count($steps) - 1)) * 100 }}%"></div>
                </div>
                
                @foreach($steps as $index => $step)
                    @php
                        $isCompleted = $index <= $currentIndex;
                        $isCurrent = $index === $currentIndex;
                    @endphp
                    <div class="relative flex flex-col items-center gap-3 z-10">
                        <div class="size-10 rounded-full flex items-center justify-center font-bold text-sm border-4 {{ $isCompleted ? 'bg-'.$statusColor.'-500 border-'.$statusColor.'-200 text-white shadow-lg shadow-'.$statusColor.'-500/40' : 'bg-muted border-background text-muted-foreground' }} transition-colors duration-500">
                            @if($isCompleted) <x-ui.icon name="check" size="4" /> @else {{ $index + 1 }} @endif
                        </div>
                        <span class="absolute -bottom-6 text-[10px] font-black uppercase tracking-widest whitespace-nowrap {{ $isCurrent ? 'text-'.$statusColor.'-600' : ($isCompleted ? 'text-foreground' : 'text-muted-foreground') }}">
                            {{ $step }}
                        </span>
                    </div>
                @endforeach
            </div>
        </x-ui.card>
        @endif

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <!-- Left Column: Details & Addresses -->
            <div class="md:col-span-2 space-y-6">
                
                <!-- Party & Order Info -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                    <x-ui.card class="overflow-hidden border-border/60 shadow-xl bg-card/30 backdrop-blur-xl rounded-3xl p-6">
                        <h4 class="text-[10px] font-black uppercase tracking-[0.2em] text-primary mb-4 flex items-center gap-2">
                            <x-ui.icon name="user" size="3" /> Party Information
                        </h4>
                        @if($order->party)
                            <p class="text-lg font-black text-foreground mb-1">{{ $order->party->name }}</p>
                            <div class="flex items-center gap-2 mb-3">
                                <span class="text-[9px] uppercase tracking-widest font-bold px-2 py-0.5 rounded-md bg-muted text-muted-foreground">{{ $order->party->type }}</span>
                                <span class="text-[10px] font-medium text-muted-foreground flex items-center gap-1"><x-ui.icon name="phone" size="3" /> {{ $order->party->phone ?? 'No Phone' }}</span>
                            </div>
                            <a href="{{ route($order->party->type === 'customer' ? 'customers.show' : 'suppliers.show', $order->party->id) }}" class="text-[10px] font-bold text-primary hover:underline flex items-center gap-1">
                                View Profile <x-ui.icon name="external-link" size="3" />
                            </a>
                        @else
                            <p class="text-sm font-medium text-muted-foreground">No party assigned</p>
                        @endif
                    </x-ui.card>

                    <x-ui.card class="overflow-hidden border-border/60 shadow-xl bg-card/30 backdrop-blur-xl rounded-3xl p-6">
                        <h4 class="text-[10px] font-black uppercase tracking-[0.2em] text-primary mb-4 flex items-center gap-2">
                            <x-ui.icon name="clock" size="3" /> Order Tracking
                        </h4>
                        <div class="space-y-4">
                            <div>
                                <p class="text-[9px] font-black uppercase tracking-widest text-muted-foreground mb-1">Placed By</p>
                                <p class="text-sm font-bold text-foreground flex items-center gap-2">
                                    {{ $order->creator->name ?? 'System / Auto' }}
                                    <span class="text-[10px] font-medium text-muted-foreground">{{ $order->created_at->format('M d, Y h:i A') }}</span>
                                </p>
                            </div>
                            <div>
                                <p class="text-[9px] font-black uppercase tracking-widest text-muted-foreground mb-1">Last Updated</p>
                                <p class="text-sm font-bold text-foreground flex items-center gap-2">
                                    {{ $order->updater->name ?? 'N/A' }}
                                    <span class="text-[10px] font-medium text-muted-foreground">{{ $order->updated_at->format('M d, Y h:i A') }}</span>
                                </p>
                            </div>
                        </div>
                    </x-ui.card>
                </div>

                <!-- Addresses -->
                <x-ui.card class="overflow-hidden border-border/60 shadow-xl bg-card/30 backdrop-blur-xl rounded-3xl">
                    <div class="p-6 border-b border-border/40 bg-muted/5 flex items-center gap-2">
                        <h4 class="text-[10px] font-black uppercase tracking-[0.2em] text-primary">Location Details</h4>
                    </div>
                    <div class="grid grid-cols-1 sm:grid-cols-2 divide-y sm:divide-y-0 sm:divide-x divide-border/40">
                        <div class="p-6">
                            <h5 class="text-[9px] font-black uppercase tracking-widest text-muted-foreground mb-3 flex items-center gap-1.5"><x-ui.icon name="map-pin" size="3" /> Shipping Address</h5>
                            @if($order->shippingAddress)
                                <p class="text-sm font-bold text-foreground mb-1">{{ $order->shippingAddress->label ?? 'Address' }}</p>
                                <p class="text-xs text-muted-foreground leading-relaxed">
                                    {{ $order->shippingAddress->address_line_1 }}<br>
                                    @if($order->shippingAddress->address_line_2){{ $order->shippingAddress->address_line_2 }}<br>@endif
                                    {{ $order->shippingAddress->village?->village_name ?? '' }} {{ $order->shippingAddress->village?->taluka_name ?? '' }}<br>
                                    {{ $order->shippingAddress->village?->district_name ?? '' }} {{ $order->shippingAddress->village?->state_name ?? '' }} - {{ $order->shippingAddress->village?->pincode ?? '' }}
                                </p>
                            @elseif($order->shipping_address)
                                <p class="text-xs text-muted-foreground leading-relaxed">{{ $order->shipping_address }}</p>
                            @else
                                <p class="text-xs text-muted-foreground italic">No shipping address provided.</p>
                            @endif
                        </div>
                        <div class="p-6">
                            <h5 class="text-[9px] font-black uppercase tracking-widest text-muted-foreground mb-3 flex items-center gap-1.5"><x-ui.icon name="file-text" size="3" /> Billing Address</h5>
                            @if($order->billingAddress)
                                <p class="text-sm font-bold text-foreground mb-1">{{ $order->billingAddress->label ?? 'Address' }}</p>
                                <p class="text-xs text-muted-foreground leading-relaxed">
                                    {{ $order->billingAddress->address_line_1 }}<br>
                                    @if($order->billingAddress->address_line_2){{ $order->billingAddress->address_line_2 }}<br>@endif
                                    {{ $order->billingAddress->village?->village_name ?? '' }} {{ $order->billingAddress->village?->taluka_name ?? '' }}<br>
                                    {{ $order->billingAddress->village?->district_name ?? '' }} {{ $order->billingAddress->village?->state_name ?? '' }} - {{ $order->billingAddress->village?->pincode ?? '' }}
                                </p>
                            @elseif($order->billing_address)
                                <p class="text-xs text-muted-foreground leading-relaxed">{{ $order->billing_address }}</p>
                            @else
                                <p class="text-xs text-muted-foreground italic">Same as shipping.</p>
                            @endif
                        </div>
                    </div>
                </x-ui.card>
                
                <!-- Items Table -->
                <x-ui.card class="overflow-hidden border-border/60 shadow-xl bg-card/30 backdrop-blur-xl rounded-3xl">
                    <div class="p-6 border-b border-border/40 bg-muted/5 flex items-center gap-2">
                        <h4 class="text-[10px] font-black uppercase tracking-[0.2em] text-primary">Itemized Order ({{ $order->items->count() }})</h4>
                    </div>
                    <div class="overflow-x-auto custom-scrollbar">
                        <table class="w-full text-left border-collapse">
                            <thead>
                                <tr class="border-b border-border/40 bg-muted/5">
                                    <th class="px-6 py-4 text-[10px] font-black uppercase tracking-widest text-muted-foreground">Product</th>
                                    <th class="px-6 py-4 text-[10px] font-black uppercase tracking-widest text-muted-foreground text-center">Qty</th>
                                    <th class="px-6 py-4 text-[10px] font-black uppercase tracking-widest text-muted-foreground text-right">Unit Price</th>
                                    <th class="px-6 py-4 text-[10px] font-black uppercase tracking-widest text-muted-foreground text-right">Discount</th>
                                    <th class="px-6 py-4 text-[10px] font-black uppercase tracking-widest text-muted-foreground text-right">Total</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-border/40">
                                @foreach($order->items as $item)
                                    <tr class="hover:bg-muted/10 transition-colors">
                                        <td class="px-6 py-4">
                                            <div class="flex items-center gap-3">
                                                <div class="size-10 rounded-xl bg-background border border-border flex items-center justify-center overflow-hidden shrink-0">
                                                    @if($item->product?->image_url)
                                                        <img src="{{ $item->product->image_url }}" class="size-full object-cover">
                                                    @else
                                                        <x-ui.icon name="image" size="4" class="text-muted-foreground/30" />
                                                    @endif
                                                </div>
                                                <div>
                                                    <p class="text-sm font-bold text-foreground">{{ $item->product?->name ?? 'Unknown Product' }}</p>
                                                    <p class="text-[10px] text-muted-foreground font-mono">{{ $item->product?->sku ?? 'N/A' }}</p>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 text-center text-sm font-black text-foreground">{{ $item->quantity }}</td>
                                        <td class="px-6 py-4 text-right text-xs font-semibold text-muted-foreground">₹{{ number_format((float) $item->unit_price, 2) }}</td>
                                        <td class="px-6 py-4 text-right text-xs font-semibold text-orange-500">₹{{ number_format((float) $item->discount_amount, 2) }}</td>
                                        <td class="px-6 py-4 text-right text-sm font-black text-primary">₹{{ number_format((float) $item->total_amount, 2) }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </x-ui.card>

            </div>

            <!-- Right Column: Financial Summary -->
            <div class="space-y-6">
                <x-ui.card class="overflow-hidden border-border/60 shadow-2xl bg-card/50 backdrop-blur-3xl rounded-3xl sticky top-6">
                    <div class="p-6 border-b border-border/40 bg-muted/10">
                        <h4 class="text-[10px] font-black uppercase tracking-[0.2em] text-primary flex items-center gap-2">
                            <x-ui.icon name="credit-card" size="3" /> Payment Summary
                        </h4>
                    </div>
                    <div class="p-6 space-y-4">
                        
                        <div class="flex justify-between items-center text-sm">
                            <span class="text-muted-foreground font-medium">Subtotal</span>
                            <span class="font-bold text-foreground">₹{{ number_format((float) $order->total_amount, 2) }}</span>
                        </div>
                        
                        @if($order->discount_amount > 0)
                        <div class="flex justify-between items-center text-sm">
                            <span class="text-orange-500/80 font-medium">Discounts</span>
                            <span class="font-bold text-orange-500">-₹{{ number_format((float) $order->discount_amount, 2) }}</span>
                        </div>
                        @endif
                        
                        @if($order->tax_amount > 0)
                        <div class="flex justify-between items-center text-sm">
                            <span class="text-muted-foreground font-medium">Taxes</span>
                            <span class="font-bold text-foreground">+₹{{ number_format((float) $order->tax_amount, 2) }}</span>
                        </div>
                        @endif

                        <div class="h-px bg-border/60 w-full my-4"></div>

                        <div class="flex justify-between items-end">
                            <div>
                                <span class="text-[10px] font-black uppercase tracking-widest text-muted-foreground block mb-1">Grand Total</span>
                                <span class="text-[10px] text-muted-foreground font-medium">Inclusive of all taxes</span>
                            </div>
                            <span class="text-2xl font-black text-primary">₹{{ number_format((float) $order->net_amount, 2) }}</span>
                        </div>

                    </div>
                    <div class="p-6 bg-primary/5 border-t border-primary/10">
                        <p class="text-xs text-primary/80 font-semibold flex items-center gap-2 justify-center text-center">
                            <x-ui.icon name="shield-check" size="4" /> Secure Order Record
                        </p>
                    </div>
                </x-ui.card>

                <x-ui.card class="overflow-hidden border-border/60 shadow-xl bg-card/30 backdrop-blur-xl rounded-3xl p-6">
                    <h4 class="text-[10px] font-black uppercase tracking-[0.2em] text-muted-foreground mb-4">Internal Notes</h4>
                    <p class="text-xs text-muted-foreground italic leading-relaxed">
                        Order processed from Warehouse: <strong>{{ $order->warehouse?->name ?? 'Default' }}</strong>. 
                        No additional notes recorded for this transaction.
                    </p>
                </x-ui.card>
            </div>
        </div>
    </div>
</x-layouts.app>
