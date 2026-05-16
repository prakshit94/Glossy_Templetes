<x-layouts.app pageTitle="Shipment Tracking: #{{ $shipment->shipment_no }}">

    <div class="p-6 lg:p-10 max-w-7xl mx-auto space-y-6">
        <!-- Header Card -->
        <x-ui.card class="overflow-hidden border-border/60 shadow-2xl bg-card/30 backdrop-blur-2xl rounded-3xl">
            <div class="p-8 flex flex-col md:flex-row md:items-center justify-between gap-6">
                <div class="flex items-center gap-5">
                    <div class="size-16 rounded-2xl bg-primary/10 border border-primary/20 text-primary flex items-center justify-center shadow-inner">
                        <x-ui.icon name="target" size="7" />
                    </div>
                    <div>
                        <div class="flex items-center gap-3 mb-1">
                            <h3 class="text-2xl font-black text-foreground tracking-tight">{{ $shipment->shipment_no }}</h3>
                            @php
                                $statusColors = [
                                    'pending' => 'warning',
                                    'shipped' => 'primary',
                                    'in_transit' => 'indigo',
                                    'delivered' => 'success',
                                    'failed' => 'destructive',
                                ];
                                $variant = $statusColors[$shipment->status] ?? 'outline';
                            @endphp
                            <x-ui.badge variant="{{ $variant }}" class="rounded-lg px-2 py-0.5 text-[10px] font-black uppercase tracking-widest">
                                {{ $shipment->status }}
                            </x-ui.badge>
                        </div>
                        <p class="text-xs text-muted-foreground font-medium flex items-center gap-2">
                            Initialized on {{ $shipment->created_at->format('M d, Y • h:i A') }}
                            <span class="size-1 rounded-full bg-muted-foreground/30"></span>
                            Track ID: {{ $shipment->tracking_no ?? 'UNASSIGNED' }}
                        </p>
                    </div>
                </div>

                <div class="flex flex-wrap items-center gap-3">
                    <a href="{{ route('order.tracking.index') }}">
                        <x-ui.button variant="outline" size="sm" class="rounded-xl font-bold uppercase tracking-widest text-[10px] h-10 px-6">
                            <x-ui.icon name="arrow-left" size="3" class="mr-2" /> Back to Tracking
                        </x-ui.button>
                    </a>
                </div>
            </div>
        </x-ui.card>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Route & Logistics Details -->
            <x-ui.card class="lg:col-span-2 overflow-hidden border-border/60 shadow-xl bg-card/30 backdrop-blur-xl rounded-3xl">
                <div class="p-6 border-b border-border/40 bg-muted/5">
                    <h4 class="text-[10px] font-black uppercase tracking-[0.2em] text-primary">Logistics Route</h4>
                </div>
                <div class="p-8 flex items-center justify-between relative">
                    <div class="flex flex-col items-center gap-3 z-10">
                        <div class="size-12 rounded-xl bg-background border border-border flex items-center justify-center shadow-sm">
                            <x-ui.icon name="home" size="5" class="text-muted-foreground" />
                        </div>
                        <div class="text-center">
                            <p class="text-[10px] font-black uppercase text-muted-foreground tracking-widest mb-1">Source</p>
                            <p class="text-sm font-bold text-foreground">{{ $shipment->order->warehouse->name ?? 'Primary Warehouse' }}</p>
                            <p class="text-[10px] text-muted-foreground">{{ $shipment->order->warehouse->code ?? 'WH-001' }}</p>
                        </div>
                    </div>

                    <div class="flex-1 flex flex-col items-center gap-2 px-10">
                        <div class="w-full h-px bg-gradient-to-r from-transparent via-primary/30 to-transparent relative">
                            <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 size-8 rounded-full bg-primary/10 border border-primary/20 flex items-center justify-center">
                                <x-ui.icon name="truck" size="4" class="text-primary animate-pulse" />
                            </div>
                        </div>
                        <span class="text-[9px] font-black uppercase tracking-widest text-primary/60">
                            {{ $shipment->carrier_name ?? 'Courier Pending' }}
                        </span>
                    </div>

                    <div class="flex flex-col items-center gap-3 z-10">
                        <div class="size-12 rounded-xl bg-background border border-border flex items-center justify-center shadow-sm">
                            <x-ui.icon name="map-pin" size="5" class="text-emerald-500" />
                        </div>
                        <div class="text-center">
                            <p class="text-[10px] font-black uppercase text-muted-foreground tracking-widest mb-1">Destination</p>
                            <p class="text-sm font-bold text-foreground">{{ $shipment->order->party->name ?? 'Customer' }}</p>
                            <p class="text-[10px] text-muted-foreground">Order: {{ $shipment->order->order_no }}</p>
                        </div>
                    </div>
                </div>
            </x-ui.card>

            <!-- Admin Status Control -->
            <x-ui.card class="overflow-hidden border-border/60 shadow-xl bg-card/30 backdrop-blur-xl rounded-3xl">
                <div class="p-6 border-b border-border/40 bg-muted/5">
                    <h4 class="text-[10px] font-black uppercase tracking-[0.2em] text-primary">Quick Update</h4>
                </div>
                <div class="p-6">
                    <form action="{{ route('order.tracking.status.update', $shipment->id) }}" method="POST" class="space-y-4">
                        @csrf
                        @method('PUT')
                        <div class="space-y-2">
                            <label class="text-[9px] font-black uppercase tracking-widest text-muted-foreground ml-1">Logistics Status</label>
                            <select name="status" class="w-full h-11 px-4 rounded-xl border border-border bg-background/50 text-xs font-black uppercase tracking-widest focus:ring-2 focus:ring-primary/20 transition-all outline-none">
                                @foreach(['pending', 'shipped', 'in_transit', 'delivered', 'failed'] as $st)
                                    <option value="{{ $st }}" {{ $shipment->status === $st ? 'selected' : '' }}>{{ strtoupper($st) }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="space-y-2">
                            <label class="text-[9px] font-black uppercase tracking-widest text-muted-foreground ml-1">Tracking Number</label>
                            <input type="text" name="tracking_no" value="{{ $shipment->tracking_no }}" placeholder="Enter AWB / Tracking #" 
                                class="w-full h-11 px-4 rounded-xl border border-border bg-background/50 text-xs font-black font-mono tracking-tighter focus:ring-2 focus:ring-primary/20 transition-all outline-none">
                        </div>
                        <x-ui.button type="submit" class="w-full h-11 rounded-xl text-[10px] font-black uppercase tracking-[0.2em] shadow-lg shadow-primary/20">
                            Update Logistics
                        </x-ui.button>
                    </form>
                </div>
            </x-ui.card>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Timeline -->
            <x-ui.card class="lg:col-span-2 overflow-hidden border-border/60 shadow-xl bg-card/30 backdrop-blur-xl rounded-[2.5rem] relative">
                <div class="p-8 border-b border-border/40 bg-muted/5 flex items-center justify-between">
                    <h4 class="text-lg font-black text-foreground tracking-tight">Tracking Milestones</h4>
                    <span class="px-3 py-1 rounded-full bg-primary/10 border border-primary/20 text-[9px] font-black text-primary uppercase tracking-widest">
                        {{ $shipment->events->count() }} Events
                    </span>
                </div>
                
                <div class="p-10 relative overflow-y-auto max-h-[600px] scrollbar-thin scrollbar-thumb-primary/20 scrollbar-track-transparent">
                    <div class="relative space-y-10 before:content-[''] before:absolute before:left-[17px] before:top-2 before:bottom-2 before:w-[2px] before:bg-gradient-to-b before:from-primary/50 before:via-primary/20 before:to-transparent">
                        @forelse($shipment->events as $event)
                            <div class="relative pl-12 group">
                                <div class="absolute left-0 top-1.5 size-[36px] rounded-full bg-background border-4 border-muted/20 flex items-center justify-center text-primary shadow-lg z-10 group-hover:scale-110 transition-transform duration-300">
                                    <x-ui.icon name="check" size="4" />
                                </div>
                                <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                                    <div class="flex-1">
                                        <h4 class="text-sm font-black text-foreground tracking-tight group-hover:text-primary transition-colors duration-300">{{ $event->event_name }}</h4>
                                        <div class="flex items-center gap-3 mt-1.5">
                                            <p class="text-[9px] font-black text-muted-foreground uppercase tracking-widest flex items-center gap-1.5">
                                                <x-ui.icon name="map-pin" size="2.5" class="text-primary/60" />
                                                {{ $event->location ?? 'Global Hub' }}
                                            </p>
                                            <span class="size-1 rounded-full bg-muted-foreground/20"></span>
                                            <p class="text-[9px] font-black text-primary uppercase tracking-widest">
                                                {{ $event->occurred_at->format('M d, h:i A') }}
                                            </p>
                                        </div>
                                        @if($event->description)
                                            <div class="mt-3 p-4 rounded-2xl bg-muted/5 border border-border/30 backdrop-blur-sm group-hover:border-primary/20 transition-colors duration-300">
                                                <p class="text-xs text-muted-foreground leading-relaxed italic">"{{ $event->description }}"</p>
                                            </div>
                                        @endif
                                    </div>
                                    <div class="shrink-0 text-right">
                                        <p class="text-[9px] font-black text-muted-foreground/40 uppercase tracking-widest">{{ $event->occurred_at->diffForHumans() }}</p>
                                    </div>
                                </div>
                            </div>
                        @empty
                            <div class="text-center py-20 opacity-20">
                                <x-ui.icon name="clock" size="16" class="mx-auto mb-4" />
                                <p class="text-sm font-black uppercase tracking-[0.3em]">No Milestones Recorded</p>
                            </div>
                        @endforelse
                    </div>
                </div>
            </x-ui.card>

            <!-- Add Log Entry Form -->
            <div class="space-y-6">
                <x-ui.card class="p-8 border-border/60 bg-card/40 backdrop-blur-2xl rounded-[2.5rem] shadow-2xl border-dashed">
                    <div class="flex items-center gap-4 mb-8">
                        <div class="size-10 rounded-xl bg-primary/10 text-primary flex items-center justify-center">
                            <x-ui.icon name="plus" size="5" />
                        </div>
                        <h4 class="text-lg font-black text-foreground tracking-tight">Log Event</h4>
                    </div>

                    <form action="{{ route('order.tracking.events.store', $shipment->id) }}" method="POST" class="space-y-6">
                        @csrf
                        <div class="space-y-2">
                            <label class="text-[10px] font-black uppercase tracking-widest text-muted-foreground ml-1">Event Action</label>
                            <input type="text" name="event_name" placeholder="e.g. Package Sorted" required 
                                class="w-full h-12 px-4 rounded-2xl border border-border bg-background/50 text-sm font-medium focus:ring-2 focus:ring-primary/20 outline-none transition-all">
                        </div>

                        <div class="space-y-2">
                            <label class="text-[10px] font-black uppercase tracking-widest text-muted-foreground ml-1">Log Location</label>
                            <input type="text" name="location" placeholder="e.g. Delhi Distribution Center" 
                                class="w-full h-12 px-4 rounded-2xl border border-border bg-background/50 text-sm font-medium focus:ring-2 focus:ring-primary/20 outline-none transition-all">
                        </div>

                        <div class="space-y-2">
                            <label class="text-[10px] font-black uppercase tracking-widest text-muted-foreground ml-1">Timestamp</label>
                            <input type="datetime-local" name="occurred_at" value="{{ now()->format('Y-m-d\TH:i') }}" required 
                                class="w-full h-12 px-4 rounded-2xl border border-border bg-background/50 text-sm font-black uppercase focus:ring-2 focus:ring-primary/20 outline-none transition-all">
                        </div>

                        <div class="space-y-2">
                            <label class="text-[10px] font-black uppercase tracking-widest text-muted-foreground ml-1">Comments</label>
                            <textarea name="description" rows="2" placeholder="Internal remarks..." 
                                class="w-full p-4 rounded-2xl border border-border bg-background/50 text-sm font-medium focus:ring-2 focus:ring-primary/20 outline-none transition-all"></textarea>
                        </div>

                        <div class="pt-2">
                            <x-ui.button type="submit" class="w-full h-14 rounded-2xl font-black uppercase tracking-[0.2em] text-xs shadow-xl shadow-primary/20 hover:scale-[1.02] active:scale-95 transition-all duration-300">
                                Record Entry
                            </x-ui.button>
                        </div>
                    </form>
                </x-ui.card>

                <!-- Small Summary Info -->
                <x-ui.card class="p-6 border-border/60 bg-primary/5 rounded-3xl border-l-4 border-l-primary shadow-sm">
                    <div class="flex items-start gap-4">
                        <x-ui.icon name="info" size="5" class="text-primary shrink-0" />
                        <div>
                            <h5 class="text-[10px] font-black uppercase tracking-widest text-primary mb-1">Status Sync</h5>
                            <p class="text-[11px] font-medium text-foreground/70 leading-relaxed">
                                Marking a shipment as <b>Delivered</b> will automatically synchronize the parent order status to maintain data integrity across modules.
                            </p>
                        </div>
                    </div>
                </x-ui.card>
            </div>
        </div>
    </div>
</x-layouts.app>
