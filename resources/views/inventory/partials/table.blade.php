<div class="overflow-x-auto">
    <table class="w-full text-left border-collapse">
        <thead>
            <tr class="bg-muted/5 border-b border-border/40">
                <th class="p-4 text-[10px] font-black uppercase tracking-widest text-muted-foreground/60">Product Details</th>
                <th class="p-4 text-[10px] font-black uppercase tracking-widest text-muted-foreground/60">Warehouse</th>
                <th class="p-4 text-[10px] font-black uppercase tracking-widest text-muted-foreground/60">Stock Control</th>
                <th class="p-4 text-[10px] font-black uppercase tracking-widest text-muted-foreground/60 text-center">Current Stock</th>
                <th class="p-4 text-[10px] font-black uppercase tracking-widest text-muted-foreground/60 text-center">Min Level</th>
                <th class="p-4 text-[10px] font-black uppercase tracking-widest text-muted-foreground/60 text-right">Stock Update</th>
            </tr>
        </thead>
        <tbody>
            @forelse($stocks as $stock)
                <tr class="border-b border-border/30 hover:bg-muted/10 transition-colors group">
                    <td class="p-4">
                        <div class="flex items-center gap-3">
                            <div class="size-10 rounded-xl bg-gradient-to-tr from-orange-500/20 to-orange-500/5 border border-orange-500/10 flex items-center justify-center overflow-hidden shrink-0 shadow-inner group-hover:scale-105 transition-transform">
                                @if($stock->product?->image_path)
                                    <img src="{{ asset('storage/' . $stock->product->image_path) }}" alt="{{ $stock->product->name }}" class="size-full object-cover">
                                @else
                                    <x-ui.icon name="package" size="5" class="text-orange-500/40" />
                                @endif
                            </div>
                            <div>
                                @if($stock->product)
                                    <a href="{{ route('products.show', $stock->product) }}" class="text-sm font-bold text-foreground group-hover:text-primary transition-colors leading-tight block">
                                        {{ $stock->product->name }}
                                    </a>
                                    <div class="text-[10px] text-muted-foreground font-mono mt-1 uppercase tracking-tight">{{ $stock->product->sku }}</div>
                                @else
                                    <span class="text-sm font-bold text-red-500 italic">Product Missing</span>
                                @endif
                            </div>
                        </div>
                    </td>
                    <td class="p-4">
                        <div class="flex items-center gap-2">
                            <div class="size-2 rounded-full bg-blue-500/40"></div>
                            <span class="text-xs font-bold text-muted-foreground uppercase tracking-widest">{{ $stock->warehouse->name }}</span>
                        </div>
                    </td>
                    <td class="p-4">
                        <div class="flex flex-col gap-1">
                            <div class="flex items-center gap-2">
                                <span class="text-[9px] font-black uppercase tracking-widest {{ $stock->product?->manage_stock ? 'text-emerald-500' : 'text-muted-foreground/30' }}">Manage Stock</span>
                            </div>
                            <div class="flex items-center gap-2">
                                <span class="text-[9px] font-black uppercase tracking-widest {{ $stock->product?->allow_overselling ? 'text-orange-500' : 'text-muted-foreground/30' }}">Allow Oversell</span>
                            </div>
                        </div>
                    </td>
                    <td class="p-4 text-center">
                        <div class="inline-flex items-center px-4 py-1.5 rounded-2xl {{ $stock->quantity <= ($stock->product?->min_stock_level ?? 0) ? 'bg-red-500/10 text-red-500 border-red-500/20 shadow-[0_0_15px_rgba(239,68,68,0.05)]' : 'bg-emerald-500/10 text-emerald-500 border-emerald-500/20' }} border text-sm font-black tracking-tight">
                            {{ number_format($stock->quantity) }}
                        </div>
                    </td>
                    <td class="p-4 text-center text-xs font-bold text-muted-foreground opacity-60">
                        {{ number_format($stock->product?->min_stock_level ?? 0) }}
                    </td>
                    <td class="p-4 text-right">
                        <form action="{{ route('inventory.update', $stock) }}" method="POST" class="flex items-center justify-end gap-2">
                            @csrf
                            @method('PUT')
                            <div class="relative">
                                <input type="number" name="quantity" value="{{ $stock->quantity }}" class="w-24 h-9 px-3 rounded-xl border border-border/40 bg-background/50 text-center text-xs font-black focus:ring-2 focus:ring-primary/20 transition-all">
                                <div class="absolute -top-6 left-1/2 -translate-x-1/2 text-[8px] font-black uppercase text-muted-foreground/40 opacity-0 group-hover:opacity-100 transition-opacity whitespace-nowrap">Edit Qty</div>
                            </div>
                            <button type="submit" class="size-9 rounded-xl bg-primary text-primary-foreground flex items-center justify-center hover:bg-primary/80 transition-all shadow-lg shadow-primary/20 hover:scale-105 active:scale-95">
                                <x-ui.icon name="check" size="4" />
                            </button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" class="p-12 text-center">
                        <div class="flex flex-col items-center gap-3 opacity-20">
                            <x-ui.icon name="inbox" size="12" />
                            <p class="text-sm font-black uppercase tracking-widest">No stock records found</p>
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
