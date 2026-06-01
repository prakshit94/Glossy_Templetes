<x-layouts.app pageTitle="Shipment Tracking: #{{ $shipment->shipment_no }}">
    @php
        $order = $shipment->order;
        $party = $order?->party;
        $warehouse = $order?->warehouse;
        $tracking = $tracking ?? $shipment->deliveryTracking;

        $statusStyles = [
            'pending' => [
                'badge' => 'bg-amber-500/10 text-amber-600 border-amber-500/25',
                'icon' => 'bg-amber-500/10 text-amber-600 border-amber-500/20',
                'dot' => 'bg-amber-500',
            ],
            'shipped' => [
                'badge' => 'bg-blue-500/10 text-blue-600 border-blue-500/25',
                'icon' => 'bg-blue-500/10 text-blue-600 border-blue-500/20',
                'dot' => 'bg-blue-500',
            ],
            'in_transit' => [
                'badge' => 'bg-indigo-500/10 text-indigo-600 border-indigo-500/25',
                'icon' => 'bg-indigo-500/10 text-indigo-600 border-indigo-500/20',
                'dot' => 'bg-indigo-500',
            ],
            'delivered' => [
                'badge' => 'bg-emerald-500/10 text-emerald-600 border-emerald-500/25',
                'icon' => 'bg-emerald-500/10 text-emerald-600 border-emerald-500/20',
                'dot' => 'bg-emerald-500',
            ],
            'failed' => [
                'badge' => 'bg-red-500/10 text-red-600 border-red-500/25',
                'icon' => 'bg-red-500/10 text-red-600 border-red-500/20',
                'dot' => 'bg-red-500',
            ],
        ];
        $statusStyle = $statusStyles[$shipment->status] ?? [
            'badge' => 'bg-muted text-muted-foreground border-border',
            'icon' => 'bg-muted text-muted-foreground border-border',
            'dot' => 'bg-muted-foreground',
        ];

        $deliveryStartedAt = $shipment->shipped_at ?? $shipment->created_at;
        $deliveryEndedAt = $shipment->delivered_at;
        $durationLabel = $deliveryEndedAt
            ? $deliveryStartedAt->diffForHumans($deliveryEndedAt, true)
            : $deliveryStartedAt->diffForHumans(null, true) . ' active';
    @endphp

    <div class="p-6 lg:p-8 max-w-7xl mx-auto space-y-6">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
            <div class="flex items-start gap-4">
                <div class="size-14 rounded-2xl border flex items-center justify-center shadow-sm {{ $statusStyle['icon'] }}">
                    <x-ui.icon name="target" size="7" />
                </div>
                <div class="min-w-0">
                    <div class="flex flex-wrap items-center gap-3">
                        <h1 class="text-2xl font-black tracking-tight text-foreground">#{{ $shipment->shipment_no }}</h1>
                        <span class="inline-flex items-center gap-2 rounded-full border px-3 py-1 text-[10px] font-black uppercase tracking-widest {{ $statusStyle['badge'] }}">
                            <span class="size-1.5 rounded-full {{ $statusStyle['dot'] }}"></span>
                            {{ str_replace('_', ' ', $shipment->status) }}
                        </span>
                    </div>
                    <div class="mt-2 flex flex-wrap items-center gap-x-4 gap-y-1 text-[11px] font-bold uppercase tracking-widest text-muted-foreground">
                        <span class="inline-flex items-center gap-1.5"><x-ui.icon name="calendar" size="3" /> Created {{ $shipment->created_at->format('M d, Y') }}</span>
                        <span class="inline-flex items-center gap-1.5"><x-ui.icon name="hash" size="3" /> {{ $shipment->tracking_no ?: 'Tracking number pending' }}</span>
                        <span class="inline-flex items-center gap-1.5"><x-ui.icon name="clock" size="3" /> {{ $durationLabel }}</span>
                    </div>
                </div>
            </div>

            <div class="flex flex-wrap items-center gap-2">
                <a href="{{ route('order.tracking.index') }}">
                    <x-ui.button variant="outline" size="sm" class="rounded-xl h-10 text-[10px] font-black uppercase tracking-widest">
                        <x-ui.icon name="arrow-left" size="3.5" class="mr-2" /> Back
                    </x-ui.button>
                </a>
                @if($order)
                    <a href="{{ route('orders.show', $order->id) }}">
                        <x-ui.button size="sm" class="rounded-xl h-10 text-[10px] font-black uppercase tracking-widest">
                            <x-ui.icon name="external-link" size="3.5" class="mr-2" /> Order {{ $order->order_no }}
                        </x-ui.button>
                    </a>
                @endif
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-4">
            <x-ui.card class="p-5 border-border/60 bg-card/50 rounded-2xl">
                <p class="text-[10px] font-black uppercase tracking-widest text-muted-foreground">Carrier</p>
                <p class="mt-2 text-sm font-black text-foreground">{{ $shipment->carrier_name ?: 'Not assigned' }}</p>
                <p class="mt-1 text-[11px] font-mono font-bold text-muted-foreground">{{ $shipment->tracking_no ?: 'AWB pending' }}</p>
            </x-ui.card>
            <x-ui.card class="p-5 border-border/60 bg-card/50 rounded-2xl">
                <p class="text-[10px] font-black uppercase tracking-widest text-muted-foreground">Customer</p>
                <p class="mt-2 text-sm font-black text-foreground truncate">{{ $party?->name ?? $party?->company_name ?? 'Unknown customer' }}</p>
                <p class="mt-1 text-[11px] font-bold text-muted-foreground">{{ $party?->phone ?? $party?->email ?? 'No contact' }}</p>
            </x-ui.card>
            <x-ui.card class="p-5 border-border/60 bg-card/50 rounded-2xl">
                <p class="text-[10px] font-black uppercase tracking-widest text-muted-foreground">Handler</p>
                <p class="mt-2 text-sm font-black text-foreground truncate">{{ $tracking?->assigned_name ?? 'Not synced' }}</p>
                <p class="mt-1 text-[11px] font-bold text-muted-foreground">{{ $tracking?->vehicle_number ?? str_replace('_', ' ', $tracking?->dispatch_type ?? 'Unassigned') }}</p>
            </x-ui.card>
            <x-ui.card class="p-5 border-border/60 bg-card/50 rounded-2xl">
                <p class="text-[10px] font-black uppercase tracking-widest text-muted-foreground">Delivered</p>
                <p class="mt-2 text-sm font-black text-foreground">{{ $shipment->delivered_at?->format('M d, Y') ?? 'Pending' }}</p>
                <p class="mt-1 text-[11px] font-bold text-muted-foreground">{{ $shipment->delivered_at?->format('h:i A') ?? 'Awaiting completion' }}</p>
            </x-ui.card>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <div class="lg:col-span-2 space-y-6">
                <x-ui.card class="overflow-hidden border-border/60 bg-card/40 rounded-2xl shadow-sm">
                    <div class="p-5 border-b border-border/50 flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
                        <div>
                            <h2 class="text-sm font-black uppercase tracking-widest text-foreground">Route Overview</h2>
                            <p class="text-xs text-muted-foreground mt-1">Warehouse to customer delivery path</p>
                        </div>
                        <span class="text-[10px] font-black uppercase tracking-widest text-muted-foreground">{{ $shipment->events->count() }} milestones</span>
                    </div>
                    <div class="p-6">
                        <div class="grid grid-cols-1 md:grid-cols-[1fr_auto_1fr] gap-6 items-stretch">
                            <div class="rounded-2xl border border-border/60 bg-background/40 p-5">
                                <div class="flex items-center gap-3">
                                    <div class="size-11 rounded-xl bg-primary/10 text-primary flex items-center justify-center">
                                        <x-ui.icon name="warehouse" size="5" />
                                    </div>
                                    <div class="min-w-0">
                                        <p class="text-[10px] font-black uppercase tracking-widest text-muted-foreground">Origin</p>
                                        <p class="text-sm font-black text-foreground truncate">{{ $warehouse?->name ?? 'Regional Hub' }}</p>
                                    </div>
                                </div>
                                <p class="mt-4 text-xs font-medium text-muted-foreground leading-relaxed">{{ $warehouse?->address_line_1 ?? $warehouse?->address ?? 'Warehouse address not available' }}</p>
                            </div>

                            <div class="hidden md:flex items-center justify-center text-muted-foreground">
                                <div class="flex items-center gap-2">
                                    <span class="w-10 h-px bg-border"></span>
                                    <x-ui.icon name="truck" size="5" />
                                    <span class="w-10 h-px bg-border"></span>
                                </div>
                            </div>

                            <div class="rounded-2xl border border-border/60 bg-background/40 p-5">
                                <div class="flex items-center gap-3">
                                    <div class="size-11 rounded-xl bg-emerald-500/10 text-emerald-600 flex items-center justify-center">
                                        <x-ui.icon name="map-pin" size="5" />
                                    </div>
                                    <div class="min-w-0">
                                        <p class="text-[10px] font-black uppercase tracking-widest text-muted-foreground">Destination</p>
                                        <p class="text-sm font-black text-foreground truncate">{{ $party?->name ?? $party?->company_name ?? 'Customer' }}</p>
                                    </div>
                                </div>
                                <p class="mt-4 text-xs font-medium text-muted-foreground leading-relaxed">{{ $order?->shipping_address ?: 'Shipping address not available' }}</p>
                            </div>
                        </div>
                    </div>
                </x-ui.card>

                @if($tracking)
                    <x-ui.card class="overflow-hidden border-border/60 bg-card/40 rounded-2xl shadow-sm">
                        <div class="p-5 border-b border-border/50 flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
                            <div>
                                <h2 class="text-sm font-black uppercase tracking-widest text-foreground">Order Delivery Tracking</h2>
                                <p class="text-xs text-muted-foreground mt-1">Order-level sync and immutable status history</p>
                            </div>
                            <span class="inline-flex w-fit rounded-full border px-3 py-1 text-[10px] font-black uppercase tracking-widest {{ $statusStyle['badge'] }}">
                                {{ str_replace('_', ' ', $tracking->current_status) }}
                            </span>
                        </div>
                        <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-5 gap-4 p-5 border-b border-border/40">
                            <div><p class="text-[9px] font-black uppercase tracking-widest text-muted-foreground">Dispatch</p><p class="text-sm font-black mt-1">{{ strtoupper($tracking->dispatch_type) }}</p></div>
                            <div><p class="text-[9px] font-black uppercase tracking-widest text-muted-foreground">Handler</p><p class="text-sm font-black mt-1 truncate">{{ $tracking->assigned_name }}</p></div>
                            <div><p class="text-[9px] font-black uppercase tracking-widest text-muted-foreground">Tracking</p><p class="text-sm font-mono font-black mt-1 truncate">{{ $tracking->tracking_number ?? '—' }}</p></div>
                            <div><p class="text-[9px] font-black uppercase tracking-widest text-muted-foreground">Vehicle</p><p class="text-sm font-black mt-1 truncate">{{ $tracking->vehicle_number ?? '—' }}</p></div>
                            <div><p class="text-[9px] font-black uppercase tracking-widest text-muted-foreground">Last Update</p><p class="text-sm font-black mt-1">{{ $tracking->last_status_at?->format('M d, h:i A') ?? '—' }}</p></div>
                        </div>
                        <div class="p-5 space-y-3">
                            @forelse($tracking->histories as $history)
                                <div class="flex gap-3 rounded-xl border border-border/50 bg-background/35 p-4">
                                    <div class="size-9 rounded-lg bg-primary/10 text-primary flex items-center justify-center shrink-0">
                                        <x-ui.icon name="activity" size="4" />
                                    </div>
                                    <div class="min-w-0">
                                        <p class="text-sm font-black text-foreground">
                                            {{ $history->previous_status ? str_replace('_', ' ', $history->previous_status) . ' to ' : '' }}{{ str_replace('_', ' ', $history->new_status) }}
                                        </p>
                                        <p class="text-[10px] font-bold uppercase tracking-widest text-muted-foreground mt-1">
                                            {{ $history->changed_at->format('M d, Y h:i A') }} · {{ $history->user?->name ?? 'System' }}
                                        </p>
                                        @if($history->remarks)
                                            <p class="text-xs text-muted-foreground mt-2">{{ $history->remarks }}</p>
                                        @endif
                                    </div>
                                </div>
                            @empty
                                <p class="text-sm text-muted-foreground py-4">No tracking history entries yet.</p>
                            @endforelse
                        </div>
                    </x-ui.card>
                @endif

                <x-ui.card class="overflow-hidden border-border/60 bg-card/40 rounded-2xl shadow-sm">
                    <div class="p-5 border-b border-border/50 flex items-center justify-between">
                        <div>
                            <h2 class="text-sm font-black uppercase tracking-widest text-foreground">Consignment Contents</h2>
                            <p class="text-xs text-muted-foreground mt-1">{{ $order?->items?->count() ?? 0 }} order line items</p>
                        </div>
                        <x-ui.icon name="box" size="5" class="text-primary" />
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full text-left">
                            <thead class="bg-muted/5 border-b border-border/40">
                                <tr>
                                    <th class="px-5 py-3 text-[10px] font-black uppercase tracking-widest text-muted-foreground">Product</th>
                                    <th class="px-5 py-3 text-[10px] font-black uppercase tracking-widest text-muted-foreground text-center">Qty</th>
                                    <th class="px-5 py-3 text-[10px] font-black uppercase tracking-widest text-muted-foreground text-right">Status</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-border/30">
                                @forelse($order?->items ?? [] as $item)
                                    <tr class="hover:bg-muted/5">
                                        <td class="px-5 py-4">
                                            <p class="text-sm font-black text-foreground">{{ $item->product?->name ?? 'Consignment item' }}</p>
                                            <p class="text-[10px] font-bold uppercase tracking-widest text-muted-foreground">{{ $item->product?->sku ?? 'N/A' }}</p>
                                        </td>
                                        <td class="px-5 py-4 text-center">
                                            <span class="inline-flex min-w-8 justify-center rounded-lg bg-muted px-2 py-1 text-xs font-black">{{ (int) $item->quantity }}</span>
                                        </td>
                                        <td class="px-5 py-4 text-right">
                                            <span class="text-[10px] font-black uppercase tracking-widest {{ str_contains($statusStyle['badge'], 'emerald') ? 'text-emerald-600' : 'text-muted-foreground' }}">{{ str_replace('_', ' ', $shipment->status) }}</span>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="3" class="px-5 py-10 text-center text-sm text-muted-foreground">No order items found.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </x-ui.card>

                <x-ui.card class="overflow-hidden border-border/60 bg-card/40 rounded-2xl shadow-sm">
                    <div class="p-5 border-b border-border/50 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                        <div>
                            <h2 class="text-sm font-black uppercase tracking-widest text-foreground">Milestone Timeline</h2>
                            <p class="text-xs text-muted-foreground mt-1">Shipment tracking events and edits</p>
                        </div>
                        <span class="rounded-full bg-primary/10 text-primary px-3 py-1 text-[10px] font-black uppercase tracking-widest">{{ $shipment->events->count() }} events</span>
                    </div>
                    <div class="p-5">
                        <div class="relative space-y-5 before:absolute before:left-4 before:top-2 before:bottom-2 before:w-px before:bg-border">
                            @forelse($shipment->events as $event)
                                <div x-data="{ editing: false }" class="relative pl-10">
                                    <div class="absolute left-0 top-1 size-8 rounded-full bg-background border border-border flex items-center justify-center text-primary">
                                        <span class="size-2 rounded-full bg-primary"></span>
                                    </div>

                                    <div x-show="!editing" class="rounded-xl border border-border/50 bg-background/35 p-4">
                                        <div class="flex flex-col gap-3 md:flex-row md:items-start md:justify-between">
                                            <div class="min-w-0">
                                                <div class="flex flex-wrap items-center gap-2">
                                                    <h3 class="text-sm font-black text-foreground">{{ $event->event_name }}</h3>
                                                    <span class="text-[10px] font-bold text-muted-foreground">{{ $event->occurred_at->format('M d, Y h:i A') }}</span>
                                                </div>
                                                <p class="mt-2 inline-flex items-center gap-1.5 text-[10px] font-black uppercase tracking-widest text-muted-foreground">
                                                    <x-ui.icon name="map-pin" size="3" /> {{ $event->location ?: 'Logistics node' }}
                                                </p>
                                                @if($event->description)
                                                    <p class="mt-3 text-xs leading-relaxed text-muted-foreground">{{ $event->description }}</p>
                                                @endif
                                            </div>
                                            <div class="flex items-center gap-2 shrink-0">
                                                <button type="button" @click="editing = true" class="size-8 rounded-lg border border-border bg-background/50 text-muted-foreground hover:text-primary hover:border-primary/40 transition-colors">
                                                    <x-ui.icon name="edit-3" size="3.5" class="mx-auto" />
                                                </button>
                                                <form action="{{ route('order.tracking.events.destroy', $event->id) }}" method="POST" onsubmit="return confirm('Delete this checkpoint?')" class="m-0">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="size-8 rounded-lg border border-border bg-background/50 text-muted-foreground hover:text-red-600 hover:border-red-500/40 transition-colors">
                                                        <x-ui.icon name="trash" size="3.5" class="mx-auto" />
                                                    </button>
                                                </form>
                                            </div>
                                        </div>
                                    </div>

                                    <div x-show="editing" x-cloak class="rounded-xl border border-border/60 bg-background/60 p-4">
                                        <form action="{{ route('order.tracking.events.update', $event->id) }}" method="POST" class="space-y-4">
                                            @csrf
                                            @method('PUT')
                                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                                <div class="space-y-1.5">
                                                    <label class="text-[10px] font-black uppercase tracking-widest text-muted-foreground">Milestone</label>
                                                    <input type="text" name="event_name" value="{{ $event->event_name }}" required class="w-full h-10 px-3 rounded-lg border border-border bg-background text-xs font-bold outline-none focus:ring-2 focus:ring-primary/20">
                                                </div>
                                                <div class="space-y-1.5">
                                                    <label class="text-[10px] font-black uppercase tracking-widest text-muted-foreground">Location</label>
                                                    <input type="text" name="location" value="{{ $event->location }}" class="w-full h-10 px-3 rounded-lg border border-border bg-background text-xs font-bold outline-none focus:ring-2 focus:ring-primary/20">
                                                </div>
                                            </div>
                                            <div class="space-y-1.5">
                                                <label class="text-[10px] font-black uppercase tracking-widest text-muted-foreground">Time</label>
                                                <input type="datetime-local" name="occurred_at" value="{{ $event->occurred_at->format('Y-m-d\TH:i') }}" required class="w-full h-10 px-3 rounded-lg border border-border bg-background text-xs font-bold outline-none focus:ring-2 focus:ring-primary/20">
                                            </div>
                                            <div class="space-y-1.5">
                                                <label class="text-[10px] font-black uppercase tracking-widest text-muted-foreground">Remarks</label>
                                                <textarea name="description" rows="2" class="w-full px-3 py-2 rounded-lg border border-border bg-background text-xs font-medium outline-none focus:ring-2 focus:ring-primary/20 resize-none">{{ $event->description }}</textarea>
                                            </div>
                                            <div class="flex justify-end gap-2">
                                                <x-ui.button type="button" variant="outline" size="sm" @click="editing = false" class="rounded-lg text-[10px] font-black uppercase tracking-widest">Cancel</x-ui.button>
                                                <x-ui.button type="submit" size="sm" class="rounded-lg text-[10px] font-black uppercase tracking-widest">Save</x-ui.button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            @empty
                                <div class="rounded-xl border border-dashed border-border p-10 text-center">
                                    <x-ui.icon name="list" size="8" class="mx-auto text-muted-foreground/40 mb-3" />
                                    <p class="text-sm font-black uppercase tracking-widest text-muted-foreground">No milestones yet</p>
                                </div>
                            @endforelse
                        </div>
                    </div>
                </x-ui.card>
            </div>

            <div class="space-y-6">
                <x-ui.card class="overflow-hidden border-border/60 bg-card/50 rounded-2xl shadow-sm">
                    <div class="p-5 border-b border-border/50">
                        <h2 class="text-sm font-black uppercase tracking-widest text-foreground">Shipment Control</h2>
                        <p class="text-xs text-muted-foreground mt-1">Update carrier and delivery state</p>
                    </div>
                    <form action="{{ route('order.tracking.status.update', $shipment->id) }}" method="POST" class="p-5 space-y-4">
                        @csrf
                        @method('PUT')
                        <div class="space-y-1.5">
                            <label class="text-[10px] font-black uppercase tracking-widest text-muted-foreground">Status</label>
                            <select name="status" class="w-full h-11 px-3 rounded-xl border border-border bg-background text-xs font-black uppercase tracking-widest outline-none focus:ring-2 focus:ring-primary/20">
                                @foreach(['pending', 'shipped', 'in_transit', 'delivered', 'failed'] as $st)
                                    <option value="{{ $st }}" @selected($shipment->status === $st)>{{ str_replace('_', ' ', $st) }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="space-y-1.5">
                            <label class="text-[10px] font-black uppercase tracking-widest text-muted-foreground">Carrier</label>
                            <select name="carrier_name" class="w-full h-11 px-3 rounded-xl border border-border bg-background text-xs font-bold outline-none focus:ring-2 focus:ring-primary/20">
                                <option value="">Select shipping option</option>
                                @foreach($services as $svc)
                                    <option value="{{ $svc->name }}" @selected($shipment->carrier_name === $svc->name)>{{ $svc->name }} ({{ $svc->code }})</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="space-y-1.5">
                            <label class="text-[10px] font-black uppercase tracking-widest text-muted-foreground">Tracking Number</label>
                            <input type="text" name="tracking_no" value="{{ $shipment->tracking_no }}" placeholder="Enter AWB or tracking number" class="w-full h-11 px-3 rounded-xl border border-border bg-background text-xs font-mono font-bold outline-none focus:ring-2 focus:ring-primary/20">
                        </div>
                        <div class="grid grid-cols-1 gap-4">
                            <div class="space-y-1.5">
                                <label class="text-[10px] font-black uppercase tracking-widest text-muted-foreground">Shipped At</label>
                                <input type="datetime-local" name="shipped_at" value="{{ $shipment->shipped_at?->format('Y-m-d\TH:i') }}" class="w-full h-11 px-3 rounded-xl border border-border bg-background text-xs font-bold outline-none focus:ring-2 focus:ring-primary/20">
                            </div>
                            <div class="space-y-1.5">
                                <label class="text-[10px] font-black uppercase tracking-widest text-muted-foreground">Delivered At</label>
                                <input type="datetime-local" name="delivered_at" value="{{ $shipment->delivered_at?->format('Y-m-d\TH:i') }}" class="w-full h-11 px-3 rounded-xl border border-border bg-background text-xs font-bold outline-none focus:ring-2 focus:ring-primary/20">
                            </div>
                        </div>
                        <x-ui.button type="submit" class="w-full h-11 rounded-xl text-[10px] font-black uppercase tracking-widest">
                            <x-ui.icon name="save" size="3.5" class="mr-2" /> Update Shipment
                        </x-ui.button>
                    </form>
                </x-ui.card>

                <x-ui.card class="overflow-hidden border-border/60 bg-card/50 rounded-2xl shadow-sm">
                    <div class="p-5 border-b border-border/50">
                        <h2 class="text-sm font-black uppercase tracking-widest text-foreground">Add Milestone</h2>
                        <p class="text-xs text-muted-foreground mt-1">Create a new tracking event</p>
                    </div>
                    <form action="{{ route('order.tracking.events.store', $shipment->id) }}" method="POST" class="p-5 space-y-4">
                        @csrf
                        <div class="space-y-1.5">
                            <label class="text-[10px] font-black uppercase tracking-widest text-muted-foreground">Milestone</label>
                            <input type="text" name="event_name" placeholder="e.g. Out for delivery" required class="w-full h-11 px-3 rounded-xl border border-border bg-background text-xs font-bold outline-none focus:ring-2 focus:ring-primary/20">
                        </div>
                        <div class="space-y-1.5">
                            <label class="text-[10px] font-black uppercase tracking-widest text-muted-foreground">Location</label>
                            <input type="text" name="location" placeholder="e.g. Pune Hub" class="w-full h-11 px-3 rounded-xl border border-border bg-background text-xs font-bold outline-none focus:ring-2 focus:ring-primary/20">
                        </div>
                        <div class="space-y-1.5">
                            <label class="text-[10px] font-black uppercase tracking-widest text-muted-foreground">Occurred At</label>
                            <input type="datetime-local" name="occurred_at" value="{{ now()->format('Y-m-d\TH:i') }}" required class="w-full h-11 px-3 rounded-xl border border-border bg-background text-xs font-bold outline-none focus:ring-2 focus:ring-primary/20">
                        </div>
                        <div class="space-y-1.5">
                            <label class="text-[10px] font-black uppercase tracking-widest text-muted-foreground">Remarks</label>
                            <textarea name="description" rows="3" placeholder="Additional logistics context" class="w-full px-3 py-2 rounded-xl border border-border bg-background text-xs font-medium outline-none focus:ring-2 focus:ring-primary/20 resize-none"></textarea>
                        </div>
                        <x-ui.button type="submit" variant="outline" class="w-full h-11 rounded-xl text-[10px] font-black uppercase tracking-widest">
                            <x-ui.icon name="plus" size="3.5" class="mr-2" /> Add Milestone
                        </x-ui.button>
                    </form>
                </x-ui.card>

                <x-ui.card class="p-5 border-emerald-500/20 bg-emerald-500/5 rounded-2xl">
                    <div class="flex items-start gap-3">
                        <div class="size-10 rounded-xl bg-emerald-500/10 text-emerald-600 flex items-center justify-center shrink-0">
                            <x-ui.icon name="shield-check" size="5" />
                        </div>
                        <div>
                            <h3 class="text-[10px] font-black uppercase tracking-widest text-emerald-700">Delivery Sync</h3>
                            <p class="mt-2 text-xs font-medium leading-relaxed text-muted-foreground">Changing status to delivered finalizes the related order using the existing order status workflow.</p>
                        </div>
                    </div>
                </x-ui.card>
            </div>
        </div>
    </div>
</x-layouts.app>
