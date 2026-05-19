{{-- ══ CART SLIDE-OVER ══ --}}
{{-- Backdrop --}}
<div x-show="isCartOpen" x-cloak
     x-transition:enter="transition ease-out duration-300"
     x-transition:enter-start="opacity-0"
     x-transition:enter-end="opacity-100"
     x-transition:leave="transition ease-in duration-200"
     x-transition:leave-start="opacity-100"
     x-transition:leave-end="opacity-0"
     @click="isCartOpen = false"
     class="fixed inset-0 bg-black/60 backdrop-blur-sm z-[90]">
</div>

{{-- Panel --}}
<div x-show="isCartOpen" x-cloak
     x-transition:enter="transition ease-out duration-300"
     x-transition:enter-start="translate-x-full"
     x-transition:enter-end="translate-x-0"
     x-transition:leave="transition ease-in duration-200"
     x-transition:leave-start="translate-x-0"
     x-transition:leave-end="translate-x-full"
     class="fixed inset-y-0 right-0 w-full max-w-lg z-[95] flex flex-col bg-card border-l border-border/50 shadow-2xl">

    {{-- Header --}}
    <div class="relative flex items-center justify-between px-6 py-5 border-b border-border/40 bg-gradient-to-r from-emerald-500/10 via-card to-card shrink-0 overflow-hidden">
        <div class="absolute inset-0 bg-gradient-to-r from-emerald-500/5 to-transparent pointer-events-none"></div>
        <div class="relative flex items-center gap-3">
            <div class="size-10 rounded-2xl bg-emerald-500/10 border border-emerald-500/20 text-emerald-500 flex items-center justify-center shadow-inner">
                <x-ui.icon name="shopping-cart" size="5" />
            </div>
            <div>
                <h2 class="text-base font-black text-foreground tracking-tight">Shopping Cart</h2>
                <p class="text-[10px] font-bold text-muted-foreground uppercase tracking-widest" x-text="cart.length + ' item' + (cart.length === 1 ? '' : 's')"></p>
            </div>
        </div>
        <button type="button" @click="isCartOpen = false"
            class="relative size-9 rounded-xl bg-muted hover:bg-muted/80 flex items-center justify-center text-muted-foreground hover:text-foreground transition-all">
            <x-ui.icon name="x" size="4" />
        </button>
    </div>

    {{-- Cart Content (Scrollable) --}}
    <div class="flex-1 overflow-y-auto" style="scrollbar-width:thin;">
        
        {{-- Items Area --}}
        <div class="p-4 space-y-3 bg-background/30">
            {{-- Empty state --}}
            <template x-if="cart.length === 0">
                <div class="flex flex-col items-center justify-center h-64 text-center gap-4 opacity-50">
                    <div class="size-20 rounded-3xl bg-muted flex items-center justify-center">
                        <x-ui.icon name="shopping-bag" size="10" class="text-muted-foreground" />
                    </div>
                    <p class="text-sm font-black uppercase tracking-widest text-foreground">Cart is empty</p>
                    <p class="text-xs text-muted-foreground">Browse products and click <strong>Add</strong> to begin.</p>
                    <button type="button" @click="isCartOpen = false; activeTab = 'order'"
                        class="mt-2 h-9 px-4 rounded-xl bg-primary text-primary-foreground text-xs font-bold shadow-lg shadow-primary/25 hover:-translate-y-0.5 transition-all">
                        Browse Products
                    </button>
                </div>
            </template>

            {{-- Items list --}}
            <template x-for="(item, index) in cart" :key="item.id">
                <div class="rounded-2xl border border-border/60 bg-card shadow-sm overflow-hidden group">
                    {{-- Item header --}}
                    <div class="flex items-start gap-3 p-4">
                        {{-- Image --}}
                        <div class="size-14 rounded-xl bg-muted border border-border/40 shrink-0 overflow-hidden flex items-center justify-center">
                            <template x-if="item.image_url">
                                <img :src="item.image_url" class="w-full h-full object-cover">
                            </template>
                            <template x-if="!item.image_url">
                                <x-ui.icon name="package" size="5" class="text-muted-foreground/30" />
                            </template>
                        </div>
                        {{-- Details --}}
                        <div class="flex-1 min-w-0">
                            <div class="flex items-start justify-between gap-2">
                                <div class="min-w-0">
                                    <p class="text-sm font-bold text-foreground truncate" x-text="item.name"></p>
                                    <p class="text-[10px] font-mono text-muted-foreground mt-0.5" x-text="item.sku"></p>
                                </div>
                                <button type="button" @click.prevent="removeFromCart(index)"
                                    class="size-6 shrink-0 rounded-lg bg-muted text-muted-foreground hover:bg-destructive/10 hover:text-destructive flex items-center justify-center transition-all">
                                    <x-ui.icon name="trash-2" size="3" />
                                </button>
                            </div>
                            {{-- Price row --}}
                            <div class="flex items-center justify-between mt-2">
                                <span class="text-[11px] text-muted-foreground font-medium" x-text="'₹' + Number(item.price).toFixed(2) + ' × ' + item.quantity"></span>
                                <span class="text-sm font-black text-emerald-500" x-text="'₹' + Number(itemLineTotal(item)).toFixed(2)"></span>
                            </div>
                        </div>
                    </div>
                    {{-- Qty + Per-item discount row --}}
                    <div class="px-4 pb-4 flex flex-wrap items-center gap-3">
                        {{-- Qty stepper --}}
                        <div class="flex items-center gap-1 bg-muted/50 border border-border/50 rounded-xl p-1 shrink-0">
                            <button type="button" @click.prevent="updateCartQty(index, -1)"
                                class="size-7 flex items-center justify-center rounded-lg hover:bg-background hover:shadow-sm text-foreground transition-all font-black">
                                <x-ui.icon name="minus" size="3" />
                            </button>
                            <span class="w-8 text-center text-xs font-black text-foreground" x-text="item.quantity"></span>
                            <button type="button" @click.prevent="updateCartQty(index, 1)"
                                class="size-7 flex items-center justify-center rounded-lg hover:bg-background hover:shadow-sm text-foreground transition-all font-black">
                                <x-ui.icon name="plus" size="3" />
                            </button>
                        </div>
                        {{-- Per-item discount --}}
                        <div class="flex items-center gap-1.5 flex-1 min-w-0 justify-end">
                            <template x-if="item.discountValue > 0">
                                <div class="flex items-center gap-1 h-7 px-2 rounded-lg border border-emerald-500/30 bg-emerald-500/10 text-emerald-600">
                                    <x-ui.icon name="tag" size="3" />
                                    <span class="text-[10px] font-black"
                                        x-text="(item.discountType === 'flat' ? '₹' : '') + Number(item.discountValue).toFixed(item.discountValue % 1 === 0 ? 0 : 2) + (item.discountType === 'flat' ? ' off' : '% off')">
                                    </span>
                                </div>
                            </template>
                        </div>
                    </div>
                </div>
            </template>
        </div>

        {{-- Footer Area (Now inside scrollable container) --}}
        <div class="border-t border-border/50 bg-card" x-show="cart.length > 0" x-cloak>

            {{-- Order-Level Discount --}}
            <div class="px-5 pt-4 pb-3 border-b border-border/40 space-y-3">
                <p class="text-[10px] font-black uppercase tracking-widest text-muted-foreground flex items-center gap-2">
                    <x-ui.icon name="tag" size="3" class="text-primary" /> Order Discount
                </p>
                <div class="flex items-center gap-2">
                    <select x-model="orderDiscountType"
                        class="h-9 w-20 px-2 rounded-xl border border-border bg-background text-xs font-bold outline-none focus:ring-2 focus:ring-primary/20 shrink-0">
                        <option value="percent">% Off</option>
                        <option value="flat">₹ Off</option>
                    </select>
                    <input type="number" x-model="orderDiscountValue" min="0"
                        :placeholder="orderDiscountType === 'percent' ? 'e.g. 10' : 'e.g. 100'"
                        class="h-9 flex-1 px-3 rounded-xl border border-border bg-background text-sm font-bold outline-none focus:ring-2 focus:ring-primary/20">
                    <template x-if="orderDiscountAmount > 0">
                        <span class="text-xs font-black text-emerald-500 shrink-0" x-text="'- ₹' + Number(orderDiscountAmount).toFixed(2)"></span>
                    </template>
                </div>

                {{-- Coupon --}}
                <div class="flex items-center gap-2" x-show="!couponApplied">
                    <input type="text" x-model="couponCode" @keydown.enter.prevent="applyCoupon()"
                        placeholder="Promo code (SAVE10, FLAT50)"
                        class="h-9 flex-1 px-3 rounded-xl border border-border bg-background text-xs font-mono uppercase outline-none focus:ring-2 focus:ring-primary/20">
                    <button type="button" @click.prevent="applyCoupon()"
                        class="h-9 px-4 rounded-xl bg-primary/10 text-primary border border-primary/20 text-xs font-black hover:bg-primary hover:text-primary-foreground transition-all uppercase tracking-wider shrink-0">
                        Apply
                    </button>
                </div>
                <div class="flex items-center justify-between px-3 py-2 rounded-xl bg-emerald-500/10 border border-emerald-500/20" x-show="couponApplied" x-cloak>
                    <div class="flex items-center gap-2 text-emerald-600">
                        <x-ui.icon name="check-circle" size="4" />
                        <span class="text-xs font-black uppercase tracking-wider" x-text="'Coupon: ' + couponCode + ' (- ₹' + Number(couponDiscount).toFixed(2) + ')'"></span>
                    </div>
                    <button type="button" @click.prevent="removeCoupon()" class="text-muted-foreground hover:text-destructive transition-colors">
                        <x-ui.icon name="x" size="3" />
                    </button>
                </div>
            </div>

            {{-- Totals --}}
            <div class="px-5 pt-3 pb-4 space-y-2">
                <div class="flex justify-between text-xs font-medium text-muted-foreground">
                    <span>Subtotal</span>
                    <span class="text-foreground font-bold" x-text="'₹' + Number(subtotal).toFixed(2)"></span>
                </div>
                <div class="flex justify-between text-xs font-medium text-emerald-600" x-show="orderDiscountAmount > 0" x-cloak>
                    <span>Order Discount</span>
                    <span x-text="'- ₹' + Number(orderDiscountAmount).toFixed(2)"></span>
                </div>
                <div class="flex justify-between text-xs font-medium text-emerald-600" x-show="couponDiscount > 0" x-cloak>
                    <span>Coupon Savings</span>
                    <span x-text="'- ₹' + Number(couponDiscount).toFixed(2)"></span>
                </div>
                <div class="flex justify-between text-xs font-medium text-muted-foreground">
                    <span>GST (<span x-text="taxRate"></span>%)</span>
                    <span class="text-foreground" x-text="'₹' + Number(taxAmount).toFixed(2)"></span>
                </div>
                <div class="h-px bg-border/60 my-2"></div>
                <div class="flex justify-between items-center">
                    <span class="text-sm font-black uppercase tracking-widest text-foreground">Grand Total</span>
                    <span class="text-2xl font-black text-primary" x-text="'₹' + Number(grandTotal).toFixed(2)"></span>
                </div>
            </div>

            {{-- Review Order Button --}}
            <div class="px-5 pb-6">
                <button type="button" @click.prevent="activeTab = 'review'; isCartOpen = false" x-bind:disabled="cart.length === 0"
                    class="w-full h-14 rounded-2xl bg-gradient-to-r from-primary to-primary/90 text-primary-foreground text-sm font-black uppercase tracking-widest shadow-xl shadow-primary/30 hover:shadow-primary/40 hover:-translate-y-0.5 transition-all duration-300 flex items-center justify-center gap-2 disabled:opacity-50 disabled:pointer-events-none">
                    <x-ui.icon name="check-square" size="4" />
                    Review & Place Order
                </button>
            </div>
        </div>
    </div>
</div>
