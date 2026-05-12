<x-layouts.app>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Edit Product') }}: {{ $product->name }}
        </h2>
    </x-slot>

    <div class="p-6 lg:p-10">
        <div class="max-w-5xl mx-auto">
            <x-ui.card class="overflow-hidden border-border/60 shadow-2xl bg-card/30 backdrop-blur-2xl rounded-3xl">
                <x-ui.card-header class="border-b border-border/40 bg-muted/10 p-6">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <div class="size-12 rounded-2xl bg-primary/10 border border-primary/20 text-primary flex items-center justify-center shadow-inner">
                                <x-ui.icon name="edit-3" size="6" />
                            </div>
                            <div>
                                <h3 class="text-lg font-black text-foreground tracking-tight">Edit Product</h3>
                                <p class="text-[10px] uppercase font-bold text-muted-foreground tracking-widest">Update item details and pricing</p>
                            </div>
                        </div>
                        <a href="{{ route('products.index') }}">
                            <x-ui.button variant="outline" size="sm" class="rounded-xl font-bold uppercase tracking-widest text-[10px] border-border hover:bg-muted transition-colors">
                                <x-ui.icon name="arrow-left" size="3" class="mr-2" />
                                Back to list
                            </x-ui.button>
                        </a>
                    </div>
                </x-ui.card-header>

                <x-ui.card-content class="p-8" x-data="{ allowOverselling: {{ $product->allow_overselling ? 'true' : 'false' }} }">
                    <form action="{{ route('products.update', $product) }}" method="POST" enctype="multipart/form-data" class="space-y-8">
                        @csrf
                        @method('PUT')

                        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                            <div class="lg:col-span-2 space-y-6">
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    <div class="space-y-2">
                                        <label for="name" class="text-[10px] font-black uppercase tracking-widest text-muted-foreground/80 ml-1">Product Name</label>
                                        <input type="text" name="name" id="name" value="{{ old('name', $product->name) }}" required 
                                            class="w-full h-12 px-4 rounded-2xl border border-border bg-background/50 focus:bg-background focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all text-sm font-medium">
                                    </div>
                                    <div class="space-y-2">
                                        <label for="sku" class="text-[10px] font-black uppercase tracking-widest text-muted-foreground/80 ml-1">SKU / Item Code</label>
                                        <input type="text" name="sku" id="sku" value="{{ old('sku', $product->sku) }}" required 
                                            class="w-full h-12 px-4 rounded-2xl border border-border bg-background/50 focus:bg-background focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all text-sm font-medium">
                                    </div>
                                </div>

                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    <div class="space-y-2">
                                        <label for="category_id" class="text-[10px] font-black uppercase tracking-widest text-muted-foreground/80 ml-1">Category & Subcategory</label>
                                        <select name="category_id" id="category_id" required 
                                            class="w-full h-12 px-4 rounded-2xl border border-border bg-background/50 focus:bg-background text-sm font-medium text-foreground dark:text-foreground">
                                            <option value="" class="bg-card">Select Category</option>
                                            @foreach($categories as $category)
                                                <optgroup label="{{ $category->name }}" class="bg-card font-black uppercase tracking-widest text-[9px]">
                                                    <option value="{{ $category->id }}" {{ old('category_id', $product->category_id) == $category->id ? 'selected' : '' }} class="bg-card text-sm font-medium">{{ $category->name }} (Main)</option>
                                                    @foreach($category->children as $child)
                                                        <option value="{{ $child->id }}" {{ old('category_id', $product->category_id) == $child->id ? 'selected' : '' }} class="bg-card text-sm font-medium">&nbsp;&nbsp;↳ {{ $child->name }}</option>
                                                    @endforeach
                                                </optgroup>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="space-y-2">
                                        <label for="brand_id" class="text-[10px] font-black uppercase tracking-widest text-muted-foreground/80 ml-1">Brand</label>
                                        <select name="brand_id" id="brand_id" 
                                            class="w-full h-12 px-4 rounded-2xl border border-border bg-background/50 focus:bg-background text-sm font-medium text-foreground">
                                            <option value="" class="bg-card">No Brand</option>
                                            @foreach($brands as $brand)
                                                <option value="{{ $brand->id }}" {{ old('brand_id', $product->brand_id) == $brand->id ? 'selected' : '' }} class="bg-card">{{ $brand->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>

                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    <div class="space-y-2">
                                        <label for="barcode" class="text-[10px] font-black uppercase tracking-widest text-muted-foreground/80 ml-1">Barcode / UPC</label>
                                        <input type="text" name="barcode" id="barcode" value="{{ old('barcode', $product->barcode) }}" 
                                            class="w-full h-12 px-4 rounded-2xl border border-border bg-background/50 focus:bg-background focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all text-sm font-medium">
                                    </div>
                                    <div class="space-y-2">
                                        <label for="weight" class="text-[10px] font-black uppercase tracking-widest text-muted-foreground/80 ml-1">Weight / Volume</label>
                                        <input type="text" name="weight" id="weight" value="{{ old('weight', $product->weight) }}" 
                                            class="w-full h-12 px-4 rounded-2xl border border-border bg-background/50 focus:bg-background focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all text-sm font-medium">
                                    </div>
                                </div>

                                <!-- Dynamic Attributes -->
                                <div class="p-6 rounded-3xl bg-muted/20 border border-border/60 space-y-6">
                                    <div class="flex items-center justify-between mb-2">
                                        <h4 class="text-[10px] font-black uppercase tracking-widest text-primary">Product Attributes</h4>
                                        <span class="text-[9px] text-muted-foreground font-bold italic">Update relevant values</span>
                                    </div>
                                    
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                                        @foreach($attributes as $attribute)
                                            <div class="space-y-3">
                                                <label class="text-[10px] font-black uppercase tracking-widest text-foreground/80 ml-1">{{ $attribute->name }}</label>
                                                <div class="flex flex-wrap gap-2">
                                                    @foreach($attribute->values as $value)
                                                        <label class="relative cursor-pointer group">
                                                            <input type="checkbox" name="attributes[]" value="{{ $value->id }}" {{ in_array($value->id, $selectedAttributes) ? 'checked' : '' }} class="sr-only peer">
                                                            <div class="px-3 py-1.5 rounded-xl border border-border bg-background/50 text-[10px] font-bold uppercase tracking-tight text-muted-foreground peer-checked:bg-primary/20 peer-checked:border-primary peer-checked:text-primary transition-all hover:bg-muted/50">
                                                                @if($attribute->type === 'color')
                                                                    <span class="inline-block size-2 rounded-full mr-1.5 shadow-sm" style="background-color: {{ $value->color_code }}"></span>
                                                                @endif
                                                                {{ $value->value }}
                                                            </div>
                                                        </label>
                                                    @endforeach
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>

                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    <div class="space-y-2">
                                        <label for="tax_rate_id" class="text-[10px] font-black uppercase tracking-widest text-muted-foreground/80 ml-1">Tax Rate</label>
                                        <select name="tax_rate_id" id="tax_rate_id" 
                                            class="w-full h-12 px-4 rounded-2xl border border-border bg-background/50 focus:bg-background text-sm font-medium text-foreground">
                                            <option value="" class="bg-card">No Tax</option>
                                            @foreach($taxRates as $rate)
                                                <option value="{{ $rate->id }}" {{ old('tax_rate_id', $product->tax_rate_id) == $rate->id ? 'selected' : '' }} class="bg-card">{{ $rate->name }} ({{ $rate->rate }}%)</option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <div class="space-y-2">
                                        <label for="hsn_code_id" class="text-[10px] font-black uppercase tracking-widest text-muted-foreground/80 ml-1">HSN Code</label>
                                        <select name="hsn_code_id" id="hsn_code_id" 
                                            class="w-full h-12 px-4 rounded-2xl border border-border bg-background/50 focus:bg-background text-sm font-medium text-foreground">
                                            <option value="" class="bg-card">No HSN</option>
                                            @foreach($hsnCodes as $hsn)
                                                <option value="{{ $hsn->id }}" {{ old('hsn_code_id', $product->hsn_code_id) == $hsn->id ? 'selected' : '' }} class="bg-card">{{ $hsn->code }} - {{ $hsn->description }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>

                                <div class="p-6 rounded-3xl bg-muted/10 border border-border/40 space-y-4">
                                    <h4 class="text-[10px] font-black uppercase tracking-widest text-primary mb-2">Inventory Settings</h4>
                                    
                                    <div class="flex items-center justify-between">
                                        <div>
                                            <p class="text-sm font-bold text-foreground">Manage Stock</p>
                                            <p class="text-[10px] text-muted-foreground">Track inventory levels for this product</p>
                                        </div>
                                        <label class="relative inline-flex items-center cursor-pointer">
                                            <input type="checkbox" name="manage_stock" value="1" {{ $product->manage_stock ? 'checked' : '' }} class="sr-only peer">
                                            <div class="w-11 h-6 bg-muted/40 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full rtl:peer-checked:after:-translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:start-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-primary"></div>
                                        </label>
                                    </div>

                                    <div class="flex flex-col gap-4">
                                        <div class="flex items-center justify-between">
                                            <div>
                                                <p class="text-sm font-bold text-foreground">Allow Overselling</p>
                                                <p class="text-[10px] text-muted-foreground">Allow customers to buy even if out of stock</p>
                                            </div>
                                            <label class="relative inline-flex items-center cursor-pointer">
                                                <input type="checkbox" name="allow_overselling" value="1" x-model="allowOverselling" class="sr-only peer">
                                                <div class="w-11 h-6 bg-muted/40 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full rtl:peer-checked:after:-translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:start-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-primary"></div>
                                            </label>
                                        </div>

                                        <div x-show="allowOverselling" x-transition class="space-y-2 pl-4 border-l-2 border-primary/20">
                                            <label for="overselling_qty" class="text-[10px] font-black uppercase tracking-widest text-primary ml-1">Overselling Limit (Qty)</label>
                                            <input type="number" name="overselling_qty" id="overselling_qty" value="{{ old('overselling_qty', $product->overselling_qty) }}" 
                                                class="w-full h-10 px-4 rounded-xl border border-primary/20 bg-primary/5 focus:bg-primary/10 focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all text-sm font-black text-primary placeholder:text-primary/20" placeholder="Max units to oversell">
                                            <p class="text-[8px] text-muted-foreground mt-1 ml-1 font-bold italic">How many extra units can be sold beyond zero stock?</p>
                                        </div>
                                    </div>
                                </div>

                                <div class="space-y-2">
                                    <label for="description" class="text-[10px] font-black uppercase tracking-widest text-muted-foreground/80 ml-1">Description</label>
                                    <textarea name="description" id="description" rows="4" 
                                        class="w-full px-4 py-3 rounded-2xl border border-border bg-background/50 focus:bg-background text-sm font-medium">{{ old('description', $product->description) }}</textarea>
                                </div>

                                <div class="space-y-2">
                                    <label for="application_instructions" class="text-[10px] font-black uppercase tracking-widest text-primary ml-1">Application Instructions & Dosage</label>
                                    <textarea name="application_instructions" id="application_instructions" rows="4" 
                                        class="w-full px-4 py-3 rounded-2xl border border-primary/20 bg-primary/5 focus:bg-primary/10 focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all text-sm font-medium" placeholder="e.g. Mix 2ml in 1L of water and spray on leaves...">{{ old('application_instructions', $product->application_instructions) }}</textarea>
                                    <p class="text-[9px] text-muted-foreground mt-1 ml-1">Update guidance for farmers on how to use this product effectively.</p>
                                </div>
                            </div>

                            <div class="space-y-6">
                                <div class="p-6 rounded-3xl bg-muted/10 border border-border/40 space-y-6 shadow-sm">
                                    <div class="space-y-2">
                                        <label for="image" class="text-[10px] font-black uppercase tracking-widest text-muted-foreground/80 ml-1">Product Image</label>
                                        <div class="relative group">
                                            <input type="file" name="image" id="image" class="absolute inset-0 w-full h-full opacity-0 cursor-pointer z-10" onchange="previewImage(this)">
                                            <div id="image-preview" class="aspect-square rounded-2xl border border-border/60 bg-background/50 flex flex-col items-center justify-center overflow-hidden shadow-inner">
                                                @if($product->image_path)
                                                    <img src="{{ asset('storage/' . $product->image_path) }}" class="w-full h-full object-cover">
                                                @else
                                                    <x-ui.icon name="image" size="8" class="opacity-20" />
                                                @endif
                                            </div>
                                        </div>
                                    </div>

                                    <div class="grid grid-cols-1 gap-4">
                                        <div class="space-y-2">
                                            <label for="purchase_price" class="text-[10px] font-black uppercase tracking-widest text-muted-foreground/80 ml-1">Purchase</label>
                                            <input type="number" step="0.01" name="purchase_price" id="purchase_price" value="{{ old('purchase_price', $product->purchase_price) }}" 
                                                class="w-full h-11 px-4 rounded-xl border border-border bg-background/50 focus:bg-background text-sm font-black">
                                        </div>
                                        <div class="space-y-2">
                                            <label for="mrp" class="text-[10px] font-black uppercase tracking-widest text-muted-foreground/80 ml-1">MRP</label>
                                            <input type="number" step="0.01" name="mrp" id="mrp" value="{{ old('mrp', $product->mrp) }}" 
                                                class="w-full h-11 px-4 rounded-xl border border-border bg-background/50 focus:bg-background text-sm font-black text-orange-500">
                                        </div>
                                        <div class="space-y-2">
                                            <label for="selling_price" class="text-[10px] font-black uppercase tracking-widest text-muted-foreground/80 ml-1">Selling</label>
                                            <input type="number" step="0.01" name="selling_price" id="selling_price" value="{{ old('selling_price', $product->selling_price) }}" 
                                                class="w-full h-11 px-4 rounded-xl border border-border bg-background/50 focus:bg-background text-sm font-black text-primary">
                                        </div>
                                    </div>

                                    <div class="space-y-2">
                                        <label for="status" class="text-[10px] font-black uppercase tracking-widest text-muted-foreground/80 ml-1">Status</label>
                                        <select name="status" id="status" 
                                            class="w-full h-11 px-4 rounded-xl border border-border bg-background/50 focus:bg-background text-[10px] font-black uppercase tracking-widest text-foreground">
                                            <option value="active" {{ $product->status == 'active' ? 'selected' : '' }} class="bg-card">Active</option>
                                            <option value="draft" {{ $product->status == 'draft' ? 'selected' : '' }} class="bg-card">Draft</option>
                                            <option value="out_of_stock" {{ $product->status == 'out_of_stock' ? 'selected' : '' }} class="bg-card">Out of Stock</option>
                                        </select>
                                    </div>
                                </div>

                                <x-ui.button type="submit" class="w-full h-14 rounded-2xl font-black uppercase tracking-[0.2em] text-xs shadow-xl shadow-primary/20 hover:scale-[1.02] active:scale-[0.98] transition-all">
                                    Update Product
                                </x-ui.button>
                            </div>
                        </div>
                    </form>
                </x-ui.card-content>
            </x-ui.card>
        </div>
    </div>
</x-layouts.app>
