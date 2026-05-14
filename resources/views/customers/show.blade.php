<x-layouts.app pageTitle="Customer Profile: {{ $customer->name }}">

    <div class="min-h-screen bg-gradient-to-br from-background via-background/90 to-background/50 pb-20 font-sans" x-data="{ 
        activeTab: 'order',
        editingAddress: null,
        deletingAddress: null,
        villageSearch: '',
        villages: [],
        searchingVillages: false,
        productSearchQuery: '',
        productSearchResults: [],
        searchingProducts: false,
        productPage: 1,
        productPerPage: 15,
        productTotal: 0,
        productFrom: 0,
        productTo: 0,
        productLastPage: 1,
        productStockFilter: 'available',
        productCategoryFilter: '',
        cart: [],
        init() {
            // Load cart from localStorage
            const savedCart = localStorage.getItem('customer_cart_{{ $customer->id }}');
            if (savedCart) {
                try {
                    this.cart = JSON.parse(savedCart);
                } catch (e) {
                    console.error('Failed to parse saved cart');
                }
            }
            
            // Watch cart for changes and save to localStorage
            this.$watch('cart', (value) => {
                localStorage.setItem('customer_cart_{{ $customer->id }}', JSON.stringify(value));
            });
            
            this.searchProducts();
        },
        async searchProducts(resetPage = false) {
            if (resetPage) this.productPage = 1;
            this.searchingProducts = true;
            try {
                const params = new URLSearchParams({
                    q: this.productSearchQuery,
                    stock: this.productStockFilter,
                    category: this.productCategoryFilter,
                    perPage: this.productPerPage,
                    page: this.productPage,
                });
                const res = await fetch(`/products-search-api?${params}`, {
                    headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
                });
                if (!res.ok) throw new Error('Network error');
                const json = await res.json();
                
                // Initialize temp inputs for each product
                this.productSearchResults = (json.data || []).map(p => ({
                    ...p,
                    _qty: 1,
                    _disc: 0,
                    _discType: 'percent'
                }));
                
                this.productTotal     = json.total || 0;
                this.productFrom      = json.from  || 0;
                this.productTo        = json.to    || 0;
                this.productLastPage  = json.last_page || 1;
            } catch (e) {
                this.notify('error', 'Failed to fetch products');
            } finally {
                this.searchingProducts = false;
            }
        },
        notify(type, message) {
            window.dispatchEvent(new CustomEvent('notify', { detail: { type, message } }));
        },
        addToCartWithOptions(product) {
            const qty = parseInt(product._qty) || 1;
            const discValue = parseFloat(product._disc) || 0;
            const discType = product._discType || 'percent';
            
            if (qty <= 0) {
                this.notify('warning', 'Please enter a valid quantity');
                return;
            }
            
            if (qty > product.available_stock && product.available_stock !== 999) {
                this.notify('warning', 'Insufficient stock available');
                return;
            }

            const existingIndex = this.cart.findIndex(i => i.id === product.id);
            if (existingIndex !== -1) {
                this.cart[existingIndex].quantity += qty;
                if (discValue > 0) {
                    this.cart[existingIndex].discountValue = discValue;
                    this.cart[existingIndex].discountType = discType;
                }
                this.notify('success', `Updated ${product.name} in cart`);
            } else {
                this.cart.push({
                    id: product.id,
                    name: product.name,
                    sku: product.sku,
                    price: product.selling_price,
                    image_url: product.image_url,
                    quantity: qty,
                    available: product.available_stock,
                    discountType: discType,
                    discountValue: discValue,
                });
                this.notify('success', `Added ${product.name} to cart`);
            }
            
            product._qty = 1;
            product._disc = 0;
        },
        addToCart(product) {
            this.addToCartWithOptions({
                ...product,
                _qty: 1,
                _disc: 0,
                _discType: 'percent'
            });
        },
        updateCartQty(index, delta) {
            const item = this.cart[index];
            if (!item) return;
            const newQty = item.quantity + delta;
            if (newQty <= 0) {
                this.removeFromCart(index);
            } else if (newQty <= item.available || item.available === 999) {
                item.quantity = newQty;
            } else {
                this.notify('warning', 'Cannot exceed available stock');
            }
        },
        removeFromCart(index) {
            const item = this.cart[index];
            this.cart.splice(index, 1);
            if (item) this.notify('info', `Removed ${item.name} from cart`);
        },
        isCartOpen: false,
        couponCode: '',
        couponApplied: false,
        couponDiscount: 0,
        orderDiscountType: 'percent',
        orderDiscountValue: 0,
        taxRate: 18,
        itemLineTotal(item) {
            const base = item.price * item.quantity;
            if (!item.discountValue || parseFloat(item.discountValue) <= 0) return base;
            const disc = item.discountType === 'percent'
                ? base * (parseFloat(item.discountValue) / 100)
                : Math.min(parseFloat(item.discountValue), base);
            return Math.max(0, base - disc);
        },
        get subtotal() {
            return this.cart.reduce((t, item) => t + this.itemLineTotal(item), 0);
        },
        get orderDiscountAmount() {
            const v = parseFloat(this.orderDiscountValue) || 0;
            return this.orderDiscountType === 'percent'
                ? Math.min(this.subtotal * v / 100, this.subtotal)
                : Math.min(v, this.subtotal);
        },
        get afterDiscount() {
            return Math.max(0, this.subtotal - this.orderDiscountAmount - this.couponDiscount);
        },
        get taxAmount() {
            return this.afterDiscount * this.taxRate / 100;
        },
        get grandTotal() {
            return this.afterDiscount + this.taxAmount;
        },
        applyCoupon() {
            const code = this.couponCode.toUpperCase().trim();
            if (code === 'SAVE10') { 
                this.couponDiscount = this.subtotal * 0.10; 
                this.couponApplied = true;
                this.notify('success', 'Coupon SAVE10 applied!');
            } else if (code === 'FLAT50') { 
                this.couponDiscount = 50; 
                this.couponApplied = true;
                this.notify('success', 'Coupon FLAT50 applied!');
            } else if (code === 'FLAT100') { 
                this.couponDiscount = 100; 
                this.couponApplied = true;
                this.notify('success', 'Coupon FLAT100 applied!');
            } else { 
                this.couponDiscount = 0; 
                this.couponApplied = false; 
                this.notify('error', 'Invalid coupon code');
            }
        },
        removeCoupon() { 
            this.couponCode = ''; 
            this.couponDiscount = 0; 
            this.couponApplied = false; 
            this.notify('info', 'Coupon removed');
        },
        openAddModal() {
            this.editingAddress = null;
            this.resetVillageSearch();
            $dispatch('open-modal', { name: 'address-modal' });
        },
        openEditModal(address) {
            this.editingAddress = address;
            this.resetVillageSearch();
            if (address && address.village) {
                this.editingAddress.village_name = address.village.village_name;
                this.editingAddress.post_office = address.village.post_so_name;
                this.editingAddress.taluka = address.village.taluka_name;
            } else {
                if (this.editingAddress) {
                    this.editingAddress.village_name = '';
                    this.editingAddress.post_office = '';
                    this.editingAddress.taluka = '';
                }
            }
            $dispatch('open-modal', { name: 'address-modal' });
        },
        openDeleteModal(address) {
            this.deletingAddress = address;
            $dispatch('open-modal', { name: 'delete-address-modal' });
        },
        resetVillageSearch() {
            this.villageSearch = '';
            this.villages = [];
        },
        async searchVillages() {
            if (this.villageSearch.length < 3) {
                this.villages = [];
                return;
            }
            this.searchingVillages = true;
            try {
                const res = await fetch(`/villages-search?q=${this.villageSearch}`, {
                    headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
                });
                if (!res.ok) throw new Error('Network response was not ok');
                const data = await res.json();
                this.villages = data.data || [];
            } catch (e) {
                console.error('Search failed:', e);
            } finally {
                this.searchingVillages = false;
            }
        },
        selectVillage(village) {
            this.villageSearch = village.name;
            this.villages = [];
            
            const villageIdInput = document.getElementById('village_id');
            if (villageIdInput) villageIdInput.value = village.id;
            
            const villageNameInput = document.getElementById('village_name');
            if (villageNameInput) villageNameInput.value = village.name || '';

            const cityInput = document.getElementById('city');
            if (cityInput) cityInput.value = village.district || '';
            
            const stateInput = document.getElementById('state');
            if (stateInput) stateInput.value = village.state || '';
            
            const pincodeInput = document.getElementById('pincode');
            if (pincodeInput) pincodeInput.value = village.pincode || '';
            
            const talukaInput = document.getElementById('taluka');
            if (talukaInput) talukaInput.value = village.taluka || '';

            const postOfficeInput = document.getElementById('post_office');
            if (postOfficeInput) postOfficeInput.value = village.post_office || '';
            
            if (this.editingAddress) {
                this.editingAddress.village_id = village.id;
                this.editingAddress.village_name = village.name;
                this.editingAddress.city = village.district;
                this.editingAddress.state = village.state;
                this.editingAddress.pincode = village.pincode;
                this.editingAddress.taluka = village.taluka;
                this.editingAddress.post_office = village.post_office;
            }
        }
    }">

        {{-- ── Premium Header & Glass Navigation ── --}}
        <div class="relative w-full bg-card/60 backdrop-blur-2xl border-b border-border/40 overflow-hidden mb-10">
            {{-- Decorative Abstract Blurs --}}
            <div class="absolute -top-32 -right-32 size-96 bg-primary/20 rounded-full blur-[100px] pointer-events-none"></div>
            <div class="absolute -bottom-32 -left-32 size-96 bg-blue-500/10 rounded-full blur-[100px] pointer-events-none"></div>
            <div class="absolute inset-0 bg-gradient-to-r from-primary/5 via-transparent to-purple-500/5 pointer-events-none"></div>

            <div class="max-w-7xl mx-auto px-6 lg:px-10 pt-12 pb-4 relative z-10">
                
                {{-- Header Content --}}
                <div class="flex flex-col md:flex-row md:items-end justify-between gap-6 mb-10">
                    <div class="flex items-center gap-5">
                        {{-- Avatar --}}
                        <div class="relative shrink-0 group">
                            <div class="size-20 rounded-3xl bg-gradient-to-br from-primary to-primary/80 text-primary-foreground flex items-center justify-center font-black text-3xl shadow-2xl shadow-primary/40 ring-4 ring-primary/10 transition-transform duration-300 group-hover:scale-105 group-hover:-rotate-3">
                                {{ $customer->initials() }}
                            </div>
                            @php
                                $dotClass = match($customer->status) {
                                    'active'    => 'bg-emerald-500 shadow-emerald-500/50',
                                    'suspended' => 'bg-red-500 shadow-red-500/50',
                                    default     => 'bg-orange-400 shadow-orange-500/50',
                                };
                            @endphp
                            <span class="absolute -bottom-1 -right-1 size-5 {{ $dotClass }} rounded-full border-4 border-background shadow-lg"></span>
                        </div>
                        
                        <div>
                            <div class="flex items-center gap-3 mb-1">
                                <h1 class="text-3xl font-black tracking-tight text-foreground bg-clip-text">{{ $customer->name }}</h1>
                                @php
                                    $badgeClass = match($customer->status) {
                                        'active'    => 'bg-emerald-500/15 text-emerald-500 border-emerald-500/30',
                                        'suspended' => 'bg-red-500/15 text-red-500 border-red-500/30',
                                        default     => 'bg-orange-500/15 text-orange-500 border-orange-500/30',
                                    };
                                @endphp
                                <span class="text-[10px] font-black uppercase tracking-[0.2em] px-3 py-1.5 rounded-xl border shadow-sm {{ $badgeClass }}">
                                    {{ $customer->status }}
                                </span>
                            </div>
                            <p class="text-sm text-muted-foreground/80 font-medium flex items-center gap-3">
                                <span class="font-mono bg-muted/50 px-2 py-0.5 rounded-md text-foreground">#{{ sprintf('%04d', $customer->id) }}</span>
                                <span class="size-1 rounded-full bg-border/80"></span>
                                Registered {{ $customer->created_at->format('F d, Y') }}
                            </p>
                        </div>
                    </div>

                    <div class="flex items-center gap-3">
                        <button type="button" @click.prevent="isCartOpen = true" style="z-index: 50;" class="group relative flex items-center justify-center h-10 px-4 rounded-2xl bg-card border border-border/80 text-sm font-bold text-foreground shadow-sm hover:shadow-md hover:border-border transition-all duration-300 hover:-translate-y-0.5">
                            <x-ui.icon name="shopping-cart" size="4" class="mr-2 opacity-70 group-hover:opacity-100 transition-opacity" /> Cart
                            <span x-show="cart && cart.length > 0" class="absolute -top-2 -right-2 flex size-5 items-center justify-center rounded-full bg-primary text-[10px] font-black text-primary-foreground shadow-lg" x-text="cart ? cart.length : 0" x-cloak></span>
                        </button>
                        <a href="{{ route('customers.index') }}" class="group relative flex items-center justify-center h-10 px-4 rounded-2xl bg-card border border-border/80 text-sm font-bold text-foreground shadow-sm hover:shadow-md hover:border-border transition-all duration-300 hover:-translate-y-0.5">
                            <x-ui.icon name="arrow-left" size="4" class="mr-2 opacity-70 group-hover:opacity-100 transition-opacity" /> Back
                        </a>
                        <a href="{{ route('customers.edit', $customer) }}" class="group relative flex items-center justify-center h-10 px-5 rounded-2xl bg-gradient-to-r from-primary to-primary/90 text-primary-foreground text-sm font-bold shadow-lg shadow-primary/25 hover:shadow-primary/40 transition-all duration-300 hover:-translate-y-0.5">
                            <x-ui.icon name="edit-3" size="4" class="mr-2 opacity-90" /> Edit Profile
                        </a>
                    </div>
                </div>

                {{-- Horizontal Pill Navigation --}}
                <div class="flex flex-row flex-nowrap items-center gap-2 overflow-x-auto whitespace-nowrap w-full pb-2" style="scrollbar-width:none;">
                    <template x-for="tab in [
                        { id: 'overview', icon: 'user',         label: 'Profile'        },
                        { id: 'order',    icon: 'shopping-bag', label: 'Order Products' },
                        { id: 'history',  icon: 'clock',        label: 'Order History'  },
                        { id: 'addresses',icon: 'map-pin',      label: 'Addresses'      },
                        { id: 'finance',  icon: 'hash',         label: 'Finance'        },
                        { id: 'system',   icon: 'settings',     label: 'System'         }
                    ]" :key="tab.id">
                        <button
                            type="button"
                            @click="activeTab = tab.id"
                            :class="activeTab === tab.id
                                ? 'bg-primary text-primary-foreground shadow-lg shadow-primary/30'
                                : 'bg-card/50 text-muted-foreground hover:bg-card hover:text-foreground border border-border/50'"
                            class="inline-flex shrink-0 items-center gap-2 px-5 py-2.5 rounded-2xl font-black text-xs uppercase tracking-widest transition-all duration-200 whitespace-nowrap"
                        >
                            <x-ui.icon x-bind:name="tab.icon" size="3.5" />
                            <span x-text="tab.label"></span>
                        </button>
                    </template>
                </div>
            </div>
        </div>

        {{-- ── Main Layout: Content Area ── --}}
        <div class="max-w-7xl mx-auto px-6 lg:px-10">

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
                                            <div class="space-y-1.5">
                                                <span class="text-[10px] font-black uppercase tracking-wider px-2.5 py-1 rounded-lg inline-block"
                                                    :class="product.available_stock > 0
                                                        ? 'bg-emerald-500/10 text-emerald-600 border border-emerald-500/20'
                                                        : 'bg-red-500/10 text-red-500 border border-red-500/20'"
                                                    x-text="product.available_stock > 0
                                                        ? (product.available_stock >= 999 ? 'In Stock' : product.available_stock + ' units')
                                                        : 'Out of Stock'">
                                                </span>
                                                <p class="text-[10px] text-muted-foreground" x-show="product.min_stock_level > 0" x-text="'Min: ' + product.min_stock_level"></p>
                                                <span x-show="product.allow_overselling" class="text-[10px] font-bold text-amber-500 flex items-center gap-1" x-cloak>
                                                    <x-ui.icon name="zap" size="3" /> Oversell OK
                                                </span>
                                            </div>
                                        </td>

                                        {{-- Qty + Discount inline before adding --}}
                                        <td class="px-4 py-3">
                                            <div class="flex items-center gap-1.5">
                                                <input type="number" x-model="product._qty" min="1"
                                                    :max="product.available_stock < 999 ? product.available_stock : 9999"
                                                    :placeholder="1"
                                                    class="h-8 w-14 px-2 rounded-lg border border-border bg-background text-xs font-bold text-center outline-none focus:ring-2 focus:ring-primary/20">
                                                <select x-model="product._discType"
                                                    class="h-8 w-12 px-1 rounded-lg border border-border bg-background text-[10px] font-bold outline-none focus:ring-2 focus:ring-primary/20">
                                                    <option value="percent">%</option>
                                                    <option value="flat">₹</option>
                                                </select>
                                                <input type="number" x-model="product._disc" min="0" placeholder="0"
                                                    class="h-8 w-14 px-2 rounded-lg border border-border bg-background text-xs font-bold text-right outline-none focus:ring-2 focus:ring-primary/20">
                                            </div>
                                        </td>

                                        {{-- Add Button --}}
                                        <td class="px-4 py-3 text-right">
                                            <button type="button"
                                                @click.prevent="addToCartWithOptions(product)"
                                                :disabled="product.available_stock <= 0"
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
                            <span class="px-3 text-[11px] font-black text-foreground">
                                Page <span x-text="productPage"></span> of <span x-text="productLastPage"></span>
                            </span>
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


            {{-- ══ TAB: Order History ══ --}}
            <div x-show="activeTab === 'history'" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-4" x-transition:enter-end="opacity-100 translate-y-0" x-cloak>
                <div x-data="{ expandedOrder: null }" class="space-y-6">
                    @if(isset($customer->orders) && $customer->orders->count())
                        @foreach($customer->orders as $order)
                            <div class="bg-card/40 backdrop-blur-3xl border border-border/50 rounded-3xl shadow-xl overflow-hidden transition-all duration-300">
                                
                                {{-- Order Summary Header (Click to expand) --}}
                                <div @click="expandedOrder = expandedOrder === {{ $order->id }} ? null : {{ $order->id }}" class="p-6 cursor-pointer hover:bg-muted/10 transition-colors flex flex-col md:flex-row md:items-center justify-between gap-4">
                                    <div class="flex items-center gap-4">
                                        <div class="size-12 rounded-2xl bg-primary/10 text-primary flex items-center justify-center shrink-0">
                                            <x-ui.icon name="package" size="5" />
                                        </div>
                                        <div>
                                            <div class="flex items-center gap-2 mb-1">
                                                <h3 class="text-lg font-black text-foreground">{{ $order->order_no }}</h3>
                                                @php
                                                    $statusColor = match ($order->status) {
                                                        'completed', 'delivered' => 'bg-emerald-500/10 text-emerald-500 border-emerald-500/20',
                                                        'cancelled', 'returned' => 'bg-red-500/10 text-red-500 border-red-500/20',
                                                        'shipped', 'in_transit' => 'bg-blue-500/10 text-blue-500 border-blue-500/20',
                                                        default => 'bg-amber-500/10 text-amber-500 border-amber-500/20',
                                                    };
                                                @endphp
                                                <span class="text-[10px] font-black uppercase tracking-widest px-2.5 py-1 rounded-lg border shadow-sm {{ $statusColor }}">
                                                    {{ str_replace('_', ' ', $order->status) }}
                                                </span>
                                            </div>
                                            <p class="text-xs text-muted-foreground font-medium flex items-center gap-2">
                                                <x-ui.icon name="calendar" size="3" /> Placed on {{ $order->created_at->format('M d, Y h:i A') }}
                                            </p>
                                        </div>
                                    </div>

                                    <div class="flex items-center gap-6">
                                        <div class="text-right">
                                            <p class="text-[10px] font-black uppercase tracking-widest text-muted-foreground mb-0.5">Order Total</p>
                                            <p class="text-xl font-black text-foreground">₹{{ number_format($order->net_amount, 2) }}</p>
                                        </div>
                                        <div class="size-8 rounded-full bg-muted/50 flex items-center justify-center text-muted-foreground group-hover:text-foreground group-hover:bg-muted transition-colors">
                                            <x-ui.icon name="chevron-down" size="4" class="transform transition-transform duration-300" x-bind:class="expandedOrder === {{ $order->id }} ? 'rotate-180' : ''" />
                                        </div>
                                    </div>
                                </div>

                                {{-- Order Full Details (Expanded) --}}
                                <div x-show="expandedOrder === {{ $order->id }}" x-collapse x-cloak>
                                    <div class="p-6 md:p-8 border-t border-border/40 bg-background/30 space-y-8">
                                        
                                        {{-- Actions Bar --}}
                                        <div class="flex flex-wrap gap-3">
                                            <a href="{{ route('orders.receipt', $order->id) }}" class="inline-flex items-center justify-center h-9 px-4 rounded-xl bg-card border border-border text-xs font-bold text-foreground shadow-sm hover:bg-muted hover:-translate-y-0.5 transition-all">
                                                <x-ui.icon name="file-text" size="3.5" class="mr-2" /> View Receipt
                                            </a>
                                            <button type="button" @click="window.print()" class="inline-flex items-center justify-center h-9 px-4 rounded-xl bg-primary text-primary-foreground text-xs font-bold shadow-lg shadow-primary/25 hover:shadow-primary/40 hover:-translate-y-0.5 transition-all">
                                                <x-ui.icon name="printer" size="3.5" class="mr-2" /> Print Order
                                            </button>
                                        </div>

                                        {{-- Progress Stepper --}}
                                        <div class="bg-card/60 backdrop-blur-md rounded-2xl border border-border/50 p-6">
                                            <h4 class="text-[11px] font-black uppercase tracking-[0.2em] text-muted-foreground mb-8 flex items-center gap-2">
                                                <span class="size-2 rounded-full bg-primary inline-block shadow-lg shadow-primary/50"></span> Fulfillment Tracking
                                            </h4>
                                            <div class="relative flex justify-between items-center w-full px-2 sm:px-8">
                                                <div class="absolute left-0 top-5 w-full h-1 bg-muted rounded-full z-0"></div>
                                                @php
                                                    $progress = match ($order->status ?? '') {
                                                        'confirmed' => '20%',
                                                        'processing' => '40%',
                                                        'ready_to_ship' => '60%',
                                                        'shipped', 'in_transit' => '80%',
                                                        'delivered', 'completed' => '100%',
                                                        default => '0%',
                                                    };
                                                @endphp
                                                <div class="absolute left-0 top-5 h-1 bg-primary rounded-full z-0 transition-all duration-1000 ease-out shadow-sm" style="width: {{ $progress }}"></div>

                                                <div class="relative z-10 flex justify-between w-full">
                                                    {{-- Placed --}}
                                                    <div class="flex flex-col items-center group">
                                                        <div class="size-10 rounded-full flex items-center justify-center bg-primary text-primary-foreground shadow-lg shadow-primary/30 ring-4 ring-background transition-transform group-hover:scale-110">
                                                            <x-ui.icon name="shopping-cart" size="4" />
                                                        </div>
                                                        <span class="mt-3 text-xs font-bold text-foreground">Placed</span>
                                                    </div>

                                                    {{-- Processing --}}
                                                    @php $isProc = in_array($order->status ?? '', ['confirmed', 'processing', 'ready_to_ship', 'shipped', 'in_transit', 'delivered', 'completed']); @endphp
                                                    <div class="flex flex-col items-center group">
                                                        <div class="size-10 rounded-full flex items-center justify-center {{ $isProc ? 'bg-primary text-primary-foreground shadow-lg shadow-primary/30' : 'bg-card border-2 border-border text-muted-foreground' }} ring-4 ring-background transition-all duration-500 group-hover:scale-110">
                                                            <x-ui.icon name="package" size="4" />
                                                        </div>
                                                        <span class="mt-3 text-xs font-bold {{ $isProc ? 'text-foreground' : 'text-muted-foreground' }}">Processing</span>
                                                    </div>

                                                    {{-- Dispatched --}}
                                                    @php $isShip = in_array($order->status ?? '', ['shipped', 'in_transit', 'delivered', 'completed']); @endphp
                                                    <div class="flex flex-col items-center group">
                                                        <div class="size-10 rounded-full flex items-center justify-center {{ $isShip ? 'bg-primary text-primary-foreground shadow-lg shadow-primary/30' : 'bg-card border-2 border-border text-muted-foreground' }} ring-4 ring-background transition-all duration-500 group-hover:scale-110">
                                                            <x-ui.icon name="truck" size="4" />
                                                        </div>
                                                        <span class="mt-3 text-xs font-bold {{ $isShip ? 'text-foreground' : 'text-muted-foreground' }}">Dispatched</span>
                                                    </div>

                                                    {{-- Delivered --}}
                                                    @php $isDone = in_array($order->status ?? '', ['delivered', 'completed']); @endphp
                                                    <div class="flex flex-col items-center group">
                                                        <div class="size-10 rounded-full flex items-center justify-center {{ $isDone ? 'bg-primary text-primary-foreground shadow-lg shadow-primary/30' : 'bg-card border-2 border-border text-muted-foreground' }} ring-4 ring-background transition-all duration-500 group-hover:scale-110">
                                                            <x-ui.icon name="check" size="4" />
                                                        </div>
                                                        <span class="mt-3 text-xs font-bold {{ $isDone ? 'text-foreground' : 'text-muted-foreground' }}">Delivered</span>
                                                    </div>
                                                </div>
                                            </div>
                                            
                                            {{-- Tracking Info --}}
                                            @if(isset($order->shipments) && $order->shipments->isNotEmpty())
                                                <div class="mt-8 bg-primary/5 rounded-xl border border-primary/10 p-5 flex flex-wrap gap-8 items-center">
                                                    <div class="flex items-center gap-3">
                                                        <div class="p-2 bg-background rounded-lg text-primary shadow-sm">
                                                            <x-ui.icon name="truck" size="4" />
                                                        </div>
                                                        <div>
                                                            <p class="text-[10px] font-black text-muted-foreground uppercase tracking-widest">Carrier</p>
                                                            <p class="text-sm font-bold text-foreground">{{ $order->shipments->first()->carrier ?? 'N/A' }}</p>
                                                        </div>
                                                    </div>
                                                    <div class="flex items-center gap-3">
                                                        <div class="p-2 bg-background rounded-lg text-primary shadow-sm">
                                                            <x-ui.icon name="hash" size="4" />
                                                        </div>
                                                        <div>
                                                            <p class="text-[10px] font-black text-muted-foreground uppercase tracking-widest">Tracking ID</p>
                                                            <p class="text-sm font-mono font-bold text-primary">{{ $order->shipments->first()->tracking_number ?? 'N/A' }}</p>
                                                        </div>
                                                    </div>
                                                </div>
                                            @endif
                                        </div>

                                        {{-- Items and Summary Grid --}}
                                        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                                            
                                            {{-- Items Table --}}
                                            <div class="lg:col-span-2 bg-card/60 backdrop-blur-md rounded-2xl border border-border/50 overflow-hidden">
                                                <div class="p-5 border-b border-border/40 flex justify-between items-center bg-muted/10">
                                                    <h4 class="text-sm font-black text-foreground">Order Items</h4>
                                                    <span class="px-2 py-0.5 rounded-md bg-background text-[10px] font-bold text-muted-foreground shadow-sm">{{ isset($order->items) ? $order->items->count() : 0 }} items</span>
                                                </div>
                                                <div class="overflow-x-auto custom-scrollbar">
                                                    <table class="w-full text-left">
                                                        <thead class="bg-muted/30 text-[10px] uppercase font-black tracking-widest text-muted-foreground">
                                                            <tr>
                                                                <th class="px-5 py-3">Product</th>
                                                                <th class="px-5 py-3 text-right">Price</th>
                                                                <th class="px-5 py-3 text-center">Qty</th>
                                                                <th class="px-5 py-3 text-right">Total</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody class="divide-y divide-border/30">
                                                            @if(isset($order->items))
                                                                @foreach($order->items as $item)
                                                                    <tr class="hover:bg-muted/10 transition-colors">
                                                                        <td class="px-5 py-4">
                                                                            <p class="text-sm font-bold text-foreground">{{ $item->product?->name ?? 'Unknown Product' }}</p>
                                                                            <p class="text-[10px] text-muted-foreground font-mono mt-0.5">{{ $item->product?->sku ?? 'N/A' }}</p>
                                                                        </td>
                                                                        <td class="px-5 py-4 text-right text-muted-foreground font-medium text-xs">
                                                                            ₹{{ number_format($item->unit_price ?? 0, 2) }}
                                                                        </td>
                                                                        <td class="px-5 py-4 text-center">
                                                                            <span class="inline-flex items-center justify-center min-w-[2rem] px-2 py-1 rounded-md bg-muted/50 text-[11px] font-black text-foreground border border-border/50">{{ $item->quantity ?? 1 }}</span>
                                                                        </td>
                                                                        <td class="px-5 py-4 text-right font-black text-foreground text-sm">
                                                                            ₹{{ number_format((($item->unit_price ?? 0) * ($item->quantity ?? 1)) - ($item->discount_amount ?? 0), 2) }}
                                                                        </td>
                                                                    </tr>
                                                                @endforeach
                                                            @endif
                                                        </tbody>
                                                    </table>
                                                </div>
                                            </div>

                                            {{-- Payment Summary & Address --}}
                                            <div class="space-y-6">
                                                <div class="bg-card/60 backdrop-blur-md rounded-2xl border border-border/50 p-6">
                                                    <h4 class="text-sm font-black text-foreground mb-5 flex items-center gap-2">
                                                        <x-ui.icon name="credit-card" size="4" class="text-muted-foreground" /> Payment Summary
                                                    </h4>
                                                    <div class="space-y-3">
                                                        <div class="flex justify-between text-xs font-medium text-muted-foreground">
                                                            <span>Subtotal</span>
                                                            <span class="text-foreground">₹{{ number_format((float) ($order->total_amount ?? 0), 2) }}</span>
                                                        </div>
                                                        @if(isset($order->discount_amount) && $order->discount_amount > 0)
                                                            <div class="flex justify-between text-xs font-medium text-emerald-500">
                                                                <span>Discount</span>
                                                                <span>- ₹{{ number_format((float) $order->discount_amount, 2) }}</span>
                                                            </div>
                                                        @endif
                                                        <div class="flex justify-between text-xs font-medium text-muted-foreground">
                                                            <span>Shipping</span>
                                                            <span class="text-foreground">₹{{ number_format((float) ($order->shipping_amount ?? 0), 2) }}</span>
                                                        </div>
                                                        <div class="flex justify-between text-xs font-medium text-muted-foreground">
                                                            <span>Tax Total</span>
                                                            <span class="text-foreground">₹{{ number_format((float) ($order->tax_amount ?? 0), 2) }}</span>
                                                        </div>
                                                        <div class="h-px bg-border/60 my-3"></div>
                                                        <div class="flex justify-between items-end">
                                                            <span class="text-sm font-black uppercase tracking-widest text-foreground">Grand Total</span>
                                                            <span class="text-2xl font-black text-primary">₹{{ number_format((float) ($order->grand_total ?? 0), 2) }}</span>
                                                        </div>
                                                    </div>
                                                </div>

                                                @if(isset($order->shippingAddress))
                                                    <div class="bg-card/60 backdrop-blur-md rounded-2xl border border-border/50 p-6 relative overflow-hidden group">
                                                        <div class="absolute -right-4 -top-4 size-16 bg-primary/5 rounded-full pointer-events-none group-hover:scale-150 transition-transform duration-500"></div>
                                                        <h4 class="text-sm font-black text-foreground mb-4 flex items-center gap-2 relative z-10">
                                                            <x-ui.icon name="map-pin" size="4" class="text-primary" /> Delivery Address
                                                        </h4>
                                                        <div class="relative z-10">
                                                            <p class="text-xs font-bold text-foreground mb-1">{{ $order->shippingAddress->contact_name ?? $customer->name }}</p>
                                                            <p class="text-[11px] text-muted-foreground leading-relaxed font-medium">
                                                                {{ $order->shippingAddress->address_line1 ?? '' }}<br>
                                                                {{ $order->shippingAddress->city ?? '' }}, {{ $order->shippingAddress->state ?? '' }} {{ $order->shippingAddress->pincode ?? '' }}
                                                            </p>
                                                            @if($order->shippingAddress->contact_phone)
                                                                <p class="text-xs font-medium text-primary mt-2 flex items-center gap-1">
                                                                    <x-ui.icon name="phone" size="3" /> {{ $order->shippingAddress->contact_phone }}
                                                                </p>
                                                            @endif
                                                        </div>
                                                    </div>
                                                @endif
                                            </div>

                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    @else
                        <div class="text-center py-24 px-4 rounded-3xl border-2 border-dashed border-border/60 bg-muted/5">
                            <div class="size-20 rounded-3xl bg-muted flex items-center justify-center mx-auto mb-6">
                                <x-ui.icon name="clock" size="8" class="text-muted-foreground/50" />
                            </div>
                            <h4 class="text-lg font-black text-foreground">No Order History</h4>
                            <p class="text-sm text-muted-foreground mt-2 max-w-sm mx-auto">This customer hasn't placed any orders yet. Once they do, they will appear here beautifully formatted.</p>
                            <x-ui.button @click="activeTab = 'order'" class="mt-8 h-12 px-6 rounded-xl gap-2 shadow-xl shadow-primary/20 text-xs font-bold uppercase tracking-widest">
                                <x-ui.icon name="shopping-bag" size="4" /> Place an Order
                            </x-ui.button>
                        </div>
                    @endif
                </div>
            </div>

            {{-- ══ TAB: Overview / Profile ══ --}}

            <div x-show="activeTab === 'overview'" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-4" x-transition:enter-end="opacity-100 translate-y-0" x-cloak>
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

                    {{-- LEFT COL: Personal Info --}}
                    <div class="lg:col-span-2 space-y-6">

                        {{-- Identity Card --}}
                        <div class="bg-card/60 backdrop-blur-xl border border-border/50 rounded-3xl shadow-xl overflow-hidden">
                            <div class="px-6 py-4 border-b border-border/40 bg-muted/10 flex items-center gap-2">
                                <x-ui.icon name="user" size="4" class="text-primary" />
                                <h3 class="text-xs font-black uppercase tracking-widest text-foreground">Personal Information</h3>
                            </div>
                            <div class="p-6 grid grid-cols-1 sm:grid-cols-2 gap-x-8 gap-y-5">
                                @php $field = fn($label,$val,$icon='info') => [$label,$val,$icon]; @endphp

                                @foreach([
                                    ['Full Name', $customer->name, 'user'],
                                    ['Email', $customer->email ?: '—', 'mail'],
                                    ['Phone', $customer->phone ?: '—', 'phone'],
                                    ['Alternate Mobile', $customer->alternatemobile ?? '—', 'phone-call'],
                                    ['Relative Mobile', $customer->relative_mobile ?? '—', 'users'],
                                    ['Customer ID', '#'.sprintf('%04d',$customer->id), 'hash'],
                                    ['Status', ucfirst($customer->status), 'activity'],
                                    ['Registered On', $customer->created_at->format('M d, Y'), 'calendar'],
                                ] as [$label, $val, $icon])
                                <div class="flex items-start gap-3">
                                    <div class="size-8 rounded-xl bg-muted/50 flex items-center justify-center text-muted-foreground shrink-0 mt-0.5">
                                        <x-ui.icon name="{{ $icon }}" size="3.5" />
                                    </div>
                                    <div class="min-w-0">
                                        <p class="text-[10px] font-black uppercase tracking-widest text-muted-foreground">{{ $label }}</p>
                                        <p class="text-sm font-bold text-foreground truncate mt-0.5">{{ $val }}</p>
                                    </div>
                                </div>
                                @endforeach
                            </div>
                        </div>

                        {{-- Tax & Business Info --}}
                        <div class="bg-card/60 backdrop-blur-xl border border-border/50 rounded-3xl shadow-xl overflow-hidden">
                            <div class="px-6 py-4 border-b border-border/40 bg-muted/10 flex items-center gap-2">
                                <x-ui.icon name="file-text" size="4" class="text-blue-500" />
                                <h3 class="text-xs font-black uppercase tracking-widest text-foreground">Tax & Business Info</h3>
                            </div>
                            <div class="p-6 grid grid-cols-1 sm:grid-cols-2 gap-x-8 gap-y-5">
                                @foreach([
                                    ['GST Number', $customer->gst_no ?: '—', 'shield'],
                                    ['PAN Number', $customer->pan_no ?: '—', 'credit-card'],
                                    ['Aadhaar', $customer->aadhaar_no ?? '—', 'id-card'],
                                    ['Business Name', $customer->business_name ?? '—', 'briefcase'],
                                ] as [$label, $val, $icon])
                                <div class="flex items-start gap-3">
                                    <div class="size-8 rounded-xl bg-blue-500/10 flex items-center justify-center text-blue-500 shrink-0 mt-0.5">
                                        <x-ui.icon name="{{ $icon }}" size="3.5" />
                                    </div>
                                    <div class="min-w-0">
                                        <p class="text-[10px] font-black uppercase tracking-widest text-muted-foreground">{{ $label }}</p>
                                        <p class="text-sm font-bold text-foreground font-mono mt-0.5">{{ $val }}</p>
                                    </div>
                                </div>
                                @endforeach
                            </div>
                        </div>
                    </div>

                    {{-- RIGHT COL: Stats + System --}}
                    <div class="space-y-6">
                        {{-- Quick Stats --}}
                        <div class="bg-card/60 backdrop-blur-xl border border-border/50 rounded-3xl shadow-xl overflow-hidden">
                            <div class="px-6 py-4 border-b border-border/40 bg-muted/10 flex items-center gap-2">
                                <x-ui.icon name="bar-chart-2" size="4" class="text-emerald-500" />
                                <h3 class="text-xs font-black uppercase tracking-widest text-foreground">Quick Stats</h3>
                            </div>
                            <div class="p-6 space-y-4">
                                <div class="flex items-center gap-4 p-4 rounded-2xl bg-emerald-500/5 border border-emerald-500/10">
                                    <div class="size-12 rounded-2xl bg-emerald-500/10 flex items-center justify-center shrink-0">
                                        <x-ui.icon name="shopping-bag" size="5" class="text-emerald-500" />
                                    </div>
                                    <div>
                                        <p class="text-[10px] font-black uppercase tracking-widest text-muted-foreground">Total Orders</p>
                                        <p class="text-3xl font-black text-foreground">{{ $customer->orders()->count() }}</p>
                                    </div>
                                </div>
                                <div class="flex items-center gap-4 p-4 rounded-2xl bg-purple-500/5 border border-purple-500/10">
                                    <div class="size-12 rounded-2xl bg-purple-500/10 flex items-center justify-center shrink-0">
                                        <x-ui.icon name="map-pin" size="5" class="text-purple-500" />
                                    </div>
                                    <div>
                                        <p class="text-[10px] font-black uppercase tracking-widest text-muted-foreground">Addresses</p>
                                        <p class="text-3xl font-black text-foreground">{{ $customer->addresses->count() }}</p>
                                    </div>
                                </div>
                                <div class="flex items-center gap-4 p-4 rounded-2xl bg-primary/5 border border-primary/10">
                                    <div class="size-12 rounded-2xl bg-primary/10 flex items-center justify-center shrink-0">
                                        <x-ui.icon name="credit-card" size="5" class="text-primary" />
                                    </div>
                                    <div>
                                        <p class="text-[10px] font-black uppercase tracking-widest text-muted-foreground">Credit Limit</p>
                                        <p class="text-2xl font-black text-foreground">₹{{ number_format($customer->credit_limit ?? 0) }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- System Info --}}
                        <div class="bg-card/60 backdrop-blur-xl border border-border/50 rounded-3xl shadow-xl overflow-hidden">
                            <div class="px-6 py-4 border-b border-border/40 bg-muted/10 flex items-center gap-2">
                                <x-ui.icon name="settings" size="4" class="text-muted-foreground" />
                                <h3 class="text-xs font-black uppercase tracking-widest text-foreground">System Info</h3>
                            </div>
                            <div class="p-6 space-y-4">
                                <div>
                                    <p class="text-[10px] font-black uppercase tracking-widest text-muted-foreground">Created At</p>
                                    <p class="text-sm font-bold text-foreground mt-0.5">{{ $customer->created_at->format('M d, Y — h:i A') }}</p>
                                    <p class="text-[11px] text-muted-foreground">{{ $customer->created_at->diffForHumans() }}</p>
                                </div>
                                <div>
                                    <p class="text-[10px] font-black uppercase tracking-widest text-muted-foreground">Last Updated</p>
                                    <p class="text-sm font-bold text-foreground mt-0.5">{{ $customer->updated_at->format('M d, Y — h:i A') }}</p>
                                    <p class="text-[11px] text-muted-foreground">{{ $customer->updated_at->diffForHumans() }}</p>
                                </div>
                                <div>
                                    <p class="text-[10px] font-black uppercase tracking-widest text-muted-foreground">Record ID</p>
                                    <p class="text-sm font-mono font-bold text-primary mt-0.5">#{{ sprintf('%04d', $customer->id) }}</p>
                                </div>
                                @if($customer->trashed())
                                <div class="flex items-center gap-2 px-3 py-2 rounded-xl bg-destructive/10 border border-destructive/20 text-destructive text-xs font-bold">
                                    <x-ui.icon name="archive" size="3.5" /> Archived Record
                                </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- ══ TAB: Finance ══ --}}
            <div x-show="activeTab === 'finance'" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-4" x-transition:enter-end="opacity-100 translate-y-0" x-cloak>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <x-ui.card class="p-8 rounded-3xl border-border/40 shadow-xl bg-card/60 backdrop-blur-xl relative overflow-hidden">
                        <div class="absolute top-0 right-0 p-8 opacity-5 pointer-events-none">
                            <x-ui.icon name="file-text" size="24" />
                        </div>
                        <h4 class="text-[11px] font-black uppercase tracking-[0.2em] text-muted-foreground mb-8 flex items-center gap-2">
                            <span class="size-2 rounded-full bg-blue-500 inline-block shadow-lg shadow-blue-500/50"></span> Tax Details
                        </h4>
                        <div class="space-y-6">
                            <div>
                                <p class="text-[10px] font-black uppercase tracking-widest text-muted-foreground mb-2">GST Number</p>
                                <p class="text-lg font-mono font-bold text-foreground">{{ $customer->gst_no ?: 'Not Provided' }}</p>
                            </div>
                            <div>
                                <p class="text-[10px] font-black uppercase tracking-widest text-muted-foreground mb-2">PAN Number</p>
                                <p class="text-lg font-mono font-bold text-foreground">{{ $customer->pan_no ?: 'Not Provided' }}</p>
                            </div>
                        </div>
                    </x-ui.card>

                    <x-ui.card class="p-8 rounded-3xl border-border/40 shadow-xl bg-card/60 backdrop-blur-xl relative overflow-hidden">
                        <div class="absolute top-0 right-0 p-8 opacity-5 pointer-events-none">
                            <x-ui.icon name="credit-card" size="24" />
                        </div>
                        <h4 class="text-[11px] font-black uppercase tracking-[0.2em] text-muted-foreground mb-8 flex items-center gap-2">
                            <span class="size-2 rounded-full bg-emerald-500 inline-block shadow-lg shadow-emerald-500/50"></span> Credit Policy
                        </h4>
                        <div class="space-y-6">
                            <div>
                                <p class="text-[10px] font-black uppercase tracking-widest text-muted-foreground mb-2">Credit Limit</p>
                                <p class="text-3xl font-black text-emerald-500">₹{{ number_format($customer->credit_limit, 2) }}</p>
                            </div>
                            <div>
                                <p class="text-[10px] font-black uppercase tracking-widest text-muted-foreground mb-2">Payment Terms</p>
                                <p class="text-xl font-bold text-foreground">{{ $customer->credit_days ?: 0 }} <span class="text-sm text-muted-foreground">Days</span></p>
                            </div>
                        </div>
                    </x-ui.card>
                </div>
            </div>

            {{-- ══ TAB: Addresses ══ --}}
            <div x-show="activeTab === 'addresses'" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-4" x-transition:enter-end="opacity-100 translate-y-0" x-cloak>
                <div class="flex items-center justify-between mb-8">
                    <h3 class="text-xl font-black tracking-tight text-foreground">Registered Addresses</h3>
                    <x-ui.button @click.prevent="openAddModal" class="rounded-xl h-10 px-5 gap-2 shadow-lg shadow-primary/20 text-xs font-bold uppercase tracking-widest">
                        <x-ui.icon name="plus" size="4" /> Add Address
                    </x-ui.button>
                </div>

                @if($customer->addresses->count())
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                        @foreach($customer->addresses as $address)
                            <div class="group relative p-6 rounded-3xl bg-card border border-border/50 shadow-sm hover:shadow-2xl hover:border-primary/40 hover:-translate-y-1 transition-all duration-300 overflow-hidden">
                                @if($address->is_default)
                                    <div class="absolute top-0 right-0 bg-primary text-primary-foreground text-[9px] font-black uppercase tracking-widest px-4 py-1.5 rounded-bl-xl shadow-md">
                                        Default
                                    </div>
                                @endif

                                <div class="flex items-center gap-3 mb-5">
                                    <div class="size-10 rounded-2xl bg-purple-500/10 text-purple-500 flex items-center justify-center">
                                        <x-ui.icon name="map-pin" size="5" />
                                    </div>
                                    <span class="text-sm font-black uppercase tracking-widest text-foreground">{{ $address->label ?: 'Address' }}</span>
                                </div>

                                <p class="text-sm font-bold text-foreground mb-1">{{ $address->address_line_1 }}</p>
                                @if($address->address_line_2)
                                    <p class="text-xs text-muted-foreground mb-4">{{ $address->address_line_2 }}</p>
                                @endif

                                <div class="mt-4 pt-4 border-t border-border/40 space-y-2">
                                    <div class="flex justify-between">
                                        <span class="text-[10px] uppercase tracking-widest text-muted-foreground font-bold">City</span>
                                        <span class="text-xs font-bold text-foreground">{{ $address->city ?: '—' }}</span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span class="text-[10px] uppercase tracking-widest text-muted-foreground font-bold">State</span>
                                        <span class="text-xs font-bold text-foreground">{{ $address->state ?: '—' }}</span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span class="text-[10px] uppercase tracking-widest text-muted-foreground font-bold">Pincode</span>
                                        <span class="text-xs font-bold font-mono text-foreground">{{ $address->pincode ?: '—' }}</span>
                                    </div>
                                </div>

                                {{-- Floating Actions on Hover --}}
                                <div class="absolute bottom-4 right-4 flex items-center gap-2 opacity-0 translate-y-4 group-hover:opacity-100 group-hover:translate-y-0 transition-all duration-300">
                                    <button @click="openEditModal({{ $address->toJson() }})" class="size-9 rounded-xl bg-card border border-border/80 text-foreground hover:text-primary hover:border-primary flex items-center justify-center shadow-lg transition-colors">
                                        <x-ui.icon name="edit-3" size="4" />
                                    </button>
                                    <button @click="openDeleteModal({{ $address->toJson() }})" class="size-9 rounded-xl bg-card border border-border/80 text-destructive hover:bg-destructive hover:text-destructive-foreground hover:border-destructive flex items-center justify-center shadow-lg transition-colors">
                                        <x-ui.icon name="trash-2" size="4" />
                                    </button>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="text-center py-24 px-4 rounded-3xl border-2 border-dashed border-border/60 bg-muted/5">
                        <div class="size-20 rounded-3xl bg-muted flex items-center justify-center mx-auto mb-6">
                            <x-ui.icon name="map" size="8" class="text-muted-foreground/50" />
                        </div>
                        <h4 class="text-lg font-black text-foreground">No Addresses Found</h4>
                        <p class="text-sm text-muted-foreground mt-2 max-w-sm mx-auto">This customer does not have any registered addresses yet.</p>
                        <x-ui.button @click.prevent="openAddModal" class="mt-8 h-12 px-6 rounded-xl gap-2 shadow-xl shadow-primary/20 text-xs font-bold uppercase tracking-widest">
                            <x-ui.icon name="plus" size="4" /> Add First Address
                        </x-ui.button>
                    </div>
                @endif
            </div>

            {{-- ══ TAB: System ══ --}}
            <div x-show="activeTab === 'system'" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-4" x-transition:enter-end="opacity-100 translate-y-0" x-cloak>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <x-ui.card class="p-8 rounded-3xl border-border/40 shadow-xl bg-card/60 backdrop-blur-xl">
                        <h4 class="text-[11px] font-black uppercase tracking-[0.2em] text-muted-foreground mb-8 flex items-center gap-2">
                            <span class="size-2 rounded-full bg-orange-500 inline-block"></span> Timestamps
                        </h4>
                        <div class="space-y-6">
                            <div class="flex items-center gap-4">
                                <div class="size-12 rounded-xl bg-muted flex items-center justify-center shrink-0">
                                    <x-ui.icon name="calendar" size="5" class="text-muted-foreground" />
                                </div>
                                <div>
                                    <p class="text-[10px] font-black uppercase tracking-widest text-muted-foreground mb-1">Created On</p>
                                    <p class="text-sm font-bold text-foreground">{{ $customer->created_at->format('M d, Y h:i A') }}</p>
                                </div>
                            </div>
                            <div class="flex items-center gap-4">
                                <div class="size-12 rounded-xl bg-muted flex items-center justify-center shrink-0">
                                    <x-ui.icon name="refresh-cw" size="5" class="text-muted-foreground" />
                                </div>
                                <div>
                                    <p class="text-[10px] font-black uppercase tracking-widest text-muted-foreground mb-1">Last Updated</p>
                                    <p class="text-sm font-bold text-foreground">{{ $customer->updated_at->diffForHumans() }}</p>
                                </div>
                            </div>
                        </div>
                    </x-ui.card>

                    <div class="p-8 rounded-3xl border-2 border-destructive/20 bg-destructive/5 relative overflow-hidden">
                        <div class="absolute -right-10 -bottom-10 size-40 bg-destructive/10 rounded-full blur-2xl"></div>
                        <h4 class="text-[11px] font-black uppercase tracking-[0.2em] text-destructive mb-6 flex items-center gap-2 relative z-10">
                            <span class="size-2 rounded-full bg-destructive inline-block shadow-lg shadow-destructive/50 animate-pulse"></span> Danger Zone
                        </h4>
                        <p class="text-sm font-bold text-foreground relative z-10">Archive Customer Record</p>
                        <p class="text-xs text-muted-foreground mt-2 mb-8 relative z-10">This action will hide the customer from main views but can be restored by an admin.</p>
                        
                        <form action="{{ route('customers.destroy', $customer) }}" method="POST" onsubmit="return confirm('Archive this customer?')" class="relative z-10">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="h-12 px-6 rounded-xl bg-destructive/10 text-destructive text-xs font-black uppercase tracking-widest hover:bg-destructive hover:text-destructive-foreground transition-all duration-300 border border-destructive/20 hover:border-destructive hover:shadow-xl hover:shadow-destructive/30 flex items-center gap-2">
                                <x-ui.icon name="archive" size="4" /> Archive Record
                            </button>
                        </form>
                    </div>
                </div>
            </div>

        </div>{{-- end content --}}

        {{-- ══ CART SLIDE-OVER ══ (inside x-data scope — required!) --}}
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

            {{-- Cart Items (scrollable) --}}
            <div class="flex-1 overflow-y-auto p-4 space-y-3 bg-background/30" style="scrollbar-width:thin;">

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
                            <div class="flex items-center gap-1.5 flex-1 min-w-0">
                                <span class="text-[10px] font-black text-muted-foreground uppercase tracking-widest shrink-0">Disc</span>
                                <select x-model="item.discountType"
                                    class="h-8 w-16 px-1.5 rounded-lg border border-border bg-background text-[10px] font-bold outline-none focus:ring-2 focus:ring-primary/20 shrink-0">
                                    <option value="percent">%</option>
                                    <option value="flat">₹</option>
                                </select>
                                <input type="number" x-model="item.discountValue" min="0"
                                    :max="item.discountType === 'percent' ? 100 : item.price * item.quantity"
                                    placeholder="0"
                                    class="h-8 flex-1 min-w-0 px-2 rounded-lg border border-border bg-background text-xs font-bold outline-none focus:ring-2 focus:ring-primary/20 text-right">
                            </div>
                        </div>
                    </div>
                </template>
            </div>

            {{-- Footer: Discounts + Summary + CTA --}}
            <div class="shrink-0 border-t border-border/50 bg-card" x-show="cart.length > 0" x-cloak>

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

                {{-- Place Order Button --}}
                <div class="px-5 pb-6">
                    <form action="{{ route('customers.orders.place', $customer) }}" method="POST">
                        @csrf
                        <input type="hidden" name="cart" :value="JSON.stringify(cart)">
                        <input type="hidden" name="order_discount_type" :value="orderDiscountType">
                        <input type="hidden" name="order_discount_value" :value="orderDiscountValue">
                        <input type="hidden" name="order_discount_amount" :value="orderDiscountAmount">
                        <input type="hidden" name="coupon_code" :value="couponApplied ? couponCode : ''">
                        <input type="hidden" name="coupon_discount" :value="couponDiscount">
                        <input type="hidden" name="tax_amount" :value="taxAmount">
                        <input type="hidden" name="subtotal" :value="subtotal">
                        <input type="hidden" name="grand_total" :value="grandTotal">
                        <button type="submit" x-bind:disabled="cart.length === 0"
                            class="w-full h-14 rounded-2xl bg-gradient-to-r from-primary to-primary/90 text-primary-foreground text-sm font-black uppercase tracking-widest shadow-xl shadow-primary/30 hover:shadow-primary/50 hover:-translate-y-0.5 transition-all duration-300 flex items-center justify-center gap-2 disabled:opacity-50 disabled:pointer-events-none">
                            Place Sales Order
                            <x-ui.icon name="arrow-right" size="4" />
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>{{-- end main div --}}

