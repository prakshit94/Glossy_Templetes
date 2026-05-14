{ 
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
    showSummary: false,
    selectedWarehouseId: '{{ $warehouses->first()?->id ?? '' }}',
    selectedBillingAddressId: '{{ $customer->addresses->where('is_default', true)->first()?->id ?? $customer->addresses->first()?->id ?? '' }}',
    selectedShippingAddressId: '{{ $customer->addresses->where('is_default', true)->first()?->id ?? $customer->addresses->first()?->id ?? '' }}',
    
    init() {
        // Clear cart if success message is present
        @if(session('success'))
            localStorage.removeItem('customer_cart_{{ $customer->id }}');
        @endif

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
        this.editingAddress = { ...address };
        this.resetVillageSearch();
        if (address && address.village) {
            this.editingAddress.village_name = address.village.village_name;
            this.editingAddress.post_office = address.village.post_so_name;
            this.editingAddress.taluka = address.village.taluka_name;
            this.editingAddress.district = address.village.district_name;
            this.editingAddress.state = address.village.state_name;
            this.editingAddress.pincode = address.village.pincode;
            this.villageSearch = address.village.village_name;
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
            console.error('Village search failed');
        } finally {
            this.searchingVillages = false;
        }
    },
    selectVillage(v) {
        if (!this.editingAddress) {
            this.editingAddress = {
                label: 'Other',
                address_line_1: '',
                address_line_2: '',
                village_id: v.id,
                village_name: v.name,
                post_office: v.post_office || '',
                taluka: v.taluka || '',
                district: v.district || '',
                state: v.state || '',
                pincode: v.pincode || '',
                city: v.district || '',
                status: 'active',
                is_default: false
            };
        } else {
            this.editingAddress.village_id = v.id;
            this.editingAddress.village_name = v.name;
            this.editingAddress.post_office = v.post_office || '';
            this.editingAddress.taluka = v.taluka || '';
            this.editingAddress.district = v.district || '';
            this.editingAddress.state = v.state || '';
            this.editingAddress.pincode = v.pincode || '';
            this.editingAddress.city = v.district || '';
        }
        this.villages = [];
        this.villageSearch = v.name;
    }
}
