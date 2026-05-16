@if($products->hasPages())
    <div class="p-4 border-b border-border/40 flex justify-end items-center">
        {{ $products->links() }}
    </div>
@endif

<x-ui.table>
    {{-- Table Header --}}
    <x-ui.table-header class="bg-muted/30">
        <x-ui.table-row class="border-b border-border/60">
            <x-ui.table-head class="w-10">
                <input type="checkbox" x-model="allSelected" @change="toggleAll" class="rounded border-border bg-background text-primary focus:ring-primary/20 transition-all">
            </x-ui.table-head>
            <x-ui.table-head class="text-[10px] font-black uppercase tracking-widest py-5">Product Identity</x-ui.table-head>
            <x-ui.table-head class="text-[10px] font-black uppercase tracking-widest">Market Value</x-ui.table-head>
            <x-ui.table-head class="text-[10px] font-black uppercase tracking-widest text-center">Inventory Metrics</x-ui.table-head>
            <x-ui.table-head class="text-[10px] font-black uppercase tracking-widest">Operational Status</x-ui.table-head>
            <x-ui.table-head class="text-right text-[10px] font-black uppercase tracking-widest">Management</x-ui.table-head>
        </x-ui.table-row>
    </x-ui.table-header>

    <x-ui.table-body>
        @forelse($products as $product)
            @php
                $onHand = (float) ($product->total_stock ?? 0);
                $reserved = (float) ($product->total_reserved ?? 0);
                $dispatched = (float) ($product->total_dispatched ?? 0);
                $available = max(0, $onHand - $reserved);
                
                // Overselling logic for UI display
                $oversellBadge = null;
                if ($product->allow_overselling) {
                    $oversellLimit = $product->overselling_qty ?: '∞';
                    $oversellBadge = "Limit: {$oversellLimit}";
                }
            @endphp
            <x-ui.table-row x-bind:class="selectedItems.includes({{ $product->id }}) ? 'bg-primary/5' : 'hover:bg-primary/[0.02]'" class="border-b border-border/40 group transition-all duration-300">
                <x-ui.table-cell>
                    <input type="checkbox" value="{{ $product->id }}" :checked="selectedItems.includes({{ $product->id }})" @change="$el.checked ? selectedItems.push({{ $product->id }}) : selectedItems = selectedItems.filter(i => i !== {{ $product->id }})" class="rounded border-border bg-background text-primary focus:ring-primary/20 transition-all">
                </x-ui.table-cell>

                <x-ui.table-cell>
                    <div class="flex items-center gap-4">
                        <div class="relative shrink-0">
                            <div class="size-14 rounded-2xl bg-gradient-to-br from-primary/10 to-primary/5 border border-primary/10 flex items-center justify-center shadow-inner group-hover:scale-105 transition-transform duration-500 overflow-hidden">
                                @if($product->image_path)
                                    <img src="{{ asset('storage/' . $product->image_path) }}" class="size-full object-cover" alt="{{ $product->name }}">
                                @else
                                    <x-ui.icon name="package" size="6" class="text-primary/30" />
                                @endif
                            </div>
                            <div class="absolute -bottom-1 -right-1 size-5 rounded-lg bg-background border border-border flex items-center justify-center shadow-sm">
                                <span class="text-[8px] font-black text-muted-foreground">ID</span>
                            </div>
                        </div>
                        <div class="flex flex-col min-w-0">
                            <a href="{{ route('products.show', $product) }}" class="text-sm font-black text-foreground truncate group-hover:text-primary transition-colors tracking-tight">
                                {{ $product->name }}
                            </a>
                            <div class="flex items-center gap-2 mt-1.5">
                                <span class="text-[9px] font-black font-mono bg-muted/30 px-2 py-0.5 rounded-md border border-border/40 text-muted-foreground uppercase tracking-tighter flex items-center gap-1.5">
                                     SKU: {{ $product->sku }}
                                     @if($product->is_sku_enabled)
                                         <span class="size-1 rounded-full bg-emerald-500 shadow-[0_0_5px_rgba(16,185,129,0.5)]"></span>
                                     @else
                                         <span class="size-1 rounded-full bg-destructive"></span>
                                     @endif
                                 </span>
                                 @if(!$product->is_sku_enabled)
                                     <span class="text-[8px] font-black text-destructive uppercase tracking-widest bg-destructive/5 px-2 py-0.5 rounded-md border border-destructive/20">
                                         Disabled SKU
                                     </span>
                                 @endif
                                 @if($product->category)
                                    <span class="text-[9px] font-bold text-primary/60 uppercase tracking-widest bg-primary/5 px-2 py-0.5 rounded-md">
                                        {{ $product->category->name }}
                                    </span>
                                @endif
                            </div>
                        </div>
                    </div>
                </x-ui.table-cell>

                <x-ui.table-cell>
                    <div class="flex flex-col gap-1">
                        <div class="flex items-baseline gap-1">
                            <span class="text-xs font-black text-foreground">₹{{ number_format($product->selling_price, 2) }}</span>
                            @if($product->mrp > $product->selling_price)
                                <span class="text-[10px] text-muted-foreground/40 line-through">₹{{ number_format($product->mrp, 2) }}</span>
                            @endif
                        </div>
                        @if($product->taxRate)
                            <div class="flex items-center gap-1">
                                <x-ui.icon name="percent" size="2" class="text-emerald-500/50" />
                                <span class="text-[9px] font-bold text-emerald-600 uppercase tracking-widest">
                                    {{ $product->taxRate->name }}
                                </span>
                            </div>
                        @endif
                    </div>
                </x-ui.table-cell>

                <x-ui.table-cell>
                    <div class="flex flex-col items-center gap-3">
                        <div class="grid grid-cols-3 gap-4 w-full max-w-[200px]">
                            <div class="flex flex-col items-center text-center">
                                <span class="text-[10px] font-black text-foreground">{{ number_format($onHand) }}</span>
                                <span class="text-[8px] font-bold text-muted-foreground uppercase tracking-widest">On Hand</span>
                            </div>
                            <div class="flex flex-col items-center text-center border-x border-border/40">
                                <span class="text-[10px] font-black text-orange-500">{{ number_format($reserved) }}</span>
                                <span class="text-[8px] font-bold text-muted-foreground uppercase tracking-widest">Reserved</span>
                            </div>
                            <div class="flex flex-col items-center text-center">
                                <span class="text-[10px] font-black text-emerald-500">{{ number_format($available) }}</span>
                                <span class="text-[8px] font-bold text-muted-foreground uppercase tracking-widest">Available</span>
                            </div>
                        </div>
                        
                        <div class="w-full max-w-[160px] space-y-1.5">
                            <div class="h-1.5 bg-muted/30 rounded-full overflow-hidden flex shadow-inner">
                                @php
                                    $reservedPercent = $onHand > 0 ? ($reserved / $onHand) * 100 : 0;
                                    $availablePercent = $onHand > 0 ? ($available / $onHand) * 100 : 0;
                                @endphp
                                <div class="h-full bg-orange-500 transition-all duration-1000" style="width: {{ $reservedPercent }}%"></div>
                                <div class="h-full bg-emerald-500 transition-all duration-1000 shadow-[0_0_10px_rgba(16,185,129,0.3)]" style="width: {{ $availablePercent }}%"></div>
                            </div>
                            @if($product->allow_overselling)
                                <div class="flex items-center justify-center gap-1.5 bg-purple-500/5 border border-purple-500/20 py-0.5 px-2 rounded-lg">
                                    <x-ui.icon name="zap" size="2" class="text-purple-500 animate-pulse" />
                                    <span class="text-[8px] font-black text-purple-600 uppercase tracking-widest">Overselling Enabled ({{ $product->overselling_qty ?: '∞' }})</span>
                                </div>
                            @endif
                        </div>
                    </div>
                </x-ui.table-cell>

                <x-ui.table-cell>
                    <div class="flex flex-col gap-2">
                        @php
                            $statusColors = [
                                'active' => 'bg-emerald-500/10 text-emerald-600 border-emerald-500/20',
                                'draft' => 'bg-amber-500/10 text-amber-600 border-amber-500/20',
                                'out_of_stock' => 'bg-red-500/10 text-red-600 border-red-500/20',
                            ];
                            $colorClass = $statusColors[$product->status] ?? 'bg-muted/10 text-muted-foreground border-border/40';
                        @endphp
                        <span class="inline-flex items-center justify-center px-3 py-1 rounded-full text-[9px] font-black uppercase tracking-widest border {{ $colorClass }}">
                            <span class="size-1 rounded-full bg-current mr-2 animate-pulse"></span>
                            {{ str_replace('_', ' ', $product->status) }}
                        </span>
                        <div class="flex items-center gap-2 px-1">
                            <x-ui.icon name="refresh-cw" size="2" class="text-muted-foreground/40" />
                            <span class="text-[9px] font-bold text-muted-foreground/60 uppercase tracking-tighter">
                                {{ $product->updated_at?->diffForHumans() ?? 'No activity' }}
                            </span>
                        </div>
                    </div>
                </x-ui.table-cell>

                <x-ui.table-cell class="text-right">
                    <div class="flex justify-end items-center gap-1.5 opacity-60 group-hover:opacity-100 transition-opacity duration-300">
                        @if(method_exists($product, 'trashed') && $product->trashed())
                            <form action="{{ route('products.restore', $product->id) }}" method="POST">
                                @csrf
                                <x-ui.button variant="ghost" size="sm" type="submit" class="h-8 px-4 text-[9px] font-black uppercase tracking-widest text-emerald-600 hover:bg-emerald-500/10 rounded-xl border border-transparent hover:border-emerald-500/20 transition-all">
                                    Restore
                                </x-ui.button>
                            </form>
                            <form action="{{ route('products.force-delete', $product->id) }}" method="POST" onsubmit="return confirm('Permanently delete?')">
                                @csrf @method('DELETE')
                                <x-ui.button variant="ghost" size="icon" type="submit" class="size-8 text-destructive hover:bg-destructive/10 rounded-xl border border-transparent hover:border-destructive/20 transition-all">
                                    <x-ui.icon name="trash-2" size="4" />
                                </x-ui.button>
                            </form>
                        @else
                            <a href="{{ route('products.show', $product) }}" title="View Details">
                                <x-ui.button variant="ghost" size="icon" class="size-8 text-muted-foreground hover:text-primary hover:bg-primary/10 rounded-xl border border-transparent hover:border-primary/20 transition-all">
                                    <x-ui.icon name="eye" size="4" />
                                </x-ui.button>
                            </a>
                            <a href="{{ route('products.edit', $product) }}" title="Edit Product">
                                <x-ui.button variant="ghost" size="icon" class="size-8 text-muted-foreground hover:text-primary hover:bg-primary/10 rounded-xl border border-transparent hover:border-primary/20 transition-all">
                                    <x-ui.icon name="edit" size="4" />
                                </x-ui.button>
                            </a>
                            <form action="{{ route('products.destroy', $product) }}" method="POST" onsubmit="return confirm('Disable product?')">
                                @csrf @method('DELETE')
                                <x-ui.button variant="ghost" size="icon" type="submit" class="size-8 text-muted-foreground hover:text-destructive hover:bg-destructive/10 rounded-xl border border-transparent hover:border-destructive/20 transition-all">
                                    <x-ui.icon name="slash" size="4" />
                                </x-ui.button>
                            </form>
                        @endif
                    </div>
                </x-ui.table-cell>
            </x-ui.table-row>
        @empty
            <x-ui.table-row>
                <x-ui.table-cell colspan="6" class="h-64 text-center">
                    <div class="flex flex-col items-center justify-center gap-6 opacity-30">
                        <x-ui.icon name="package" size="16" stroke-width="1" />
                        <div class="space-y-1">
                            <p class="text-sm font-black uppercase tracking-[0.3em]">No items found</p>
                            <p class="text-[10px] font-bold uppercase tracking-widest">Adjust your filters or add a new product</p>
                        </div>
                        <x-ui.button variant="outline" size="sm" onclick="location.reload()" class="rounded-xl border-border px-6">
                            Reset View
                        </x-ui.button>
                    </div>
                </x-ui.table-cell>
            </x-ui.table-row>
        @endforelse
    </x-ui.table-body>
</x-ui.table>

@if($products->hasPages())
    <div class="p-4 border-t border-border/40 bg-muted/5 flex justify-end items-center rounded-b-3xl">
        {{ $products->links() }}
    </div>
@endif