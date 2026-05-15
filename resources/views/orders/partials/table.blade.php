<div class="overflow-x-auto custom-scrollbar">
    <table class="w-full text-left border-collapse">
        <thead>
            <tr class="border-b border-border/40 bg-muted/5">
                <th class="px-6 py-4 text-[10px] font-black uppercase tracking-widest text-muted-foreground whitespace-nowrap">Order #</th>
                <th class="px-6 py-4 text-[10px] font-black uppercase tracking-widest text-muted-foreground whitespace-nowrap">Type</th>
                <th class="px-6 py-4 text-[10px] font-black uppercase tracking-widest text-muted-foreground whitespace-nowrap">Party</th>
                <th class="px-6 py-4 text-[10px] font-black uppercase tracking-widest text-muted-foreground whitespace-nowrap">Warehouse</th>
                <th class="px-6 py-4 text-[10px] font-black uppercase tracking-widest text-muted-foreground whitespace-nowrap">Status</th>
                <th class="px-6 py-4 text-[10px] font-black uppercase tracking-widest text-muted-foreground text-right whitespace-nowrap">Net Amount</th>
                <th class="px-6 py-4 text-[10px] font-black uppercase tracking-widest text-muted-foreground text-right whitespace-nowrap">Actions</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-border/40">
            @forelse($orders as $order)
                <tr class="hover:bg-muted/10 transition-colors group">
                    <td class="px-6 py-4">
                        <p class="text-sm font-bold text-foreground">{{ $order->order_no }}</p>
                        <p class="text-[10px] text-muted-foreground">{{ optional($order->order_date)->format('M d, Y h:i A') }}</p>
                    </td>
                    <td class="px-6 py-4">
                        <x-ui.badge variant="{{ $order->type === 'sale' ? 'default' : 'outline' }}" class="uppercase text-[9px] font-black tracking-widest">
                            {{ $order->type }}
                        </x-ui.badge>
                    </td>
                    <td class="px-6 py-4">
                        <p class="text-sm font-semibold text-foreground truncate max-w-[150px]">{{ $order->party?->name ?? 'N/A' }}</p>
                    </td>
                    <td class="px-6 py-4">
                        <p class="text-sm font-semibold text-foreground truncate max-w-[150px]">{{ $order->warehouse?->name ?? 'N/A' }}</p>
                    </td>
                    <td class="px-6 py-4">
                        @php
                            $statusVariant = match($order->status) {
                                'shipped', 'delivered', 'completed' => 'success',
                                'cancelled', 'returned' => 'destructive',
                                'pending' => 'warning',
                                'processing', 'in_transit' => 'default',
                                default => 'outline'
                            };
                        @endphp
                        <x-ui.badge variant="{{ $statusVariant }}" class="uppercase text-[9px] font-black tracking-widest">
                            {{ str_replace('_', ' ', $order->status) }}
                        </x-ui.badge>
                    </td>
                    <td class="px-6 py-4 text-right">
                        <span class="text-sm font-black text-foreground">₹{{ number_format((float) $order->net_amount, 2) }}</span>
                    </td>
                    <td class="px-6 py-4 text-right">
                        <div class="flex items-center justify-end gap-2 opacity-100 lg:opacity-0 lg:group-hover:opacity-100 transition-opacity">
                            <a href="{{ route('orders.show', $order) }}" title="View Order">
                                <button type="button" class="size-8 rounded-xl bg-background border border-border flex items-center justify-center text-muted-foreground hover:text-primary hover:border-primary transition-all">
                                    <x-ui.icon name="eye" size="3.5" />
                                </button>
                            </a>
                            <a href="{{ route('orders.edit', $order) }}" title="Edit Order">
                                <button type="button" class="size-8 rounded-xl bg-background border border-border flex items-center justify-center text-muted-foreground hover:text-primary hover:border-primary transition-all">
                                    <x-ui.icon name="edit-3" size="3.5" />
                                </button>
                            </a>
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" class="px-6 py-14 text-center">
                        <div class="flex flex-col items-center justify-center">
                            <div class="size-16 rounded-full bg-muted/50 flex items-center justify-center mb-4 text-muted-foreground/50">
                                <x-ui.icon name="inbox" size="8" />
                            </div>
                            <h4 class="text-sm font-bold text-foreground">No orders found</h4>
                            <p class="text-xs text-muted-foreground mt-1">Try adjusting your search or filters.</p>
                        </div>
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

@if($orders->hasPages())
    <div class="p-4 border-t border-border/40 bg-muted/5 flex justify-between items-center">
        <div class="text-[10px] font-bold text-muted-foreground uppercase tracking-widest">
            Showing {{ $orders->firstItem() }} to {{ $orders->lastItem() }} of {{ $orders->total() }} entries
        </div>
        <div>
            {{ $orders->links('pagination::tailwind') }}
        </div>
    </div>
@endif
