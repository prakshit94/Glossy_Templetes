<x-layouts.app>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-foreground leading-tight">
            {{ __('New Stock Adjustment') }}
        </h2>
    </x-slot>

    <div class="p-6 lg:p-10" x-data="{
        items: [{ product_id: '', current_qty: 0, new_qty: 0, difference: 0, open: false, search: '' }],
        products: @js($products),
        warehouseStock: {},
        warehouseId: '',
        isLoadingStock: false,
        
        async fetchStock() {
            if (!this.warehouseId) {
                this.warehouseStock = {};
                return;
            }
            this.isLoadingStock = true;
            try {
                const res = await fetch(`/warehouses/${this.warehouseId}/stock`);
                this.warehouseStock = await res.json();
                // Update existing items current qty
                this.items.forEach(item => {
                    item.current_qty = this.warehouseStock[item.product_id]?.quantity || 0;
                    this.updateDifference(this.items.indexOf(item));
                });
            } catch (e) {
                console.error('Failed to fetch stock', e);
            }
            this.isLoadingStock = false;
        },

        addItem() {
            this.items.push({ product_id: '', current_qty: 0, new_qty: 0, difference: 0, open: false, search: '' });
        },
        
        removeItem(index) {
            if (this.items.length > 1) {
                this.items.splice(index, 1);
            }
        },

        selectProduct(index, product) {
            this.items[index].product_id = product.id;
            this.items[index].current_qty = this.warehouseStock[product.id]?.quantity || 0;
            this.items[index].open = false;
            this.items[index].search = `${product.name} (${product.sku})`;
            this.updateDifference(index);
        },

        getFilteredProducts(index) {
            const search = this.items[index].search.toLowerCase();
            if (!search) return this.products;
            return this.products.filter(p => 
                p.name.toLowerCase().includes(search) || 
                p.sku.toLowerCase().includes(search)
            );
        },

        updateDifference(index) {
            this.items[index].difference = (this.items[index].new_qty || 0) - (this.items[index].current_qty || 0);
        }
    }">
        <div class="max-w-5xl mx-auto">
            <x-ui.card class="overflow-hidden border-border/60 shadow-2xl bg-card/30 backdrop-blur-2xl rounded-3xl">
                <x-ui.card-header class="border-b border-border/40 bg-muted/10 p-6">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <div class="size-12 rounded-2xl bg-primary/10 border border-primary/20 text-primary flex items-center justify-center shadow-inner">
                                <x-ui.icon name="sliders" size="6" />
                            </div>
                            <div>
                                <h3 class="text-lg font-black text-foreground tracking-tight">Create Stock Adjustment</h3>
                                <p class="text-[10px] uppercase font-bold text-muted-foreground tracking-widest">Adjust inventory levels manually</p>
                            </div>
                        </div>
                        <a href="{{ route('adjustments.index') }}">
                            <x-ui.button variant="outline" size="sm" class="rounded-xl font-bold uppercase tracking-widest text-[10px] border-border hover:bg-muted transition-colors">
                                <x-ui.icon name="arrow-left" size="3" class="mr-2" />
                                Back to list
                            </x-ui.button>
                        </a>
                    </div>
                </x-ui.card-header>

                <x-ui.card-content class="p-8">
                    <form action="{{ route('adjustments.store') }}" method="POST" class="space-y-8">
                        @csrf

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                            <div class="space-y-2">
                                <label for="warehouse_id" class="text-[10px] font-black uppercase tracking-widest text-muted-foreground/80 ml-1">Warehouse</label>
                                <select name="warehouse_id" id="warehouse_id" required 
                                    x-model="warehouseId" @change="fetchStock"
                                    class="w-full h-12 px-4 rounded-2xl border border-border bg-background/50 focus:bg-background focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all text-sm font-medium text-foreground">
                                    <option value="">Select Warehouse</option>
                                    @foreach($warehouses as $warehouse)
                                        <option value="{{ $warehouse->id }}">{{ $warehouse->name }} ({{ $warehouse->code }})</option>
                                    @endforeach
                                </select>
                                @error('warehouse_id') <p class="text-[10px] text-destructive font-bold mt-1 ml-1">{{ $message }}</p> @enderror
                            </div>

                            <div class="space-y-2">
                                <label for="reason" class="text-[10px] font-black uppercase tracking-widest text-muted-foreground/80 ml-1">Reason for Adjustment</label>
                                <input type="text" name="reason" id="reason" value="{{ old('reason') }}" required 
                                    class="w-full h-12 px-4 rounded-2xl border border-border bg-background/50 focus:bg-background focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all text-sm font-medium" placeholder="e.g. Damaged stock, Correction">
                                @error('reason') <p class="text-[10px] text-destructive font-bold mt-1 ml-1">{{ $message }}</p> @enderror
                            </div>
                        </div>

                        <div class="space-y-4">
                            <div class="flex items-center justify-between px-1">
                                <div class="flex items-center gap-3">
                                    <h4 class="text-[10px] font-black uppercase tracking-widest text-primary">Adjustment Items</h4>
                                    <div x-show="isLoadingStock" class="flex items-center gap-1.5 px-2 py-0.5 rounded-lg bg-primary/10 text-primary">
                                        <x-ui.icon name="refresh-cw" size="3" class="animate-spin" />
                                        <span class="text-[9px] font-bold uppercase tracking-tight">Syncing Stock...</span>
                                    </div>
                                </div>
                                <button type="button" @click="addItem" class="text-[10px] font-black uppercase tracking-widest text-primary hover:text-primary/80 transition-colors flex items-center gap-1">
                                    <x-ui.icon name="plus" size="3" /> Add Item
                                </button>
                            </div>

                            <div class="space-y-3">
                                <template x-for="(item, index) in items" :key="index">
                                    <div class="grid grid-cols-1 md:grid-cols-12 gap-4 items-end p-4 rounded-2xl bg-muted/10 border border-border/40 group relative">
                                        <!-- Product Search Dropdown -->
                                        <div class="md:col-span-5 space-y-2 relative">
                                            <label class="text-[9px] font-black uppercase tracking-widest text-muted-foreground/60 ml-1">Product</label>
                                            <div class="relative">
                                                <input type="text" 
                                                    placeholder="Search product..."
                                                    x-model="item.search"
                                                    @focus="item.open = true"
                                                    @click.away="item.open = false"
                                                    class="w-full h-10 px-4 rounded-xl border border-border bg-background/50 focus:bg-background text-xs font-medium pr-10">
                                                <input type="hidden" :name="`items[${index}][product_id]`" :value="item.product_id">
                                                <div class="absolute right-3 top-1/2 -translate-y-1/2 text-muted-foreground/40 pointer-events-none">
                                                    <x-ui.icon name="search" size="3" />
                                                </div>

                                                <!-- Dropdown List -->
                                                <div x-show="item.open" 
                                                    x-transition:enter="transition ease-out duration-200"
                                                    x-transition:enter-start="opacity-0 translate-y-1"
                                                    x-transition:enter-end="opacity-100 translate-y-0"
                                                    class="absolute z-[100] mt-1 w-full bg-popover border border-border rounded-xl shadow-2xl p-1 max-h-60 overflow-y-auto custom-scrollbar">
                                                    <template x-for="product in getFilteredProducts(index)" :key="product.id">
                                                        <button type="button" 
                                                            @click="selectProduct(index, product)"
                                                            class="w-full text-left px-3 py-2 rounded-lg hover:bg-muted text-xs transition-colors flex flex-col gap-0.5">
                                                            <span class="font-bold text-foreground" x-text="product.name"></span>
                                                            <span class="text-[10px] text-muted-foreground uppercase" x-text="product.sku"></span>
                                                        </button>
                                                    </template>
                                                    <div x-show="getFilteredProducts(index).length === 0" class="px-3 py-4 text-center text-[10px] text-muted-foreground italic font-medium">
                                                        No products found...
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="md:col-span-2 space-y-2">
                                            <label class="text-[9px] font-black uppercase tracking-widest text-muted-foreground/60 ml-1">Current Qty</label>
                                            <div class="h-10 flex items-center px-4 rounded-xl border border-border bg-muted/20 text-xs font-black text-muted-foreground" x-text="item.current_qty"></div>
                                            <input type="hidden" :name="`items[${index}][current_qty]`" :value="item.current_qty">
                                        </div>

                                        <div class="md:col-span-2 space-y-2">
                                            <label class="text-[9px] font-black uppercase tracking-widest text-muted-foreground/60 ml-1">New Qty</label>
                                            <input type="number" :name="`items[${index}][new_qty]`" x-model.number="item.new_qty" @input="updateDifference(index)" step="0.01" required 
                                                class="w-full h-10 px-4 rounded-xl border border-border bg-background/50 focus:bg-background text-xs font-black">
                                        </div>

                                        <div class="md:col-span-2 space-y-2">
                                            <label class="text-[9px] font-black uppercase tracking-widest text-muted-foreground/60 ml-1">Difference</label>
                                            <div class="h-10 flex items-center px-4 rounded-xl border border-border bg-muted/20 text-xs font-black" :class="item.difference > 0 ? 'text-emerald-500' : (item.difference < 0 ? 'text-red-500' : 'text-muted-foreground')" x-text="item.difference > 0 ? `+${item.difference}` : item.difference"></div>
                                        </div>

                                        <div class="md:col-span-1 flex justify-center">
                                            <button type="button" @click="removeItem(index)" class="h-10 w-10 rounded-xl flex items-center justify-center text-destructive hover:bg-destructive/10 transition-colors">
                                                <x-ui.icon name="trash-2" size="4" />
                                            </button>
                                        </div>
                                    </div>
                                </template>
                            </div>
                        </div>

                        <div class="flex justify-end pt-4">
                            <x-ui.button type="submit" class="h-14 px-10 rounded-2xl font-black uppercase tracking-[0.2em] text-xs shadow-xl shadow-primary/20 hover:scale-[1.02] active:scale-[0.98] transition-all">
                                Create Adjustment
                            </x-ui.button>
                        </div>
                    </form>
                </x-ui.card-content>
            </x-ui.card>
        </div>
    </div>

    <style>
        .custom-scrollbar::-webkit-scrollbar { width: 4px; }
        .custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
        .custom-scrollbar::-webkit-scrollbar-thumb { background: rgba(var(--primary), 0.2); border-radius: 10px; }
    </style>
</x-layouts.app>
