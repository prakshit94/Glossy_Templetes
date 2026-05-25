@php
    $isEdit = isset($offer) && $offer;
@endphp

<div
    class="p-6 lg:p-10 max-w-6xl mx-auto"
    x-data="{
        offerType: '{{ old('type', $offer->type ?? 'order_discount') }}'
    }">

    <x-ui.card
        class="overflow-hidden border-border/60 shadow-2xl bg-card/30 backdrop-blur-2xl rounded-3xl">

        <!-- Header -->
        <x-ui.card-header class="border-b border-border/40 bg-muted/10 p-6">

            <div class="flex items-center gap-3">

                <div
                    class="size-10 rounded-2xl bg-primary/10 border border-primary/20 text-primary flex items-center justify-center shadow-inner">

                    <x-ui.icon
                        :name="$isEdit ? 'edit-3' : 'tag'"
                        size="5" />

                </div>

                <div>

                    <h3
                        class="text-sm font-black text-foreground uppercase tracking-widest">

                        {{ $isEdit ? 'Edit Offer' : 'Create Offer' }}

                    </h3>

                    <p class="text-[11px] text-muted-foreground mt-1">

                        Configure automatic discounts and BOGO campaigns without changing checkout logic.

                    </p>

                </div>

            </div>

        </x-ui.card-header>

        <!-- Form -->
        <x-ui.card-content class="p-6">

            <form
                action="{{ $isEdit ? route('offers.update', $offer) : route('offers.store') }}"
                method="POST"
                class="space-y-8">

                @csrf

                @if($isEdit)
                    @method('PUT')
                @endif

                <!-- Basic -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

                    <!-- Name -->
                    <div class="space-y-2 md:col-span-2">

                        <label
                            for="name"
                            class="text-[10px] font-black uppercase tracking-widest text-muted-foreground/80 ml-1">

                            Offer Name

                        </label>

                        <input
                            type="text"
                            name="name"
                            id="name"
                            value="{{ old('name', $offer->name ?? '') }}"
                            required
                            placeholder="e.g. Summer Sale 2026"
                            class="w-full h-11 px-4 rounded-xl border border-border bg-background/50 focus:bg-background focus:ring-2 focus:ring-primary/20 transition-all text-sm font-bold outline-none">

                        @error('name')

                            <p class="text-xs text-destructive mt-1">

                                {{ $message }}

                            </p>

                        @enderror

                    </div>

                    <!-- Type -->
                    <div class="space-y-2">

                        <label
                            for="type"
                            class="text-[10px] font-black uppercase tracking-widest text-muted-foreground/80 ml-1">

                            Offer Type

                        </label>

                        <select
                            name="type"
                            id="type"
                            x-model="offerType"
                            class="w-full h-11 px-4 rounded-xl border border-border bg-background/50 text-[11px] font-black uppercase tracking-widest focus:bg-background focus:ring-2 focus:ring-primary/20 transition-all outline-none">

                            <option value="order_discount">

                                Order Discount

                            </option>

                            <option value="bogo">

                                Buy One Get One

                            </option>

                        </select>

                        @error('type')

                            <p class="text-xs text-destructive mt-1">

                                {{ $message }}

                            </p>

                        @enderror

                    </div>

                    <!-- Priority -->
                    <div class="space-y-2">

                        <label
                            for="priority"
                            class="text-[10px] font-black uppercase tracking-widest text-muted-foreground/80 ml-1">

                            Priority

                        </label>

                        <input
                            type="number"
                            name="priority"
                            id="priority"
                            value="{{ old('priority', $offer->priority ?? 0) }}"
                            placeholder="0"
                            class="w-full h-11 px-4 rounded-xl border border-border bg-background/50 focus:bg-background focus:ring-2 focus:ring-primary/20 transition-all text-sm font-medium outline-none">

                        @error('priority')

                            <p class="text-xs text-destructive mt-1">

                                {{ $message }}

                            </p>

                        @enderror

                    </div>

                </div>

                <!-- Divider -->
                <div class="h-px bg-border/40"></div>

                <!-- Order Discount -->
                <div
                    x-show="offerType === 'order_discount'"
                    x-transition
                    class="grid grid-cols-1 md:grid-cols-2 gap-6">

                    <!-- Discount Type -->
                    <div class="space-y-2">

                        <label
                            for="discount_type"
                            class="text-[10px] font-black uppercase tracking-widest text-muted-foreground/80 ml-1">

                            Discount Type

                        </label>

                        <select
                            name="discount_type"
                            id="discount_type"
                            class="w-full h-11 px-4 rounded-xl border border-border bg-background/50 text-[11px] font-black uppercase tracking-widest focus:bg-background focus:ring-2 focus:ring-primary/20 transition-all outline-none">

                            <option
                                value="fixed"
                                {{ old('discount_type', $offer->discount_type ?? 'fixed') === 'fixed' ? 'selected' : '' }}>

                                Fixed Amount

                            </option>

                            <option
                                value="percentage"
                                {{ old('discount_type', $offer->discount_type ?? '') === 'percentage' ? 'selected' : '' }}>

                                Percentage

                            </option>

                        </select>

                    </div>

                    <!-- Value -->
                    <div class="space-y-2">

                        <label
                            for="value"
                            class="text-[10px] font-black uppercase tracking-widest text-muted-foreground/80 ml-1">

                            Discount Value

                        </label>

                        <input
                            type="number"
                            step="0.01"
                            min="0"
                            name="value"
                            id="value"
                            value="{{ old('value', $offer->value ?? 0) }}"
                            placeholder="e.g. 20"
                            class="w-full h-11 px-4 rounded-xl border border-border bg-background/50 focus:bg-background focus:ring-2 focus:ring-primary/20 transition-all text-sm font-black text-primary outline-none">

                    </div>

                    <!-- Min Spend -->
                    <div class="space-y-2">

                        <label
                            for="min_spend"
                            class="text-[10px] font-black uppercase tracking-widest text-muted-foreground/80 ml-1">

                            Minimum Spend

                        </label>

                        <input
                            type="number"
                            step="0.01"
                            min="0"
                            name="min_spend"
                            id="min_spend"
                            value="{{ old('min_spend', $offer->min_spend ?? 0) }}"
                            placeholder="e.g. 1000"
                            class="w-full h-11 px-4 rounded-xl border border-border bg-background/50 focus:bg-background focus:ring-2 focus:ring-primary/20 transition-all text-sm font-medium outline-none">

                    </div>

                    <!-- Max Discount -->
                    <div class="space-y-2">

                        <label
                            for="max_discount"
                            class="text-[10px] font-black uppercase tracking-widest text-muted-foreground/80 ml-1">

                            Max Discount

                        </label>

                        <input
                            type="number"
                            step="0.01"
                            min="0"
                            name="max_discount"
                            id="max_discount"
                            value="{{ old('max_discount', $offer->max_discount ?? '') }}"
                            placeholder="e.g. 500"
                            class="w-full h-11 px-4 rounded-xl border border-border bg-background/50 focus:bg-background focus:ring-2 focus:ring-primary/20 transition-all text-sm font-medium outline-none">

                    </div>

                </div>

                <!-- Divider -->
                <div
                    x-show="offerType === 'bogo'"
                    x-transition
                    class="h-px bg-border/40">
                </div>

                <!-- BOGO -->
                <div
                    x-show="offerType === 'bogo'"
                    x-transition
                    class="grid grid-cols-1 md:grid-cols-2 gap-6">

                    <!-- Product -->
                    <div class="space-y-2 md:col-span-2">

                        <label
                            for="product_id"
                            class="text-[10px] font-black uppercase tracking-widest text-muted-foreground/80 ml-1">

                            Product

                        </label>

                        <select
                            name="product_id"
                            id="product_id"
                            class="w-full h-11 px-4 rounded-xl border border-border bg-background/50 text-sm font-medium focus:bg-background focus:ring-2 focus:ring-primary/20 transition-all outline-none">

                            <option value="">

                                Select Product

                            </option>

                            @foreach($products as $product)

                                <option
                                    value="{{ $product->id }}"
                                    {{ (string) old('product_id', $offer->product_id ?? '') === (string) $product->id ? 'selected' : '' }}>

                                    {{ $product->name }} ({{ $product->sku }})

                                </option>

                            @endforeach

                        </select>

                    </div>

                    <!-- Buy Qty -->
                    <div class="space-y-2">

                        <label
                            for="buy_qty"
                            class="text-[10px] font-black uppercase tracking-widest text-muted-foreground/80 ml-1">

                            Buy Quantity

                        </label>

                        <input
                            type="number"
                            min="1"
                            name="buy_qty"
                            id="buy_qty"
                            value="{{ old('buy_qty', $offer->buy_qty ?? 1) }}"
                            placeholder="1"
                            class="w-full h-11 px-4 rounded-xl border border-border bg-background/50 focus:bg-background focus:ring-2 focus:ring-primary/20 transition-all text-sm font-medium outline-none">

                    </div>

                    <!-- Get Qty -->
                    <div class="space-y-2">

                        <label
                            for="get_qty"
                            class="text-[10px] font-black uppercase tracking-widest text-muted-foreground/80 ml-1">

                            Free Quantity

                        </label>

                        <input
                            type="number"
                            min="1"
                            name="get_qty"
                            id="get_qty"
                            value="{{ old('get_qty', $offer->get_qty ?? 1) }}"
                            placeholder="1"
                            class="w-full h-11 px-4 rounded-xl border border-border bg-background/50 focus:bg-background focus:ring-2 focus:ring-primary/20 transition-all text-sm font-medium outline-none">

                    </div>

                </div>

                <!-- Divider -->
                <div class="h-px bg-border/40"></div>

                <!-- Schedule -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

                    <!-- Starts -->
                    <div class="space-y-2">

                        <label
                            for="starts_at"
                            class="text-[10px] font-black uppercase tracking-widest text-muted-foreground/80 ml-1">

                            Starts At

                        </label>

                        <input
                            type="datetime-local"
                            name="starts_at"
                            id="starts_at"
                            value="{{ old('starts_at', isset($offer?->starts_at) ? $offer->starts_at->format('Y-m-d\\TH:i') : '') }}"
                            class="w-full h-11 px-4 rounded-xl border border-border bg-background/50 focus:bg-background focus:ring-2 focus:ring-primary/20 transition-all text-sm font-medium outline-none">

                    </div>

                    <!-- Ends -->
                    <div class="space-y-2">

                        <label
                            for="ends_at"
                            class="text-[10px] font-black uppercase tracking-widest text-muted-foreground/80 ml-1">

                            Ends At

                        </label>

                        <input
                            type="datetime-local"
                            name="ends_at"
                            id="ends_at"
                            value="{{ old('ends_at', isset($offer?->ends_at) ? $offer->ends_at->format('Y-m-d\\TH:i') : '') }}"
                            class="w-full h-11 px-4 rounded-xl border border-border bg-background/50 focus:bg-background focus:ring-2 focus:ring-primary/20 transition-all text-sm font-medium outline-none">

                    </div>

                </div>

                <!-- Status -->
                <div
                    class="rounded-2xl border border-border/50 bg-muted/10 p-4 flex items-center gap-3">

                    <input
                        type="checkbox"
                        name="is_active"
                        value="1"
                        {{ old('is_active', $offer->is_active ?? true) ? 'checked' : '' }}
                        class="size-5 rounded border-border text-primary focus:ring-primary">

                    <span
                        class="text-sm font-bold text-foreground">

                        Offer is active and eligible for automatic application

                    </span>

                </div>

                <!-- Actions -->
                <div class="flex flex-col sm:flex-row gap-3 pt-2">

                    <a
                        href="{{ route('offers.index') }}"
                        class="flex-1 h-12 rounded-xl border border-border bg-background hover:bg-muted transition-all flex items-center justify-center text-[10px] font-black uppercase tracking-widest">

                        Cancel

                    </a>

                    <x-ui.button
                        type="submit"
                        class="flex-1 h-12 rounded-xl font-black uppercase tracking-widest text-[10px] shadow-lg shadow-primary/20">

                        {{ $isEdit ? 'Update Offer' : 'Create Offer' }}

                    </x-ui.button>

                </div>

            </form>

        </x-ui.card-content>

    </x-ui.card>

</div>