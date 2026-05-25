@php
    $coupon = $coupon ?? null;
@endphp

<div class="p-6 lg:p-10 max-w-5xl mx-auto">

    <x-ui.card class="overflow-hidden border-border/60 shadow-2xl bg-card/30 backdrop-blur-2xl rounded-3xl">

        <!-- Header -->
        <x-ui.card-header class="border-b border-border/40 bg-muted/10 p-6">

            <div class="flex items-center gap-3">

                <div
                    class="size-10 rounded-2xl bg-primary/10 border border-primary/20 text-primary flex items-center justify-center shadow-inner">

                    <x-ui.icon :name="$pageIcon" size="5" />

                </div>

                <div>

                    <h3 class="text-sm font-black text-foreground uppercase tracking-widest">
                        {{ $pageTitle }}
                    </h3>

                    <p class="text-[11px] text-muted-foreground mt-1">
                        {{ $pageSubtitle }}
                    </p>

                </div>

            </div>

        </x-ui.card-header>

        <!-- Form -->
        <x-ui.card-content class="p-6">

            <form action="{{ $formAction }}"
                method="POST"
                class="space-y-8">

                @csrf

                @if($formMethod !== 'POST')
                    @method($formMethod)
                @endif

                <!-- Basic Info -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

                    <!-- Coupon Code -->
                    <div class="space-y-2">

                        <label
                            for="code"
                            class="text-[10px] font-black uppercase tracking-widest text-muted-foreground/80 ml-1">
                            Promo Code
                        </label>

                        <input
                            type="text"
                            name="code"
                            id="code"
                            value="{{ old('code', $coupon->code ?? '') }}"
                            required
                            placeholder="e.g. SUMMER50"
                            class="w-full h-11 px-4 rounded-xl border border-border bg-background/50 focus:bg-background focus:ring-2 focus:ring-primary/20 transition-all text-sm font-black uppercase text-primary outline-none">

                        @error('code')
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
                            Discount Type
                        </label>

                        <select
                            name="type"
                            id="type"
                            class="w-full h-11 px-4 rounded-xl border border-border bg-background/50 text-[11px] font-black uppercase tracking-widest focus:bg-background focus:ring-2 focus:ring-primary/20 transition-all outline-none">

                            <option
                                value="percentage"
                                {{ old('type', $coupon->type ?? '') == 'percentage' ? 'selected' : '' }}>
                                Percentage (%)
                            </option>

                            <option
                                value="fixed"
                                {{ old('type', $coupon->type ?? '') == 'fixed' ? 'selected' : '' }}>
                                Fixed Amount (₹)
                            </option>

                        </select>

                        @error('type')
                            <p class="text-xs text-destructive mt-1">
                                {{ $message }}
                            </p>
                        @enderror

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
                            name="value"
                            id="value"
                            value="{{ old('value', $coupon->value ?? '') }}"
                            required
                            placeholder="e.g. 10"
                            class="w-full h-11 px-4 rounded-xl border border-border bg-background/50 focus:bg-background focus:ring-2 focus:ring-primary/20 transition-all text-sm font-black text-primary outline-none">

                        @error('value')
                            <p class="text-xs text-destructive mt-1">
                                {{ $message }}
                            </p>
                        @enderror

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
                            name="max_discount"
                            id="max_discount"
                            value="{{ old('max_discount', $coupon->max_discount ?? '') }}"
                            placeholder="e.g. 500"
                            class="w-full h-11 px-4 rounded-xl border border-border bg-background/50 focus:bg-background focus:ring-2 focus:ring-primary/20 transition-all text-sm font-medium outline-none">

                        <p class="text-[10px] text-muted-foreground ml-1">
                            Used only for percentage discounts.
                        </p>

                        @error('max_discount')
                            <p class="text-xs text-destructive mt-1">
                                {{ $message }}
                            </p>
                        @enderror

                    </div>

                </div>

                <!-- Divider -->
                <div class="h-px bg-border/40"></div>

                <!-- Advanced -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">

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
                            name="min_spend"
                            id="min_spend"
                            value="{{ old('min_spend', $coupon->min_spend ?? 0) }}"
                            placeholder="e.g. 1000"
                            class="w-full h-11 px-4 rounded-xl border border-border bg-background/50 focus:bg-background focus:ring-2 focus:ring-primary/20 transition-all text-sm font-medium outline-none">

                        @error('min_spend')
                            <p class="text-xs text-destructive mt-1">
                                {{ $message }}
                            </p>
                        @enderror

                    </div>

                    <!-- Usage Limit -->
                    <div class="space-y-2">

                        <label
                            for="usage_limit"
                            class="text-[10px] font-black uppercase tracking-widest text-muted-foreground/80 ml-1">
                            Usage Limit
                        </label>

                        <input
                            type="number"
                            name="usage_limit"
                            id="usage_limit"
                            value="{{ old('usage_limit', $coupon->usage_limit ?? '') }}"
                            placeholder="e.g. 100"
                            class="w-full h-11 px-4 rounded-xl border border-border bg-background/50 focus:bg-background focus:ring-2 focus:ring-primary/20 transition-all text-sm font-medium outline-none">

                        @if($coupon)

                            <p class="text-[10px] text-muted-foreground ml-1">
                                Used {{ $coupon->used_count }} times so far.
                            </p>

                        @endif

                        @error('usage_limit')
                            <p class="text-xs text-destructive mt-1">
                                {{ $message }}
                            </p>
                        @enderror

                    </div>

                    <!-- Expiry -->
                    <div class="space-y-2">

                        <label
                            for="expiry_date"
                            class="text-[10px] font-black uppercase tracking-widest text-muted-foreground/80 ml-1">
                            Expiry Date
                        </label>

                        <input
                            type="date"
                            name="expiry_date"
                            id="expiry_date"
                            value="{{ old('expiry_date', $coupon && $coupon->expiry_date ? $coupon->expiry_date->format('Y-m-d') : '') }}"
                            class="w-full h-11 px-4 rounded-xl border border-border bg-background/50 focus:bg-background focus:ring-2 focus:ring-primary/20 transition-all text-sm font-medium outline-none">

                        @error('expiry_date')
                            <p class="text-xs text-destructive mt-1">
                                {{ $message }}
                            </p>
                        @enderror

                    </div>

                </div>

                <!-- Status -->
                <div
                    class="rounded-2xl border border-border/50 bg-muted/10 p-4 flex items-center gap-3">

                    <input
                        type="checkbox"
                        name="is_active"
                        id="is_active"
                        value="1"
                        {{ old('is_active', $coupon->is_active ?? true) ? 'checked' : '' }}
                        class="size-5 rounded border-border text-primary focus:ring-primary">

                    <label
                        for="is_active"
                        class="text-sm font-bold text-foreground cursor-pointer">

                        Coupon is active and available for validation

                    </label>

                </div>

                <!-- Actions -->
                <div class="flex flex-col sm:flex-row gap-3 pt-2">

                    <a href="{{ route('coupons.index') }}"
                        class="flex-1 h-12 rounded-xl border border-border bg-background hover:bg-muted transition-all flex items-center justify-center text-[10px] font-black uppercase tracking-widest">

                        Cancel

                    </a>

                    <x-ui.button
                        type="submit"
                        class="flex-1 h-12 rounded-xl font-black uppercase tracking-widest text-[10px] shadow-lg shadow-primary/20">

                        {{ $submitLabel }}

                    </x-ui.button>

                </div>

            </form>

        </x-ui.card-content>

    </x-ui.card>

</div>