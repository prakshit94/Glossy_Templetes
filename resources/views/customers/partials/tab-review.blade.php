{{-- ══ TAB: Order Review ══ --}}
<div x-show="activeTab === 'review'" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-4" x-transition:enter-end="opacity-100 translate-y-0" x-cloak>
    <div class="space-y-6">
        {{-- Review Header --}}
        <div class="bg-card/40 backdrop-blur-3xl border border-border/50 rounded-3xl p-8 shadow-2xl flex flex-col md:flex-row justify-between items-start md:items-center gap-6">
            <div class="flex items-center gap-5">
                <div class="size-16 rounded-[2rem] bg-primary/10 text-primary flex items-center justify-center shadow-inner border border-primary/20">
                    <x-ui.icon name="check-square" size="8" />
                </div>
                <div>
                    <h2 class="text-2xl font-black text-foreground tracking-tight uppercase">Order Review</h2>
                    <p class="text-[11px] text-muted-foreground font-black uppercase tracking-[0.2em]">Finalize details before placing order</p>
                </div>
            </div>
            <div class="flex items-center gap-3 w-full md:w-auto">
                <button type="button" @click="activeTab = 'order'" class="flex-1 md:flex-none h-12 px-6 rounded-2xl border border-border bg-card text-xs font-black uppercase tracking-widest hover:bg-muted transition-all">
                    Edit Items
                </button>
                <form action="{{ route('customers.orders.place', $customer) }}" method="POST" class="flex-1 md:flex-none">
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
                    
                    <button type="submit" class="w-full h-12 px-8 rounded-2xl bg-primary text-primary-foreground text-xs font-black uppercase tracking-widest shadow-xl shadow-primary/30 hover:shadow-primary/50 hover:-translate-y-0.5 transition-all flex items-center justify-center gap-2">
                        <x-ui.icon name="zap" size="4" />
                        Place Final Order
                    </button>
                </form>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            {{-- Left Column: Details --}}
            <div class="lg:col-span-2 space-y-6">
                {{-- Logistics Details --}}
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    {{-- Warehouse Selection --}}
                    <div class="p-8 rounded-[2rem] bg-card/60 backdrop-blur-xl border border-border/40 shadow-xl space-y-6">
                        <h4 class="text-[11px] font-black uppercase tracking-[0.2em] text-primary flex items-center gap-2">
                            <x-ui.icon name="truck" size="3" /> Dispatch Logistics
                        </h4>
                        <div>
                            <label class="text-[10px] font-black uppercase tracking-widest text-muted-foreground block mb-2">Dispatch Warehouse</label>
                            <select x-model="selectedWarehouseId" class="w-full h-11 px-4 rounded-xl border border-border bg-background/50 text-xs font-bold focus:ring-2 focus:ring-primary/20 outline-none transition-all">
                                @foreach($warehouses as $wh)
                                    <option value="{{ $wh->id }}">{{ $wh->name }} - {{ $wh->location }}</option>
                                @endforeach
                            </select>
                            <p class="text-[9px] text-muted-foreground mt-2 italic font-medium">Items will be dispatched from this location.</p>
                        </div>
                    </div>

                    {{-- Customer Quick Info --}}
                    <div class="p-8 rounded-[2rem] bg-card/60 backdrop-blur-xl border border-border/40 shadow-xl space-y-6">
                        <h4 class="text-[11px] font-black uppercase tracking-[0.2em] text-primary flex items-center gap-2">
                            <x-ui.icon name="user" size="3" /> Customer Profile
                        </h4>
                        <div class="space-y-4">
                            <div>
                                <p class="text-[10px] font-black uppercase tracking-widest text-muted-foreground mb-1">Full Name</p>
                                <p class="text-sm font-bold text-foreground">{{ $customer->name }}</p>
                            </div>
                            <div>
                                <p class="text-[10px] font-black uppercase tracking-widest text-muted-foreground mb-1">Mobile</p>
                                <p class="text-sm font-bold text-foreground">{{ $customer->phone ?? 'N/A' }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Address Selection Card --}}
                <div class="p-8 rounded-[2rem] bg-card/60 backdrop-blur-xl border border-border/40 shadow-xl space-y-6">
                    <div class="flex items-center justify-between">
                        <h4 class="text-[11px] font-black uppercase tracking-[0.2em] text-primary flex items-center gap-2">
                            <x-ui.icon name="map-pin" size="3" /> Address Selection
                        </h4>
                        <button type="button" @click.prevent="openAddModal" class="text-[10px] font-black uppercase tracking-widest text-primary hover:underline flex items-center gap-1">
                            <x-ui.icon name="plus" size="3" /> Add New Address
                        </button>
                    </div>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        {{-- Billing Address --}}
                        <div>
                            <label class="text-[10px] font-black uppercase tracking-widest text-muted-foreground block mb-2">Billing Address</label>
                            <div class="space-y-3 max-h-[300px] overflow-y-auto pr-2 custom-scrollbar">
                                @foreach($customer->addresses as $addr)
                                    <label class="relative flex flex-col p-4 rounded-2xl border-2 cursor-pointer transition-all hover:bg-muted/10"
                                        :class="selectedBillingAddressId == {{ $addr->id }} ? 'border-primary bg-primary/5 shadow-md shadow-primary/10' : 'border-border/50 bg-background/50 hover:border-border'">
                                        <input type="radio" x-model="selectedBillingAddressId" value="{{ $addr->id }}" class="sr-only">
                                        <div class="flex items-center justify-between mb-2">
                                            <span class="text-[10px] font-black uppercase tracking-widest" :class="selectedBillingAddressId == {{ $addr->id }} ? 'text-primary' : 'text-muted-foreground'">{{ $addr->label }}</span>
                                            <template x-if="selectedBillingAddressId == {{ $addr->id }}">
                                                <x-ui.icon name="check-circle" size="3.5" class="text-primary" />
                                            </template>
                                        </div>
                                        <p class="text-xs font-bold text-foreground leading-tight">{{ $addr->address_line_1 }}</p>
                                        <p class="text-[10px] text-muted-foreground mt-1">{{ $addr->village?->name }}, {{ $addr->village?->district }}</p>
                                    </label>
                                @endforeach
                            </div>
                        </div>

                        {{-- Shipping Address --}}
                        <div>
                            <label class="text-[10px] font-black uppercase tracking-widest text-muted-foreground block mb-2">Shipping Address</label>
                            <div class="space-y-3 max-h-[300px] overflow-y-auto pr-2 custom-scrollbar">
                                @foreach($customer->addresses as $addr)
                                    <label class="relative flex flex-col p-4 rounded-2xl border-2 cursor-pointer transition-all hover:bg-muted/10"
                                        :class="selectedShippingAddressId == {{ $addr->id }} ? 'border-primary bg-primary/5 shadow-md shadow-primary/10' : 'border-border/50 bg-background/50 hover:border-border'">
                                        <input type="radio" x-model="selectedShippingAddressId" value="{{ $addr->id }}" class="sr-only">
                                        <div class="flex items-center justify-between mb-2">
                                            <span class="text-[10px] font-black uppercase tracking-widest" :class="selectedShippingAddressId == {{ $addr->id }} ? 'text-primary' : 'text-muted-foreground'">{{ $addr->label }}</span>
                                            <template x-if="selectedShippingAddressId == {{ $addr->id }}">
                                                <x-ui.icon name="check-circle" size="3.5" class="text-primary" />
                                            </template>
                                        </div>
                                        <p class="text-xs font-bold text-foreground leading-tight">{{ $addr->address_line_1 }}</p>
                                        <p class="text-[10px] text-muted-foreground mt-1">{{ $addr->village?->name }}, {{ $addr->village?->district }}</p>
                                    </label>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Product Table Card --}}
                <div class="bg-card/60 backdrop-blur-xl border border-border/40 rounded-[2rem] shadow-xl overflow-hidden">
                    <div class="px-8 py-6 border-b border-border/40 flex items-center justify-between">
                        <h4 class="text-[11px] font-black uppercase tracking-[0.2em] text-primary flex items-center gap-2">
                            <x-ui.icon name="package" size="3" /> Order Items
                        </h4>
                        <span class="px-3 py-1 rounded-lg bg-primary/10 text-primary text-[10px] font-black uppercase tracking-widest" x-text="cart.length + ' Items Selected'"></span>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full text-left">
                            <thead class="bg-muted/30 text-[10px] uppercase font-black tracking-widest text-muted-foreground">
                                <tr>
                                    <th class="px-8 py-4">Product</th>
                                    <th class="px-4 py-4 text-center">Qty</th>
                                    <th class="px-4 py-4 text-right">Unit Price</th>
                                    <th class="px-4 py-4 text-right">Discount</th>
                                    <th class="px-8 py-4 text-right">Total</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-border/30">
                                <template x-for="item in cart" :key="item.id">
                                    <tr class="hover:bg-muted/20 transition-colors">
                                        <td class="px-8 py-4">
                                            <p class="text-sm font-black text-foreground" x-text="item.name"></p>
                                            <p class="text-[10px] text-muted-foreground font-mono mt-0.5" x-text="item.sku"></p>
                                        </td>
                                        <td class="px-4 py-4 text-center">
                                            <span class="text-xs font-black text-foreground px-2 py-1 rounded-lg bg-muted" x-text="item.quantity"></span>
                                        </td>
                                        <td class="px-4 py-4 text-right">
                                            <span class="text-xs font-bold text-muted-foreground" x-text="'₹' + Number(item.price).toFixed(2)"></span>
                                        </td>
                                        <td class="px-4 py-4 text-right">
                                            <span class="text-xs font-black text-emerald-600" x-text="item.discountValue > 0 ? (item.discountType === 'percent' ? item.discountValue + '%' : '₹' + Number(item.discountValue).toFixed(2)) : '-'"></span>
                                        </td>
                                        <td class="px-8 py-4 text-right">
                                            <span class="text-sm font-black text-foreground" x-text="'₹' + Number(itemLineTotal(item)).toFixed(2)"></span>
                                        </td>
                                    </tr>
                                </template>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            {{-- Right Column: Summary --}}
            <div class="space-y-6">
                {{-- Address Display Card --}}
                <div class="p-8 rounded-[2rem] bg-gradient-to-br from-primary/5 to-transparent border border-primary/10 shadow-xl space-y-6">
                    <h4 class="text-[11px] font-black uppercase tracking-[0.2em] text-primary flex items-center gap-2">
                        <x-ui.icon name="map-pin" size="3" /> Selected Summary
                    </h4>
                    <div class="space-y-6 divide-y divide-border/40">
                        {{-- Billing --}}
                        <div class="pt-4 first:pt-0">
                            <p class="text-[10px] font-black uppercase tracking-widest text-muted-foreground mb-3">Billing To</p>
                            <template x-for="addr in {{ json_encode($customer->addresses->map(function($a){ return ['id'=>$a->id, 'label'=>$a->label, 'line'=>$a->address_line_1, 'village'=>$a->village?->name, 'dist'=>$a->village?->district, 'pin'=>$a->village?->pincode]; })) }}" :key="'bill-'+addr.id">
                                <div x-show="selectedBillingAddressId == addr.id" class="space-y-1">
                                    <p class="text-sm font-black text-foreground" x-text="addr.label"></p>
                                    <p class="text-[11px] text-muted-foreground leading-tight" x-text="addr.line"></p>
                                    <p class="text-[11px] text-muted-foreground font-bold" x-text="addr.village + ', ' + addr.dist"></p>
                                </div>
                            </template>
                        </div>
                        {{-- Shipping --}}
                        <div class="pt-4">
                            <p class="text-[10px] font-black uppercase tracking-widest text-muted-foreground mb-3">Shipping To</p>
                            <template x-for="addr in {{ json_encode($customer->addresses->map(function($a){ return ['id'=>$a->id, 'label'=>$a->label, 'line'=>$a->address_line_1, 'village'=>$a->village?->name, 'dist'=>$a->village?->district, 'pin'=>$a->village?->pincode]; })) }}" :key="'ship-'+addr.id">
                                <div x-show="selectedShippingAddressId == addr.id" class="space-y-1">
                                    <p class="text-sm font-black text-foreground" x-text="addr.label"></p>
                                    <p class="text-[11px] text-muted-foreground leading-tight" x-text="addr.line"></p>
                                    <p class="text-[11px] text-muted-foreground font-bold" x-text="addr.village + ', ' + addr.dist"></p>
                                </div>
                            </template>
                        </div>
                    </div>
                </div>

                {{-- Summary Card --}}
                <div class="p-8 rounded-[2rem] bg-card border border-border shadow-2xl space-y-8 relative overflow-hidden">
                    <div class="absolute -right-20 -top-20 size-64 bg-primary/5 rounded-full blur-3xl pointer-events-none"></div>
                    
                    <h4 class="text-[11px] font-black uppercase tracking-[0.2em] text-foreground flex items-center gap-2 border-b border-border pb-4">
                        Order Summary
                    </h4>

                    <div class="space-y-4">
                        <div class="flex justify-between text-xs font-bold text-muted-foreground">
                            <span>Items Subtotal</span>
                            <span class="text-foreground" x-text="'₹' + Number(subtotal).toFixed(2)"></span>
                        </div>
                        <div class="flex justify-between text-xs font-black text-emerald-600" x-show="orderDiscountAmount > 0">
                            <span>Order Discount</span>
                            <span x-text="'- ₹' + Number(orderDiscountAmount).toFixed(2)"></span>
                        </div>
                        <div class="flex justify-between text-xs font-black text-emerald-600" x-show="couponDiscount > 0">
                            <span>Coupon Savings</span>
                            <span x-text="'- ₹' + Number(couponDiscount).toFixed(2)"></span>
                        </div>
                        <div class="flex justify-between text-xs font-bold text-muted-foreground">
                            <span>GST (<span x-text="taxRate"></span>%)</span>
                            <span class="text-foreground" x-text="'₹' + Number(taxAmount).toFixed(2)"></span>
                        </div>
                        
                        <div class="h-px bg-border/60 my-6"></div>
                        
                        <div class="flex justify-between items-center">
                            <div>
                                <p class="text-[10px] font-black uppercase tracking-[0.2em] text-muted-foreground mb-1">Payable Total</p>
                                <p class="text-3xl font-black text-primary" x-text="'₹' + Number(grandTotal).toFixed(2)"></p>
                            </div>
                        </div>
                    </div>

                    <div class="p-4 rounded-2xl bg-muted/30 border border-border/50">
                        <div class="flex gap-3">
                            <x-ui.icon name="alert-circle" size="4" class="text-primary mt-0.5 shrink-0" />
                            <p class="text-[10px] text-muted-foreground leading-relaxed">
                                By clicking "Place Final Order", you confirm that the inventory levels and dispatch logistics are verified.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
