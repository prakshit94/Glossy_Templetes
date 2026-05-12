@if($products->hasPages())
    <div class="p-4 border-b border-border/40 flex justify-end items-center">
        {{ $products->links() }}
    </div>
@endif

<x-ui.table>
    <x-ui.table-header class="bg-muted/30">
        <x-ui.table-row class="border-b border-border/60">
            <x-ui.table-head class="w-10">
                <input type="checkbox" x-model="allSelected" @change="toggleAll" 
                    class="rounded border-border bg-background text-primary focus:ring-primary/20">
            </x-ui.table-head>
            <x-ui.table-head>Product Identity</x-ui.table-head>
            <x-ui.table-head>Price & Tax</x-ui.table-head>
            <x-ui.table-head class="text-center">Specifications</x-ui.table-head>
            <x-ui.table-head class="text-center">Inventory</x-ui.table-head>
            <x-ui.table-head>Activity</x-ui.table-head>
            <x-ui.table-head class="text-right">Actions</x-ui.table-head>
        </x-ui.table-row>
    </x-ui.table-header>
    <x-ui.table-body>
        @forelse($products as $product)
        <x-ui.table-row x-bind:class="selectedItems.includes({{ $product->id }}) ? 'bg-primary/5' : 'hover:bg-primary/[0.02] transition-colors'" class="border-b border-border/40 group">
            <!-- Selection -->
            <x-ui.table-cell>
                <input type="checkbox" name="product_ids[]" value="{{ $product->id }}" :checked="selectedItems.includes({{ $product->id }})" @change="if($el.checked) { if(!selectedItems.includes({{ $product->id }})) selectedItems.push({{ $product->id }}) } else { selectedItems = selectedItems.filter(i => i !== {{ $product->id }}) }"
                    class="rounded border-border bg-background text-primary focus:ring-primary/20">
            </x-ui.table-cell>

            <!-- Product Identity -->
            <x-ui.table-cell>
                <div class="flex items-center gap-4">
                    <div class="relative shrink-0">
                        <div class="size-12 rounded-2xl bg-gradient-to-br from-primary/20 to-primary/5 border border-primary/10 flex items-center justify-center shadow-inner group-hover:scale-110 transition-transform duration-500 overflow-hidden">
                            @if($product->image_path)
                                <img src="{{ asset('storage/' . $product->image_path) }}" class="size-full object-cover" alt="">
                            @else
                                <x-ui.icon name="package" size="5" class="text-primary/40" />
                            @endif
                        </div>
                    </div>
                    <div class="flex flex-col min-w-0">
                        <a href="{{ route('products.show', $product) }}" class="text-sm font-bold text-foreground truncate group-hover:text-primary transition-colors">
                            {{ $product->name }}
                        </a>
                        <div class="flex items-center gap-2 mt-0.5">
                            <span class="text-[9px] font-mono font-bold text-muted-foreground/40 tracking-tighter">#{{ sprintf('%03d', $product->id) }}</span>
                            <div class="flex items-center gap-1 bg-muted/10 px-1.5 py-0.5 rounded border border-border/40" title="SKU Code">
                                <x-ui.icon name="hash" size="2" class="text-primary/60" />
                                <span class="text-[8px] font-black font-mono text-muted-foreground/60 uppercase tracking-tighter">{{ $product->sku }}</span>
                            </div>
                            @if($product->hsnCode)
                                <div class="flex items-center gap-1 bg-muted/5 px-1.5 py-0.5 rounded border border-border/20" title="HSN Code">
                                    <span class="text-[8px] font-bold text-muted-foreground/40 italic">HSN: {{ $product->hsnCode->code }}</span>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </x-ui.table-cell>

            <!-- Financials -->
            <x-ui.table-cell>
                <div class="flex flex-col">
                    <span class="text-sm font-bold text-primary tracking-tight">₹{{ number_format($product->selling_price, 2) }}</span>
                    <div class="flex items-center gap-1.5 mt-0.5">
                        <span class="text-[9px] text-muted-foreground/40 line-through">₹{{ number_format($product->mrp, 2) }}</span>
                        @if($product->taxRate)
                            <span class="text-[8px] font-black text-emerald-500/60 uppercase tracking-widest">{{ $product->taxRate->name }}</span>
                        @endif
                    </div>
                </div>
            </x-ui.table-cell>

            <x-ui.table-cell>
                <div class="flex flex-col items-center gap-2">
                    <div class="flex flex-wrap justify-center gap-1 max-w-[120px]">
                        <x-ui.badge variant="outline" className="text-[8px] px-1.5 py-0.5 rounded-lg {{ $product->manage_stock ? 'bg-emerald-500/10 border-emerald-500/20 text-emerald-600' : 'bg-muted/10 border-border/40 text-muted-foreground/40' }} font-black uppercase tracking-tighter">
                            <x-ui.icon name="archive" size="2" class="mr-1 inline" /> Stocked
                        </x-ui.badge>
                        <x-ui.badge variant="outline" className="text-[8px] px-1.5 py-0.5 rounded-lg {{ $product->allow_overselling ? 'bg-purple-500/10 border-purple-500/20 text-purple-600' : 'bg-muted/10 border-border/40 text-muted-foreground/40' }} font-black uppercase tracking-tighter">
                            <x-ui.icon name="zap" size="2" class="mr-1 inline" /> Oversell
                        </x-ui.badge>
                        @if($product->weight)
                            <x-ui.badge variant="outline" className="text-[8px] px-1.5 py-0.5 rounded-lg bg-orange-500/10 border-orange-500/20 text-orange-600 font-black uppercase tracking-tighter">
                                <x-ui.icon name="monitor" size="2" class="mr-1 inline" /> {{ $product->weight }}
                            </x-ui.badge>
                        @endif
                        @if($product->category)
                            <x-ui.badge variant="outline" className="text-[8px] px-1.5 py-0.5 rounded-lg bg-blue-500/10 border-blue-500/20 text-blue-600 font-black uppercase tracking-tighter">
                                {{ $product->category->name }}
                            </x-ui.badge>
                        @endif
                    </div>
                </div>
            </x-ui.table-cell>

            <x-ui.table-cell>
                <div class="flex flex-col items-center gap-1.5">
                    <div class="flex flex-col items-center">
                        <span class="text-sm font-bold {{ $product->total_stock <= $product->min_stock_level ? 'text-orange-500' : 'text-foreground' }}">
                            {{ number_format($product->total_stock) }}
                        </span>
                        <span class="text-[8px] font-black text-muted-foreground/40 uppercase tracking-widest mt-[-2px]">Units</span>
                    </div>
                    <div class="w-16 h-1 bg-muted/20 rounded-full overflow-hidden relative shadow-inner">
                        @php
                            $percent = $product->total_stock > 0 ? min(100, ($product->total_stock / ($product->min_stock_level * 2 ?: 100)) * 100) : 0;
                            $barColor = $product->total_stock <= 0 ? 'bg-red-500' : ($product->total_stock <= $product->min_stock_level ? 'bg-orange-500' : 'bg-emerald-500');
                        @endphp
                        <div class="h-full {{ $barColor }} transition-all duration-1000" style="width: {{ $percent }}%"></div>
                    </div>
                    @php
                        $statusColors = [
                            'active' => 'bg-emerald-500/10 text-emerald-500 border-emerald-500/20',
                            'draft' => 'bg-blue-500/10 text-blue-500 border-blue-500/20',
                            'out_of_stock' => 'bg-red-500/10 text-red-500 border-red-500/20',
                        ];
                        $colorClass = $statusColors[$product->status] ?? 'bg-muted/10 text-muted-foreground border-border/40';
                    @endphp
                    <span class="px-1.5 py-0 rounded border {{ $colorClass }} text-[7px] font-black uppercase tracking-tighter">
                        {{ str_replace('_', ' ', $product->status) }}
                    </span>
                </div>
            </x-ui.table-cell>

            <!-- Inventory Ledger -->
            <x-ui.table-cell>
                <div class="flex flex-col gap-1">
                    <div class="flex items-center gap-1.5">
                        <x-ui.icon name="plus" size="3" class="text-muted-foreground/40" />
                        <span class="text-[10px] font-bold text-foreground/80 tracking-tight">Added {{ $product->created_at?->diffForHumans() ?? 'N/A' }}</span>
                    </div>
                    <div class="flex items-center gap-1.5">
                        <x-ui.icon name="refresh-cw" size="3" class="text-muted-foreground/30" />
                        <span class="text-[9px] font-bold text-muted-foreground/40 tracking-tighter uppercase">Sync {{ $product->updated_at?->diffForHumans() ?? 'N/A' }}</span>
                    </div>
                </div>
            </x-ui.table-cell>

            <!-- Actions -->
            <x-ui.table-cell class="text-right">
                <div class="flex justify-end gap-1.5 transition-all duration-300">
                    @if(method_exists($product, 'trashed') && $product->trashed())
                        <form action="{{ route('products.restore', $product->id) }}" method="POST">
                            @csrf
                            <x-ui.button variant="ghost" size="sm" type="submit" class="h-8 px-3 text-[10px] font-black uppercase tracking-widest text-emerald-600 hover:bg-emerald-500/10 rounded-xl border border-transparent hover:border-emerald-500/20">
                                Restore
                            </x-ui.button>
                        </form>
                        <form action="{{ route('products.force-delete', $product->id) }}" method="POST" onsubmit="return confirm('PERMANENTLY delete this product?')">
                            @csrf
                            @method('DELETE')
                            <x-ui.button variant="ghost" size="icon" type="submit" class="size-8 text-destructive hover:bg-destructive/10 rounded-xl border border-transparent hover:border-destructive/20">
                                <x-ui.icon name="trash-2" size="4" />
                            </x-ui.button>
                        </form>
                    @else
                        <a href="{{ route('products.show', $product) }}">
                            <x-ui.button variant="ghost" size="icon" class="size-8 text-muted-foreground hover:text-primary hover:bg-primary/10 rounded-xl border border-transparent hover:border-primary/20 transition-all">
                                <x-ui.icon name="eye" size="4" />
                            </x-ui.button>
                        </a>
                        <a href="{{ route('products.edit', $product) }}">
                            <x-ui.button variant="ghost" size="icon" class="size-8 text-muted-foreground hover:text-primary hover:bg-primary/10 rounded-xl border border-transparent hover:border-primary/20 transition-all">
                                <x-ui.icon name="edit" size="4" />
                            </x-ui.button>
                        </a>
                        <form action="{{ route('products.destroy', $product) }}" method="POST" onsubmit="return confirm('Disable this product?')">
                            @csrf
                            @method('DELETE')
                            <x-ui.button variant="ghost" size="icon" type="submit" class="size-8 text-muted-foreground hover:text-destructive hover:bg-destructive/10 rounded-xl border border-transparent hover:border-destructive/20 transition-all" title="Disable Product">
                                <x-ui.icon name="slash" size="4" />
                            </x-ui.button>
                        </form>
                    @endif
                </div>
            </x-ui.table-cell>
        </x-ui.table-row>
        @empty
        <x-ui.table-row>
            <x-ui.table-cell colspan="7" class="h-60 text-center">
                <div class="flex flex-col items-center justify-center gap-4 opacity-30">
                    <x-ui.icon name="package" size="16" stroke-width="1" />
                    <p class="text-sm font-black uppercase tracking-[0.2em]">No products matching catalog filters</p>
                    <x-ui.button variant="outline" size="sm" onclick="location.reload()" class="rounded-xl border-border">Reset Catalog</x-ui.button>
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