{{-- ══════════════════════════════
             Address Modal (unchanged logic)
        ══════════════════════════════ --}}
        <x-ui.modal id="address-modal" maxWidth="2xl">
            <div class="p-8">
                <div class="flex items-center justify-between mb-6">
                    <div class="flex items-center gap-3">
                        <div class="size-10 rounded-2xl bg-primary/10 border border-primary/20 text-primary flex items-center justify-center shadow-inner">
                            <x-ui.icon name="map-pin" size="5" />
                        </div>
                        <div>
                            <h3 class="text-sm font-black text-foreground uppercase tracking-widest" x-text="editingAddress ? 'Edit Address' : 'Add New Address'"></h3>
                            <p class="text-[10px] text-muted-foreground font-bold tracking-tight">Manage customer registered addresses</p>
                        </div>
                    </div>
                    <button type="button" @click="$dispatch('close-modal', { name: 'address-modal' })" class="size-8 rounded-lg hover:bg-muted flex items-center justify-center transition-colors">
                        <x-ui.icon name="x" size="4" />
                    </button>
                </div>

                <form :action="editingAddress ? `/customers/{{ $customer->id }}/addresses/${editingAddress.id}` : `/customers/{{ $customer->id }}/addresses`" method="POST" class="space-y-5">
                    @csrf
                    <template x-if="editingAddress">
                        @method('PUT')
                    </template>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                        <div class="space-y-2">
                            <label for="label" class="text-[10px] font-black uppercase tracking-widest text-muted-foreground/80 ml-1">Address Label</label>
                            <input type="text" name="label" id="label" :value="editingAddress ? editingAddress.label : ''" placeholder="e.g. Home, Office" required 
                                class="w-full h-11 px-4 rounded-xl border border-border bg-background/50 focus:bg-background focus:ring-2 focus:ring-primary/20 transition-all text-sm font-medium outline-none">
                        </div>

                        <div class="space-y-2">
                            <label for="status" class="text-[10px] font-black uppercase tracking-widest text-muted-foreground/80 ml-1">Status</label>
                            <select name="status" id="status" class="w-full h-11 px-4 rounded-xl border border-border bg-background/50 text-[10px] font-black uppercase tracking-widest focus:bg-background focus:ring-2 focus:ring-primary/20 outline-none transition-all">
                                <option value="active" :selected="editingAddress && editingAddress.status == 'active'">Active</option>
                                <option value="inactive" :selected="editingAddress && editingAddress.status == 'inactive'">Inactive</option>
                            </select>
                        </div>
                    </div>

                    <div class="space-y-2 relative">
                        <label for="villageSearch" class="text-[10px] font-black uppercase tracking-widest text-muted-foreground/80 ml-1">Search Village / Area (Optional)</label>
                        <div class="relative">
                            <x-ui.icon name="search" size="4" class="absolute left-3 top-1/2 -translate-y-1/2 text-muted-foreground" />
                            <input type="text" id="villageSearch" x-model="villageSearch" @input.debounce.500ms="searchVillages" placeholder="Search by village name or pincode..." autocomplete="off"
                                class="w-full h-11 pl-9 pr-4 rounded-xl border border-border bg-background/50 focus:bg-background focus:ring-2 focus:ring-primary/20 transition-all text-sm font-medium outline-none">
                            <div x-show="searchingVillages" x-cloak class="absolute right-3 top-1/2 -translate-y-1/2">
                                <x-ui.icon name="refresh-cw" size="4" class="animate-spin text-primary" />
                            </div>
                        </div>
                        
                        <div x-show="villages.length > 0" x-cloak @click.away="villages = []" class="absolute z-50 w-full mt-1 bg-card border border-border rounded-xl shadow-lg shadow-primary/5 max-h-60 overflow-y-auto backdrop-blur-xl">
                            <template x-for="village in villages" :key="village.id">
                                <div @click="selectVillage(village)" class="p-3 border-b border-border/40 hover:bg-primary/5 cursor-pointer transition-colors last:border-0 group">
                                    <p class="text-sm font-bold text-foreground group-hover:text-primary transition-colors" x-text="village.name"></p>
                                    <p class="text-[10px] text-muted-foreground uppercase tracking-widest mt-0.5">
                                        <span x-text="village.taluka"></span>, <span x-text="village.district"></span> - <span x-text="village.pincode"></span>
                                        <template x-if="village.post_office">
                                            <span> | PO: <span x-text="village.post_office"></span></span>
                                        </template>
                                    </p>
                                </div>
                            </template>
                        </div>
                    </div>
                    
                    <input type="hidden" name="village_id" id="village_id" :value="editingAddress ? editingAddress.village_id : ''">

                    <div class="space-y-2">
                        <label for="address_line_1" class="text-[10px] font-black uppercase tracking-widest text-muted-foreground/80 ml-1">Address Line 1</label>
                        <input type="text" name="address_line_1" id="address_line_1" :value="editingAddress ? editingAddress.address_line_1 : ''" required 
                            class="w-full h-11 px-4 rounded-xl border border-border bg-background/50 focus:bg-background focus:ring-2 focus:ring-primary/20 transition-all text-sm font-medium outline-none">
                    </div>

                    <div class="space-y-2">
                        <label for="address_line_2" class="text-[10px] font-black uppercase tracking-widest text-muted-foreground/80 ml-1">Address Line 2 (Optional)</label>
                        <input type="text" name="address_line_2" id="address_line_2" :value="editingAddress ? editingAddress.address_line_2 : ''" 
                            class="w-full h-11 px-4 rounded-xl border border-border bg-background/50 focus:bg-background focus:ring-2 focus:ring-primary/20 transition-all text-sm font-medium outline-none">
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                        <div class="space-y-2">
                            <label for="village_name" class="text-[10px] font-black uppercase tracking-widest text-muted-foreground/80 ml-1">Village</label>
                            <input type="text" name="village_name" id="village_name" :value="editingAddress ? editingAddress.village_name : ''" 
                                class="w-full h-11 px-4 rounded-xl border border-border bg-background/50 focus:bg-background focus:ring-2 focus:ring-primary/20 transition-all text-sm font-medium outline-none">
                        </div>
                        <div class="space-y-2">
                            <label for="post_office" class="text-[10px] font-black uppercase tracking-widest text-muted-foreground/80 ml-1">Post Office</label>
                            <input type="text" name="post_office" id="post_office" :value="editingAddress ? editingAddress.post_office : ''" 
                                class="w-full h-11 px-4 rounded-xl border border-border bg-background/50 focus:bg-background focus:ring-2 focus:ring-primary/20 transition-all text-sm font-medium outline-none">
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-4 gap-5">
                        <div class="space-y-2">
                            <label for="taluka" class="text-[10px] font-black uppercase tracking-widest text-muted-foreground/80 ml-1">Taluka</label>
                            <input type="text" name="taluka" id="taluka" :value="editingAddress ? editingAddress.taluka : ''" 
                                class="w-full h-11 px-4 rounded-xl border border-border bg-background/50 focus:bg-background focus:ring-2 focus:ring-primary/20 transition-all text-sm font-medium outline-none">
                        </div>
                        <div class="space-y-2">
                            <label for="city" class="text-[10px] font-black uppercase tracking-widest text-muted-foreground/80 ml-1">District</label>
                            <input type="text" name="city" id="city" :value="editingAddress ? editingAddress.city : ''" required 
                                class="w-full h-11 px-4 rounded-xl border border-border bg-background/50 focus:bg-background focus:ring-2 focus:ring-primary/20 transition-all text-sm font-medium outline-none">
                        </div>
                        <div class="space-y-2">
                            <label for="state" class="text-[10px] font-black uppercase tracking-widest text-muted-foreground/80 ml-1">State</label>
                            <input type="text" name="state" id="state" :value="editingAddress ? editingAddress.state : ''" required 
                                class="w-full h-11 px-4 rounded-xl border border-border bg-background/50 focus:bg-background focus:ring-2 focus:ring-primary/20 transition-all text-sm font-medium outline-none">
                        </div>
                        <div class="space-y-2">
                            <label for="pincode" class="text-[10px] font-black uppercase tracking-widest text-muted-foreground/80 ml-1">Pincode</label>
                            <input type="text" name="pincode" id="pincode" :value="editingAddress ? editingAddress.pincode : ''" required 
                                class="w-full h-11 px-4 rounded-xl border border-border bg-background/50 focus:bg-background focus:ring-2 focus:ring-primary/20 transition-all text-sm font-medium outline-none">
                        </div>
                    </div>

                    <div class="flex items-center gap-2 pt-2 ml-1">
                        <input type="checkbox" name="is_default" id="is_default" value="1" :checked="editingAddress && editingAddress.is_default"
                            class="rounded border-border text-primary focus:ring-primary/20">
                        <label for="is_default" class="text-xs font-medium text-foreground cursor-pointer">Set as default address</label>
                    </div>

                    <div class="flex items-center justify-end gap-3 pt-4 border-t border-border/40">
                        <x-ui.button type="button" variant="outline" @click="$dispatch('close-modal', { name: 'address-modal' })" class="rounded-xl font-black uppercase tracking-widest text-[10px]">
                            Cancel
                        </x-ui.button>
                        <x-ui.button type="submit" class="rounded-xl font-black uppercase tracking-widest text-[10px] shadow-lg shadow-primary/20">
                            Save Address
                        </x-ui.button>
                    </div>
                </form>
            </div>
        </x-ui.modal>

        {{-- Delete Address Modal --}}
        <x-ui.modal id="delete-address-modal" maxWidth="sm">
            <div class="p-8 text-center">
                <div class="size-16 rounded-full bg-destructive/10 text-destructive flex items-center justify-center mx-auto mb-4">
                    <x-ui.icon name="alert-triangle" size="8" />
                </div>
                <h3 class="text-lg font-black text-foreground mb-2">Delete Address?</h3>
                <p class="text-sm text-muted-foreground mb-6">Are you sure you want to delete <span class="font-bold text-foreground" x-text="deletingAddress?.label || 'this address'"></span>? This action cannot be undone.</p>
                
                <form :action="deletingAddress ? `/customers/{{ $customer->id }}/addresses/${deletingAddress.id}` : '#'" method="POST">
                    @csrf
                    @method('DELETE')
                    <div class="flex flex-col sm:flex-row items-center justify-center gap-3">
                        <x-ui.button type="button" variant="outline" @click="$dispatch('close-modal', { name: 'delete-address-modal' })" class="w-full sm:w-auto rounded-xl font-black uppercase tracking-widest text-[10px]">
                            Cancel
                        </x-ui.button>
                        <x-ui.button type="submit" variant="destructive" class="w-full sm:w-auto rounded-xl font-black uppercase tracking-widest text-[10px] shadow-lg shadow-destructive/20 bg-destructive text-destructive-foreground hover:bg-destructive/90">
                            Delete Address
                        </x-ui.button>
                    </div>
                </form>
            </div>
        </x-ui.modal>
        
        <x-ui.toaster />

    </div>
</x-layouts.app>