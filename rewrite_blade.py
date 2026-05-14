import re

with open('resources/views/customers/show.blade.php', 'r') as f:
    content = f.read()

# Extract modals part
modals_start_str = "{{-- ══════════════════════════════"
modals_index = content.find(modals_start_str)
modals_content = content[modals_index:]

# Wait, the closing </div> and </x-layouts.app> are inside the original file.
# Let's just manually include the closing tags.

new_blade = """<x-layouts.app pageTitle="Customer Profile: {{ $customer->name }}">

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
        cart: [],
        init() {
            this.searchProducts();
        },
        async searchProducts() {
            this.searchingProducts = true;
            try {
                const query = this.productSearchQuery.length >= 2 ? `?q=${this.productSearchQuery}` : '';
                if (this.productSearchQuery.length > 0 && this.productSearchQuery.length < 2) {
                    this.productSearchResults = [];
                    this.searchingProducts = false;
                    return;
                }
                const res = await fetch(`/products-search-api${query}`, {
                    headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
                });
                if (!res.ok) throw new Error('Network response was not ok');
                const data = await res.json();
                this.productSearchResults = data.data || [];
            } catch (e) {
                console.error('Search failed:', e);
            } finally {
                this.searchingProducts = false;
            }
        },
        addToCart(product) {
            const existing = this.cart.find(i => i.id === product.id);
            if (existing) {
                if (existing.quantity < product.available_stock || product.available_stock === 999) {
                    existing.quantity++;
                }
            } else {
                this.cart.push({
                    id: product.id,
                    name: product.name,
                    sku: product.sku,
                    price: product.selling_price,
                    image_url: product.image_url,
                    quantity: 1,
                    available: product.available_stock
                });
            }
        },
        updateCartQty(index, delta) {
            const item = this.cart[index];
            if (!item) return;
            const newQty = item.quantity + delta;
            if (newQty <= 0) {
                this.cart.splice(index, 1);
            } else if (newQty <= item.available || item.available === 999) {
                item.quantity = newQty;
            }
        },
        get cartTotal() {
            return this.cart.reduce((total, item) => total + (item.price * item.quantity), 0);
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
                        <a href="{{ route('customers.index') }}" class="group relative flex items-center justify-center h-10 px-4 rounded-2xl bg-card border border-border/80 text-sm font-bold text-foreground shadow-sm hover:shadow-md hover:border-border transition-all duration-300 hover:-translate-y-0.5">
                            <x-ui.icon name="arrow-left" size="4" class="mr-2 opacity-70 group-hover:opacity-100 transition-opacity" /> Back
                        </a>
                        <a href="{{ route('customers.edit', $customer) }}" class="group relative flex items-center justify-center h-10 px-5 rounded-2xl bg-gradient-to-r from-primary to-primary/90 text-primary-foreground text-sm font-bold shadow-lg shadow-primary/25 hover:shadow-primary/40 transition-all duration-300 hover:-translate-y-0.5">
                            <x-ui.icon name="edit-3" size="4" class="mr-2 opacity-90" /> Edit Profile
                        </a>
                    </div>
                </div>

                {{-- Horizontal Pill Navigation --}}
                <div class="flex items-center gap-1 overflow-x-auto custom-scrollbar pb-2">
                    <template x-for="tab in [
                        { id: 'overview', icon: 'user', label: 'Overview' },
                        { id: 'order', icon: 'shopping-bag', label: 'Order Products' },
                        { id: 'finance', icon: 'hash', label: 'Finance' },
                        { id: 'addresses', icon: 'map', label: 'Addresses' },
                        { id: 'system', icon: 'clock', label: 'System' }
                    ]" :key="tab.id">
                        <button 
                            @click="activeTab = tab.id"
                            :class="activeTab === tab.id 
                                ? 'bg-primary text-primary-foreground shadow-md shadow-primary/20 scale-100' 
                                : 'bg-transparent text-muted-foreground hover:bg-muted/50 hover:text-foreground scale-95 hover:scale-100'"
                            class="flex items-center gap-2.5 px-5 py-2.5 rounded-2xl font-black text-xs uppercase tracking-widest transition-all duration-300 whitespace-nowrap"
                        >
                            <x-ui.icon x-bind:name="tab.icon" size="4" :class="activeTab === tab.id ? 'opacity-100' : 'opacity-60'" />
                            <span x-text="tab.label"></span>
                        </button>
                    </template>
                </div>
            </div>
        </div>

        {{-- ── Main Layout: Content Area ── --}}
        <div class="max-w-7xl mx-auto px-6 lg:px-10">

            {{-- ══ TAB: Order Products (Redesigned with Data Table) ══ --}}
            <div x-show="activeTab === 'order'" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-4" x-transition:enter-end="opacity-100 translate-y-0" x-cloak>
                <div class="flex flex-col xl:flex-row gap-8">
                    
                    {{-- Product Data Table Area --}}
                    <div class="flex-1 min-w-0">
                        <div class="bg-card/40 backdrop-blur-3xl border border-border/50 rounded-3xl shadow-2xl overflow-hidden flex flex-col h-[700px]">
                            
                            {{-- Header & Search --}}
                            <div class="p-6 border-b border-border/40 bg-gradient-to-b from-muted/10 to-transparent">
                                <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
                                    <div>
                                        <h3 class="text-xl font-black tracking-tight text-foreground flex items-center gap-3">
                                            <span class="size-10 rounded-2xl bg-primary/10 text-primary flex items-center justify-center">
                                                <x-ui.icon name="package" size="5" />
                                            </span>
                                            Available Products
                                        </h3>
                                        <p class="text-xs text-muted-foreground font-medium mt-1 ml-13">Browse or search products to add to cart.</p>
                                    </div>

                                    <div class="relative w-full sm:w-80 group">
                                        <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                                            <x-ui.icon name="search" size="4" class="text-muted-foreground group-focus-within:text-primary transition-colors" />
                                        </div>
                                        <input type="text" x-model="productSearchQuery" @input.debounce.300ms="searchProducts" placeholder="Search by name, SKU..."
                                            class="w-full h-12 pl-11 pr-10 rounded-2xl border border-border/60 bg-background/50 backdrop-blur-xl focus:bg-background focus:ring-4 focus:ring-primary/10 focus:border-primary/50 transition-all text-sm font-semibold outline-none shadow-sm placeholder:font-medium">
                                        <div x-show="searchingProducts" x-cloak class="absolute inset-y-0 right-0 pr-4 flex items-center">
                                            <x-ui.icon name="refresh-cw" size="4" class="animate-spin text-primary" />
                                        </div>
                                    </div>
                                </div>
                            </div>

                            {{-- Data Table --}}
                            <div class="flex-1 overflow-auto custom-scrollbar">
                                <table class="w-full text-left border-collapse">
                                    <thead>
                                        <tr class="bg-muted/30 backdrop-blur-md sticky top-0 z-10 border-b border-border/40">
                                            <th class="px-6 py-4 text-[10px] font-black uppercase tracking-widest text-muted-foreground">Product</th>
                                            <th class="px-6 py-4 text-[10px] font-black uppercase tracking-widest text-muted-foreground">SKU</th>
                                            <th class="px-6 py-4 text-[10px] font-black uppercase tracking-widest text-muted-foreground">Price</th>
                                            <th class="px-6 py-4 text-[10px] font-black uppercase tracking-widest text-muted-foreground">Stock</th>
                                            <th class="px-6 py-4 text-[10px] font-black uppercase tracking-widest text-muted-foreground text-right">Action</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-border/30">
                                        <template x-if="productSearchResults.length === 0 && !searchingProducts">
                                            <tr>
                                                <td colspan="5" class="py-16 text-center">
                                                    <div class="size-16 rounded-full bg-muted/50 flex items-center justify-center mx-auto mb-4">
                                                        <x-ui.icon name="package-x" size="8" class="text-muted-foreground/50" />
                                                    </div>
                                                    <p class="text-sm font-bold text-foreground">No products found</p>
                                                    <p class="text-xs text-muted-foreground mt-1">Try adjusting your search criteria</p>
                                                </td>
                                            </tr>
                                        </template>

                                        <template x-for="product in productSearchResults" :key="product.id">
                                            <tr class="group hover:bg-primary/5 transition-colors duration-200">
                                                <td class="px-6 py-4">
                                                    <div class="flex items-center gap-4">
                                                        <div class="size-12 rounded-xl bg-card border border-border/60 shadow-sm overflow-hidden shrink-0 flex items-center justify-center">
                                                            <template x-if="product.image_url">
                                                                <img :src="product.image_url" class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-110">
                                                            </template>
                                                            <template x-if="!product.image_url">
                                                                <x-ui.icon name="image" size="4" class="text-muted-foreground/30" />
                                                            </template>
                                                        </div>
                                                        <div>
                                                            <p class="text-sm font-bold text-foreground group-hover:text-primary transition-colors line-clamp-1" x-text="product.name"></p>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td class="px-6 py-4">
                                                    <span class="text-[11px] font-mono text-muted-foreground font-semibold bg-muted/50 px-2 py-1 rounded-md" x-text="product.sku"></span>
                                                </td>
                                                <td class="px-6 py-4">
                                                    <span class="text-sm font-black text-foreground" x-text="'₹' + Number(product.selling_price).toFixed(2)"></span>
                                                </td>
                                                <td class="px-6 py-4">
                                                    <span class="text-[10px] font-black uppercase tracking-widest px-2.5 py-1 rounded-lg shadow-sm"
                                                          :class="product.available_stock > 0 ? 'bg-emerald-500/10 text-emerald-500 border border-emerald-500/20' : 'bg-red-500/10 text-red-500 border border-red-500/20'"
                                                          x-text="product.available_stock > 0 ? (product.available_stock === 999 ? 'In Stock' : product.available_stock + ' in stock') : 'Out of Stock'">
                                                    </span>
                                                </td>
                                                <td class="px-6 py-4 text-right">
                                                    <x-ui.button @click.prevent="addToCart(product)" size="sm" x-bind:disabled="product.available_stock <= 0" 
                                                        class="h-9 px-4 rounded-xl text-xs font-bold gap-2 shadow-lg shadow-primary/20 hover:shadow-primary/40 hover:-translate-y-0.5 transition-all">
                                                        <x-ui.icon name="plus" size="3" /> Add
                                                    </x-ui.button>
                                                </td>
                                            </tr>
                                        </template>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    {{-- Elegant Cart Sidebar --}}
                    <div class="w-full xl:w-[400px] shrink-0">
                        <div class="bg-card border border-border/50 rounded-3xl shadow-2xl overflow-hidden flex flex-col h-[700px] relative">
                            {{-- Cart Header --}}
                            <div class="p-6 border-b border-border/40 bg-gradient-to-r from-emerald-500/10 to-transparent">
                                <h3 class="text-lg font-black tracking-tight text-foreground flex items-center gap-3">
                                    <x-ui.icon name="shopping-cart" size="5" class="text-emerald-500" />
                                    Shopping Cart
                                </h3>
                                <p class="text-[11px] text-muted-foreground font-bold mt-1 uppercase tracking-widest"><span x-text="cart.length"></span> items selected</p>
                            </div>

                            {{-- Cart Items --}}
                            <div class="flex-1 overflow-y-auto custom-scrollbar p-6 space-y-4 bg-muted/10">
                                <template x-if="cart.length === 0">
                                    <div class="text-center py-20 opacity-40">
                                        <x-ui.icon name="shopping-bag" size="12" class="mx-auto mb-4" />
                                        <p class="text-sm font-black uppercase tracking-widest">Cart is empty</p>
                                        <p class="text-[10px] mt-2 font-bold">Add products to begin.</p>
                                    </div>
                                </template>

                                <template x-for="(item, index) in cart" :key="item.id">
                                    <div class="p-4 rounded-2xl border border-border/60 bg-card shadow-sm hover:shadow-md transition-all group">
                                        <div class="flex gap-4">
                                            <div class="size-14 rounded-xl bg-muted border border-border/40 shrink-0 overflow-hidden flex items-center justify-center">
                                                <template x-if="item.image_url">
                                                    <img :src="item.image_url" class="w-full h-full object-cover">
                                                </template>
                                                <template x-if="!item.image_url">
                                                    <x-ui.icon name="package" size="4" class="text-muted-foreground/30" />
                                                </template>
                                            </div>
                                            <div class="flex-1 min-w-0">
                                                <p class="text-sm font-bold text-foreground truncate mb-1" x-text="item.name"></p>
                                                <div class="flex items-center justify-between mb-3">
                                                    <p class="text-[10px] font-black text-muted-foreground uppercase tracking-wider" x-text="'₹' + Number(item.price).toFixed(2) + ' each'"></p>
                                                    <p class="text-sm font-black text-emerald-500" x-text="'₹' + Number(item.price * item.quantity).toFixed(2)"></p>
                                                </div>
                                                <div class="flex items-center gap-2">
                                                    <div class="flex items-center gap-1 bg-muted/50 border border-border/50 rounded-xl p-1">
                                                        <button @click.prevent="updateCartQty(index, -1)" class="size-7 flex items-center justify-center rounded-lg hover:bg-background hover:shadow-sm text-foreground transition-all">
                                                            <x-ui.icon name="minus" size="3" />
                                                        </button>
                                                        <span class="w-8 text-center text-xs font-black" x-text="item.quantity"></span>
                                                        <button @click.prevent="updateCartQty(index, 1)" class="size-7 flex items-center justify-center rounded-lg hover:bg-background hover:shadow-sm text-foreground transition-all">
                                                            <x-ui.icon name="plus" size="3" />
                                                        </button>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </template>
                            </div>

                            {{-- Cart Footer --}}
                            <div class="p-6 bg-card border-t border-border/50">
                                <div class="flex items-end justify-between mb-6">
                                    <div>
                                        <span class="text-[10px] font-black uppercase tracking-widest text-muted-foreground block mb-1">Total Amount</span>
                                        <span class="text-3xl font-black text-foreground bg-clip-text text-transparent bg-gradient-to-r from-foreground to-foreground/70" x-text="'₹' + Number(cartTotal).toFixed(2)"></span>
                                    </div>
                                </div>

                                <form action="{{ route('customers.orders.place', $customer) }}" method="POST">
                                    @csrf
                                    <input type="hidden" name="cart" :value="JSON.stringify(cart)">
                                    <x-ui.button type="submit" class="w-full h-14 rounded-2xl text-sm font-black uppercase tracking-widest shadow-xl shadow-primary/30 hover:shadow-primary/50 hover:-translate-y-1 transition-all duration-300 relative overflow-hidden group" x-bind:disabled="cart.length === 0">
                                        <div class="absolute inset-0 bg-white/20 translate-y-full group-hover:translate-y-0 transition-transform duration-300 ease-out"></div>
                                        <span class="relative z-10 flex items-center justify-center gap-2">
                                            Place Sales Order <x-ui.icon name="arrow-right" size="4" />
                                        </span>
                                    </x-ui.button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- ══ TAB: Overview ══ --}}
            <div x-show="activeTab === 'overview'" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-4" x-transition:enter-end="opacity-100 translate-y-0" x-cloak>
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                    
                    {{-- Contact Cards --}}
                    <div class="lg:col-span-2 grid grid-cols-1 sm:grid-cols-2 gap-6">
                        {{-- Email --}}
                        <div class="group p-6 rounded-3xl bg-card border border-border/50 shadow-sm hover:shadow-xl hover:border-primary/30 transition-all duration-300 relative overflow-hidden">
                            <div class="absolute -right-6 -top-6 size-24 bg-primary/5 rounded-full pointer-events-none group-hover:scale-150 transition-transform duration-700"></div>
                            <div class="size-12 rounded-2xl bg-primary/10 text-primary flex items-center justify-center mb-4">
                                <x-ui.icon name="mail" size="5" />
                            </div>
                            <p class="text-[10px] font-black uppercase tracking-[0.2em] text-muted-foreground mb-1">Email Address</p>
                            <p class="text-base font-bold text-foreground truncate">{{ $customer->email ?: 'Not Provided' }}</p>
                        </div>
                        {{-- Phone --}}
                        <div class="group p-6 rounded-3xl bg-card border border-border/50 shadow-sm hover:shadow-xl hover:primary/30 transition-all duration-300 relative overflow-hidden">
                            <div class="absolute -right-6 -top-6 size-24 bg-primary/5 rounded-full pointer-events-none group-hover:scale-150 transition-transform duration-700"></div>
                            <div class="size-12 rounded-2xl bg-primary/10 text-primary flex items-center justify-center mb-4">
                                <x-ui.icon name="phone" size="5" />
                            </div>
                            <p class="text-[10px] font-black uppercase tracking-[0.2em] text-muted-foreground mb-1">Phone Number</p>
                            <p class="text-base font-bold text-foreground">{{ $customer->phone ?: 'Not Provided' }}</p>
                        </div>
                    </div>

                    {{-- Quick Stats --}}
                    <div class="bg-gradient-to-br from-card to-muted/20 rounded-3xl border border-border/50 p-6 flex flex-col justify-center">
                        <div class="flex items-center gap-4 mb-6">
                            <div class="size-12 rounded-full bg-emerald-500/10 flex items-center justify-center">
                                <x-ui.icon name="shopping-cart" size="5" class="text-emerald-500" />
                            </div>
                            <div>
                                <p class="text-[10px] font-black uppercase tracking-widest text-muted-foreground">Total Orders</p>
                                <p class="text-2xl font-black text-foreground">{{ $customer->orders()->count() ?? 0 }}</p>
                            </div>
                        </div>
                        <div class="flex items-center gap-4">
                            <div class="size-12 rounded-full bg-purple-500/10 flex items-center justify-center">
                                <x-ui.icon name="map-pin" size="5" class="text-purple-500" />
                            </div>
                            <div>
                                <p class="text-[10px] font-black uppercase tracking-widest text-muted-foreground">Addresses</p>
                                <p class="text-2xl font-black text-foreground">{{ $customer->addresses->count() }}</p>
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
    </div>{{-- end main div --}}

    {{-- Modals placeholder --}}
"""

new_blade += modals_content

with open('resources/views/customers/show.blade.php', 'w') as f:
    f.write(new_blade)
