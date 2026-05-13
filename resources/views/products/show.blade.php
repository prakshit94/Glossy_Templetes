<x-layouts.app pageTitle="Product Details">

    <div class="p-6 lg:p-10">
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            
            <!-- Left Side: Product Info -->
            <div class="lg:col-span-2 space-y-8">
                <x-ui.card class="overflow-hidden border-border/40 shadow-2xl bg-card/30 backdrop-blur-2xl rounded-3xl">
                    <div class="p-8">
                        <div class="flex flex-col md:flex-row gap-8">
                            <div class="w-full md:w-64 aspect-square rounded-3xl bg-muted/20 border border-border/50 overflow-hidden flex items-center justify-center shrink-0">
                                @if($product->image_path)
                                    <img src="{{ asset('storage/' . $product->image_path) }}" class="w-full h-full object-cover">
                                @else
                                    <x-ui.icon name="package" size="12" class="opacity-10 text-primary" />
                                @endif
                            </div>
                            <div class="flex-1 space-y-4">
                                <div>
                                    <div class="flex items-center gap-2">
                                        <span class="px-2 py-1 rounded-lg bg-primary/10 border border-primary/20 text-[10px] font-black text-primary uppercase tracking-widest">
                                            {{ $product->category->parent->name ?? $product->category->name }}
                                        </span>
                                        @if($product->category->parent)
                                            <span class="text-muted-foreground opacity-20"><x-ui.icon name="chevron-right" size="3" /></span>
                                            <span class="px-2 py-1 rounded-lg bg-muted/30 border border-border/40 text-[10px] font-black text-muted-foreground uppercase tracking-widest">
                                                {{ $product->category->name }}
                                            </span>
                                        @endif
                                    </div>
                                    <h1 class="text-3xl font-black text-foreground mt-2">{{ $product->name }}</h1>
                                    <div class="flex items-center gap-3 mt-1">
                                        <p class="text-sm font-mono text-muted-foreground">{{ $product->sku }}</p>
                                        @if($product->hsnCode)
                                            <span class="text-[9px] px-2 py-0.5 rounded-full bg-muted/30 border border-border/40 text-muted-foreground font-bold uppercase">HSN: {{ $product->hsnCode->code }}</span>
                                        @endif
                                    </div>
                                </div>
                                
                                <div class="grid grid-cols-2 gap-6 pt-4">
                                    <div>
                                        <p class="text-[10px] font-black uppercase tracking-widest text-muted-foreground">Selling Price</p>
                                        <div class="flex items-baseline gap-2">
                                            <p class="text-2xl font-black text-primary">₹{{ number_format($product->selling_price, 2) }}</p>
                                            @if($product->taxRate)
                                                <span class="text-[10px] font-bold text-emerald-500/80">+ {{ $product->taxRate->rate }}% {{ $product->taxRate->name }}</span>
                                            @endif
                                        </div>
                                    </div>
                                    <div>
                                        <p class="text-[10px] font-black uppercase tracking-widest text-muted-foreground">MRP</p>
                                        <p class="text-xl font-bold text-foreground opacity-60">₹{{ number_format($product->mrp, 2) }}</p>
                                    </div>
                                </div>

                                @if($product->attributeValues->count() > 0)
                                    <div class="pt-6 border-t border-border/30">
                                        <p class="text-[10px] font-black uppercase tracking-widest text-muted-foreground mb-3">Product Attributes</p>
                                        <div class="flex flex-wrap gap-4">
                                            @foreach($product->attributeValues->groupBy('attribute_id') as $attrId => $values)
                                                <div class="space-y-1">
                                                    <span class="text-[9px] font-black uppercase text-muted-foreground/40">{{ $values->first()->attribute->name }}</span>
                                                    <div class="flex gap-1.5">
                                                        @foreach($values as $val)
                                                            <span class="px-2 py-1 rounded-lg bg-muted/30 border border-border/40 text-[10px] font-bold text-foreground flex items-center gap-2">
                                                                @if($val->attribute->type === 'color')
                                                                    <span class="size-2 rounded-full" style="background-color: {{ $val->color_code }}"></span>
                                                                @endif
                                                                {{ $val->value }}
                                                            </span>
                                                        @endforeach
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                @endif

                                <div class="pt-4">
                                    <p class="text-[10px] font-black uppercase tracking-widest text-muted-foreground mb-2">Description</p>
                                    <p class="text-sm text-muted-foreground leading-relaxed">
                                        {{ $product->description ?: 'No description provided.' }}
                                    </p>
                                </div>

                                @if($product->application_instructions)
                                    <div class="mt-8 p-6 rounded-3xl bg-primary/5 border border-primary/10 space-y-3">
                                        <div class="flex items-center gap-2 text-primary">
                                            <x-ui.icon name="info" size="4" />
                                            <h4 class="text-[10px] font-black uppercase tracking-widest">Application Instructions & Dosage</h4>
                                        </div>
                                        <p class="text-sm text-foreground/80 leading-relaxed italic">
                                            "{{ $product->application_instructions }}"
                                        </p>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </x-ui.card>

                <!-- Stock by Warehouse -->
                <x-ui.card class="overflow-hidden border-border/40 shadow-2xl bg-card/30 backdrop-blur-2xl rounded-3xl">
                    <x-ui.card-header class="border-b border-border/40 bg-muted/5 p-6">
                        <h3 class="text-sm font-black text-foreground uppercase tracking-widest">Stock Availability</h3>
                    </x-ui.card-header>
                    <x-ui.card-content class="p-0">
                        <table class="w-full text-left border-collapse">
                            <thead>
                                <tr class="bg-muted/5 border-b border-border/40">
                                    <th class="p-4 text-[10px] font-black uppercase tracking-widest text-muted-foreground/60">Warehouse</th>
                                    <th class="p-4 text-[10px] font-black uppercase tracking-widest text-muted-foreground/60 text-center">Quantity</th>
                                    <th class="p-4 text-[10px] font-black uppercase tracking-widest text-muted-foreground/60 text-right">Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($product->stocks as $stock)
                                    <tr class="border-b border-border/30">
                                        <td class="p-4 font-bold text-sm text-foreground">{{ $stock->warehouse->name }}</td>
                                        <td class="p-4 text-center font-black text-lg text-primary">{{ number_format($stock->quantity) }}</td>
                                        <td class="p-4 text-right">
                                            <span class="px-2 py-1 rounded-full bg-emerald-500/10 text-emerald-500 border border-emerald-500/20 text-[10px] font-black uppercase">In Stock</span>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </x-ui.card-content>
                </x-ui.card>
            </div>

            <!-- Right Side: Actions & Quick Info -->
            <div class="space-y-6">
                <x-ui.card class="overflow-hidden border-border/40 shadow-2xl bg-card/30 backdrop-blur-2xl rounded-3xl">
                    <div class="p-6 space-y-4">
                        <div class="flex items-center justify-between mb-2">
                            <span class="text-[10px] font-black uppercase tracking-widest text-muted-foreground">Current Status</span>
                            @php
                                $statusColors = [
                                    'active' => 'bg-emerald-500/10 text-emerald-500 border-emerald-500/20',
                                    'draft' => 'bg-blue-500/10 text-blue-500 border-blue-500/20',
                                    'out_of_stock' => 'bg-red-500/10 text-red-500 border-red-500/20',
                                ];
                                $colorClass = $statusColors[$product->status] ?? 'bg-muted/40 text-muted-foreground border-border/40';
                            @endphp
                            <span class="px-2.5 py-1 rounded-full border {{ $colorClass }} text-[10px] font-black uppercase tracking-widest">
                                {{ str_replace('_', ' ', $product->status) }}
                            </span>
                        </div>

                        <a href="{{ route('products.edit', $product) }}" class="w-full block">
                            <x-ui.button class="w-full h-12 rounded-xl font-black uppercase tracking-widest text-[10px] shadow-lg shadow-primary/20">
                                <x-ui.icon name="edit-3" size="3.5" class="mr-2" />
                                Edit Product
                            </x-ui.button>
                        </a>
                        <x-ui.button variant="outline" class="w-full h-12 rounded-xl font-black uppercase tracking-widest text-[10px] hover:bg-muted/30">
                            <x-ui.icon name="refresh-cw" size="3.5" class="mr-2" />
                            Adjust Inventory
                        </x-ui.button>
                    </div>
                </x-ui.card>

                <!-- Quick Facts -->
                <x-ui.card class="overflow-hidden border-border/40 shadow-2xl bg-card/30 backdrop-blur-2xl rounded-3xl">
                    <x-ui.card-header class="border-b border-border/40 bg-muted/5 p-4">
                        <h4 class="text-[10px] font-black uppercase tracking-widest text-muted-foreground">Quick Facts</h4>
                    </x-ui.card-header>
                    <div class="p-6 space-y-4">
                        <div class="flex items-center justify-between">
                            <span class="text-[10px] font-bold text-muted-foreground/60 uppercase">Brand</span>
                            <span class="text-xs font-black text-foreground">{{ $product->brand->name ?? 'No Brand' }}</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-[10px] font-bold text-muted-foreground/60 uppercase">HSN Code</span>
                            <span class="text-xs font-mono font-bold text-foreground">{{ $product->hsnCode->code ?? 'N/A' }}</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-[10px] font-bold text-muted-foreground/60 uppercase">Tax Class</span>
                            <span class="text-xs font-bold text-emerald-500">{{ $product->taxRate ? $product->taxRate->name : 'Tax Exempt' }}</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-[10px] font-bold text-muted-foreground/60 uppercase">Total Stock</span>
                            <span class="text-sm font-black text-primary">{{ number_format($product->total_stock) }}</span>
                        </div>
                    </div>
                </x-ui.card>
            </div>

        </div>
    </div>
</x-layouts.app>
