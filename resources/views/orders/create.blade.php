<x-layouts.app pageTitle="Create Order">

    <div class="p-6 lg:p-10">
        <div class="max-w-5xl mx-auto">
            <x-ui.card class="overflow-hidden border-border/60 shadow-2xl bg-card/30 backdrop-blur-2xl rounded-3xl">
                <x-ui.card-header class="border-b border-border/40 bg-muted/10 p-6">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <div class="size-12 rounded-2xl bg-primary/10 border border-primary/20 text-primary flex items-center justify-center shadow-inner">
                                <x-ui.icon name="shopping-cart" size="6" />
                            </div>
                            <div>
                                <h3 class="text-lg font-black text-foreground tracking-tight">Create New Order</h3>
                                <p class="text-[10px] uppercase font-bold text-muted-foreground tracking-widest">Sales and purchase workflow</p>
                            </div>
                        </div>
                        <a href="{{ route('orders.index') }}">
                            <x-ui.button variant="outline" size="sm" class="rounded-xl font-bold uppercase tracking-widest text-[10px] border-border hover:bg-muted transition-colors">
                                <x-ui.icon name="arrow-left" size="3" class="mr-2" />
                                Back to list
                            </x-ui.button>
                        </a>
                    </div>
                </x-ui.card-header>

                <x-ui.card-content class="p-8">
                    <form action="{{ route('orders.store') }}" method="POST" class="space-y-8">
                        @csrf

                        @if($errors->any())
                            <div class="rounded-2xl border border-destructive/20 bg-destructive/10 px-4 py-3 text-sm font-semibold text-destructive">
                                {{ $errors->first() }}
                            </div>
                        @endif

                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                            <div class="space-y-2">
                                <label class="text-[10px] font-black uppercase tracking-widest text-muted-foreground/80 ml-1">Order Type</label>
                                <select name="type" required class="w-full h-12 px-4 rounded-2xl border border-border bg-background/50 focus:bg-background focus:ring-2 focus:ring-primary/20 transition-all text-sm font-medium">
                                    <option value="sale" {{ old('type') === 'sale' ? 'selected' : '' }}>Sale</option>
                                    <option value="purchase" {{ old('type') === 'purchase' ? 'selected' : '' }}>Purchase</option>
                                </select>
                            </div>

                            <div class="space-y-2">
                                <label class="text-[10px] font-black uppercase tracking-widest text-muted-foreground/80 ml-1">Party</label>
                                <select name="party_id" required class="w-full h-12 px-4 rounded-2xl border border-border bg-background/50 focus:bg-background focus:ring-2 focus:ring-primary/20 transition-all text-sm font-medium">
                                    <option value="">Select Party</option>
                                    @foreach($parties as $party)
                                        <option value="{{ $party->id }}" {{ (string) old('party_id') === (string) $party->id ? 'selected' : '' }}>
                                            {{ $party->name }} ({{ $party->type }})
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="space-y-2">
                                <label class="text-[10px] font-black uppercase tracking-widest text-muted-foreground/80 ml-1">Warehouse</label>
                                <select name="warehouse_id" required class="w-full h-12 px-4 rounded-2xl border border-border bg-background/50 focus:bg-background focus:ring-2 focus:ring-primary/20 transition-all text-sm font-medium">
                                    <option value="">Select Warehouse</option>
                                    @foreach($warehouses as $warehouse)
                                        <option value="{{ $warehouse->id }}" {{ (string) old('warehouse_id') === (string) $warehouse->id ? 'selected' : '' }}>
                                            {{ $warehouse->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="space-y-2">
                                <label class="text-[10px] font-black uppercase tracking-widest text-muted-foreground/80 ml-1">Order Date</label>
                                <input type="datetime-local" name="order_date" value="{{ old('order_date') }}" required
                                    class="w-full h-12 px-4 rounded-2xl border border-border bg-background/50 focus:bg-background focus:ring-2 focus:ring-primary/20 transition-all text-sm font-medium" />
                            </div>
                        </div>

                        <div class="space-y-4">
                            <div class="flex items-center justify-between px-1">
                                <h4 class="text-[10px] font-black uppercase tracking-widest text-primary">Order Items</h4>
                                <p class="text-[9px] text-muted-foreground font-bold uppercase tracking-widest">Fill at least one valid row</p>
                            </div>

                            <div class="overflow-x-auto rounded-2xl border border-border/60">
                                <table class="w-full text-left border-collapse">
                                    <thead>
                                        <tr class="border-b border-border/40 bg-muted/5">
                                            <th class="px-4 py-3 text-[10px] font-black uppercase tracking-widest text-muted-foreground">Product</th>
                                            <th class="px-4 py-3 text-[10px] font-black uppercase tracking-widest text-muted-foreground text-right">Qty</th>
                                            <th class="px-4 py-3 text-[10px] font-black uppercase tracking-widest text-muted-foreground text-right">Unit Price</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-border/40">
                                        @for($i = 0; $i < 6; $i++)
                                            <tr class="hover:bg-muted/10 transition-colors">
                                                <td class="px-4 py-3">
                                                    <select name="items[{{ $i }}][product_id]" class="w-full h-10 px-3 rounded-xl border border-border bg-background/50 text-sm">
                                                        <option value="">Select Product</option>
                                                        @foreach($products as $product)
                                                            <option value="{{ $product->id }}">{{ $product->name }} ({{ $product->sku }})</option>
                                                        @endforeach
                                                    </select>
                                                </td>
                                                <td class="px-4 py-3">
                                                    <input type="number" name="items[{{ $i }}][quantity]" min="0.01" step="0.01"
                                                        class="w-full h-10 px-3 rounded-xl border border-border bg-background/50 text-sm font-semibold text-right" />
                                                </td>
                                                <td class="px-4 py-3">
                                                    <input type="number" name="items[{{ $i }}][unit_price]" min="0" step="0.01"
                                                        class="w-full h-10 px-3 rounded-xl border border-border bg-background/50 text-sm font-semibold text-right" />
                                                </td>
                                            </tr>
                                        @endfor
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <div class="flex items-center justify-end gap-3 pt-2">
                            <a href="{{ route('orders.index') }}">
                                <x-ui.button variant="outline" type="button" class="h-12 px-6 rounded-2xl font-bold uppercase tracking-widest text-[10px]">
                                    Cancel
                                </x-ui.button>
                            </a>
                            <x-ui.button type="submit" class="h-12 px-8 rounded-2xl font-black uppercase tracking-[0.2em] text-xs shadow-xl shadow-primary/20">
                                Create Order
                            </x-ui.button>
                        </div>
                    </form>
                </x-ui.card-content>
            </x-ui.card>
        </div>
    </div>
</x-layouts.app>
