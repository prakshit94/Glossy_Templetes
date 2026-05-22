@php
    $isEdit = isset($offer) && $offer;
@endphp

<div class="p-6 lg:p-10 max-w-5xl mx-auto space-y-8" x-data="{ offerType: '{{ old('type', $offer->type ?? 'order_discount') }}' }">
    <div class="flex flex-col lg:flex-row lg:items-end justify-between gap-6">
        <div class="flex items-center gap-4">
            <div class="size-14 rounded-[1.25rem] bg-gradient-to-br from-primary/20 via-primary/10 to-background border border-primary/20 text-primary flex items-center justify-center shadow-inner shadow-primary/10">
                <x-ui.icon name="{{ $isEdit ? 'edit-3' : 'tag' }}" size="7" />
            </div>
            <div>
                <p class="text-[10px] font-black uppercase tracking-[0.3em] text-primary/70 mb-2">Marketing Control</p>
                <h1 class="text-3xl font-black tracking-tighter text-foreground">{{ $isEdit ? 'Edit Offer' : 'Create Offer' }}</h1>
                <p class="text-sm text-muted-foreground max-w-2xl">Configure automatic order discounts and product-level BOGO campaigns without changing checkout logic.</p>
            </div>
        </div>
        <a href="{{ route('offers.index') }}" class="text-[11px] font-black text-muted-foreground hover:text-foreground transition-colors uppercase tracking-[0.2em]">Back to Offers</a>
    </div>

    <form action="{{ $isEdit ? route('offers.update', $offer) : route('offers.store') }}" method="POST" class="space-y-6">
        @csrf
        @if($isEdit)
            @method('PUT')
        @endif

        <x-ui.card class="overflow-hidden border-border/60 shadow-2xl bg-card/30 backdrop-blur-2xl rounded-[2rem]">
            <x-ui.card-content class="p-6 md:p-8 space-y-8">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="space-y-2 md:col-span-2">
                        <label for="name" class="text-[10px] font-black uppercase tracking-widest text-muted-foreground ml-1">Offer Name</label>
                        <input type="text" name="name" id="name" value="{{ old('name', $offer->name ?? '') }}" required class="w-full h-11 px-4 rounded-2xl border border-border bg-background/50 text-sm font-bold outline-none focus:ring-2 focus:ring-primary/20">
                    </div>

                    <div class="space-y-2">
                        <label for="type" class="text-[10px] font-black uppercase tracking-widest text-muted-foreground ml-1">Offer Type</label>
                        <select name="type" id="type" x-model="offerType" class="w-full h-11 px-4 rounded-2xl border border-border bg-background/50 text-sm font-bold outline-none focus:ring-2 focus:ring-primary/20">
                            <option value="order_discount">Order Discount</option>
                            <option value="bogo">Buy One Get One</option>
                        </select>
                    </div>

                    <div class="space-y-2">
                        <label for="priority" class="text-[10px] font-black uppercase tracking-widest text-muted-foreground ml-1">Priority</label>
                        <input type="number" name="priority" id="priority" value="{{ old('priority', $offer->priority ?? 0) }}" class="w-full h-11 px-4 rounded-2xl border border-border bg-background/50 text-sm font-bold outline-none focus:ring-2 focus:ring-primary/20">
                    </div>

                    <div class="space-y-2" x-show="offerType === 'order_discount'">
                        <label for="discount_type" class="text-[10px] font-black uppercase tracking-widest text-muted-foreground ml-1">Discount Type</label>
                        <select name="discount_type" id="discount_type" class="w-full h-11 px-4 rounded-2xl border border-border bg-background/50 text-sm font-bold outline-none focus:ring-2 focus:ring-primary/20">
                            <option value="fixed" {{ old('discount_type', $offer->discount_type ?? 'fixed') === 'fixed' ? 'selected' : '' }}>Fixed Amount</option>
                            <option value="percentage" {{ old('discount_type', $offer->discount_type ?? '') === 'percentage' ? 'selected' : '' }}>Percentage</option>
                        </select>
                    </div>

                    <div class="space-y-2" x-show="offerType === 'order_discount'">
                        <label for="value" class="text-[10px] font-black uppercase tracking-widest text-muted-foreground ml-1">Discount Value</label>
                        <input type="number" step="0.01" min="0" name="value" id="value" value="{{ old('value', $offer->value ?? 0) }}" class="w-full h-11 px-4 rounded-2xl border border-border bg-background/50 text-sm font-bold outline-none focus:ring-2 focus:ring-primary/20">
                    </div>

                    <div class="space-y-2" x-show="offerType === 'order_discount'">
                        <label for="min_spend" class="text-[10px] font-black uppercase tracking-widest text-muted-foreground ml-1">Minimum Spend</label>
                        <input type="number" step="0.01" min="0" name="min_spend" id="min_spend" value="{{ old('min_spend', $offer->min_spend ?? 0) }}" class="w-full h-11 px-4 rounded-2xl border border-border bg-background/50 text-sm font-bold outline-none focus:ring-2 focus:ring-primary/20">
                    </div>

                    <div class="space-y-2" x-show="offerType === 'order_discount'">
                        <label for="max_discount" class="text-[10px] font-black uppercase tracking-widest text-muted-foreground ml-1">Max Discount</label>
                        <input type="number" step="0.01" min="0" name="max_discount" id="max_discount" value="{{ old('max_discount', $offer->max_discount ?? '') }}" class="w-full h-11 px-4 rounded-2xl border border-border bg-background/50 text-sm font-bold outline-none focus:ring-2 focus:ring-primary/20">
                    </div>

                    <div class="space-y-2 md:col-span-2" x-show="offerType === 'bogo'">
                        <label for="product_id" class="text-[10px] font-black uppercase tracking-widest text-muted-foreground ml-1">Product</label>
                        <select name="product_id" id="product_id" class="w-full h-11 px-4 rounded-2xl border border-border bg-background/50 text-sm font-bold outline-none focus:ring-2 focus:ring-primary/20">
                            <option value="">Select Product</option>
                            @foreach($products as $product)
                                <option value="{{ $product->id }}" {{ (string) old('product_id', $offer->product_id ?? '') === (string) $product->id ? 'selected' : '' }}>{{ $product->name }} ({{ $product->sku }})</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="space-y-2" x-show="offerType === 'bogo'">
                        <label for="buy_qty" class="text-[10px] font-black uppercase tracking-widest text-muted-foreground ml-1">Buy Qty</label>
                        <input type="number" min="1" name="buy_qty" id="buy_qty" value="{{ old('buy_qty', $offer->buy_qty ?? 1) }}" class="w-full h-11 px-4 rounded-2xl border border-border bg-background/50 text-sm font-bold outline-none focus:ring-2 focus:ring-primary/20">
                    </div>

                    <div class="space-y-2" x-show="offerType === 'bogo'">
                        <label for="get_qty" class="text-[10px] font-black uppercase tracking-widest text-muted-foreground ml-1">Free Qty</label>
                        <input type="number" min="1" name="get_qty" id="get_qty" value="{{ old('get_qty', $offer->get_qty ?? 1) }}" class="w-full h-11 px-4 rounded-2xl border border-border bg-background/50 text-sm font-bold outline-none focus:ring-2 focus:ring-primary/20">
                    </div>

                    <div class="space-y-2">
                        <label for="starts_at" class="text-[10px] font-black uppercase tracking-widest text-muted-foreground ml-1">Starts At</label>
                        <input type="datetime-local" name="starts_at" id="starts_at" value="{{ old('starts_at', isset($offer?->starts_at) ? $offer->starts_at->format('Y-m-d\\TH:i') : '') }}" class="w-full h-11 px-4 rounded-2xl border border-border bg-background/50 text-sm font-bold outline-none focus:ring-2 focus:ring-primary/20">
                    </div>

                    <div class="space-y-2">
                        <label for="ends_at" class="text-[10px] font-black uppercase tracking-widest text-muted-foreground ml-1">Ends At</label>
                        <input type="datetime-local" name="ends_at" id="ends_at" value="{{ old('ends_at', isset($offer?->ends_at) ? $offer->ends_at->format('Y-m-d\\TH:i') : '') }}" class="w-full h-11 px-4 rounded-2xl border border-border bg-background/50 text-sm font-bold outline-none focus:ring-2 focus:ring-primary/20">
                    </div>
                </div>

                <div class="rounded-[1.5rem] border border-border/50 bg-muted/10 p-4 flex items-center gap-3">
                    <input type="checkbox" name="is_active" value="1" {{ old('is_active', $offer->is_active ?? true) ? 'checked' : '' }} class="size-5 rounded border-border text-primary focus:ring-primary">
                    <span class="text-sm font-bold text-foreground">Offer is active and eligible for automatic application</span>
                </div>
            </x-ui.card-content>

            <div class="p-6 bg-gradient-to-r from-background via-muted/10 to-background border-t border-border/40 flex flex-col sm:flex-row items-center justify-end gap-3 rounded-b-[2rem]">
                <a href="{{ route('offers.index') }}" class="h-11 px-6 rounded-2xl border border-border bg-background text-sm font-bold flex items-center justify-center hover:bg-muted transition-colors">Cancel</a>
                <button type="submit" class="h-11 px-6 rounded-2xl bg-primary text-primary-foreground text-sm font-black uppercase tracking-[0.2em] hover:-translate-y-0.5 hover:shadow-lg hover:shadow-primary/30 transition-all duration-300">
                    {{ $isEdit ? 'Update Offer' : 'Create Offer' }}
                </button>
            </div>
        </x-ui.card>
    </form>
</div>
