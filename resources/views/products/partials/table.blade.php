@if($products->hasPages())
    <div class="px-6 py-4 border-b border-border/40 bg-muted/10 flex flex-col md:flex-row md:items-center md:justify-between gap-3">
        <p class="text-[11px] font-bold text-muted-foreground uppercase tracking-widest">
            Showing {{ $products->firstItem() ?? 0 }}-{{ $products->lastItem() ?? 0 }} of {{ $products->total() }}
        </p>
        {{ $products->links() }}
    </div>
@endif

<x-ui.table>
    <x-ui.table-header class="bg-muted/20">
        <x-ui.table-row class="border-b border-border/60">
            @can('products.delete')
            <x-ui.table-head class="w-10">
                <input type="checkbox" :checked="allSelected" @change="allSelected = $event.target.checked; toggleAll()" class="rounded-xl border-border bg-background text-primary focus:ring-primary/20 transition-all">
            </x-ui.table-head>
            @endcan
            <x-ui.table-head class="text-[10px] font-black uppercase tracking-widest py-5">Product Identity</x-ui.table-head>
            <x-ui.table-head class="text-[10px] font-black uppercase tracking-widest">Market Value</x-ui.table-head>
            <x-ui.table-head class="text-[10px] font-black uppercase tracking-widest">Available Offers</x-ui.table-head>
            <x-ui.table-head class="text-[10px] font-black uppercase tracking-widest text-center min-w-[220px]">Inventory Metrics</x-ui.table-head>
            <x-ui.table-head class="text-[10px] font-black uppercase tracking-widest">Operational Status</x-ui.table-head>
            <x-ui.table-head class="text-right text-[10px] font-black uppercase tracking-widest">Management</x-ui.table-head>
        </x-ui.table-row>
    </x-ui.table-header>

    <x-ui.table-body>
        @forelse($products as $product)
            @php
                $onHand = (float) ($product->total_stock ?? 0);
                $reserved = (float) ($product->total_reserved ?? 0);
                $available = max(0, $onHand - $reserved);
                $reservedPercent = $onHand > 0 ? min(100, ($reserved / $onHand) * 100) : 0;
                $availablePercent = $onHand > 0 ? min(100, ($available / $onHand) * 100) : 0;
                $statusColors = [
                    'active' => 'bg-emerald-500/10 text-emerald-600 border-emerald-500/20',
                    'draft' => 'bg-amber-500/10 text-amber-600 border-amber-500/20',
                    'out_of_stock' => 'bg-red-500/10 text-red-600 border-red-500/20',
                ];
                $colorClass = $statusColors[$product->status] ?? 'bg-muted/10 text-muted-foreground border-border/40';

                $margin = $product->purchase_price > 0 ? round((($product->selling_price - $product->purchase_price) / $product->purchase_price) * 100, 1) : 0;
                if (!empty($product->grade)) {
                    $grade = $product->grade;
                    if ($grade === 'A') $gradeColor = 'bg-emerald-500/10 text-emerald-600 border-emerald-500/20';
                    elseif ($grade === 'B') $gradeColor = 'bg-green-500/10 text-green-600 border-green-500/20';
                    elseif ($grade === 'C') $gradeColor = 'bg-amber-500/10 text-amber-600 border-amber-500/20';
                    else $gradeColor = 'bg-red-500/10 text-red-600 border-red-500/20';
                } else {
                    $grade = 'D';
                    $gradeColor = 'bg-red-500/10 text-red-600 border-red-500/20';
                    if ($margin >= 50) { $grade = 'A'; $gradeColor = 'bg-emerald-500/10 text-emerald-600 border-emerald-500/20'; }
                    elseif ($margin >= 30) { $grade = 'B'; $gradeColor = 'bg-green-500/10 text-green-600 border-green-500/20'; }
                    elseif ($margin >= 10) { $grade = 'C'; $gradeColor = 'bg-amber-500/10 text-amber-600 border-amber-500/20'; }
                }
            @endphp

            <x-ui.table-row x-bind:class="selectedItems.includes({{ $product->id }}) ? 'bg-primary/5 ring-1 ring-primary/10' : 'hover:bg-primary/[0.02]'" class="border-b border-border/40 group transition-all duration-300">
                @can('products.delete')
                <x-ui.table-cell>
                    <input type="checkbox" name="product_ids[]" value="{{ $product->id }}" :checked="selectedItems.includes({{ $product->id }})" @change="toggleItem({{ $product->id }}, $event.target.checked)" class="rounded-xl border-border bg-background text-primary focus:ring-primary/20 transition-all">
                </x-ui.table-cell>
                @endcan

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
                            <div class="absolute -bottom-1 -right-1 min-w-5 h-5 px-1 rounded-lg bg-background border border-border flex items-center justify-center shadow-sm">
                                <span class="text-[8px] font-black text-muted-foreground">{{ $product->id }}</span>
                            </div>
                        </div>
                        <div class="flex flex-col min-w-0">
                            <a href="{{ route('products.show', $product) }}" class="text-sm font-black text-foreground truncate group-hover:text-primary transition-colors tracking-tight">
                                {{ $product->name }}
                            </a>
                            <div class="flex flex-wrap items-center gap-2 mt-1.5">
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
                                    <span class="text-[9px] font-bold text-primary/70 uppercase tracking-widest bg-primary/5 px-2 py-0.5 rounded-md">
                                        {{ $product->category->name }}
                                    </span>
                                @endif
                                <span class="text-[9px] font-black uppercase tracking-widest px-2 py-0.5 rounded-md border {{ $gradeColor }}" title="Margin: {{ $margin }}%">
                                    Grade {{ $grade }}
                                </span>
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
                                <x-ui.icon name="percent" size="3" class="text-emerald-500/50" />
                                <span class="text-[9px] font-bold text-emerald-600 uppercase tracking-widest">
                                    {{ $product->taxRate->name }}
                                </span>
                            </div>
                        @endif
                    </div>
                </x-ui.table-cell>

                <x-ui.table-cell>
                    @php
                        $prodOffersCount = $product->activeOffers->count();
                        $globalOffersCount = isset($storeWideOffers) ? $storeWideOffers->count() : 0;
                        $hasBaseDiscount = (float) $product->default_discount > 0;
                        $totalOffersCount = $prodOffersCount + $globalOffersCount + ($hasBaseDiscount ? 1 : 0);
                        $hasOffers = $totalOffersCount > 0;
                    @endphp

                    <div class="flex items-center">
                        @if($hasOffers)
                            <button
                                type="button"
                                @click="$dispatch('open-offers-modal', {
                                    productName: @js($product->name),
                                    productSku: @js($product->sku),
                                    totalCount: {{ $totalOffersCount }},
                                    hasBaseDiscount: {{ $hasBaseDiscount ? 'true' : 'false' }},
                                    baseDiscountValue: @js(rtrim(rtrim(number_format((float) $product->default_discount, 2), '0'), '.')),
                                    baseDiscountType: @js($product->default_discount_type),
                                    productOffers: [
                                        @foreach($product->activeOffers as $offer)
                                        {
                                            name: @js($offer->name),
                                            type: @js($offer->type),
                                            discountType: @js($offer->discount_type ?? ''),
                                            value: @js(number_format((float) ($offer->value ?? 0), 2)),
                                            buyQty: {{ $offer->buy_qty ?? 0 }},
                                            getQty: {{ $offer->get_qty ?? 0 }},
                                            priority: {{ $offer->priority ?? 0 }},
                                            minSpend: @js(number_format((float) ($offer->min_spend ?? 0), 2)),
                                            maxDiscount: @js(number_format((float) ($offer->max_discount ?? 0), 2)),
                                            endsAt: @js($offer->ends_at ? $offer->ends_at->format('M d, Y') : null)
                                        },
                                        @endforeach
                                    ],
                                    globalOffers: [
                                        @if(isset($storeWideOffers))
                                        @foreach($storeWideOffers as $offer)
                                        {
                                            name: @js($offer->name),
                                            discountType: @js($offer->discount_type ?? ''),
                                            value: @js(number_format((float) ($offer->value ?? 0), 2)),
                                            priority: {{ $offer->priority ?? 0 }},
                                            minSpend: @js(number_format((float) ($offer->min_spend ?? 0), 2)),
                                            maxDiscount: @js(number_format((float) ($offer->max_discount ?? 0), 2)),
                                            endsAt: @js($offer->ends_at ? $offer->ends_at->format('M d, Y') : null)
                                        },
                                        @endforeach
                                        @endif
                                    ]
                                })"
                                class="group/btn flex items-center gap-2 px-3 py-2 rounded-xl border border-rose-500/20 bg-rose-500/[0.03] hover:bg-rose-500/[0.08] hover:border-rose-500/40 transition-all duration-200 cursor-pointer">
                                <div class="size-6 rounded-lg bg-rose-500/10 flex items-center justify-center text-rose-500/70 shrink-0">
                                    <x-ui.icon name="tag" size="3" />
                                </div>
                                <div class="flex flex-col items-start min-w-0">
                                    <span class="text-[9px] font-black text-rose-500/80 uppercase tracking-widest leading-none whitespace-nowrap">
                                        {{ $totalOffersCount }} {{ Str::plural('Offer', $totalOffersCount) }}
                                    </span>
                                    <span class="text-[8px] font-bold text-muted-foreground/50 uppercase tracking-wider mt-0.5 group-hover/btn:text-rose-500/60 transition-colors whitespace-nowrap">
                                        View Details
                                    </span>
                                </div>
                                <x-ui.icon name="chevron-right" size="3" class="text-muted-foreground/30 group-hover/btn:text-rose-500/60 transition-colors shrink-0" />
                            </button>
                        @else
                            <span class="text-[10px] font-bold text-muted-foreground/30 italic">No offers</span>
                        @endif
                    </div>
                </x-ui.table-cell>

                <x-ui.table-cell>
                    <div class="flex flex-col items-center gap-2 py-1 min-w-[210px]">
                        <div class="flex items-stretch divide-x divide-border/40 w-full rounded-xl border border-border/40 bg-muted/5 overflow-hidden">
                            <div class="flex flex-col items-center justify-center gap-0.5 flex-1 py-2 px-1">
                                <span class="text-sm font-black text-foreground leading-none">{{ number_format($onHand) }}</span>
                                <span class="text-[8px] font-bold text-muted-foreground uppercase tracking-widest">On Hand</span>
                            </div>
                            <div class="flex flex-col items-center justify-center gap-0.5 flex-1 py-2 px-1 bg-orange-500/5">
                                <span class="text-sm font-black text-orange-500 leading-none">{{ number_format($reserved) }}</span>
                                <span class="text-[8px] font-bold text-muted-foreground uppercase tracking-widest">Reserved</span>
                            </div>
                            <div class="flex flex-col items-center justify-center gap-0.5 flex-1 py-2 px-1 bg-emerald-500/5">
                                <span class="text-sm font-black text-emerald-500 leading-none">{{ number_format($available) }}</span>
                                <span class="text-[8px] font-bold text-muted-foreground uppercase tracking-widest">Available</span>
                            </div>
                        </div>

                        <div class="h-1.5 w-full bg-muted/30 rounded-full overflow-hidden flex shadow-inner">
                            <div class="h-full bg-orange-500 transition-all duration-1000 rounded-l-full" style="width: {{ $reservedPercent }}%"></div>
                            <div class="h-full bg-emerald-500 transition-all duration-1000 shadow-[0_0_6px_rgba(16,185,129,0.4)]" style="width: {{ $availablePercent }}%"></div>
                        </div>

                        @if($product->allow_overselling)
                            <div class="flex items-center justify-center gap-1 bg-primary/5 border border-primary/20 py-1 px-2 rounded-lg w-full">
                                <x-ui.icon name="zap" size="2" class="text-primary" />
                                <span class="text-[8px] font-black text-primary uppercase tracking-widest">Oversell ({{ $product->overselling_qty ?: 'INF' }})</span>
                            </div>
                        @endif
                    </div>
                </x-ui.table-cell>

                <x-ui.table-cell>
                    <div class="flex flex-col gap-2">
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
                    <div class="flex justify-end items-center gap-1.5 opacity-80 group-hover:opacity-100 transition-opacity duration-300">
                        @if(method_exists($product, 'trashed') && $product->trashed())
                            @can('products.edit')
                            <form action="{{ route('products.restore', $product->id) }}" method="POST">
                                @csrf
                                <x-ui.button variant="ghost" size="sm" type="submit" class="h-8 px-4 text-[9px] font-black uppercase tracking-widest text-emerald-600 hover:bg-emerald-500/10 rounded-xl border border-transparent hover:border-emerald-500/20 transition-all">
                                    Restore
                                </x-ui.button>
                            </form>
                            @endcan
                            @can('products.delete')
                            <form action="{{ route('products.force-delete', $product->id) }}" method="POST" onsubmit="return confirm('Permanently delete?')">
                                @csrf
                                @method('DELETE')
                                <x-ui.button variant="ghost" size="icon" type="submit" class="size-8 text-destructive hover:bg-destructive/10 rounded-xl border border-transparent hover:border-destructive/20 transition-all">
                                    <x-ui.icon name="trash-2" size="4" />
                                </x-ui.button>
                            </form>
                            @endcan
                        @else
                            <a href="{{ route('products.show', $product) }}" title="View Details">
                                <x-ui.button variant="ghost" size="icon" class="size-8 text-muted-foreground hover:text-primary hover:bg-primary/10 rounded-xl border border-transparent hover:border-primary/20 transition-all">
                                    <x-ui.icon name="eye" size="4" />
                                </x-ui.button>
                            </a>
                            @can('products.edit')
                            <a href="{{ route('products.edit', $product) }}" title="Edit Product">
                                <x-ui.button variant="ghost" size="icon" class="size-8 text-muted-foreground hover:text-primary hover:bg-primary/10 rounded-xl border border-transparent hover:border-primary/20 transition-all">
                                    <x-ui.icon name="edit" size="4" />
                                </x-ui.button>
                            </a>
                            @endcan
                            @can('products.delete')
                            <form action="{{ route('products.destroy', $product) }}" method="POST" onsubmit="return confirm('Disable product?')">
                                @csrf
                                @method('DELETE')
                                <x-ui.button variant="ghost" size="icon" type="submit" class="size-8 text-muted-foreground hover:text-destructive hover:bg-destructive/10 rounded-xl border border-transparent hover:border-destructive/20 transition-all">
                                    <x-ui.icon name="slash" size="4" />
                                </x-ui.button>
                            </form>
                            @endcan
                        @endif
                    </div>
                </x-ui.table-cell>
            </x-ui.table-row>
        @empty
            <x-ui.table-row>
                <x-ui.table-cell colspan="7" class="h-64 text-center">
                    <div class="flex flex-col items-center justify-center gap-6 opacity-40">
                        <x-ui.icon name="package" size="16" stroke-width="1" />
                        <div class="space-y-1">
                            <p class="text-sm font-black uppercase tracking-[0.3em]">No items found</p>
                            <p class="text-[10px] font-bold uppercase tracking-widest">Adjust your filters or add a new product</p>
                        </div>
                        <x-ui.button type="button" variant="outline" size="sm" onclick="location.reload()" class="rounded-xl border-border px-6">
                            Reset View
                        </x-ui.button>
                    </div>
                </x-ui.table-cell>
            </x-ui.table-row>
        @endforelse
    </x-ui.table-body>
