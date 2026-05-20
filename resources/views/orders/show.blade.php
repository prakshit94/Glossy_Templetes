<x-layouts.app pageTitle="Order Details">

    <div class="p-6 lg:p-10 space-y-6">
        <!-- Header -->
        <x-ui.card class="overflow-hidden border-border/60 shadow-2xl bg-card/30 backdrop-blur-2xl rounded-3xl">
            <div class="p-8 flex flex-col md:flex-row md:items-center justify-between gap-6 relative">
                <!-- Status Background Glow -->
                @php
                    $statusColor = match($order->status) {
                        'delivered', 'completed' => 'emerald',
                        'dispatched', 'shipped' => 'blue',
                        'ready_to_ship' => 'indigo',
                        'processing' => 'amber',
                        'confirmed' => 'indigo',
                        'cancelled', 'returned' => 'red',
                        'pending' => 'orange',
                        default => 'primary'
                    };
                @endphp
                <div class="absolute top-0 right-0 -mr-16 -mt-16 size-64 bg-{{ $statusColor }}-500/10 blur-[60px] rounded-full pointer-events-none"></div>
                
                <div class="flex items-center gap-5 relative z-10">
                    <div class="size-16 rounded-2xl bg-{{ $statusColor }}-500/10 border border-{{ $statusColor }}-500/20 text-{{ $statusColor }}-500 flex items-center justify-center shadow-inner">
                        <x-ui.icon name="{{ match($order->status) { 'delivered' => 'check-circle', 'dispatched', 'shipped' => 'truck', 'cancelled' => 'x-circle', default => 'package' } }}" size="8" />
                    </div>
                    <div>
                        <div class="flex items-center gap-3 mb-1">
                            <h3 class="text-2xl font-black text-foreground tracking-tight">{{ $order->order_no }}</h3>
                            <x-ui.badge variant="{{ match($order->status) { 'shipped', 'dispatched', 'delivered', 'completed' => 'success', 'cancelled', 'returned' => 'destructive', 'pending' => 'warning', 'processing' => 'warning', 'ready_to_ship' => 'indigo', default => 'default' } }}" class="rounded-lg px-2 py-0.5 text-[10px] font-black uppercase tracking-widest">
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

                    @if($order->invoice)
                        <a href="{{ route('orders.invoice-pdf', $order) }}" target="_blank">
                            <x-ui.button variant="outline" size="sm" class="rounded-xl font-bold uppercase tracking-widest text-[10px] text-blue-600 border-blue-600/30 hover:bg-blue-600/10">
                                <x-ui.icon name="file-text" size="3" class="mr-2" /> Invoice PDF
                            </x-ui.button>
                        </a>
                    @else
                        <form action="{{ route('orders.generate-invoice', $order) }}" method="POST" class="inline">
                            @csrf
                            <x-ui.button type="submit" variant="outline" size="sm" class="rounded-xl font-bold uppercase tracking-widest text-[10px] text-indigo-600 border-indigo-600/30 hover:bg-indigo-600/10">
                                <x-ui.icon name="file-plus" size="3" class="mr-2" /> Generate Invoice
                            </x-ui.button>
                        </form>
                    @endif
                    
                    <a href="{{ route('orders.cod-pdf', $order) }}" target="_blank">
                        <x-ui.button variant="outline" size="sm" class="rounded-xl font-bold uppercase tracking-widest text-[10px] text-emerald-600 border-emerald-600/30 hover:bg-emerald-600/10">
                            <x-ui.icon name="printer" size="3" class="mr-2" /> COD PDF
                        </x-ui.button>
                    </a>

                    {{-- Confirm (pending → confirmed) --}}
                    @if($order->status === 'pending')
                        <form action="{{ route('orders.confirm', $order) }}" method="POST">
                            @csrf
                            <x-ui.button size="sm" class="rounded-xl font-bold uppercase tracking-widest text-[10px] bg-indigo-500 hover:bg-indigo-600 text-white shadow-lg shadow-indigo-500/20">
                                <x-ui.icon name="check-circle" size="3" class="mr-2" /> Confirm Order
                            </x-ui.button>
                        </form>
                    @endif

                    {{-- Mark Processing (confirmed → processing) --}}
                    @if($order->status === 'confirmed')
                        <form action="{{ route('orders.processing', $order) }}" method="POST">
                            @csrf
                            <x-ui.button size="sm" class="rounded-xl font-bold uppercase tracking-widest text-[10px] bg-amber-500 hover:bg-amber-600 text-white shadow-lg shadow-amber-500/20">
                                <x-ui.icon name="loader" size="3" class="mr-2" /> Mark Processing
                            </x-ui.button>
                        </form>
                        <x-ui.button size="sm" @click="$dispatch('open-modal', { name: 'create-shipment-modal' })" class="rounded-xl font-bold uppercase tracking-widest text-[10px] bg-indigo-500 hover:bg-indigo-600 text-white shadow-lg shadow-indigo-500/20">
                            <x-ui.icon name="package" size="3" class="mr-2" /> Mark Ready to Ship
                        </x-ui.button>
                    @endif

                    {{-- Mark Ready to Ship (processing → ready_to_ship) --}}
                    @if($order->status === 'processing')
                        <x-ui.button size="sm" @click="$dispatch('open-modal', { name: 'create-shipment-modal' })" class="rounded-xl font-bold uppercase tracking-widest text-[10px] bg-indigo-500 hover:bg-indigo-600 text-white shadow-lg shadow-indigo-500/20">
                            <x-ui.icon name="package" size="3" class="mr-2" /> Mark Ready to Ship
                        </x-ui.button>
                    @endif

                    {{-- Dispatch Order (ready_to_ship → dispatched) --}}
                    @if($order->status === 'ready_to_ship')
                        <form action="{{ route('orders.dispatch', $order) }}" method="POST">
                            @csrf
                            <x-ui.button size="sm" class="rounded-xl font-bold uppercase tracking-widest text-[10px] bg-blue-500 hover:bg-blue-600 text-white shadow-lg shadow-blue-500/20">
                                <x-ui.icon name="truck" size="3" class="mr-2" /> Dispatch Order
                            </x-ui.button>
                        </form>
                    @endif

                    {{-- Deliver (dispatched/shipped → delivered) --}}
                    @if(in_array($order->status, ['dispatched', 'shipped']))
                        <form action="{{ route('orders.deliver', $order) }}" method="POST">
                            @csrf
                            <x-ui.button size="sm" class="rounded-xl font-bold uppercase tracking-widest text-[10px] bg-emerald-500 hover:bg-emerald-600 text-white shadow-lg shadow-emerald-500/20">
                                <x-ui.icon name="check-circle" size="3" class="mr-2" /> Mark Delivered
                            </x-ui.button>
                        </form>
                    @endif

                    {{-- Cancel (any active status) --}}
                    @if(!in_array($order->status, ['delivered', 'cancelled', 'returned']))
                        <form action="{{ route('orders.cancel', $order) }}" method="POST" onsubmit="return confirm('Cancel this order?')">
                            @csrf
                            <x-ui.button variant="outline" size="sm" class="rounded-xl font-bold uppercase tracking-widest text-[10px] text-destructive border-destructive/30 hover:bg-destructive/10">
                                <x-ui.icon name="x-circle" size="3" class="mr-2" /> Cancel
                            </x-ui.button>
                        </form>
                    @endif
                </div>
            </div>
        </x-ui.card>

        <!-- Lifecycle Steps -->
        @php
            $steps = [
                ['key' => 'pending',    'label' => 'Pending',    'icon' => 'clock'],
                ['key' => 'confirmed',  'label' => 'Confirmed',  'icon' => 'check-circle'],
                ['key' => 'processing', 'label' => 'Processing', 'icon' => 'loader'],
                ['key' => 'ready_to_ship', 'label' => 'Ready to Ship', 'icon' => 'package'],
                ['key' => $order->status === 'shipped' ? 'shipped' : 'dispatched', 'label' => $order->status === 'shipped' ? 'Shipped' : 'Dispatched', 'icon' => 'truck'],
                ['key' => 'delivered',  'label' => 'Delivered',  'icon' => 'check-circle'],
            ];
            $stepKeys = array_column($steps, 'key');
            $currentIndex = array_search($order->status, $stepKeys);
            $isCancelled = in_array($order->status, ['cancelled', 'returned']);
        @endphp
        @if(!$isCancelled)
        <x-ui.card class="overflow-hidden border-border/60 shadow-xl bg-card/30 backdrop-blur-xl rounded-3xl p-8">
            <div class="relative flex items-start justify-between w-full max-w-4xl mx-auto">
                {{-- Progress connector line --}}
                <div class="absolute left-0 right-0 top-5 -translate-y-1/2 h-1 bg-muted/60 rounded-full overflow-hidden" style="margin: 0 2.5rem">
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

            @if($isCancelled)
                <div class="mt-6 flex items-center justify-center gap-2 text-destructive">
                    <x-ui.icon name="x-circle" size="4" />
                    <span class="text-xs font-black uppercase tracking-widest">Order {{ ucfirst($order->status) }}</span>
                </div>
            @endif
        </x-ui.card>
        @else
        <x-ui.card class="overflow-hidden border-border/60 shadow-xl bg-card/30 backdrop-blur-xl rounded-3xl p-6">
            <div class="flex items-center justify-center gap-3 text-destructive">
                <x-ui.icon name="x-circle" size="6" />
                <div>
                    <p class="text-sm font-black uppercase tracking-widest">Order {{ ucfirst($order->status) }}</p>
                    <p class="text-[10px] text-muted-foreground font-bold uppercase tracking-widest">This order cannot be progressed further.</p>
                </div>
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

                            @if($order->shipments->isNotEmpty())
                                <div class="h-px bg-border/40 my-3"></div>
                                @php $shipment = $order->shipments->first(); @endphp
                                <div class="grid grid-cols-2 gap-4">
                                    <div>
                                        <p class="text-[9px] font-black uppercase tracking-widest text-muted-foreground mb-1">Carrier / Company</p>
                                        <p class="text-xs font-bold text-foreground flex items-center gap-1">
                                            <x-ui.icon name="truck" size="3" class="text-primary" />
                                            {{ $shipment->carrier_name ?? 'N/A' }}
                                        </p>
                                    </div>
                                    <div>
                                        <p class="text-[9px] font-black uppercase tracking-widest text-muted-foreground mb-1">Tracking ID</p>
                                        <p class="text-xs font-bold text-primary flex items-center gap-1">
                                            <x-ui.icon name="hash" size="3" />
                                            {{ $shipment->tracking_no ?? 'N/A' }}
                                        </p>
                                    </div>
                                </div>
                                <div class="pt-3">
                                    <a href="{{ route('order.tracking.show', $shipment) }}">
                                        <x-ui.button variant="outline" size="sm" class="w-full rounded-xl font-bold uppercase tracking-widest text-[9px] h-9 border-primary/20 hover:bg-primary/5 text-primary">
                                            <x-ui.icon name="target" size="3" class="mr-1.5" /> Track Shipment Details
                                        </x-ui.button>
                                    </a>
                                </div>
                            @endif
                        </div>
                    </x-ui.card>
                </div>

                <!-- Addresses -->
                <x-ui.card class="overflow-hidden border-border/60 shadow-xl bg-card/30 backdrop-blur-xl rounded-3xl">
                    <div class="p-6 border-b border-border/40 bg-muted/5 flex items-center gap-2">
                        <h4 class="text-[10px] font-black uppercase tracking-[0.2em] text-primary">Location Details</h4>
                    </div>
                    <div class="grid grid-cols-1 sm:grid-cols-2 divide-y sm:divide-y-0 sm:divide-x divide-border/40">
                        <div class="p-6 space-y-4">
                            <h5 class="text-[9px] font-black uppercase tracking-widest text-muted-foreground flex items-center gap-1.5"><x-ui.icon name="map-pin" size="3" /> Shipping Address</h5>
                            @if($order->shippingAddress)
                                <div>
                                    <p class="text-sm font-bold text-foreground mb-1">{{ $order->shippingAddress->label ?? 'Shipping' }}</p>
                                    <p class="text-xs text-muted-foreground font-medium mb-3">{{ $order->shippingAddress->address_line_1 }}@if($order->shippingAddress->address_line_2), {{ $order->shippingAddress->address_line_2 }}@endif</p>
                                    <div class="pt-3 border-t border-border/40 space-y-2">
                                        <div class="flex justify-between">
                                            <span class="text-[10px] uppercase tracking-widest text-muted-foreground font-bold">Village</span>
                                            <span class="text-xs font-bold text-foreground">{{ $order->shippingAddress->village?->village_name ?? $order->shippingAddress->city ?: '—' }}</span>
                                        </div>
                                        <div class="flex justify-between">
                                            <span class="text-[10px] uppercase tracking-widest text-muted-foreground font-bold">Post Office</span>
                                            <span class="text-xs font-bold text-foreground">{{ $order->shippingAddress->village?->post_so_name ?: '—' }}</span>
                                        </div>
                                        <div class="flex justify-between">
                                            <span class="text-[10px] uppercase tracking-widest text-muted-foreground font-bold">Taluka</span>
                                            <span class="text-xs font-bold text-foreground">{{ $order->shippingAddress->village?->taluka_name ?: '—' }}</span>
                                        </div>
                                        <div class="flex justify-between">
                                            <span class="text-[10px] uppercase tracking-widest text-muted-foreground font-bold">District</span>
                                            <span class="text-xs font-bold text-foreground">{{ $order->shippingAddress->village?->district_name ?? $order->shippingAddress->city ?: '—' }}</span>
                                        </div>
                                        <div class="flex justify-between">
                                            <span class="text-[10px] uppercase tracking-widest text-muted-foreground font-bold">State</span>
                                            <span class="text-xs font-bold text-foreground">{{ $order->shippingAddress->village?->state_name ?? $order->shippingAddress->state ?: '—' }}</span>
                                        </div>
                                        <div class="flex justify-between">
                                            <span class="text-[10px] uppercase tracking-widest text-muted-foreground font-bold">Pincode</span>
                                            <span class="text-xs font-bold font-mono text-foreground">{{ $order->shippingAddress->village?->pincode ?? $order->shippingAddress->pincode ?: '—' }}</span>
                                        </div>
                                    </div>
                                </div>
                            @elseif($order->shipping_address)
                                <p class="text-xs text-muted-foreground leading-relaxed">{{ $order->shipping_address }}</p>
                            @else
                                <p class="text-xs text-muted-foreground italic">No shipping address provided.</p>
                            @endif
                        </div>
                        <div class="p-6 space-y-4">
                            <h5 class="text-[9px] font-black uppercase tracking-widest text-muted-foreground flex items-center gap-1.5"><x-ui.icon name="file-text" size="3" /> Billing Address</h5>
                            @if($order->billingAddress)
                                <div>
                                    <p class="text-sm font-bold text-foreground mb-1">{{ $order->billingAddress->label ?? 'Billing' }}</p>
                                    <p class="text-xs text-muted-foreground font-medium mb-3">{{ $order->billingAddress->address_line_1 }}@if($order->billingAddress->address_line_2), {{ $order->billingAddress->address_line_2 }}@endif</p>
                                    <div class="pt-3 border-t border-border/40 space-y-2">
                                        <div class="flex justify-between">
                                            <span class="text-[10px] uppercase tracking-widest text-muted-foreground font-bold">Village</span>
                                            <span class="text-xs font-bold text-foreground">{{ $order->billingAddress->village?->village_name ?? $order->billingAddress->city ?: '—' }}</span>
                                        </div>
                                        <div class="flex justify-between">
                                            <span class="text-[10px] uppercase tracking-widest text-muted-foreground font-bold">Post Office</span>
                                            <span class="text-xs font-bold text-foreground">{{ $order->billingAddress->village?->post_so_name ?: '—' }}</span>
                                        </div>
                                        <div class="flex justify-between">
                                            <span class="text-[10px] uppercase tracking-widest text-muted-foreground font-bold">Taluka</span>
                                            <span class="text-xs font-bold text-foreground">{{ $order->billingAddress->village?->taluka_name ?: '—' }}</span>
                                        </div>
                                        <div class="flex justify-between">
                                            <span class="text-[10px] uppercase tracking-widest text-muted-foreground font-bold">District</span>
                                            <span class="text-xs font-bold text-foreground">{{ $order->billingAddress->village?->district_name ?? $order->billingAddress->city ?: '—' }}</span>
                                        </div>
                                        <div class="flex justify-between">
                                            <span class="text-[10px] uppercase tracking-widest text-muted-foreground font-bold">State</span>
                                            <span class="text-xs font-bold text-foreground">{{ $order->billingAddress->village?->state_name ?? $order->billingAddress->state ?: '—' }}</span>
                                        </div>
                                        <div class="flex justify-between">
                                            <span class="text-[10px] uppercase tracking-widest text-muted-foreground font-bold">Pincode</span>
                                            <span class="text-xs font-bold font-mono text-foreground">{{ $order->billingAddress->village?->pincode ?? $order->billingAddress->pincode ?: '—' }}</span>
                                        </div>
                                    </div>
                                </div>
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

                        @php
                            $paidAmount = $order->payments->where('status', 'completed')->sum('amount');
                            $dueAmount = max(0, $order->net_amount - $paidAmount);
                        @endphp

                        <div class="pt-4 mt-4 border-t border-border/40 grid grid-cols-2 gap-4">
                            <div class="p-3 rounded-xl bg-emerald-500/10 border border-emerald-500/20 text-center">
                                <span class="text-[9px] font-black uppercase tracking-widest text-emerald-600 block mb-1">Amount Paid</span>
                                <span class="text-sm font-black text-emerald-500">₹{{ number_format($paidAmount, 2) }}</span>
                            </div>
                            <div class="p-3 rounded-xl bg-orange-500/10 border border-orange-500/20 text-center">
                                <span class="text-[9px] font-black uppercase tracking-widest text-orange-600 block mb-1">Pending Due</span>
                                <span class="text-sm font-black text-orange-500">₹{{ number_format($dueAmount, 2) }}</span>
                            </div>
                        </div>
                    </div>
                    <div class="p-6 bg-primary/5 border-t border-primary/10">
                        <p class="text-xs text-primary/80 font-semibold flex items-center gap-2 justify-center text-center">
                            <x-ui.icon name="shield-check" size="4" /> Secure Order Record
                        </p>
                    </div>
                </x-ui.card>

                @if($order->payments->isNotEmpty())
                    <x-ui.card class="overflow-hidden border-border/60 shadow-xl bg-card/30 backdrop-blur-xl rounded-3xl">
                        <div class="p-5 border-b border-border/40 bg-muted/5">
                            <h4 class="text-[10px] font-black uppercase tracking-[0.2em] text-primary flex items-center gap-1.5">
                                <x-ui.icon name="list" size="3.5" /> Payment Ledger
                            </h4>
                        </div>
                        <div class="divide-y divide-border/40">
                            @foreach($order->payments as $payment)
                                <div class="p-4 flex items-center justify-between hover:bg-muted/5 transition-colors">
                                    <div class="flex items-center gap-3">
                                        @php
                                            $pColor = match($payment->status) {
                                                'completed' => 'emerald',
                                                'pending' => 'amber',
                                                'failed', 'refunded' => 'red',
                                                default => 'primary'
                                            };
                                        @endphp
                                        <div class="size-9 rounded-xl bg-{{ $pColor }}-500/10 text-{{ $pColor }}-500 flex items-center justify-center">
                                            <x-ui.icon name="{{ match($payment->status) { 'completed' => 'check-circle', 'pending' => 'clock', 'failed' => 'x-circle', 'refunded' => 'refresh-ccw', default => 'credit-card' } }}" size="4" />
                                        </div>
                                        <div>
                                            <p class="text-xs font-bold text-foreground">{{ $payment->payment_no }}</p>
                                            <p class="text-[10px] text-muted-foreground font-medium">{{ $payment->payment_date->format('M d, Y') }} • {{ $payment->payment_method }}</p>
                                        </div>
                                    </div>
                                    <div class="text-right">
                                        <p class="text-sm font-black text-foreground">₹{{ number_format($payment->amount, 2) }}</p>
                                        <span class="text-[9px] font-black uppercase tracking-widest text-{{ $pColor }}-500 bg-{{ $pColor }}-500/10 px-1.5 py-0.5 rounded">{{ $payment->status }}</span>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </x-ui.card>
                @endif

                <x-ui.card class="overflow-hidden border-border/60 shadow-xl bg-card/30 backdrop-blur-xl rounded-3xl p-6 space-y-4">
                    <h4 class="text-[10px] font-black uppercase tracking-[0.2em] text-primary flex items-center gap-1.5">
                        <x-ui.icon name="warehouse" size="3.5" /> Dispatch Hub (Warehouse)
                    </h4>
                    <div class="space-y-2">
                        <div class="flex justify-between items-center">
                            <span class="text-[10px] uppercase tracking-widest text-muted-foreground font-bold">Warehouse</span>
                            <span class="text-xs font-bold text-foreground">{{ $order->warehouse?->name ?? 'Default Warehouse' }}</span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-[10px] uppercase tracking-widest text-muted-foreground font-bold">Entity</span>
                            <span class="text-xs font-bold text-foreground">{{ $order->warehouse?->company_name ?: 'Krushify Agro Pvt. Ltd.' }}</span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-[10px] uppercase tracking-widest text-muted-foreground font-bold">GSTIN</span>
                            <span class="text-xs font-bold font-mono text-primary">{{ $order->warehouse?->gstin ?: '24AAMCK0386L1Z6' }}</span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-[10px] uppercase tracking-widest text-muted-foreground font-bold">Mobile</span>
                            <span class="text-xs font-bold text-foreground">{{ $order->warehouse?->phone ?: '+91 9199125925' }}</span>
                        </div>
                    </div>
                </x-ui.card>
            </div>
        </div>
    </div>

    <!-- Create Shipment Modal -->
    <x-ui.modal id="create-shipment-modal" maxWidth="md">
        <form action="{{ route('orders.ship', $order) }}" method="POST" class="p-6 space-y-4">
            @csrf
            <div>
                <h3 class="text-lg font-black text-foreground mb-1">Ready to Ship Details</h3>
                <p class="text-xs text-muted-foreground font-semibold uppercase tracking-wider">Order {{ $order->order_no }}</p>
            </div>
            
            <div class="h-px bg-border/60 w-full my-2"></div>
            
            <div class="space-y-4">
                <div class="space-y-2">
                    <label for="carrier_name" class="text-[10px] font-black uppercase tracking-widest text-muted-foreground/80">Shipping Company / Carrier</label>
                    <select id="carrier_name" name="carrier_name" class="h-11 w-full rounded-xl border border-input bg-background/50 px-3 py-2 text-sm text-foreground focus:outline-none focus-visible:ring-2 focus-visible:ring-primary/20 appearance-none cursor-pointer">
                        <option value="">-- Select Shipping Option --</option>
                        @foreach($services as $svc)
                            <option value="{{ $svc->name }}">{{ $svc->name }} ({{ $svc->code }})</option>
                        @endforeach
                    </select>
                </div>
                
                <div class="space-y-2">
                    <label for="tracking_no" class="text-[10px] font-black uppercase tracking-widest text-muted-foreground/80">Tracking ID</label>
                    <input type="text" id="tracking_no" name="tracking_no" placeholder="e.g. TRK123456789" class="h-11 w-full rounded-xl border border-input bg-background/50 px-3 py-2 text-sm text-foreground focus:outline-none focus-visible:ring-2 focus-visible:ring-primary/20">
                </div>
            </div>
            
            <div class="flex items-center justify-end gap-3 pt-4 border-t border-border/40">
                <x-ui.button type="button" variant="outline" size="sm" @click="$dispatch('close-modal', { name: 'create-shipment-modal' })" class="rounded-xl font-bold uppercase tracking-widest text-[10px] h-10">
                    Cancel
                </x-ui.button>
                <x-ui.button type="submit" size="sm" class="rounded-xl font-bold uppercase tracking-widest text-[10px] h-10 bg-indigo-500 hover:bg-indigo-600 text-white shadow-lg shadow-indigo-500/20">
                    <x-ui.icon name="package" size="3" class="mr-2" /> Mark Ready to Ship
                </x-ui.button>
            </div>
        </form>
    </x-ui.modal>
</x-layouts.app>
