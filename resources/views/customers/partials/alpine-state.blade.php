{ 
    activeTab: @if(session('active_tab')) '{{ session('active_tab') }}' @else localStorage.getItem('customer_active_tab_{{ $customer->id }}') || 'overview' @endif,
    editingOrderId: null,
    editingOrderDetails: null,
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
    sameAsBilling: localStorage.getItem('customer_same_as_billing_{{ $customer->id }}') === 'false' ? false : true,
    
    editOrder(order) {
        this.editingOrderId = order.id;
        this.editingOrderDetails = order;
        
        this.cart = (order.items || []).map(item => {
            return {
                id: item.product_id,
                name: item.product?.name || 'Unknown Product',
                sku: item.product?.sku || '',
                price: parseFloat(item.unit_price) || 0,
                image_url: item.product?.image_url || '',
                quantity: parseInt(item.quantity) || 1,
                available: 999, 
                discountType: 'amount',
                discountValue: parseFloat(item.discount_amount) || 0
            };
        });
        
        if (order.billing_address_id) this.selectedBillingAddressId = order.billing_address_id;
        if (order.shipping_address_id) {
            this.selectedShippingAddressId = order.shipping_address_id;
            this.sameAsBilling = (order.billing_address_id == order.shipping_address_id);
        } else {
            this.sameAsBilling = true;
        }
        
        if (order.warehouse_id) this.selectedWarehouseId = order.warehouse_id;
        
        this.activeTab = 'order';
        this.notify('info', 'Order loaded for editing. You can now modify the cart.');
        window.scrollTo({ top: 0, behavior: 'smooth' });
    },
    
    cancelEditOrder() {
        this.editingOrderId = null;
        this.editingOrderDetails = null;
        this.cart = [];
        this.activeTab = 'history';
        this.notify('info', 'Edit mode cancelled.');
    },

    init() {
        @if(request()->has('edit_order'))
            // Auto-load order editing from external routes (like Global Orders page)
            setTimeout(() => {
                const autoEditId = {{ request()->query('edit_order') }};
                const orders = window['customerOrders_{{ $customer->id }}'] || [];
                const orderToEdit = orders.find(o => o.id === autoEditId);
                if (orderToEdit) {
                    this.editOrder(orderToEdit);
                } else {
                    this.notify('error', 'Order not found or access denied.');
                }
            }, 300); // Wait for the DOM and window.customerOrders script to fully load
        @endif

        window.addEventListener('edit-order', (e) => {
            const orderId = e.detail;
            const orders = window['customerOrders_{{ $customer->id }}'] || [];
            const order = orders.find(o => o.id === orderId);
            if (order) this.editOrder(order);
        });

        // Watch for billing address changes to sync shipping if 'sameAsBilling' is active
        this.$watch('selectedBillingAddressId', (val) => {
            if (this.sameAsBilling) this.selectedShippingAddressId = val;
        });
        
        // Watch for sameAsBilling toggle
        this.$watch('sameAsBilling', (val) => {
            localStorage.setItem('customer_same_as_billing_{{ $customer->id }}', val);
            if (val) this.selectedShippingAddressId = this.selectedBillingAddressId;
        });

        // Clear cart ONLY if an order was successfully placed or updated
        @if(session('success') && (str_contains(session('success'), 'Order') || str_contains(session('success'), 'order')))
            localStorage.removeItem('customer_cart_{{ $customer->id }}');
            localStorage.removeItem('customer_active_tab_{{ $customer->id }}');
            localStorage.removeItem('customer_same_as_billing_{{ $customer->id }}');
            this.editingOrderId = null;
            this.editingOrderDetails = null;
            this.cart = [];
            // Force a slight delay to ensure watchers don't override this with stale data
            setTimeout(() => {
                localStorage.removeItem('customer_cart_{{ $customer->id }}');
            }, 100);
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
        
        
        // Watch activeTab and save to localStorage
        this.$watch('activeTab', (val) => {
            if (val !== 'close') {
                localStorage.setItem('customer_active_tab_{{ $customer->id }}', val);
            }
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
                exclude_order_id: this.editingOrderId || '',
            });
            const res = await fetch(`/products-search-api?${params}`, {
                headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
            });
            if (!res.ok) throw new Error('Network error');
            const json = await res.json();
            
            // Initialize temp inputs: _disc & _discType come from the product's pre-set defaults
            this.productSearchResults = (json.data || []).map(p => ({
                ...p,
                _qty: 1,
                _disc: parseFloat(p.default_discount) || 0,
                _discType: p.default_discount_type || 'percent'  // locked to product default type
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
        
        const existingIndex = this.cart.findIndex(i => i.id === product.id);
        const existingQty = existingIndex !== -1 ? this.cart[existingIndex].quantity : 0;
        
        if (existingQty + qty > product.available_stock && product.available_stock !== 999) {
            this.notify('warning', 'Insufficient stock available');
            return;
        }

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
            : Math.min(parseFloat(item.discountValue) * item.quantity, base);
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
        if (!code) return;
        
        const csrfToken = document.querySelector('meta[name=csrf-token]').getAttribute('content');
        fetch('{{ route('coupons.validate') }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json'
            },
            body: JSON.stringify({
                code: code,
                subtotal: this.subtotal
            })
        })
        .then(res => res.json())
        .then(data => {
            if (data.valid) {
                this.couponDiscount = data.discount;
                this.couponApplied = true;
                this.notify('success', data.message);
            } else {
                this.couponDiscount = 0;
                this.couponApplied = false;
                this.notify('error', data.message || 'Invalid promo code');
            }
        })
        .catch(error => {
            console.error(error);
            this.notify('error', 'Failed to validate promo code');
        });
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
            this.editingAddress.village_name = address.village.village_name || address.village.name;
            this.editingAddress.post_office = address.village.post_so_name || address.village.post_office;
            this.editingAddress.taluka = address.village.taluka_name || address.village.taluka;
            this.editingAddress.district = address.village.district_name || address.village.district;
            this.editingAddress.city = address.village.district_name || address.village.district || address.village.city;
            this.editingAddress.state = (address.village && address.village.state_name) ? address.village.state_name : (address.state || '');
            this.editingAddress.pincode = address.village.pincode;
            this.villageSearch = this.editingAddress.village_name;
        } else if (address) {
            // Fallback for direct address fields if village object is missing
            this.editingAddress.city = address.city || address.district;
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
            this.editingAddress.village_name = v.name || v.village_name;
            this.editingAddress.post_office = v.post_office || v.post_so_name || '';
            this.editingAddress.taluka = v.taluka || v.taluka_name || '';
            this.editingAddress.district = v.district || v.district_name || '';
            this.editingAddress.state = v.state_name || v.state || '';
            this.editingAddress.pincode = v.pincode || '';
            this.editingAddress.city = v.district || v.district_name || '';
        }
        this.villages = [];
        this.villageSearch = v.name;
    }
}
