{{-- ══ TAB: Order Review ══ --}}
<div x-show="activeTab === 'review'" 
     x-transition:enter="transition ease-out duration-500" 
     x-transition:enter-start="opacity-0 translate-y-4" 
     x-transition:enter-end="opacity-100 translate-y-0" 
     x-cloak>
    
    <div class="space-y-8 max-w-5xl mx-auto">
        {{-- ── Action Bar ── --}}
        <div class="flex flex-col sm:flex-row justify-between items-center gap-4 bg-card/40 backdrop-blur-xl p-4 rounded-2xl border border-border/40">
            <button type="button" @click="activeTab = 'order'" 
                class="w-full sm:w-auto h-11 px-6 rounded-xl border border-border bg-background text-[10px] font-black uppercase tracking-widest hover:bg-muted transition-all flex items-center justify-center gap-2">
                <x-ui.icon name="arrow-left" size="4" /> Back to Cart
            </button>
            <div class="hidden sm:block text-[9px] font-black uppercase tracking-[0.3em] text-muted-foreground/60">Final Validation Phase</div>
        </div>

        {{-- ── Main Review Area ── --}}
        <div class="space-y-8">
            
            {{-- 1. Full Customer Profile --}}
            <div class="p-8 rounded-[2rem] bg-card border border-border shadow-sm">
                <h4 class="text-[10px] font-black uppercase tracking-[0.2em] text-primary flex items-center gap-3 mb-8">
                    <span class="size-8 rounded-xl bg-primary/10 flex items-center justify-center">
                        <x-ui.icon name="user" size="4" />
                    </span>
                    Customer Identification
                </h4>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-8">
                    <div>
                        <p class="text-[9px] font-black uppercase tracking-widest text-muted-foreground mb-1">Full Name</p>
                        <p class="text-sm font-black text-foreground">{{ $customer->name }}</p>
                    </div>
                    <div>
                        <p class="text-[9px] font-black uppercase tracking-widest text-muted-foreground mb-1">Mobile</p>
                        <p class="text-sm font-black text-foreground">{{ $customer->phone ?? 'N/A' }}</p>
                    </div>
                    <div>
                        <p class="text-[9px] font-black uppercase tracking-widest text-muted-foreground mb-1">Alt Mobile</p>
                        <p class="text-sm font-black text-foreground">{{ $customer->alternatemobile ?? '—' }}</p>
                    </div>
                    <div>
                        <p class="text-[9px] font-black uppercase tracking-widest text-muted-foreground mb-1">Relative Mobile</p>
                        <p class="text-sm font-black text-foreground">{{ $customer->relative_mobile ?? '—' }}</p>
                    </div>
                </div>
            </div>

            {{-- 2. Billing Address --}}
            <div class="p-8 rounded-[2rem] bg-card border border-border shadow-sm space-y-6">
                <div class="flex items-center justify-between">
                    <h4 class="text-[10px] font-black uppercase tracking-[0.2em] text-primary flex items-center gap-3">
                        <span class="size-8 rounded-xl bg-primary/10 flex items-center justify-center">
                            <x-ui.icon name="file-text" size="4" />
                        </span>
                        Billing Address
                    </h4>
                    <button type="button" @click.prevent="openAddModal" class="text-[9px] font-black uppercase tracking-widest text-primary hover:underline flex items-center gap-1">
                        <x-ui.icon name="plus" size="3" /> New Address
                    </button>
                </div>
                
                <div class="grid grid-cols-1 gap-4">
                    @foreach($customer->addresses as $addr)
                        <label class="relative flex flex-col p-6 rounded-3xl border-2 cursor-pointer transition-all duration-300 group/addr"
                            :class="selectedBillingAddressId == {{ $addr->id }} ? 'border-primary bg-primary/[0.02]' : 'border-border/40 bg-muted/5 hover:border-border'">
                            <input type="radio" x-model="selectedBillingAddressId" value="{{ $addr->id }}" class="sr-only">
                            
                            <div class="flex items-start justify-between mb-4">
                                <div class="flex items-center gap-2">
                                    <span class="text-[9px] font-black uppercase tracking-widest px-3 py-1 rounded-full border border-border bg-background"
                                        :class="selectedBillingAddressId == {{ $addr->id }} ? 'border-primary text-primary' : ''">
                                        {{ $addr->label ?: 'Address' }}
                                    </span>
                                    @if($addr->is_default)
                                        <span class="text-[8px] font-black uppercase tracking-widest text-emerald-600 bg-emerald-500/10 px-2 py-0.5 rounded-full border border-emerald-500/20">Default</span>
                                    @endif
                                </div>
                                <template x-if="selectedBillingAddressId == {{ $addr->id }}">
                                    <x-ui.icon name="check-circle" size="5" class="text-primary" />
                                </template>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-x-12 gap-y-4">
                                <div>
                                    <p class="text-[9px] font-black uppercase tracking-widest text-muted-foreground mb-1">Street / Landmark</p>
                                    <p class="text-sm font-black text-foreground">{{ $addr->address_line_1 }}</p>
                                    @if($addr->address_line_2)
                                        <p class="text-xs text-muted-foreground font-medium mt-1">{{ $addr->address_line_2 }}</p>
                                    @endif
                                </div>
                                <div class="grid grid-cols-2 gap-4">
                                    <div>
                                        <p class="text-[9px] font-black uppercase tracking-widest text-muted-foreground mb-1">Village</p>
                                        <p class="text-xs font-black text-foreground">{{ $addr->village?->village_name ?? $addr->village_name ?? '—' }}</p>
                                    </div>
                                    <div>
                                        <p class="text-[9px] font-black uppercase tracking-widest text-muted-foreground mb-1">Post Office</p>
                                        <p class="text-xs font-black text-foreground">{{ $addr->village?->post_so_name ?? $addr->post_office ?? '—' }}</p>
                                    </div>
                                    <div>
                                        <p class="text-[9px] font-black uppercase tracking-widest text-muted-foreground mb-1">Taluka</p>
                                        <p class="text-xs font-black text-foreground">{{ $addr->village?->taluka_name ?? $addr->taluka ?? '—' }}</p>
                                    </div>
                                    <div>
                                        <p class="text-[9px] font-black uppercase tracking-widest text-muted-foreground mb-1">District / Pincode</p>
                                        <p class="text-xs font-black text-foreground">{{ $addr->village?->district_name ?? $addr->city ?? '—' }} - {{ $addr->village?->pincode ?? $addr->pincode }}</p>
                                    </div>
                                </div>
                            </div>
                            
                            {{-- Edit Action --}}
                            <button type="button" @click.stop.prevent="openEditModal({{ $addr->toJson() }})" 
                                class="absolute top-6 right-6 size-8 rounded-xl bg-background border border-border flex items-center justify-center text-muted-foreground hover:text-primary hover:border-primary opacity-0 group-hover/addr:opacity-100 transition-all">
                                <x-ui.icon name="edit-3" size="3.5" />
                            </button>
                        </label>
                    @endforeach
                </div>
            </div>

            {{-- 3. Shipping Address --}}
            <div class="p-8 rounded-[2rem] bg-card border border-border shadow-sm space-y-6">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                    <h4 class="text-[10px] font-black uppercase tracking-[0.2em] text-primary flex items-center gap-3">
                        <span class="size-8 rounded-xl bg-primary/10 flex items-center justify-center">
                            <x-ui.icon name="truck" size="4" />
                        </span>
                        Shipping Address
                    </h4>
                    <div class="flex items-center gap-6">
                        <label class="flex items-center gap-2 cursor-pointer group">
                            <input type="checkbox" x-model="sameAsBilling" class="rounded border-border text-primary focus:ring-primary/20">
                            <span class="text-[10px] font-black uppercase tracking-widest text-foreground group-hover:text-primary transition-colors">Same as Billing</span>
                        </label>
                        <button type="button" @click.prevent="openAddModal" class="text-[9px] font-black uppercase tracking-widest text-primary hover:underline flex items-center gap-1">
                            <x-ui.icon name="plus" size="3" /> New Address
                        </button>
                    </div>
                </div>

                <div x-show="!sameAsBilling" x-transition class="grid grid-cols-1 gap-4">
                    @foreach($customer->addresses as $addr)
                        <label class="relative flex flex-col p-6 rounded-3xl border-2 cursor-pointer transition-all duration-300 group/addr"
                            :class="selectedShippingAddressId == {{ $addr->id }} ? 'border-primary bg-primary/[0.02]' : 'border-border/40 bg-muted/5 hover:border-border'">
                            <input type="radio" x-model="selectedShippingAddressId" value="{{ $addr->id }}" class="sr-only">
                            
                            <div class="flex items-start justify-between mb-4">
                                <div class="flex items-center gap-2">
                                    <span class="text-[9px] font-black uppercase tracking-widest px-3 py-1 rounded-full border border-border bg-background"
                                        :class="selectedShippingAddressId == {{ $addr->id }} ? 'border-primary text-primary' : ''">
                                        {{ $addr->label ?: 'Address' }}
                                    </span>
                                </div>
                                <template x-if="selectedShippingAddressId == {{ $addr->id }}">
                                    <x-ui.icon name="check-circle" size="5" class="text-primary" />
                                </template>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-x-12 gap-y-4">
                                <div>
                                    <p class="text-[9px] font-black uppercase tracking-widest text-muted-foreground mb-1">Street / Landmark</p>
                                    <p class="text-sm font-black text-foreground">{{ $addr->address_line_1 }}</p>
                                    @if($addr->address_line_2)
                                        <p class="text-xs text-muted-foreground font-medium mt-1">{{ $addr->address_line_2 }}</p>
                                    @endif
                                </div>
                                <div class="grid grid-cols-2 gap-4">
                                    <div>
                                        <p class="text-[9px] font-black uppercase tracking-widest text-muted-foreground mb-1">Village</p>
                                        <p class="text-xs font-black text-foreground">{{ $addr->village?->village_name ?? $addr->village_name ?? '—' }}</p>
                                    </div>
                                    <div>
                                        <p class="text-[9px] font-black uppercase tracking-widest text-muted-foreground mb-1">Post Office</p>
                                        <p class="text-xs font-black text-foreground">{{ $addr->village?->post_so_name ?? $addr->post_office ?? '—' }}</p>
                                    </div>
                                    <div>
                                        <p class="text-[9px] font-black uppercase tracking-widest text-muted-foreground mb-1">Taluka</p>
                                        <p class="text-xs font-black text-foreground">{{ $addr->village?->taluka_name ?? $addr->taluka ?? '—' }}</p>
                                    </div>
                                    <div>
                                        <p class="text-[9px] font-black uppercase tracking-widest text-muted-foreground mb-1">District / Pincode</p>
                                        <p class="text-xs font-black text-foreground">{{ $addr->village?->district_name ?? $addr->city ?? '—' }} - {{ $addr->village?->pincode ?? $addr->pincode }}</p>
                                    </div>
                                </div>
                            </div>
                            
                            {{-- Edit Action --}}
                            <button type="button" @click.stop.prevent="openEditModal({{ $addr->toJson() }})" 
                                class="absolute top-6 right-6 size-8 rounded-xl bg-background border border-border flex items-center justify-center text-muted-foreground hover:text-primary hover:border-primary opacity-0 group-hover/addr:opacity-100 transition-all">
                                <x-ui.icon name="edit-3" size="3.5" />
                            </button>
                        </label>
                    @endforeach
                </div>

                <div x-show="sameAsBilling" class="p-8 rounded-[1.5rem] border border-dashed border-border/60 bg-muted/5 flex flex-col items-center justify-center text-center">
                    <div class="size-10 rounded-full bg-emerald-500/10 text-emerald-600 flex items-center justify-center mb-3">
                        <x-ui.icon name="check" size="5" />
                    </div>
                    <p class="text-xs font-bold text-muted-foreground">Synchronized with Billing Address</p>
                </div>
            </div>

            {{-- 4. Dispatch Information (Warehouse) --}}
            <div class="p-8 rounded-[2rem] bg-card border border-border shadow-sm">
                <h4 class="text-[10px] font-black uppercase tracking-[0.2em] text-primary flex items-center gap-3 mb-8">
                    <span class="size-8 rounded-xl bg-primary/10 flex items-center justify-center">
                        <x-ui.icon name="warehouse" size="4" />
                    </span>
                    Dispatch Information
                </h4>
                <div class="max-w-md">
                    <label class="text-[9px] font-black uppercase tracking-widest text-muted-foreground mb-3 block ml-1">Select Dispatch Hub</label>
                    <select x-model="selectedWarehouseId" class="w-full h-12 px-5 rounded-2xl border border-border bg-background/50 text-sm font-black focus:ring-4 focus:ring-primary/10 outline-none transition-all">
                        @foreach($warehouses as $wh)
                            <option value="{{ $wh->id }}">{{ $wh->name }} — {{ $wh->location }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            {{-- 5. Order Items Matrix --}}
            <div class="bg-card border border-border rounded-[2rem] shadow-sm overflow-hidden">
                <div class="px-8 py-6 border-b border-border/40 flex items-center justify-between bg-muted/5">
                    <h4 class="text-[10px] font-black uppercase tracking-[0.2em] text-foreground flex items-center gap-3">
                        <x-ui.icon name="shopping-bag" size="4" /> Order Items
                    </h4>
                    <span class="text-[10px] font-black text-muted-foreground uppercase tracking-widest" x-text="cart.length + ' Product Units'"></span>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-left">
                        <thead>
                            <tr class="bg-muted/10">
                                <th class="px-8 py-5 text-[9px] font-black uppercase tracking-widest text-muted-foreground">Product Specification</th>
                                <th class="px-6 py-5 text-[9px] font-black uppercase tracking-widest text-muted-foreground text-center">Qty</th>
                                <th class="px-6 py-5 text-[9px] font-black uppercase tracking-widest text-muted-foreground text-right">Unit Price</th>
                                <th class="px-8 py-5 text-[9px] font-black uppercase tracking-widest text-muted-foreground text-right">Net Total</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-border/20">
                            <template x-for="item in cart" :key="item.id">
                                <tr class="hover:bg-primary/[0.01] transition-colors">
                                    <td class="px-8 py-6">
                                        <div class="flex items-center gap-5">
                                            <div class="size-12 rounded-xl bg-muted/40 border border-border/40 flex items-center justify-center shrink-0 overflow-hidden">
                                                <template x-if="item.image_url">
                                                    <img :src="item.image_url" class="size-full object-cover">
                                                </template>
                                                <template x-if="!item.image_url">
                                                    <x-ui.icon name="package" size="5" class="text-muted-foreground/30" />
                                                </template>
                                            </div>
                                            <div>
                                                <p class="text-sm font-black text-foreground" x-text="item.name"></p>
                                                <div class="flex items-center gap-3 mt-1">
                                                    <span class="text-[10px] font-mono font-black text-primary/70 uppercase tracking-tighter" x-text="item.sku"></span>
                                                    <div class="size-1 bg-border rounded-full"></div>
                                                    <span class="text-[9px] font-black text-muted-foreground uppercase tracking-widest">Brand Ref: {{ $customer->brand ?? 'N/A' }}</span>
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-6 text-center">
                                        <span class="inline-flex items-center justify-center h-9 px-4 rounded-xl bg-muted/60 text-xs font-black text-foreground border border-border/40" x-text="item.quantity"></span>
                                    </td>
                                    <td class="px-6 py-6 text-right">
                                        <span class="text-xs font-bold text-muted-foreground" x-text="'₹' + Number(item.price).toLocaleString('en-IN', {minimumFractionDigits: 2})"></span>
                                    </td>
                                    <td class="px-8 py-6 text-right">
                                        <span class="text-sm font-black text-foreground" x-text="'₹' + Number(itemLineTotal(item)).toLocaleString('en-IN', {minimumFractionDigits: 2})"></span>
                                    </td>
                                </tr>
                            </template>
                        </tbody>
                    </table>
                </div>

                {{-- Order Summary Block (Below Items) --}}
                <div class="p-10 bg-muted/5 border-t border-border/40">
                    <div class="flex flex-col lg:flex-row justify-between gap-12">
                        <div class="lg:w-1/2 space-y-4">
                            <div class="p-6 rounded-3xl bg-background border border-border/60 flex gap-4 items-start">
                                <div class="size-10 rounded-2xl bg-primary/5 border border-primary/10 flex items-center justify-center text-primary shrink-0">
                                    <x-ui.icon name="shield-check" size="5" />
                                </div>
                                <p class="text-[10px] text-muted-foreground leading-relaxed font-semibold">
                                    By confirming, you authorize inventory allocation and logistics protocol initiation for the specified destinations.
                                </p>
                            </div>
                        </div>
                        <div class="lg:w-1/3 space-y-6">
                            <div class="flex justify-between items-center text-xs">
                                <span class="font-black text-muted-foreground/60 uppercase tracking-widest">Gross Subtotal</span>
                                <span class="font-black text-foreground" x-text="'₹' + Number(subtotal).toLocaleString('en-IN', {minimumFractionDigits: 2})"></span>
                            </div>
                            <template x-if="orderDiscountAmount > 0">
                                <div class="flex justify-between items-center text-xs">
                                    <span class="font-black text-emerald-600 uppercase tracking-widest">Order Adjustment</span>
                                    <span class="font-black text-emerald-600" x-text="'- ₹' + Number(orderDiscountAmount).toLocaleString('en-IN', {minimumFractionDigits: 2})"></span>
                                </div>
                            </template>
                            <div class="flex justify-between items-center text-xs">
                                <span class="font-black text-muted-foreground/60 uppercase tracking-widest">Statutory Tax (<span x-text="taxRate"></span>%)</span>
                                <span class="font-black text-foreground" x-text="'₹' + Number(taxAmount).toLocaleString('en-IN', {minimumFractionDigits: 2})"></span>
                            </div>
                            
                            <div class="pt-6 border-t border-border space-y-8">
                                <div class="flex justify-between items-center">
                                    <span class="text-[10px] font-black uppercase tracking-[0.3em] text-muted-foreground">Final Payable</span>
                                    <span class="text-4xl font-black text-primary tracking-tighter" x-text="'₹' + Number(grandTotal).toLocaleString('en-IN', {minimumFractionDigits: 2})"></span>
                                </div>
                                
                                {{-- ── Confirm Button Moved Here ── --}}
                                <form action="{{ route('customers.orders.place', $customer) }}" method="POST" class="w-full">
                                    @csrf
                                    <input type="hidden" name="cart" :value="JSON.stringify(cart)">
                                    <input type="hidden" name="order_discount_amount" :value="orderDiscountAmount">
                                    <input type="hidden" name="coupon_code" :value="couponApplied ? couponCode : ''">
                                    <input type="hidden" name="coupon_discount" :value="couponDiscount">
                                    <input type="hidden" name="tax_amount" :value="taxAmount">
                                    <input type="hidden" name="subtotal" :value="subtotal">
                                    <input type="hidden" name="grand_total" :value="grandTotal">
                                    <input type="hidden" name="warehouse_id" :value="selectedWarehouseId">
                                    <input type="hidden" name="billing_address_id" :value="selectedBillingAddressId">
                                    <input type="hidden" name="address_id" :value="selectedShippingAddressId">
                                    
                                    <button type="submit" 
                                        class="w-full h-14 rounded-2xl bg-primary text-primary-foreground text-xs font-black uppercase tracking-widest shadow-xl shadow-primary/20 hover:shadow-primary/40 hover:-translate-y-1 transition-all flex items-center justify-center gap-3">
                                        Confirm & Place Order <x-ui.icon name="zap" size="5" />
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
