@if($products->hasPages())
    <div class="p-4 border-b border-border/40 flex justify-end items-center">
        {{ $products->links() }}
    </div>
@endif

<x-ui.table>
    {{-- Table Header --}}
    <x-ui.table-header class="bg-muted/30">
        <x-ui.table-row class="border-b border-border/60">
            
            {{-- Select All --}}
            <x-ui.table-head class="w-10">
                <input
                    type="checkbox"
                    x-model="allSelected"
                    @change="toggleAll"
                    class="rounded border-border bg-background text-primary focus:ring-primary/20"
                >
            </x-ui.table-head>

            {{-- Product --}}
            <x-ui.table-head>Product</x-ui.table-head>

            {{-- Price --}}
            <x-ui.table-head>Pricing</x-ui.table-head>

            {{-- Specs --}}
            <x-ui.table-head class="text-center">Details</x-ui.table-head>

            {{-- Stock --}}
            <x-ui.table-head class="text-center">Stock</x-ui.table-head>

            {{-- Activity --}}
            <x-ui.table-head>Activity</x-ui.table-head>

            {{-- Actions --}}
            <x-ui.table-head class="text-right">Actions</x-ui.table-head>

        </x-ui.table-row>
    </x-ui.table-header>

    <x-ui.table-body>

        @forelse($products as $product)

            <x-ui.table-row
                x-bind:class="selectedItems.includes({{ $product->id }}) 
                    ? 'bg-primary/5' 
                    : 'hover:bg-primary/[0.02] transition-colors'"
                class="border-b border-border/40 group"
            >

                {{-- Checkbox --}}
                <x-ui.table-cell>
                    <input
                        type="checkbox"
                        name="product_ids[]"
                        value="{{ $product->id }}"
                        :checked="selectedItems.includes({{ $product->id }})"
                        @change="
                            if($el.checked) {
                                if(!selectedItems.includes({{ $product->id }})) {
                                    selectedItems.push({{ $product->id }})
                                }
                            } else {
                                selectedItems = selectedItems.filter(i => i !== {{ $product->id }})
                            }
                        "
                        class="rounded border-border bg-background text-primary focus:ring-primary/20"
                    >
                </x-ui.table-cell>

                {{-- Product Info --}}
                <x-ui.table-cell>
                    <div class="flex items-center gap-4">

                        {{-- Product Image --}}
                        <div class="relative shrink-0">
                            <div class="size-12 rounded-2xl bg-gradient-to-br from-primary/20 to-primary/5 border border-primary/10 flex items-center justify-center shadow-inner group-hover:scale-110 transition-transform duration-500 overflow-hidden">

                                @if($product->image_path)
                                    <img
                                        src="{{ asset('storage/' . $product->image_path) }}"
                                        class="size-full object-cover"
                                        alt="{{ $product->name }}"
                                    >
                                @else
                                    <x-ui.icon
                                        name="package"
                                        size="5"
                                        class="text-primary/40"
                                    />
                                @endif

                            </div>
                        </div>

                        {{-- Product Details --}}
                        <div class="flex flex-col min-w-0">

                            <a
                                href="{{ route('products.show', $product) }}"
                                class="text-sm font-bold text-foreground truncate group-hover:text-primary transition-colors"
                            >
                                {{ $product->name }}
                            </a>

                            <div class="flex items-center gap-2 mt-1 flex-wrap">

                                {{-- Product ID --}}
                                <span class="text-[10px] font-mono font-bold text-muted-foreground/50">
                                    #{{ sprintf('%03d', $product->id) }}
                                </span>

                                {{-- SKU --}}
                                <div
                                    class="flex items-center gap-1 bg-muted/10 px-1.5 py-0.5 rounded border border-border/40"
                                    title="SKU"
                                >
                                    <x-ui.icon
                                        name="hash"
                                        size="2"
                                        class="text-primary/60"
                                    />

                                    <span class="text-[9px] font-black font-mono text-muted-foreground/70 uppercase">
                                        {{ $product->sku }}
                                    </span>
                                </div>

                                {{-- HSN --}}
                                @if($product->hsnCode)
                                    <div
                                        class="flex items-center gap-1 bg-muted/5 px-1.5 py-0.5 rounded border border-border/20"
                                        title="HSN Code"
                                    >
                                        <span class="text-[9px] font-semibold text-muted-foreground/60">
                                            HSN: {{ $product->hsnCode->code }}
                                        </span>
                                    </div>
                                @endif

                            </div>
                        </div>
                    </div>
                </x-ui.table-cell>

                {{-- Pricing --}}
                <x-ui.table-cell>

                    <div class="flex flex-col">

                        {{-- Selling Price --}}
                        <span class="text-sm font-bold text-primary tracking-tight">
                            ₹{{ number_format($product->selling_price, 2) }}
                        </span>

                        <div class="flex items-center gap-2 mt-1">

                            {{-- MRP --}}
                            <span class="text-[10px] text-muted-foreground/50 line-through">
                                ₹{{ number_format($product->mrp, 2) }}
                            </span>

                            {{-- Tax --}}
                            @if($product->taxRate)
                                <span class="text-[9px] font-bold text-emerald-600 uppercase">
                                    {{ $product->taxRate->name }}
                                </span>
                            @endif

                        </div>
                    </div>

                </x-ui.table-cell>

                {{-- Specifications --}}
                <x-ui.table-cell>

                    <div class="flex flex-col items-center gap-2">

                        <div class="flex flex-wrap justify-center gap-1 max-w-[140px]">

                            {{-- Stock Managed --}}
                            <x-ui.badge
                                variant="outline"
                                className="
                                    text-[9px] px-2 py-1 rounded-lg font-semibold
                                    {{ $product->manage_stock
                                        ? 'bg-emerald-500/10 border-emerald-500/20 text-emerald-600'
                                        : 'bg-muted/10 border-border/40 text-muted-foreground/50'
                                    }}
                                "
                            >
                                <x-ui.icon name="archive" size="2" class="mr-1 inline" />
                                Stock
                            </x-ui.badge>

                            {{-- Overselling --}}
                            <x-ui.badge
                                variant="outline"
                                className="
                                    text-[9px] px-2 py-1 rounded-lg font-semibold
                                    {{ $product->allow_overselling
                                        ? 'bg-purple-500/10 border-purple-500/20 text-purple-600'
                                        : 'bg-muted/10 border-border/40 text-muted-foreground/50'
                                    }}
                                "
                            >
                                <x-ui.icon name="zap" size="2" class="mr-1 inline" />
                                Oversell
                            </x-ui.badge>

                            {{-- Weight --}}
                            @if($product->weight)
                                <x-ui.badge
                                    variant="outline"
                                    className="text-[9px] px-2 py-1 rounded-lg bg-orange-500/10 border-orange-500/20 text-orange-600 font-semibold"
                                >
                                    <x-ui.icon name="monitor" size="2" class="mr-1 inline" />
                                    {{ $product->weight }}
                                </x-ui.badge>
                            @endif

                            {{-- Category --}}
                            @if($product->category)
                                <x-ui.badge
                                    variant="outline"
                                    className="text-[9px] px-2 py-1 rounded-lg bg-blue-500/10 border-blue-500/20 text-blue-600 font-semibold"
                                >
                                    {{ $product->category->name }}
                                </x-ui.badge>
                            @endif

                        </div>
                    </div>

                </x-ui.table-cell>

                {{-- Stock --}}
                <x-ui.table-cell>

                    <div class="flex flex-col items-center gap-2">

                        {{-- Quantity --}}
                        <div class="flex flex-col items-center">
                            <span class="text-sm font-bold {{ $product->total_stock <= $product->min_stock_level ? 'text-orange-500' : 'text-foreground' }}">
                                {{ number_format($product->total_stock) }}
                            </span>

                            <span class="text-[9px] font-semibold text-muted-foreground/50 uppercase">
                                Units
                            </span>
                        </div>

                        {{-- Stock Bar --}}
                        <div class="w-16 h-1.5 bg-muted/20 rounded-full overflow-hidden relative shadow-inner">

                            @php
                                $percent = $product->total_stock > 0
                                    ? min(100, ($product->total_stock / ($product->min_stock_level * 2 ?: 100)) * 100)
                                    : 0;

                                $barColor = $product->total_stock <= 0
                                    ? 'bg-red-500'
                                    : ($product->total_stock <= $product->min_stock_level
                                        ? 'bg-orange-500'
                                        : 'bg-emerald-500');
                            @endphp

                            <div
                                class="h-full {{ $barColor }} transition-all duration-1000"
                                style="width: {{ $percent }}%"
                            ></div>

                        </div>

                        {{-- Status Badge --}}
                        @php
                            $statusColors = [
                                'active' => 'bg-emerald-500/15 text-emerald-700 border border-emerald-500/30',
                                'draft' => 'bg-blue-500/15 text-blue-700 border border-blue-500/30',
                                'out_of_stock' => 'bg-red-500/15 text-red-700 border border-red-500/30',
                            ];

                            $colorClass = $statusColors[$product->status]
                                ?? 'bg-muted/10 text-muted-foreground border border-border/40';
                        @endphp

                        <span class="px-2 py-1 rounded-md text-[10px] font-bold uppercase tracking-wide {{ $colorClass }}">
                            {{ str_replace('_', ' ', $product->status) }}
                        </span>

                    </div>

                </x-ui.table-cell>

                {{-- Activity --}}
                <x-ui.table-cell>

                    <div class="flex flex-col gap-2">

                        {{-- Created --}}
                        <div class="flex items-center gap-2">
                            <x-ui.icon
                                name="plus"
                                size="3"
                                class="text-muted-foreground/40"
                            />

                            <span class="text-[11px] font-medium text-foreground/80">
                                Added {{ $product->created_at?->diffForHumans() ?? 'N/A' }}
                            </span>
                        </div>

                        {{-- Updated --}}
                        <div class="flex items-center gap-2">
                            <x-ui.icon
                                name="refresh-cw"
                                size="3"
                                class="text-muted-foreground/30"
                            />

                            <span class="text-[10px] font-medium text-muted-foreground/50 uppercase">
                                Updated {{ $product->updated_at?->diffForHumans() ?? 'N/A' }}
                            </span>
                        </div>

                    </div>

                </x-ui.table-cell>

                {{-- Actions --}}
                <x-ui.table-cell class="text-right">

                    <div class="flex justify-end gap-1.5 transition-all duration-300">

                        @if(method_exists($product, 'trashed') && $product->trashed())

                            {{-- Restore --}}
                            <form
                                action="{{ route('products.restore', $product->id) }}"
                                method="POST"
                            >
                                @csrf

                                <x-ui.button
                                    variant="ghost"
                                    size="sm"
                                    type="submit"
                                    class="h-8 px-3 text-[10px] font-bold uppercase tracking-wider text-emerald-600 hover:bg-emerald-500/10 rounded-xl border border-transparent hover:border-emerald-500/20"
                                >
                                    Restore
                                </x-ui.button>
                            </form>

                            {{-- Permanent Delete --}}
                            <form
                                action="{{ route('products.force-delete', $product->id) }}"
                                method="POST"
                                onsubmit="return confirm('PERMANENTLY delete this product?')"
                            >
                                @csrf
                                @method('DELETE')

                                <x-ui.button
                                    variant="ghost"
                                    size="icon"
                                    type="submit"
                                    class="size-8 text-destructive hover:bg-destructive/10 rounded-xl border border-transparent hover:border-destructive/20"
                                >
                                    <x-ui.icon name="trash-2" size="4" />
                                </x-ui.button>
                            </form>

                        @else

                            {{-- View --}}
                            <a href="{{ route('products.show', $product) }}">
                                <x-ui.button
                                    variant="ghost"
                                    size="icon"
                                    class="size-8 text-muted-foreground hover:text-primary hover:bg-primary/10 rounded-xl border border-transparent hover:border-primary/20 transition-all"
                                >
                                    <x-ui.icon name="eye" size="4" />
                                </x-ui.button>
                            </a>

                            {{-- Edit --}}
                            <a href="{{ route('products.edit', $product) }}">
                                <x-ui.button
                                    variant="ghost"
                                    size="icon"
                                    class="size-8 text-muted-foreground hover:text-primary hover:bg-primary/10 rounded-xl border border-transparent hover:border-primary/20 transition-all"
                                >
                                    <x-ui.icon name="edit" size="4" />
                                </x-ui.button>
                            </a>

                            {{-- Disable --}}
                            <form
                                action="{{ route('products.destroy', $product) }}"
                                method="POST"
                                onsubmit="return confirm('Disable this product?')"
                            >
                                @csrf
                                @method('DELETE')

                                <x-ui.button
                                    variant="ghost"
                                    size="icon"
                                    type="submit"
                                    class="size-8 text-muted-foreground hover:text-destructive hover:bg-destructive/10 rounded-xl border border-transparent hover:border-destructive/20 transition-all"
                                    title="Disable Product"
                                >
                                    <x-ui.icon name="slash" size="4" />
                                </x-ui.button>
                            </form>

                        @endif

                    </div>

                </x-ui.table-cell>

            </x-ui.table-row>

        @empty

            {{-- Empty State --}}
            <x-ui.table-row>

                <x-ui.table-cell colspan="7" class="h-60 text-center">

                    <div class="flex flex-col items-center justify-center gap-4 opacity-40">

                        <x-ui.icon
                            name="package"
                            size="16"
                            stroke-width="1"
                        />

                        <p class="text-sm font-bold uppercase tracking-[0.2em]">
                            No products found
                        </p>

                        <x-ui.button
                            variant="outline"
                            size="sm"
                            onclick="location.reload()"
                            class="rounded-xl border-border"
                        >
                            Reset Filters
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