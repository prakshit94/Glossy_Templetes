{{-- ══ TAB: Order Products ══ --}}
<div x-show="activeTab === 'order'" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-4" x-transition:enter-end="opacity-100 translate-y-0" x-cloak>
    <div class="bg-card/40 backdrop-blur-3xl border border-border/50 rounded-3xl shadow-2xl overflow-hidden">

        {{-- Table Header: Title + Filters + Search --}}
        <div class="p-5 border-b border-border/40 bg-gradient-to-b from-muted/10 to-transparent space-y-4">

            {{-- Row 1: Title + Cart Badge --}}
            <div class="flex items-center justify-between gap-4">
                <div class="flex items-center gap-3">
                    <div class="size-10 rounded-2xl bg-primary/10 text-primary flex items-center justify-center">
                        <x-ui.icon name="package" size="5" />
                    </div>
                    <div>
                        <h3 class="text-base font-black tracking-tight text-foreground">Available Products</h3>
                        <p class="text-[10px] text-muted-foreground font-medium uppercase tracking-widest mt-0.5">
                            <span x-text="productTotal"></span> products &nbsp;·&nbsp; Showing <span x-text="productFrom"></span>–<span x-text="productTo"></span>
                        </p>
                    </div>
                </div>
                <button type="button" @click="isCartOpen = true"
                    class="relative flex items-center gap-2 h-10 px-4 rounded-2xl bg-emerald-500/10 border border-emerald-500/20 text-emerald-600 text-xs font-black hover:bg-emerald-500 hover:text-white transition-all">
                    <x-ui.icon name="shopping-cart" size="4" />
                    <span>Cart</span>
                    <span x-show="cart.length > 0" class="absolute -top-2 -right-2 size-5 rounded-full bg-emerald-500 text-white text-[10px] font-black flex items-center justify-center" x-text="cart.length" x-cloak></span>
                </button>
            </div>

            {{-- Row 2: Filters --}}
            <div class="flex flex-wrap items-center gap-2">

                {{-- Per Page --}}
                <select x-model="productPerPage" @change="searchProducts(true)"
                    class="h-9 px-3 rounded-xl border border-border bg-background/60 text-xs font-bold outline-none focus:ring-2 focus:ring-primary/20 shrink-0">
                    <option value="10">10 / page</option>
                    <option value="15" selected>15 / page</option>
                    <option value="25">25 / page</option>
                    <option value="50">50 / page</option>
                </select>

                {{-- Stock Filter Tabs --}}
                <div class="flex items-center bg-muted/30 p-1 rounded-xl border border-border/50 gap-1">
                    <template x-for="opt in [{v:'available',l:'In Stock'},{v:'out_of_stock',l:'Out of Stock'},{v:'',l:'All'}]" :key="opt.v">
                        <button type="button" @click="productStockFilter = opt.v; searchProducts(true)"
                            :class="productStockFilter === opt.v ? 'bg-card shadow-sm text-primary' : 'text-muted-foreground hover:text-foreground'"
                            class="px-3 py-1.5 rounded-lg text-[10px] font-black uppercase tracking-widest transition-all whitespace-nowrap"
                            x-text="opt.l">
                        </button>
                    </template>
                </div>

                {{-- Category Filter --}}
                <select x-model="productCategoryFilter" @change="searchProducts(true)"
                    class="h-9 px-3 rounded-xl border border-border bg-background/60 text-xs font-bold outline-none focus:ring-2 focus:ring-primary/20 shrink-0 min-w-[120px]">
                    <option value="">All Categories</option>
                    @foreach($categories as $cat)
                        <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                    @endforeach
                </select>

                {{-- Search --}}
                <div class="relative flex-1 min-w-[180px] group">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <x-ui.icon name="search" size="4" class="text-muted-foreground group-focus-within:text-primary transition-colors" />
                    </div>
                    <input type="text" x-model="productSearchQuery"
                        @input.debounce.400ms="searchProducts(true)"
                        placeholder="Search by name, SKU, barcode..."
                        class="w-full h-9 pl-9 pr-4 rounded-xl border border-border bg-background/60 text-xs font-medium outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary/50">
                    <div x-show="searchingProducts" class="absolute inset-y-0 right-3 flex items-center" x-cloak>
                        <x-ui.icon name="refresh-cw" size="3.5" class="animate-spin text-primary" />
                    </div>
                </div>

                {{-- Clear button --}}
                <button type="button" x-show="productSearchQuery || productStockFilter !== 'available'" x-cloak
                    @click="productSearchQuery = ''; productStockFilter = 'available'; productCategoryFilter = ''; searchProducts(true)"
                    class="h-9 px-3 rounded-xl border border-border text-xs font-bold text-muted-foreground hover:text-destructive hover:border-destructive/30 transition-all">
                    Clear
                </button>
            </div>
        </div>

        {{-- Table --}}
        <div class="overflow-x-auto" style="max-height:520px;overflow-y:auto;scrollbar-width:thin;">
            <table class="w-full text-left border-collapse text-sm">
                <thead class="sticky top-0 z-10">
                    <tr class="bg-muted/40 backdrop-blur-md border-b border-border/50">
                        <th class="px-4 py-3 text-[10px] font-black uppercase tracking-widest text-muted-foreground w-[280px]">Product</th>
                        <th class="px-4 py-3 text-[10px] font-black uppercase tracking-widest text-muted-foreground">SKU / Category</th>
                        <th class="px-4 py-3 text-[10px] font-black uppercase tracking-widest text-muted-foreground">Pricing</th>
                        <th class="px-4 py-3 text-[10px] font-black uppercase tracking-widest text-muted-foreground">Stock</th>
                        <th class="px-4 py-3 text-[10px] font-black uppercase tracking-widest text-muted-foreground">Qty & Disc</th>
                        <th class="px-4 py-3 text-[10px] font-black uppercase tracking-widest text-muted-foreground text-right">Add</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-border/20">

                    {{-- Loading state --}}
                    <template x-if="searchingProducts">
                        <tr>
                            <td colspan="6" class="py-16 text-center">
                                <div class="flex flex-col items-center gap-3 opacity-50">
                                    <x-ui.icon name="refresh-cw" size="8" class="animate-spin text-primary" />
                                    <p class="text-xs font-bold uppercase tracking-widest">Loading products...</p>
                                </div>
                            </td>
                        </tr>
                    </template>

                    {{-- Empty state --}}
                    <template x-if="!searchingProducts && productSearchResults.length === 0">
                        <tr>
                            <td colspan="6" class="py-16 text-center">
                                <div class="flex flex-col items-center gap-3 opacity-40">
                                    <div class="size-16 rounded-2xl bg-muted flex items-center justify-center">
                                        <x-ui.icon name="package-x" size="8" class="text-muted-foreground" />
                                    </div>
                                    <p class="text-sm font-black uppercase tracking-widest">No products found</p>
                                    <p class="text-xs text-muted-foreground">Try adjusting your search or filters</p>
                                </div>
                            </td>
                        </tr>
                    </template>

                    {{-- Product rows --}}
                    <template x-for="product in productSearchResults" :key="product.id">
                        <tr class="group hover:bg-primary/[0.03] transition-colors duration-150"
                            :class="cart.find(i => i.id === product.id) ? 'bg-emerald-500/5' : ''">

                            {{-- Product Identity --}}
                            <td class="px-4 py-3">
                                <div class="flex items-center gap-3">
                                    <div class="size-12 rounded-xl bg-muted border border-border/40 shrink-0 overflow-hidden flex items-center justify-center">
                                        <template x-if="product.image_url">
                                            <img :src="product.image_url" class="w-full h-full object-cover">
                                        </template>
                                        <template x-if="!product.image_url">
                                            <x-ui.icon name="image" size="5" class="text-muted-foreground/30" />
                                        </template>
                                    </div>
                                    <div class="min-w-0">
                                        <p class="text-sm font-bold text-foreground group-hover:text-primary transition-colors line-clamp-2" x-text="product.name"></p>
                                        <div class="flex items-center gap-1.5 mt-1" x-show="product.brand">
                                            <span class="text-[10px] text-muted-foreground font-medium" x-text="product.brand"></span>
                                        </div>
                                        {{-- In-cart indicator --}}
                                        <template x-if="cart.find(i => i.id === product.id)">
                                            <span class="inline-flex items-center gap-1 text-[10px] font-black text-emerald-500 mt-1">
                                                <x-ui.icon name="check-circle" size="3" /> In Cart
                                            </span>
                                        </template>
                                    </div>
                                </div>
                            </td>

                            {{-- SKU / Category --}}
                            <td class="px-4 py-3">
                                <div class="space-y-1.5">
                                    <span class="text-[11px] font-mono font-bold bg-muted/60 px-2 py-0.5 rounded-lg text-foreground/80" x-text="product.sku"></span>
                                    <div x-show="product.category">
                                        <span class="text-[10px] font-bold px-2 py-0.5 rounded-lg bg-primary/10 text-primary border border-primary/10" x-text="product.category"></span>
                                    </div>
                                    <div x-show="product.tax_label">
                                        <span class="text-[10px] font-bold text-muted-foreground" x-text="'GST: ' + product.tax_rate + '%'"></span>
                                    </div>
                                </div>
                            </td>

                            {{-- Pricing --}}
                            <td class="px-4 py-3">
                                <div class="space-y-1">
                                    <p class="text-sm font-black text-foreground" x-text="'₹' + Number(product.selling_price).toFixed(2)"></p>
                                    <p class="text-[10px] text-muted-foreground line-through" x-show="product.mrp && product.mrp > product.selling_price" x-text="'MRP ₹' + Number(product.mrp).toFixed(2)"></p>
                                    <p class="text-[10px] text-muted-foreground" x-show="product.purchase_price" x-text="'Cost ₹' + Number(product.purchase_price).toFixed(2)"></p>
                                </div>
                            </td>

                            {{-- Stock --}}
                            <td class="px-4 py-3">
                                <div class="space-y-1.5" x-data="{
                                    get dynamicStock() {
                                        let qtyInCart = cart.find(i => i.id === product.id)?.quantity || 0;
                                        return Math.max(0, product.physical_available - qtyInCart);
                                    }
                                }">
                                    <span class="text-[10px] font-black uppercase tracking-wider px-2.5 py-1 rounded-lg inline-block"
                                        :class="dynamicStock > 0
                                            ? 'bg-emerald-500/10 text-emerald-600 border border-emerald-500/20'
                                            : 'bg-red-500/10 text-red-500 border border-red-500/20'"
                                        x-text="dynamicStock > 0
                                            ? (product.physical_available >= 999 ? 'In Stock' : dynamicStock + ' units')
                                            : 'Out of Stock'">
                                    </span>
                                    <p class="text-[10px] text-muted-foreground" x-show="product.min_stock_level > 0" x-text="'Min: ' + product.min_stock_level"></p>
                                    <span x-show="product.allow_overselling" class="text-[10px] font-bold text-amber-500 flex items-center gap-1" x-cloak>
                                        <x-ui.icon name="zap" size="3" /> Oversell OK <span x-show="product.overselling_qty > 0" x-text="'(Max: ' + product.overselling_qty + ')'"></span>
                                    </span>
                                </div>
                            </td>

                            {{-- Qty + Discount inline before adding --}}
                            <td class="px-4 py-3">
                                <div class="flex flex-col gap-1.5" x-data="{
                                    get dynamicStock() {
                                        let qtyInCart = cart.find(i => i.id === product.id)?.quantity || 0;
                                        return Math.max(0, product.available_stock - qtyInCart);
                                    }
                                }">
                                    <div class="flex items-center gap-1">
                                        <button type="button" @click="product._qty = Math.max(1, (parseInt(product._qty) || 1) - 1)"
                                            class="size-8 rounded-lg border border-border bg-background hover:bg-muted flex items-center justify-center transition-colors">
                                            <x-ui.icon name="minus" size="3" />
                                        </button>
                                        <input type="number" x-model="product._qty" min="1" 
                                            :max="product.available_stock < 999 ? dynamicStock : 9999"
                                            class="h-8 w-12 rounded-lg border border-border bg-background text-xs font-bold text-center outline-none focus:ring-2 focus:ring-primary/20 [appearance:textfield] [&::-webkit-outer-spin-button]:appearance-none [&::-webkit-inner-spin-button]:appearance-none">
                                        <button type="button" @click="product._qty = (parseInt(product._qty) || 1) + 1"
                                            :disabled="product.available_stock !== 999 && product._qty >= dynamicStock"
                                            class="size-8 rounded-lg border border-border bg-background hover:bg-muted disabled:opacity-30 flex items-center justify-center transition-colors">
                                            <x-ui.icon name="plus" size="3" />
                                        </button>
                                    </div>
                                    <div class="flex items-center gap-1">
                                        <select x-model="product._discType"
                                            class="h-8 w-12 px-1 rounded-lg border border-border bg-background text-[10px] font-bold outline-none focus:ring-2 focus:ring-primary/20">
                                            <option value="percent">%</option>
                                            <option value="flat">₹</option>
                                        </select>
                                        <input type="number" x-model="product._disc" min="0" placeholder="0"
                                            class="h-8 w-14 px-2 rounded-lg border border-border bg-background text-xs font-bold text-right outline-none focus:ring-2 focus:ring-primary/20">
                                    </div>
                                </div>
                            </td>

                            {{-- Add Button --}}
                            <td class="px-4 py-3 text-right">
                                <button type="button"
                                    @click.prevent="addToCartWithOptions(product)"
                                    :disabled="product.available_stock !== 999 && Math.max(0, product.available_stock - (cart.find(i => i.id === product.id)?.quantity || 0)) <= 0"
                                    class="h-9 px-4 rounded-xl text-xs font-black uppercase tracking-wider transition-all shadow-sm flex items-center gap-1.5 ml-auto"
                                    :class="cart.find(i => i.id === product.id)
                                        ? 'bg-emerald-500/10 text-emerald-600 border border-emerald-500/20 hover:bg-emerald-500 hover:text-white'
                                        : 'bg-primary text-primary-foreground shadow-primary/20 hover:shadow-primary/40 hover:-translate-y-0.5 disabled:opacity-40 disabled:pointer-events-none'">
                                    <template x-if="cart.find(i => i.id === product.id)">
                                        <x-ui.icon name="plus" size="3" />
                                    </template>
                                    <template x-if="!cart.find(i => i.id === product.id)">
                                        <x-ui.icon name="shopping-cart" size="3" />
                                    </template>
                                    <span x-text="cart.find(i => i.id === product.id) ? 'Add More' : 'Add'"></span>
                                </button>
                            </td>
                        </tr>
                    </template>
                </tbody>
            </table>
        </div>

        {{-- Pagination Footer --}}
        <div class="px-5 py-4 border-t border-border/40 bg-muted/5 flex flex-col sm:flex-row items-center justify-between gap-3">
            <p class="text-[11px] text-muted-foreground font-medium">
                Showing <span class="font-black text-foreground" x-text="productFrom"></span>–<span class="font-black text-foreground" x-text="productTo"></span>
                of <span class="font-black text-foreground" x-text="productTotal"></span> products
            </p>
            <div class="flex items-center gap-1">
                <button type="button" @click="productPage = 1; searchProducts()"
                    :disabled="productPage <= 1"
                    class="size-8 rounded-lg border border-border hover:bg-muted text-xs font-bold disabled:opacity-30 flex items-center justify-center transition-all">
                    <x-ui.icon name="chevrons-left" size="3.5" />
                </button>
                <button type="button" @click="productPage--; searchProducts()"
                    :disabled="productPage <= 1"
                    class="size-8 rounded-lg border border-border hover:bg-muted text-xs font-bold disabled:opacity-30 flex items-center justify-center transition-all">
                    <x-ui.icon name="chevron-left" size="3.5" />
                </button>
                
                <div class="flex items-center px-2 text-[10px] font-black uppercase tracking-widest text-muted-foreground">
                    Page <span class="text-foreground mx-1" x-text="productPage"></span> of <span class="text-foreground mx-1" x-text="productLastPage"></span>
                </div>

                <button type="button" @click="productPage++; searchProducts()"
                    :disabled="productPage >= productLastPage"
                    class="size-8 rounded-lg border border-border hover:bg-muted text-xs font-bold disabled:opacity-30 flex items-center justify-center transition-all">
                    <x-ui.icon name="chevron-right" size="3.5" />
                </button>
                <button type="button" @click="productPage = productLastPage; searchProducts()"
                    :disabled="productPage >= productLastPage"
                    class="size-8 rounded-lg border border-border hover:bg-muted text-xs font-bold disabled:opacity-30 flex items-center justify-center transition-all">
                    <x-ui.icon name="chevrons-right" size="3.5" />
                </button>
            </div>
        </div>
    </div>
</div>