</x-ui.table>

@if($products->hasPages())
    <div class="px-6 py-4 border-t border-border/40 bg-muted/10 flex flex-col md:flex-row md:items-center md:justify-between gap-3 rounded-b-3xl">
        <p class="text-[11px] font-bold text-muted-foreground uppercase tracking-widest">
            Page {{ $products->currentPage() }} of {{ $products->lastPage() }}
        </p>
        {{ $products->links() }}
    </div>
@endif

{{-- ══════════════════════════════════════════
     OFFERS MODAL — AlpineJS, no controller change
     ══════════════════════════════════════════ --}}
<div
    x-data="{
        open: false,
        productName: '',
        productSku: '',
        totalCount: 0,
        hasBaseDiscount: false,
        baseDiscountValue: '',
        baseDiscountType: '',
        productOffers: [],
        globalOffers: [],
        formatDiscount(value, type) {
            const v = parseFloat(value);
            const trimmed = v % 1 === 0 ? v.toString() : value.replace(/\.?0+$/, '');
            return type === 'percentage' ? trimmed + '%' : '₹' + value;
        }
    }"
    x-on:open-offers-modal.window="
        productName    = $event.detail.productName;
        productSku     = $event.detail.productSku;
        totalCount     = $event.detail.totalCount;
        hasBaseDiscount  = $event.detail.hasBaseDiscount;
        baseDiscountValue = $event.detail.baseDiscountValue;
        baseDiscountType  = $event.detail.baseDiscountType;
        productOffers  = $event.detail.productOffers;
        globalOffers   = $event.detail.globalOffers;
        open = true;
    "
    x-show="open"
    x-cloak
    class="fixed inset-0 z-[9999] flex items-center justify-center p-4">

    {{-- Backdrop --}}
    <div
        class="absolute inset-0 bg-black/60 backdrop-blur-sm"
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        @click="open = false">
    </div>

    {{-- Modal Panel --}}
    <div
        class="relative z-10 w-full max-w-lg max-h-[80vh] flex flex-col rounded-3xl border border-border/60 bg-card/95 backdrop-blur-2xl shadow-2xl overflow-hidden"
        x-transition:enter="transition ease-out duration-250"
        x-transition:enter-start="opacity-0 scale-95 translate-y-4"
        x-transition:enter-end="opacity-100 scale-100 translate-y-0"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100 scale-100 translate-y-0"
        x-transition:leave-end="opacity-0 scale-95 translate-y-2"
        @click.stop>

        {{-- Modal Header --}}
        <div class="flex items-center justify-between gap-3 px-6 py-5 border-b border-border/40 bg-muted/10 shrink-0">
            <div class="flex items-center gap-3">
                <div class="size-10 rounded-2xl bg-rose-500/10 border border-rose-500/20 flex items-center justify-center shrink-0">
                    <x-ui.icon name="tag" size="5" class="text-rose-500" />
                </div>
                <div>
                    <h3 class="text-sm font-black text-foreground" x-text="productName"></h3>
                    <div class="flex items-center gap-2 mt-0.5">
                        <span class="text-[9px] font-mono font-black uppercase tracking-widest text-muted-foreground/60 bg-muted/40 px-2 py-0.5 rounded border border-border/20" x-text="productSku"></span>
                        <span class="text-[9px] font-black text-rose-500/70 uppercase tracking-widest" x-text="totalCount + ' ' + (totalCount === 1 ? 'Offer' : 'Offers') + ' Available'"></span>
                    </div>
                </div>
            </div>
            <button
                type="button"
                @click="open = false"
                class="size-9 rounded-xl hover:bg-muted flex items-center justify-center text-muted-foreground hover:text-foreground transition-all shrink-0">
                <x-ui.icon name="x" size="4" />
            </button>
        </div>

        {{-- Modal Body --}}
        <div class="overflow-y-auto flex-1 p-6 space-y-5">

            {{-- Base Discount --}}
            <template x-if="hasBaseDiscount">
                <div class="space-y-2">
                    <p class="text-[9px] font-black uppercase tracking-widest text-emerald-500/70 flex items-center gap-1.5">
                        <span class="inline-block size-1.5 rounded-full bg-emerald-500"></span>
                        Base Offer
                    </p>
                    <div class="p-4 rounded-2xl bg-emerald-500/[0.03] border border-emerald-500/15 hover:bg-emerald-500/[0.06] transition-all">
                        <div class="flex items-start justify-between gap-3">
                            <div class="space-y-1">
                                <p class="text-sm font-black text-foreground">Product Base Discount</p>
                                <p class="text-[9px] font-bold text-muted-foreground/70 uppercase tracking-widest">Applied automatically to this product at checkout</p>
                            </div>
                            <span class="text-xs font-black text-emerald-500 bg-emerald-500/10 border border-emerald-500/20 px-2.5 py-1 rounded-xl whitespace-nowrap" x-text="baseDiscountValue + (baseDiscountType === 'percent' ? '%' : '₹') + ' OFF'"></span>
                        </div>
                    </div>
                </div>
            </template>

            {{-- Product Campaigns --}}
            <template x-if="productOffers.length > 0">
                <div class="space-y-2">
                    <p class="text-[9px] font-black uppercase tracking-widest text-rose-500/70 flex items-center gap-1.5">
                        <span class="inline-block size-1.5 rounded-full bg-rose-500"></span>
                        Product Campaigns
                    </p>
                    <template x-for="offer in productOffers" :key="offer.name">
                        <div class="p-4 rounded-2xl bg-rose-500/[0.03] border border-rose-500/15 hover:bg-rose-500/[0.06] transition-all">
                            <div class="flex items-start justify-between gap-3 mb-2">
                                <div class="flex items-center gap-2 min-w-0">
                                    <p class="text-sm font-black text-foreground truncate" x-text="offer.name"></p>
                                </div>
                                <span
                                    class="text-[7px] font-black px-2 py-1 rounded-lg uppercase tracking-widest whitespace-nowrap shrink-0"
                                    :class="offer.type === 'bogo' ? 'bg-violet-500/10 text-violet-600 border border-violet-500/20' : 'bg-rose-500/10 text-rose-600 border border-rose-500/20'"
                                    x-text="offer.type === 'bogo' ? 'BOGO' : 'Discount'">
                                </span>
                            </div>
                            <p
                                class="text-base font-black text-rose-500 leading-none mb-3"
                                x-text="offer.type === 'bogo'
                                    ? ('Buy ' + offer.buyQty + ' Get ' + offer.getQty + ' Free')
                                    : (formatDiscount(offer.value, offer.discountType) + ' OFF')">
                            </p>
                            <div class="flex flex-wrap gap-x-4 gap-y-1 text-[8px] font-bold text-muted-foreground/65 uppercase tracking-wide border-t border-border/30 pt-2.5">
                                <span x-text="'Priority: ' + offer.priority"></span>
                                <template x-if="parseFloat(offer.minSpend) > 0">
                                    <span x-text="'Min Spend: ₹' + offer.minSpend"></span>
                                </template>
                                <template x-if="parseFloat(offer.maxDiscount) > 0">
                                    <span x-text="'Max Disc: ₹' + offer.maxDiscount"></span>
                                </template>
                                <span
                                    :class="offer.endsAt ? 'text-orange-500/80' : ''"
                                    x-text="offer.endsAt ? ('Ends: ' + offer.endsAt) : 'No expiry'">
                                </span>
                            </div>
                        </div>
                    </template>
                </div>
            </template>

            {{-- Global Store Offers --}}
            <template x-if="globalOffers.length > 0">
                <div class="space-y-2">
                    <p class="text-[9px] font-black uppercase tracking-widest text-blue-500/70 flex items-center gap-1.5">
                        <span class="inline-block size-1.5 rounded-full bg-blue-500"></span>
                        Global Store Offers
                    </p>
                    <template x-for="offer in globalOffers" :key="offer.name">
                        <div class="p-4 rounded-2xl bg-blue-500/[0.03] border border-blue-500/15 hover:bg-blue-500/[0.06] transition-all">
                            <div class="flex items-start justify-between gap-3 mb-2">
                                <p class="text-sm font-black text-foreground truncate" x-text="offer.name"></p>
                                <span class="text-[7px] font-black px-2 py-1 rounded-lg bg-blue-500/10 text-blue-600 border border-blue-500/20 uppercase tracking-widest whitespace-nowrap shrink-0">Global</span>
                            </div>
                            <p class="text-base font-black text-blue-500 leading-none mb-3" x-text="formatDiscount(offer.value, offer.discountType) + ' OFF'"></p>
                            <div class="flex flex-wrap gap-x-4 gap-y-1 text-[8px] font-bold text-muted-foreground/65 uppercase tracking-wide border-t border-border/30 pt-2.5">
                                <span x-text="'Priority: ' + offer.priority"></span>
                                <template x-if="parseFloat(offer.minSpend) > 0">
                                    <span x-text="'Min Spend: ₹' + offer.minSpend"></span>
                                </template>
                                <template x-if="parseFloat(offer.maxDiscount) > 0">
                                    <span x-text="'Max Disc: ₹' + offer.maxDiscount"></span>
                                </template>
                                <span
                                    :class="offer.endsAt ? 'text-orange-500/80' : ''"
                                    x-text="offer.endsAt ? ('Ends: ' + offer.endsAt) : 'No expiry'">
                                </span>
                            </div>
                        </div>
                    </template>
                </div>
            </template>

        </div>

        {{-- Modal Footer --}}
        <div class="px-6 py-4 border-t border-border/40 bg-muted/10 shrink-0 flex items-center justify-end gap-3">
            <button
                type="button"
                @click="open = false"
                class="h-10 px-6 rounded-xl border border-border bg-background hover:bg-muted transition-all text-[10px] font-black uppercase tracking-widest">
                Close
            </button>
        </div>

    </div>
</div>
