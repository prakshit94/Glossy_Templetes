@php
    $coupon = $coupon ?? null;
@endphp

<div class="p-6 lg:p-10 max-w-5xl mx-auto space-y-8">
    <div class="flex flex-col lg:flex-row lg:items-end justify-between gap-6">
        <div class="flex items-center gap-4">
            <div class="size-14 rounded-[1.25rem] bg-gradient-to-br from-primary/20 via-primary/10 to-background border border-primary/20 text-primary flex items-center justify-center shadow-inner shadow-primary/10">
                <x-ui.icon :name="$pageIcon" size="7" />
            </div>
            <div>
                <p class="text-[10px] font-black uppercase tracking-[0.3em] text-primary/70 mb-2">Marketing Control</p>
                <h1 class="text-3xl font-black tracking-tighter text-foreground">{{ $pageTitle }}</h1>
                <p class="text-sm text-muted-foreground max-w-2xl">{{ $pageSubtitle }}</p>
            </div>
        </div>
        <a href="{{ route('coupons.index') }}" class="text-[11px] font-black text-muted-foreground hover:text-foreground transition-colors uppercase tracking-[0.2em]">
            Back to Coupons
        </a>
    </div>

    <form action="{{ $formAction }}" method="POST">
        @csrf
        @if($formMethod !== 'POST')
            @method($formMethod)
        @endif

        <x-ui.card class="overflow-hidden border-border/60 shadow-2xl bg-card/30 backdrop-blur-2xl rounded-[2rem]">
            <x-ui.card-content class="p-6 md:p-8 space-y-8">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="space-y-2">
                        <label for="code" class="text-[10px] font-black uppercase tracking-widest text-muted-foreground ml-1">Promo Code</label>
                        <input type="text" name="code" id="code" value="{{ old('code', $coupon->code ?? '') }}" required class="w-full h-11 px-4 rounded-2xl border border-border bg-background/50 focus:bg-background text-sm font-black text-primary uppercase outline-none focus:ring-2 focus:ring-primary/20" placeholder="e.g. SUMMER50">
                        @error('code') <p class="text-xs text-destructive mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div class="space-y-2">
                        <label for="type" class="text-[10px] font-black uppercase tracking-widest text-muted-foreground ml-1">Discount Type</label>
                        <select name="type" id="type" class="w-full h-11 px-4 rounded-2xl border border-border bg-background/50 focus:bg-background text-sm font-bold text-foreground outline-none focus:ring-2 focus:ring-primary/20">
                            <option value="percentage" {{ old('type', $coupon->type ?? '') == 'percentage' ? 'selected' : '' }}>Percentage (%)</option>
                            <option value="fixed" {{ old('type', $coupon->type ?? '') == 'fixed' ? 'selected' : '' }}>Fixed Amount (₹)</option>
                        </select>
                        @error('type') <p class="text-xs text-destructive mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div class="space-y-2">
                        <label for="value" class="text-[10px] font-black uppercase tracking-widest text-muted-foreground ml-1">Discount Value</label>
                        <input type="number" step="0.01" name="value" id="value" value="{{ old('value', $coupon->value ?? '') }}" required class="w-full h-11 px-4 rounded-2xl border border-border bg-background/50 focus:bg-background text-sm font-bold text-foreground outline-none focus:ring-2 focus:ring-primary/20" placeholder="e.g. 10">
                        @error('value') <p class="text-xs text-destructive mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div class="space-y-2">
                        <label for="max_discount" class="text-[10px] font-black uppercase tracking-widest text-muted-foreground ml-1">Max Discount Amount</label>
                        <input type="number" step="0.01" name="max_discount" id="max_discount" value="{{ old('max_discount', $coupon->max_discount ?? '') }}" class="w-full h-11 px-4 rounded-2xl border border-border bg-background/50 focus:bg-background text-sm font-bold text-foreground outline-none focus:ring-2 focus:ring-primary/20" placeholder="e.g. 500">
                        <p class="text-[10px] text-muted-foreground ml-1">Used when the discount type is percentage.</p>
                        @error('max_discount') <p class="text-xs text-destructive mt-1">{{ $message }}</p> @enderror
                    </div>
                </div>

                <div class="h-px bg-border/40 w-full"></div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div class="space-y-2">
                        <label for="min_spend" class="text-[10px] font-black uppercase tracking-widest text-muted-foreground ml-1">Minimum Spend</label>
                        <input type="number" step="0.01" name="min_spend" id="min_spend" value="{{ old('min_spend', $coupon->min_spend ?? 0) }}" class="w-full h-11 px-4 rounded-2xl border border-border bg-background/50 focus:bg-background text-sm font-bold text-foreground outline-none focus:ring-2 focus:ring-primary/20">
                        @error('min_spend') <p class="text-xs text-destructive mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div class="space-y-2">
                        <label for="usage_limit" class="text-[10px] font-black uppercase tracking-widest text-muted-foreground ml-1">Usage Limit</label>
                        <input type="number" name="usage_limit" id="usage_limit" value="{{ old('usage_limit', $coupon->usage_limit ?? '') }}" class="w-full h-11 px-4 rounded-2xl border border-border bg-background/50 focus:bg-background text-sm font-bold text-foreground outline-none focus:ring-2 focus:ring-primary/20" placeholder="e.g. 100">
                        @if($coupon)
                            <p class="text-[10px] text-muted-foreground ml-1">Used {{ $coupon->used_count }} times so far.</p>
                        @endif
                        @error('usage_limit') <p class="text-xs text-destructive mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div class="space-y-2">
                        <label for="expiry_date" class="text-[10px] font-black uppercase tracking-widest text-muted-foreground ml-1">Expiry Date</label>
                        <input type="date" name="expiry_date" id="expiry_date" value="{{ old('expiry_date', $coupon && $coupon->expiry_date ? $coupon->expiry_date->format('Y-m-d') : '') }}" class="w-full h-11 px-4 rounded-2xl border border-border bg-background/50 focus:bg-background text-sm font-bold text-foreground outline-none focus:ring-2 focus:ring-primary/20">
                        @error('expiry_date') <p class="text-xs text-destructive mt-1">{{ $message }}</p> @enderror
                    </div>
                </div>

                <div class="rounded-[1.5rem] border border-border/50 bg-muted/10 p-4 flex items-center gap-3">
                    <input type="checkbox" name="is_active" id="is_active" value="1" {{ old('is_active', $coupon->is_active ?? true) ? 'checked' : '' }} class="size-5 rounded border-border text-primary focus:ring-primary">
                    <label for="is_active" class="text-sm font-bold text-foreground cursor-pointer">Coupon is active and available for validation</label>
                </div>
            </x-ui.card-content>

            <div class="p-6 bg-gradient-to-r from-background via-muted/10 to-background border-t border-border/40 flex flex-col sm:flex-row justify-end gap-3 rounded-b-[2rem]">
                <a href="{{ route('coupons.index') }}" class="h-11 px-6 rounded-2xl border border-border bg-background text-sm font-bold flex items-center justify-center hover:bg-muted transition-colors">Cancel</a>
                <button type="submit" class="h-11 px-8 rounded-2xl bg-primary text-primary-foreground text-sm font-black uppercase tracking-[0.2em] hover:-translate-y-0.5 hover:shadow-lg hover:shadow-primary/30 transition-all duration-300">
                    {{ $submitLabel }}
                </button>
            </div>
        </x-ui.card>
    </form>
</div>
