<div class="overflow-x-auto">
    <table class="w-full text-left border-collapse">
        <thead>
            <tr class="bg-muted/5 border-b border-border/40">
                <th class="p-4 text-[10px] font-black uppercase tracking-widest text-muted-foreground/60">Product Detail</th>
                <th class="p-4 text-[10px] font-black uppercase tracking-widest text-muted-foreground/60">Warehouse</th>
                <th class="p-4 text-[10px] font-black uppercase tracking-widest text-muted-foreground/60 text-center">Physical Qty</th>
                <th class="p-4 text-[10px] font-black uppercase tracking-widest text-muted-foreground/60 text-center">Reserved</th>
                <th class="p-4 text-[10px] font-black uppercase tracking-widest text-muted-foreground/60 text-center">Available</th>
                <th class="p-4 text-[10px] font-black uppercase tracking-widest text-muted-foreground/60 text-center">Dispatched</th>
                <th class="p-4 text-[10px] font-black uppercase tracking-widest text-muted-foreground/60 text-center">Delivered</th>
            </tr>
        </thead>
        <tbody>
            @forelse($stocks as $stock)
                @php
                    $available = (float) $stock->quantity - (float) $stock->reserved_qty;
                    $isLowStock = $available <= ($stock->product?->min_stock_level ?? 0);
                    $isOutOfStock = $available <= 0;
                @endphp
                <tr class="border-b border-border/30 hover:bg-muted/10 transition-colors group">
                    <!-- Product Info -->
                    <td class="p-4 min-w-[280px]">
                        <div class="flex items-center gap-4">
                            <div class="relative group/img shrink-0">
                                <div class="size-12 rounded-2xl bg-gradient-to-tr from-primary/20 to-primary/5 border border-primary/10 flex items-center justify-center overflow-hidden shadow-inner group-hover/img:scale-110 transition-transform duration-500">
                                    @if($stock->product?->image_path)
                                        <img src="{{ asset('storage/' . $stock->product->image_path) }}" alt="{{ $stock->product->name }}" class="size-full object-cover">
                                    @else
                                        <x-ui.icon name="package" size="6" class="text-primary/40" />
                                    @endif
                                </div>
                                @if($isOutOfStock)
                                    <div class="absolute -top-1 -right-1 size-4 rounded-full bg-destructive border-2 border-background shadow-lg shadow-destructive/20 animate-pulse"></div>
                                @elseif($isLowStock)
                                    <div class="absolute -top-1 -right-1 size-4 rounded-full bg-orange-500 border-2 border-background shadow-lg shadow-orange-500/20"></div>
                                @endif
                            </div>
                            <div class="min-w-0">
                                <a href="{{ route('products.show', $stock->product_id) }}" class="text-xs font-black text-foreground group-hover:text-primary transition-colors leading-tight block truncate uppercase tracking-tight">
                                    {{ $stock->product?->name }}
                                </a>
                                <div class="flex items-center gap-2 mt-1">
                                    <span class="text-[9px] font-bold text-muted-foreground/60 bg-muted/40 px-1.5 py-0.5 rounded border border-border/40 font-mono">
                                        {{ $stock->product?->sku }}
                                    </span>
                                    <span class="text-[9px] font-black uppercase tracking-widest text-primary/60">
                                        {{ $stock->product?->category?->name }}
                                    </span>
                                </div>
                            </div>
                        </div>
                    </td>

                    <!-- Warehouse -->
                    <td class="p-4">
                        <div class="flex items-center gap-2">
                            <div class="size-2 rounded-full bg-blue-500 shadow-[0_0_8px_rgba(59,130,246,0.4)]"></div>
                            <span class="text-[10px] font-black text-foreground uppercase tracking-widest">{{ $stock->warehouse->name }}</span>
                        </div>
                    </td>

                    <!-- Physical Qty -->
                    <td class="p-4 text-center">
                        <div class="text-xs font-black text-foreground">
                            {{ number_format($stock->quantity, 2) }}
                        </div>
                    </td>

                    <!-- Reserved -->
                    <td class="p-4 text-center">
                        @if($stock->reserved_qty > 0)
                            <div class="inline-flex items-center gap-1.5 px-2 py-1 rounded-lg bg-orange-500/10 text-orange-600 border border-orange-500/20 text-[10px] font-black tracking-tight">
                                <x-ui.icon name="lock" size="3" />
                                {{ number_format($stock->reserved_qty, 2) }}
                            </div>
                        @else
                            <span class="text-[10px] font-bold text-muted-foreground/30">—</span>
                        @endif
                    </td>

                    <!-- Available -->
                    <td class="p-4 text-center">
                        <div class="inline-flex items-center px-4 py-1.5 rounded-2xl {{ $isOutOfStock ? 'bg-destructive/10 text-destructive border-destructive/20 shadow-[0_0_15px_rgba(239,68,68,0.05)]' : ($isLowStock ? 'bg-orange-500/10 text-orange-600 border-orange-500/20 shadow-[0_0_15px_rgba(249,115,22,0.05)]' : 'bg-emerald-500/10 text-emerald-600 border-emerald-500/20') }} border text-sm font-black tracking-tighter">
                            {{ number_format($available, 2) }}
                        </div>
                    </td>

                    <!-- Dispatched -->
                    <td class="p-4 text-center">
                        <div class="text-[10px] font-black text-muted-foreground/60 uppercase tracking-tighter">
                             {{ number_format($stock->dispatched_qty ?? 0, 2) }}
                        </div>
                    </td>

                    <!-- Delivered -->
                    <td class="p-4 text-center border-r border-border/10">
                        <div class="inline-flex items-center px-2 py-0.5 rounded-lg bg-emerald-500/10 text-emerald-600 border border-emerald-500/20 text-[10px] font-black tracking-tight">
                            {{ number_format($stock->delivered_qty, 2) }}
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" class="p-20 text-center">
                        <div class="flex flex-col items-center gap-4">
                            <div class="size-20 rounded-3xl bg-muted/10 border border-border/40 flex items-center justify-center text-muted-foreground/20">
                                <x-ui.icon name="inbox" size="10" />
                            </div>
                            <div>
                                <p class="text-sm font-black uppercase tracking-widest text-foreground/40">No matching inventory found</p>
                                <p class="text-[10px] font-bold text-muted-foreground uppercase tracking-widest mt-1">Try adjusting your filters or search query</p>
                            </div>
                        </div>
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

@if($stocks->hasPages())
    <div class="p-6 border-t border-border/30 bg-muted/5">
        {{ $stocks->links() }}
    </div>
@endif
