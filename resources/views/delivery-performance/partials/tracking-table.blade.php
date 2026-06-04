<div class="overflow-x-auto relative">
    <div class="pointer-events-none absolute inset-x-8 top-0 h-px bg-gradient-to-r from-transparent via-primary/15 to-transparent hidden sm:block"></div>
    <x-ui.table>
        <x-ui.table-header class="bg-muted/30">
            <x-ui.table-row class="border-b border-border/60">
                <x-ui.table-head class="text-[10px] font-black uppercase tracking-widest text-muted-foreground/70 whitespace-nowrap pl-5">Order</x-ui.table-head>
                <x-ui.table-head class="text-[10px] font-black uppercase tracking-widest text-muted-foreground/70 whitespace-nowrap">Dispatch</x-ui.table-head>
                <x-ui.table-head class="text-[10px] font-black uppercase tracking-widest text-muted-foreground/70 whitespace-nowrap">Handler</x-ui.table-head>
                <x-ui.table-head class="text-[10px] font-black uppercase tracking-widest text-muted-foreground/70 whitespace-nowrap">Tracking</x-ui.table-head>
                <x-ui.table-head class="text-[10px] font-black uppercase tracking-widest text-muted-foreground/70 whitespace-nowrap">Status</x-ui.table-head>
                <x-ui.table-head class="text-[10px] font-black uppercase tracking-widest text-muted-foreground/70 whitespace-nowrap">Dates</x-ui.table-head>
                <x-ui.table-head class="text-[10px] font-black uppercase tracking-widest text-muted-foreground/70 text-right pr-5">Action</x-ui.table-head>
            </x-ui.table-row>
        </x-ui.table-header>
        <x-ui.table-body>
            @forelse($trackings as $tracking)
                <x-ui.table-row class="hover:bg-primary/[0.03] hover:z-50 relative border-b border-border/40 transition-colors duration-200">
                    <x-ui.table-cell class="pl-5 align-middle py-3">
                        <a href="{{ route('orders.show', $tracking->order_id) }}" class="text-sm font-black text-foreground hover:text-primary">
                            {{ $tracking->order?->order_no ?? 'Order #' . $tracking->order_id }}
                        </a>
                        <p class="text-[10px] font-bold uppercase tracking-widest text-muted-foreground mt-1">
                            {{ $tracking->order?->party?->name ?: ($tracking->order?->party?->company_name ?? 'Customer') }}
                        </p>
                    </x-ui.table-cell>
                    <x-ui.table-cell class="align-middle py-3">
                        <span class="text-[10px] font-black uppercase tracking-widest">{{ str_replace('_', ' ', $tracking->dispatch_type) }}</span>
                        <p class="text-[10px] text-muted-foreground mt-1">{{ $tracking->parcel_id ?? 'No parcel ID' }}</p>
                    </x-ui.table-cell>
                    <x-ui.table-cell class="align-middle py-3">
                        <p class="text-xs font-black">{{ $tracking->assigned_name }}</p>
                        <p class="text-[10px] font-bold text-muted-foreground uppercase tracking-widest">{{ $tracking->vehicle_number ?? 'No vehicle' }}</p>
                    </x-ui.table-cell>
                    <x-ui.table-cell class="align-middle py-3">
                        <p class="text-xs font-mono font-black">{{ $tracking->tracking_number ?? 'Not assigned' }}</p>
                        <p class="text-[10px] text-muted-foreground mt-1">{{ $tracking->courier_provider_name ?? 'LMD' }}</p>
                    </x-ui.table-cell>
                    <x-ui.table-cell class="align-middle py-3">
                        @php
                            $variant = match ($tracking->current_status) {
                                'delivered' => 'success',
                                'returned', 'delivery_failed', 'lost', 'cancelled' => 'destructive',
                                'out_for_delivery', 'in_transit', 'dispatched' => 'info',
                                default => 'outline',
                            };
                        @endphp
                        <x-ui.badge :variant="$variant" className="uppercase text-[9px] font-black tracking-widest rounded-lg shadow-sm">
                            {{ str_replace('_', ' ', $tracking->current_status) }}
                        </x-ui.badge>
                    </x-ui.table-cell>
                    <x-ui.table-cell class="align-middle py-3">
                        <p class="text-[10px] font-bold">Dispatch: {{ $tracking->dispatch_date?->format('M d, Y h:i A') ?? 'Pending' }}</p>
                        <p class="text-[10px] font-bold text-emerald-500 mt-1">Delivered: {{ $tracking->delivered_date?->format('M d, Y h:i A') ?? '—' }}</p>
                        <p class="text-[10px] font-bold text-red-500 mt-1">Returned: {{ $tracking->returned_date?->format('M d, Y h:i A') ?? '—' }}</p>
                    </x-ui.table-cell>
                    <x-ui.table-cell class="align-middle py-3 pr-5 text-right">
                        @if($tracking->shipment_id)
                            <a href="{{ route('order.tracking.show', $tracking->shipment_id) }}">
                                <x-ui.button variant="outline" size="sm" class="rounded-xl text-[9px] font-black uppercase tracking-widest">
                                    Timeline
                                </x-ui.button>
                            </a>
                        @else
                            <span class="text-[10px] font-bold text-muted-foreground">No shipment</span>
                        @endif
                    </x-ui.table-cell>
                </x-ui.table-row>
            @empty
                <x-ui.table-row>
                    <x-ui.table-cell colspan="7" class="py-20 text-center">
                        <div class="flex flex-col items-center gap-3 opacity-40">
                            <x-ui.icon name="target" size="10" />
                            <p class="text-sm font-black uppercase tracking-[0.2em]">No tracking records found</p>
                        </div>
                    </x-ui.table-cell>
                </x-ui.table-row>
            @endforelse
        </x-ui.table-body>
    </x-ui.table>
</div>

@if($trackings->hasPages())
    <div class="px-6 py-5 border-t border-border/40 bg-muted/5">
        {{ $trackings->links() }}
    </div>
@endif
