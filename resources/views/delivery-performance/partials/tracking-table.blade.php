<div class="overflow-x-auto">
    <table class="w-full border-collapse">
        <thead>
            <tr class="bg-muted/5 border-b border-border/40 text-left">
                <th class="px-6 py-4 text-[10px] font-black uppercase tracking-widest text-muted-foreground/70">Order</th>
                <th class="px-6 py-4 text-[10px] font-black uppercase tracking-widest text-muted-foreground/70">Dispatch</th>
                <th class="px-6 py-4 text-[10px] font-black uppercase tracking-widest text-muted-foreground/70">Handler</th>
                <th class="px-6 py-4 text-[10px] font-black uppercase tracking-widest text-muted-foreground/70">Tracking</th>
                <th class="px-6 py-4 text-[10px] font-black uppercase tracking-widest text-muted-foreground/70">Status</th>
                <th class="px-6 py-4 text-[10px] font-black uppercase tracking-widest text-muted-foreground/70">Dates</th>
                <th class="px-6 py-4 text-[10px] font-black uppercase tracking-widest text-muted-foreground/70 text-right">Action</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-border/20">
            @forelse($trackings as $tracking)
                <tr class="hover:bg-muted/5 transition-colors">
                    <td class="px-6 py-5">
                        <a href="{{ route('orders.show', $tracking->order_id) }}" class="text-sm font-black text-foreground hover:text-primary">
                            {{ $tracking->order?->order_no ?? 'Order #' . $tracking->order_id }}
                        </a>
                        <p class="text-[10px] font-bold uppercase tracking-widest text-muted-foreground mt-1">
                            {{ $tracking->order?->party?->name ?: ($tracking->order?->party?->company_name ?? 'Customer') }}
                        </p>
                    </td>
                    <td class="px-6 py-5">
                        <span class="text-[10px] font-black uppercase tracking-widest">{{ str_replace('_', ' ', $tracking->dispatch_type) }}</span>
                        <p class="text-[10px] text-muted-foreground mt-1">{{ $tracking->parcel_id ?? 'No parcel ID' }}</p>
                    </td>
                    <td class="px-6 py-5">
                        <p class="text-xs font-black">{{ $tracking->assigned_name }}</p>
                        <p class="text-[10px] font-bold text-muted-foreground uppercase tracking-widest">{{ $tracking->vehicle_number ?? 'No vehicle' }}</p>
                    </td>
                    <td class="px-6 py-5">
                        <p class="text-xs font-mono font-black">{{ $tracking->tracking_number ?? 'Not assigned' }}</p>
                        <p class="text-[10px] text-muted-foreground mt-1">{{ $tracking->courier_provider_name ?? 'LMD' }}</p>
                    </td>
                    <td class="px-6 py-5">
                        @php
                            $variant = match ($tracking->current_status) {
                                'delivered' => 'success',
                                'returned', 'delivery_failed', 'lost', 'cancelled' => 'destructive',
                                'out_for_delivery', 'in_transit', 'dispatched' => 'info',
                                default => 'outline',
                            };
                        @endphp
                        <x-ui.badge :variant="$variant" className="uppercase text-[9px] font-black tracking-widest rounded-lg">
                            {{ str_replace('_', ' ', $tracking->current_status) }}
                        </x-ui.badge>
                    </td>
                    <td class="px-6 py-5">
                        <p class="text-[10px] font-bold">Dispatch: {{ $tracking->dispatch_date?->format('M d, Y h:i A') ?? 'Pending' }}</p>
                        <p class="text-[10px] font-bold text-emerald-500 mt-1">Delivered: {{ $tracking->delivered_date?->format('M d, Y h:i A') ?? '—' }}</p>
                        <p class="text-[10px] font-bold text-red-500 mt-1">Returned: {{ $tracking->returned_date?->format('M d, Y h:i A') ?? '—' }}</p>
                    </td>
                    <td class="px-6 py-5 text-right">
                        @if($tracking->shipment_id)
                            <a href="{{ route('order.tracking.show', $tracking->shipment_id) }}">
                                <x-ui.button variant="outline" size="sm" class="rounded-xl text-[9px] font-black uppercase tracking-widest">
                                    Timeline
                                </x-ui.button>
                            </a>
                        @else
                            <span class="text-[10px] font-bold text-muted-foreground">No shipment</span>
                        @endif
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" class="px-6 py-20 text-center">
                        <div class="flex flex-col items-center gap-3 opacity-40">
                            <x-ui.icon name="target" size="10" />
                            <p class="text-sm font-black uppercase tracking-[0.2em]">No tracking records found</p>
                        </div>
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

@if($trackings->hasPages())
    <div class="px-6 py-5 border-t border-border/40 bg-muted/5">
        {{ $trackings->links() }}
    </div>
@endif
