<x-layouts.app pageTitle="Shipment Tracking: #{{ $shipment->shipment_no }}">

    <div class="p-6 lg:p-10 max-w-7xl mx-auto space-y-8">
        <!-- Header Card: Modern Identity -->
        <x-ui.card class="overflow-hidden border-border/60 shadow-2xl bg-card/30 backdrop-blur-3xl rounded-[2rem] ring-1 ring-white/10">
            <div class="p-8 flex flex-col md:flex-row md:items-center justify-between gap-8 relative">
                <!-- Status Glow Background -->
                @php
                    $statusColors = [
                        'pending' => 'amber',
                        'shipped' => 'blue',
                        'in_transit' => 'indigo',
                        'delivered' => 'emerald',
                        'failed' => 'red',
                    ];
                    $color = $statusColors[$shipment->status] ?? 'primary';
                @endphp
                <div class="absolute top-0 right-0 -mr-20 -mt-20 size-80 bg-{{ $color }}-500/10 blur-[80px] rounded-full pointer-events-none animate-pulse"></div>

                <div class="flex items-center gap-6 relative z-10">
                    <div class="size-20 rounded-3xl bg-{{ $color }}-500/10 border border-{{ $color }}-500/20 text-{{ $color }}-500 flex items-center justify-center shadow-2xl ring-1 ring-{{ $color }}-500/20 group hover:rotate-3 transition-transform duration-500">
                        <x-ui.icon name="target" size="10" class="group-hover:scale-110 transition-transform" />
                    </div>
                    <div>
                        <div class="flex items-center gap-4 mb-2">
                            <h3 class="text-3xl font-black text-foreground tracking-tighter">{{ $shipment->shipment_no }}</h3>
                            <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-{{ $color }}-500/10 border border-{{ $color }}-500/20 text-{{ $color }}-500 shadow-sm">
                                <span class="size-2 rounded-full bg-current animate-ping"></span>
                                <span class="text-[10px] font-black uppercase tracking-[0.2em]">{{ $shipment->status }}</span>
                            </div>
                        </div>
                        <div class="flex flex-wrap items-center gap-x-4 gap-y-2 text-xs font-bold text-muted-foreground/60 uppercase tracking-widest">
                            <div class="flex items-center gap-2">
                                <x-ui.icon name="calendar" size="3.5" class="text-{{ $color }}-500/60" />
                                <span>Init: {{ $shipment->created_at->format('M d, Y') }}</span>
                            </div>
                            <span class="size-1 rounded-full bg-border"></span>
                            <div class="flex items-center gap-2">
                                <x-ui.icon name="hash" size="3.5" class="text-{{ $color }}-500/60" />
                                <span class="font-mono tracking-tighter text-foreground">{{ $shipment->tracking_no ?? 'Awaiting AWB' }}</span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="flex flex-wrap items-center gap-3 relative z-10">
                    <a href="{{ route('order.tracking.index') }}">
                        <x-ui.button variant="outline" size="sm" class="rounded-2xl font-black uppercase tracking-widest text-[10px] h-12 px-8 border-border/40 bg-background/20 backdrop-blur-md hover:bg-background/40 transition-all">
                            <x-ui.icon name="arrow-left" size="3.5" class="mr-2" /> Back to Dashboard
                        </x-ui.button>
                    </a>
                </div>
            </div>
        </x-ui.card>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- Route Visualizer -->
            <x-ui.card class="lg:col-span-2 overflow-hidden border-border/60 shadow-xl bg-card/30 backdrop-blur-2xl rounded-[2rem]">
                <div class="p-6 border-b border-border/40 bg-muted/10 flex items-center gap-3">
                    <x-ui.icon name="map" size="4" class="text-primary" />
                    <h4 class="text-[10px] font-black uppercase tracking-[0.3em] text-primary">Logistics Infrastructure & Route</h4>
                </div>
                <div class="p-10 flex flex-col md:flex-row items-center justify-between gap-8 relative">
                    {{-- Connector Path --}}
                    <div class="absolute left-1/2 md:left-24 right-1/2 md:right-24 top-24 md:top-1/2 h-px bg-gradient-to-r from-primary/10 via-primary/40 to-emerald-500/20 -translate-y-1/2 z-0 hidden md:block">
                        <div class="absolute inset-0 bg-gradient-to-r from-transparent via-primary/50 to-transparent animate-shimmer" style="background-size: 200% 100%"></div>
                    </div>

                    <div class="flex flex-col items-center gap-4 z-10 group">
                        <div class="size-20 rounded-3xl bg-background border-2 border-primary/20 flex items-center justify-center shadow-2xl group-hover:border-primary transition-all duration-500 ring-8 ring-primary/5">
                            <x-ui.icon name="warehouse" size="8" class="text-primary/80 group-hover:scale-110 transition-transform" />
                        </div>
                        <div class="text-center">
                            <p class="text-[10px] font-black uppercase text-muted-foreground tracking-widest mb-1.5 opacity-60">Source Origin</p>
                            <p class="text-lg font-black text-foreground tracking-tight">{{ $shipment->order->warehouse->name ?? 'Regional Hub' }}</p>
                            <p class="text-[10px] font-bold text-primary tracking-[0.2em] uppercase mt-1">{{ $shipment->order->warehouse->code ?? 'H-01' }}</p>
                        </div>
                    </div>

                    <div class="flex-1 flex flex-col items-center gap-4 py-8 md:py-0 relative">
                        <div class="size-16 rounded-full bg-primary/10 border-2 border-primary/20 flex items-center justify-center shadow-inner relative z-10 group cursor-help">
                            <x-ui.icon name="truck" size="6" class="text-primary animate-bounce-slow" />
                            <div class="absolute -top-12 left-1/2 -translate-x-1/2 bg-foreground text-background px-3 py-1 rounded-lg text-[9px] font-black uppercase tracking-widest opacity-0 group-hover:opacity-100 transition-opacity whitespace-nowrap shadow-xl">
                                {{ $shipment->carrier_name ?? 'In Dispatch' }}
                            </div>
                        </div>
                        <div class="flex flex-col items-center text-center">
                            <span class="text-[9px] font-black uppercase tracking-[0.3em] text-primary/60 animate-pulse">Live Transit</span>
                            @if($shipment->delivered_at)
                                <span class="text-[9px] font-bold text-muted-foreground mt-1">Completed in {{ $shipment->created_at->diffInDays($shipment->delivered_at) }} Days</span>
                            @endif
                        </div>
                    </div>

                    <div class="flex flex-col items-center gap-4 z-10 group">
                        <div class="size-20 rounded-3xl bg-background border-2 border-emerald-500/20 flex items-center justify-center shadow-2xl group-hover:border-emerald-500 transition-all duration-500 ring-8 ring-emerald-500/5">
                            <x-ui.icon name="map-pin" size="8" class="text-emerald-500 group-hover:scale-110 transition-transform" />
                        </div>
                        <div class="text-center">
                            <p class="text-[10px] font-black uppercase text-muted-foreground tracking-widest mb-1.5 opacity-60">Final Destination</p>
                            <p class="text-lg font-black text-foreground tracking-tight">{{ $shipment->order->party->name ?? 'Customer' }}</p>
                            <p class="text-[10px] font-bold text-emerald-500 tracking-[0.2em] uppercase mt-1">Order {{ $shipment->order->order_no }}</p>
                        </div>
                    </div>
                </div>
            </x-ui.card>

            <!-- Status Control Panel -->
            <x-ui.card class="overflow-hidden border-border/60 shadow-xl bg-card/40 backdrop-blur-3xl rounded-[2rem] border-dashed">
                <div class="p-6 border-b border-border/40 bg-muted/10 flex items-center gap-3">
                    <x-ui.icon name="settings-2" size="4" class="text-primary" />
                    <h4 class="text-[10px] font-black uppercase tracking-[0.3em] text-primary">Logistics Control</h4>
                </div>
                <div class="p-8">
                    <form action="{{ route('order.tracking.status.update', $shipment->id) }}" method="POST" class="space-y-6">
                        @csrf
                        @method('PUT')
                        <div class="space-y-3">
                            <label class="text-[10px] font-black uppercase tracking-widest text-muted-foreground/80 flex items-center gap-2 ml-1">
                                <x-ui.icon name="activity" size="3" /> State Transition
                            </label>
                            <select name="status" class="w-full h-14 px-5 rounded-[1.25rem] border border-border bg-background/40 text-xs font-black uppercase tracking-widest focus:ring-4 focus:ring-primary/10 transition-all outline-none appearance-none cursor-pointer hover:bg-background/60">
                                @foreach(['pending', 'shipped', 'in_transit', 'delivered', 'failed'] as $st)
                                    <option value="{{ $st }}" {{ $shipment->status === $st ? 'selected' : '' }}>{{ strtoupper($st) }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="space-y-3">
                            <label class="text-[10px] font-black uppercase tracking-widest text-muted-foreground/80 flex items-center gap-2 ml-1">
                                <x-ui.icon name="hash" size="3" /> AWB / Air Waybill
                            </label>
                            <input type="text" name="tracking_no" value="{{ $shipment->tracking_no }}" placeholder="Enter Tracking Number" 
                                class="w-full h-14 px-5 rounded-[1.25rem] border border-border bg-background/40 text-xs font-black font-mono tracking-widest focus:ring-4 focus:ring-primary/10 transition-all outline-none hover:bg-background/60">
                        </div>
                        <x-ui.button type="submit" class="w-full h-14 rounded-[1.25rem] text-[11px] font-black uppercase tracking-[0.3em] shadow-xl shadow-primary/20 hover:scale-[1.02] active:scale-95 transition-all">
                            Commit Updates
                        </x-ui.button>
                    </form>
                </div>
            </x-ui.card>
        </div>

        <!-- Package Contents Card -->
        <x-ui.card class="overflow-hidden border-border/60 shadow-xl bg-card/30 backdrop-blur-xl rounded-[2rem]">
            <div class="p-6 border-b border-border/40 bg-muted/10 flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <x-ui.icon name="box" size="4" class="text-primary" />
                    <h4 class="text-[10px] font-black uppercase tracking-[0.3em] text-primary">Consignment Contents</h4>
                </div>
                <span class="text-[10px] font-bold text-muted-foreground uppercase tracking-widest">{{ $shipment->order->items->count() }} line items</span>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="border-b border-border/40 bg-muted/5">
                            <th class="px-8 py-4 text-[9px] font-black uppercase tracking-widest text-muted-foreground/60">Product</th>
                            <th class="px-8 py-4 text-[9px] font-black uppercase tracking-widest text-muted-foreground/60 text-center">Qty</th>
                            <th class="px-8 py-4 text-[9px] font-black uppercase tracking-widest text-muted-foreground/60 text-right">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-border/20">
                        @foreach($shipment->order->items as $item)
                            <tr class="hover:bg-primary/[0.02] transition-colors">
                                <td class="px-8 py-5">
                                    <div class="flex items-center gap-4">
                                        <div class="size-10 rounded-xl bg-background border border-border flex items-center justify-center overflow-hidden shrink-0 shadow-sm">
                                            @if($item->product?->image_url)
                                                <img src="{{ $item->product->image_url }}" class="size-full object-cover">
                                            @else
                                                <x-ui.icon name="image" size="4" class="text-muted-foreground/20" />
                                            @endif
                                        </div>
                                        <div>
                                            <p class="text-sm font-black text-foreground tracking-tight">{{ $item->product?->name ?? 'Consignment Item' }}</p>
                                            <p class="text-[10px] font-bold text-muted-foreground uppercase tracking-widest">{{ $item->product?->sku ?? 'N/A' }}</p>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-8 py-5 text-center">
                                    <span class="inline-flex items-center justify-center size-8 rounded-lg bg-muted text-foreground text-xs font-black">{{ (int) $item->quantity }}</span>
                                </td>
                                <td class="px-8 py-5 text-right">
                                    <span class="text-[10px] font-black text-emerald-500 uppercase tracking-widest">In Transit</span>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </x-ui.card>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- Timeline Visualizer -->
            <x-ui.card class="lg:col-span-2 overflow-hidden border-border/60 shadow-xl bg-card/30 backdrop-blur-2xl rounded-[2.5rem] relative">
                <div class="p-8 border-b border-border/40 bg-muted/5 flex items-center justify-between">
                    <div class="flex items-center gap-4">
                        <div class="size-10 rounded-2xl bg-primary/10 flex items-center justify-center text-primary">
                            <x-ui.icon name="list" size="5" />
                        </div>
                        <div>
                            <h4 class="text-xl font-black text-foreground tracking-tight">Milestone Chronology</h4>
                            <p class="text-[10px] font-bold text-muted-foreground uppercase tracking-widest mt-1 italic">Verified logistics checkpoints</p>
                        </div>
                    </div>
                    <span class="px-4 py-2 rounded-2xl bg-primary/5 border border-primary/10 text-[10px] font-black text-primary uppercase tracking-widest shadow-inner">
                        {{ $shipment->events->count() }} Data Points
                    </span>
                </div>
                
                <div class="p-10 relative overflow-y-auto max-h-[650px] custom-scrollbar">
                    <div class="relative space-y-12 before:content-[''] before:absolute before:left-[19px] before:top-4 before:bottom-4 before:w-[3px] before:bg-gradient-to-b before:from-primary/60 before:via-primary/20 before:to-transparent">
                        @forelse($shipment->events as $event)
                            <div class="relative pl-14 group">
                                <div class="absolute left-0 top-1 size-10 rounded-full bg-background border-[6px] border-muted/30 flex items-center justify-center text-primary shadow-2xl z-10 group-hover:scale-125 group-hover:border-primary/20 transition-all duration-500">
                                    <div class="size-2.5 rounded-full bg-primary group-hover:animate-ping"></div>
                                </div>
                                <div class="flex flex-col md:flex-row md:items-start justify-between gap-6">
                                    <div class="flex-1 space-y-3">
                                        <div class="flex items-center gap-3">
                                            <h4 class="text-base font-black text-foreground tracking-tight group-hover:text-primary transition-colors duration-500">{{ $event->event_name }}</h4>
                                            <span class="text-[10px] font-black text-primary/40 uppercase tracking-widest">{{ $event->occurred_at->format('H:i') }}</span>
                                        </div>
                                        <div class="flex items-center gap-4">
                                            <div class="px-3 py-1 rounded-lg bg-muted/20 border border-border/40 flex items-center gap-2 group-hover:border-primary/30 transition-colors">
                                                <x-ui.icon name="map-pin" size="3" class="text-primary/60" />
                                                <span class="text-[10px] font-black text-muted-foreground uppercase tracking-widest">{{ $event->location ?? 'Logistics Node' }}</span>
                                            </div>
                                            <div class="px-3 py-1 rounded-lg bg-primary/5 border border-primary/10 flex items-center gap-2">
                                                <x-ui.icon name="calendar" size="3" class="text-primary/60" />
                                                <span class="text-[10px] font-black text-primary uppercase tracking-widest">{{ $event->occurred_at->format('M d, Y') }}</span>
                                            </div>
                                        </div>
                                        @if($event->description)
                                            <div class="relative p-5 rounded-[1.5rem] bg-background/40 border border-border/40 backdrop-blur-sm group-hover:bg-background/60 transition-all duration-500 overflow-hidden">
                                                <div class="absolute top-0 left-0 w-1 h-full bg-primary/20"></div>
                                                <p class="text-[13px] text-muted-foreground leading-relaxed italic font-medium">"{{ $event->description }}"</p>
                                            </div>
                                        @endif
                                    </div>
                                    <div class="shrink-0 pt-1">
                                        <div class="flex flex-col items-end gap-1">
                                            <span class="text-[10px] font-black text-foreground tracking-widest uppercase opacity-40">{{ $event->occurred_at->diffForHumans() }}</span>
                                            <div class="size-1 rounded-full bg-primary/20"></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @empty
                            <div class="text-center py-24 opacity-20">
                                <x-ui.icon name="box" size="20" class="mx-auto mb-6 animate-pulse" />
                                <p class="text-sm font-black uppercase tracking-[0.4em]">Awaiting Logistics Data</p>
                            </div>
                        @endforelse
                    </div>
                </div>
            </x-ui.card>

            <!-- Event Logging Sidebar -->
            <div class="space-y-8">
                <x-ui.card class="p-8 border-border/60 bg-card/40 backdrop-blur-3xl rounded-[2.5rem] shadow-2xl border-dashed relative overflow-hidden group">
                    <div class="absolute -right-10 -bottom-10 size-40 bg-primary/5 rounded-full blur-3xl group-hover:bg-primary/10 transition-all"></div>
                    
                    <div class="flex items-center gap-4 mb-10">
                        <div class="size-12 rounded-[1.25rem] bg-primary/10 text-primary flex items-center justify-center shadow-inner">
                            <x-ui.icon name="plus-circle" size="6" />
                        </div>
                        <div>
                            <h4 class="text-xl font-black text-foreground tracking-tight">Post Milestone</h4>
                            <p class="text-[10px] font-bold text-muted-foreground uppercase tracking-widest mt-0.5">Add verified checkpoint</p>
                        </div>
                    </div>

                    <form action="{{ route('order.tracking.events.store', $shipment->id) }}" method="POST" class="space-y-6 relative z-10">
                        @csrf
                        <div class="space-y-3">
                            <label class="text-[10px] font-black uppercase tracking-widest text-muted-foreground/60 ml-2">Milestone Title</label>
                            <input type="text" name="event_name" placeholder="e.g. Cleared Customs" required 
                                class="w-full h-14 px-5 rounded-[1.25rem] border border-border bg-background/40 text-sm font-bold focus:ring-4 focus:ring-primary/10 outline-none transition-all hover:bg-background/60">
                        </div>

                        <div class="space-y-3">
                            <label class="text-[10px] font-black uppercase tracking-widest text-muted-foreground/60 ml-2">Node / Location</label>
                            <input type="text" name="location" placeholder="e.g. Mumbai Hub-02" 
                                class="w-full h-14 px-5 rounded-[1.25rem] border border-border bg-background/40 text-sm font-bold focus:ring-4 focus:ring-primary/10 outline-none transition-all hover:bg-background/60">
                        </div>

                        <div class="space-y-3">
                            <label class="text-[10px] font-black uppercase tracking-widest text-muted-foreground/60 ml-2">Checkpoint Time</label>
                            <input type="datetime-local" name="occurred_at" value="{{ now()->format('Y-m-d\TH:i') }}" required 
                                class="w-full h-14 px-5 rounded-[1.25rem] border border-border bg-background/40 text-xs font-black uppercase tracking-widest focus:ring-4 focus:ring-primary/10 outline-none transition-all hover:bg-background/60">
                        </div>

                        <div class="space-y-3">
                            <label class="text-[10px] font-black uppercase tracking-widest text-muted-foreground/60 ml-2">Data Integrity Remark</label>
                            <textarea name="description" rows="3" placeholder="Additional logistics context..." 
                                class="w-full p-5 rounded-[1.5rem] border border-border bg-background/40 text-sm font-medium focus:ring-4 focus:ring-primary/10 outline-none transition-all hover:bg-background/60 resize-none"></textarea>
                        </div>

                        <div class="pt-4">
                            <x-ui.button type="submit" class="w-full h-16 rounded-[1.25rem] font-black uppercase tracking-[0.3em] text-xs shadow-2xl shadow-primary/20 hover:scale-[1.03] active:scale-95 transition-all duration-300">
                                Register Checkpoint
                            </x-ui.button>
                        </div>
                    </form>
                </x-ui.card>

                <!-- Data Integrity Notification -->
                <x-ui.card class="p-6 border-border/60 bg-emerald-500/5 rounded-[2rem] border-l-[6px] border-l-emerald-500 shadow-lg relative overflow-hidden group">
                    <div class="absolute top-0 right-0 p-4 opacity-10 group-hover:rotate-12 transition-transform">
                        <x-ui.icon name="shield-check" size="16" />
                    </div>
                    <div class="flex items-start gap-4 relative z-10">
                        <div class="size-10 rounded-xl bg-emerald-500/10 flex items-center justify-center text-emerald-500">
                            <x-ui.icon name="info" size="5" />
                        </div>
                        <div>
                            <h5 class="text-[10px] font-black uppercase tracking-[0.2em] text-emerald-600 mb-2">Automated State Sync</h5>
                            <p class="text-[12px] font-bold text-foreground/60 leading-relaxed italic">
                                Transitioning to <span class="text-emerald-600 font-black">Delivered</span> will immediately finalize the parent order record. This action is audited and irreversible.
                            </p>
                        </div>
                    </div>
                </x-ui.card>
            </div>
        </div>
    </div>

    <style>
        @keyframes shimmer {
            0% { background-position: -200% 0; }
            100% { background-position: 200% 0; }
        }
        .animate-shimmer {
            animation: shimmer 3s infinite linear;
        }
        @keyframes bounce-slow {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-10px); }
        }
        .animate-bounce-slow {
            animation: bounce-slow 3s infinite ease-in-out;
        }
        .custom-scrollbar::-webkit-scrollbar {
            width: 4px;
        }
        .custom-scrollbar::-webkit-scrollbar-track {
            background: transparent;
        }
        .custom-scrollbar::-webkit-scrollbar-thumb {
            background: rgba(var(--primary), 0.1);
            border-radius: 10px;
        }
        .custom-scrollbar::-webkit-scrollbar-thumb:hover {
            background: rgba(var(--primary), 0.2);
        }
    </style>
</x-layouts.app>
