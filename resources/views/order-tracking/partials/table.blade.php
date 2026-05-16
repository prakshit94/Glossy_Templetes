<div class="overflow-x-auto overflow-y-visible">
    <table class="w-full border-collapse">
        <thead>
            <tr class="bg-muted/5 border-b border-border/40 text-left">
                <th class="px-6 py-5 text-[10px] font-black uppercase tracking-widest text-muted-foreground/80 w-10">
                    <input type="checkbox" x-model="allSelected" @change="selectedShipments = allSelected ? @js($shipments->pluck('id')->all()) : []"
                        class="rounded border-border/60 bg-background/50 text-primary focus:ring-primary/20">
                </th>
                <th class="px-6 py-5 text-[10px] font-black uppercase tracking-widest text-muted-foreground/80">Shipment Identity</th>
                <th class="px-6 py-5 text-[10px] font-black uppercase tracking-widest text-muted-foreground/80">Customer & Order</th>
                <th class="px-6 py-5 text-[10px] font-black uppercase tracking-widest text-muted-foreground/80">Current Status</th>
                <th class="px-6 py-5 text-[10px] font-black uppercase tracking-widest text-muted-foreground/80">Last Milestone</th>
                <th class="px-6 py-5 text-[10px] font-black uppercase tracking-widest text-muted-foreground/80 text-right">Actions</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-border/20">
            @forelse($shipments as $shipment)
                <tr class="group hover:bg-muted/5 transition-all" :class="selectedShipments.includes({{ $shipment->id }}) ? 'bg-primary/5' : ''">
                    <td class="px-6 py-6">
                        <input type="checkbox" value="{{ $shipment->id }}" x-model="selectedShipments"
                            class="rounded border-border/60 bg-background/50 text-primary focus:ring-primary/20">
                    </td>
                    <td class="px-6 py-6">
                        <div class="flex items-center gap-4">
                            <div class="size-11 rounded-xl bg-primary/5 border border-primary/10 flex items-center justify-center text-primary shadow-inner">
                                <x-ui.icon name="package" size="5" />
                            </div>
                            <div>
                                <p class="text-sm font-black text-foreground tracking-tight group-hover:text-primary transition-colors">#{{ $shipment->shipment_no }}</p>
                                <p class="text-[9px] font-bold text-muted-foreground uppercase tracking-widest flex items-center gap-1.5 mt-0.5">
                                    <x-ui.icon name="truck" size="2.5" />
                                    {{ $shipment->carrier_name ?? 'Logistics Pending' }}
                                    @if($shipment->tracking_no)
                                        <span class="size-1 rounded-full bg-muted-foreground/30"></span>
                                        {{ $shipment->tracking_no }}
                                    @endif
                                </p>
                            </div>
                        </div>
                    </td>
                    <td class="px-6 py-6">
                        <div class="space-y-1">
                            <a href="{{ route('orders.show', $shipment->order_id) }}" class="text-xs font-black text-foreground hover:text-primary transition-colors flex items-center gap-1.5">
                                {{ $shipment->order->order_no }}
                                <x-ui.icon name="external-link" size="3" class="opacity-40" />
                            </a>
                            <p class="text-[10px] font-bold text-muted-foreground uppercase tracking-widest">
                                {{ $shipment->order->party->name ?? 'Unknown Customer' }}
                            </p>
                        </div>
                    </td>
                    <td class="px-6 py-6">
                        @php
                            $statusColors = [
                                'pending' => 'bg-amber-500/10 text-amber-500 border-amber-500/20',
                                'shipped' => 'bg-blue-500/10 text-blue-500 border-blue-500/20',
                                'in_transit' => 'bg-indigo-500/10 text-indigo-500 border-indigo-500/20',
                                'delivered' => 'bg-emerald-500/10 text-emerald-500 border-emerald-500/20',
                                'failed' => 'bg-destructive/10 text-destructive border-destructive/20',
                            ];
                            $color = $statusColors[$shipment->status] ?? 'bg-muted/10 text-muted-foreground border-border/40';
                        @endphp
                        <div class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full border {{ $color }} shadow-sm">
                            <span class="size-1.5 rounded-full bg-current {{ $shipment->status === 'in_transit' ? 'animate-pulse' : '' }}"></span>
                            <span class="text-[10px] font-black uppercase tracking-widest">{{ str_replace('_', ' ', $shipment->status) }}</span>
                        </div>
                    </td>
                    <td class="px-6 py-6">
                        @if($lastEvent = $shipment->events->last())
                            <div class="flex flex-col">
                                <span class="text-[10px] font-black text-foreground uppercase tracking-tight">{{ $lastEvent->event_name }}</span>
                                <span class="text-[9px] font-bold opacity-60 flex items-center gap-1 mt-0.5">
                                    <x-ui.icon name="clock" size="2.5" />
                                    {{ $lastEvent->occurred_at->diffForHumans() }}
                                </span>
                            </div>
                        @else
                            <span class="text-[10px] font-bold opacity-40 uppercase tracking-widest italic">No milestones yet</span>
                        @endif
                    </td>
                    <td class="px-6 py-6 text-right">
                        <div class="flex items-center justify-end gap-2">
                            <a href="{{ route('order.tracking.show', $shipment->id) }}">
                                <x-ui.button variant="outline" size="sm" class="rounded-xl border-border/60 hover:border-primary/40 hover:bg-primary/5 transition-all group/btn h-9 px-4">
                                    <span class="text-[9px] font-black uppercase tracking-widest mr-2">Track Detail</span>
                                    <x-ui.icon name="arrow-right" size="3" class="group-hover/btn:translate-x-0.5 transition-transform" />
                                </x-ui.button>
                            </a>
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" class="px-6 py-24 text-center">
                        <div class="flex flex-col items-center gap-4 opacity-30">
                            <div class="size-20 rounded-full bg-muted/20 flex items-center justify-center">
                                <x-ui.icon name="target" size="10" />
                            </div>
                            <div>
                                <p class="text-lg font-black uppercase tracking-[0.2em]">No Shipments Detected</p>
                                <p class="text-xs font-bold uppercase tracking-widest text-muted-foreground mt-1">Try adjusting your filters or search query</p>
                            </div>
                        </div>
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

@if($shipments->hasPages())
    <div class="px-8 py-6 bg-muted/5 border-t border-border/40">
        {{ $shipments->links() }}
    </div>
@endif
