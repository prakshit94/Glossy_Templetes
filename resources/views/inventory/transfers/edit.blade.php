<x-layouts.app>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-foreground leading-tight">
            {{ __('Edit Stock Transfer') }}
        </h2>
    </x-slot>

    <div class="p-6 lg:p-10" x-data="{
        items: @js($transfer->items->map(fn($i) => ['product_id' => $i->product_id, 'quantity' => $i->quantity])),
        products: @js($products),
        
        addItem() {
            this.items.push({ product_id: '', quantity: 1 });
        },
        
        removeItem(index) {
            if (this.items.length > 1) {
                this.items.splice(index, 1);
            }
        }
    }">
        <div class="max-w-5xl mx-auto">
            <x-ui.card class="overflow-hidden border-border/60 shadow-2xl bg-card/30 backdrop-blur-2xl rounded-3xl">
                <x-ui.card-header class="border-b border-border/40 bg-muted/10 p-6">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <div class="size-12 rounded-2xl bg-primary/10 border border-primary/20 text-primary flex items-center justify-center shadow-inner">
                                <x-ui.icon name="edit" size="6" />
                            </div>
                            <div>
                                <h3 class="text-lg font-black text-foreground tracking-tight">Edit Transfer: {{ $transfer->transfer_no }}</h3>
                                <p class="text-[10px] uppercase font-bold text-muted-foreground tracking-widest">Update transfer details</p>
                            </div>
                        </div>
                        <a href="{{ route('transfers.index') }}">
                            <x-ui.button variant="outline" size="sm" class="rounded-xl font-bold uppercase tracking-widest text-[10px] border-border hover:bg-muted transition-colors">
                                <x-ui.icon name="arrow-left" size="3" class="mr-2" />
                                Back to list
                            </x-ui.button>
                        </a>
                    </div>
                </x-ui.card-header>

                <x-ui.card-content class="p-8">
                    <form action="{{ route('transfers.update', $transfer) }}" method="POST" class="space-y-8">
                        @csrf
                        @method('PUT')

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                            <div class="space-y-2">
                                <label for="from_warehouse_id" class="text-[10px] font-black uppercase tracking-widest text-muted-foreground/80 ml-1">From Warehouse</label>
                                <select name="from_warehouse_id" id="from_warehouse_id" required 
                                    class="w-full h-12 px-4 rounded-2xl border border-border bg-background/50 focus:bg-background focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all text-sm font-medium text-foreground">
                                    @foreach($warehouses as $warehouse)
                                        <option value="{{ $warehouse->id }}" {{ $transfer->from_warehouse_id == $warehouse->id ? 'selected' : '' }}>{{ $warehouse->name }} ({{ $warehouse->code }})</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="space-y-2">
                                <label for="to_warehouse_id" class="text-[10px] font-black uppercase tracking-widest text-muted-foreground/80 ml-1">To Warehouse</label>
                                <select name="to_warehouse_id" id="to_warehouse_id" required 
                                    class="w-full h-12 px-4 rounded-2xl border border-border bg-background/50 focus:bg-background focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all text-sm font-medium text-foreground">
                                    @foreach($warehouses as $warehouse)
                                        <option value="{{ $warehouse->id }}" {{ $transfer->to_warehouse_id == $warehouse->id ? 'selected' : '' }}>{{ $warehouse->name }} ({{ $warehouse->code }})</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="space-y-4">
                            <div class="flex items-center justify-between px-1">
                                <h4 class="text-[10px] font-black uppercase tracking-widest text-primary">Transfer Items</h4>
                                <button type="button" @click="addItem" class="text-[10px] font-black uppercase tracking-widest text-primary hover:text-primary/80 transition-colors flex items-center gap-1">
                                    <x-ui.icon name="plus" size="3" /> Add Item
                                </button>
                            </div>

                            <div class="space-y-3">
                                <template x-for="(item, index) in items" :key="index">
                                    <div class="grid grid-cols-1 md:grid-cols-12 gap-4 items-end p-4 rounded-2xl bg-muted/10 border border-border/40 group relative">
                                        <div class="md:col-span-7 space-y-2">
                                            <label class="text-[9px] font-black uppercase tracking-widest text-muted-foreground/60 ml-1">Product</label>
                                            <select :name="`items[${index}][product_id]`" x-model="item.product_id" required 
                                                class="w-full h-10 px-4 rounded-xl border border-border bg-background/50 focus:bg-background text-xs font-medium">
                                                <option value="">Select Product</option>
                                                <template x-for="product in products" :key="product.id">
                                                    <option :value="product.id" x-text="`${product.name} (${product.sku})`"></option>
                                                </template>
                                            </select>
                                        </div>
                                        <div class="md:col-span-4 space-y-2">
                                            <label class="text-[9px] font-black uppercase tracking-widest text-muted-foreground/60 ml-1">Quantity</label>
                                            <input type="number" :name="`items[${index}][quantity]`" x-model="item.quantity" min="1" step="0.01" required 
                                                class="w-full h-10 px-4 rounded-xl border border-border bg-background/50 focus:bg-background text-xs font-black">
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
                                Update Transfer
                            </x-ui.button>
                        </div>
                    </form>
                </x-ui.card-content>
            </x-ui.card>
        </div>
    </div>
</x-layouts.app>
