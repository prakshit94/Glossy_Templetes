<x-layouts.app pageTitle="Create Coupon">
    <div class="p-6 lg:p-10 max-w-4xl mx-auto">
        <div class="flex items-center justify-between mb-8">
            <div class="flex items-center gap-4">
                <div class="size-12 rounded-2xl bg-primary/10 border border-primary/20 text-primary flex items-center justify-center shadow-inner">
                    <x-ui.icon name="gift" size="6" />
                </div>
                <div>
                    <h1 class="text-2xl font-black tracking-tight text-foreground">Create Coupon</h1>
                    <p class="text-xs font-bold text-muted-foreground uppercase tracking-widest">Add a new promo code</p>
                </div>
            </div>
            <a href="{{ route('coupons.index') }}" class="text-xs font-bold text-muted-foreground hover:text-foreground transition-colors uppercase tracking-widest">
                &larr; Back to List
            </a>
        </div>

        <form action="{{ route('coupons.store') }}" method="POST">
            @csrf
            <x-ui.card class="overflow-hidden border-border/60 shadow-xl bg-card/30 backdrop-blur-2xl rounded-3xl">
                <x-ui.card-content class="p-6 md:p-8 space-y-8">
                    {{-- Basic Info --}}
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="space-y-2">
                            <label for="code" class="text-[10px] font-black uppercase tracking-widest text-muted-foreground ml-1">Promo Code</label>
                            <input type="text" name="code" id="code" value="{{ old('code') }}" required class="w-full h-11 px-4 rounded-xl border border-border bg-background/50 focus:bg-background text-sm font-black text-primary uppercase outline-none focus:ring-2 focus:ring-primary/20" placeholder="e.g. SUMMER50">
                            @error('code') <p class="text-xs text-destructive mt-1">{{ $message }}</p> @enderror
                        </div>
                        
                        <div class="space-y-2">
                            <label for="type" class="text-[10px] font-black uppercase tracking-widest text-muted-foreground ml-1">Discount Type</label>
                            <select name="type" id="type" class="w-full h-11 px-4 rounded-xl border border-border bg-background/50 focus:bg-background text-sm font-bold text-foreground outline-none focus:ring-2 focus:ring-primary/20">
                                <option value="percentage" {{ old('type') == 'percentage' ? 'selected' : '' }}>Percentage (%)</option>
                                <option value="fixed" {{ old('type') == 'fixed' ? 'selected' : '' }}>Fixed Amount (₹)</option>
                            </select>
                            @error('type') <p class="text-xs text-destructive mt-1">{{ $message }}</p> @enderror
                        </div>
                        
                        <div class="space-y-2">
                            <label for="value" class="text-[10px] font-black uppercase tracking-widest text-muted-foreground ml-1">Discount Value</label>
                            <input type="number" step="0.01" name="value" id="value" value="{{ old('value') }}" required class="w-full h-11 px-4 rounded-xl border border-border bg-background/50 focus:bg-background text-sm font-bold text-foreground outline-none focus:ring-2 focus:ring-primary/20" placeholder="e.g. 10">
                            @error('value') <p class="text-xs text-destructive mt-1">{{ $message }}</p> @enderror
                        </div>

                        <div class="space-y-2">
                            <label for="max_discount" class="text-[10px] font-black uppercase tracking-widest text-muted-foreground ml-1">Max Discount Amount (₹)</label>
                            <input type="number" step="0.01" name="max_discount" id="max_discount" value="{{ old('max_discount') }}" class="w-full h-11 px-4 rounded-xl border border-border bg-background/50 focus:bg-background text-sm font-bold text-foreground outline-none focus:ring-2 focus:ring-primary/20" placeholder="e.g. 500 (Optional)">
                            <p class="text-[9px] text-muted-foreground ml-1">Applies only for percentage discounts</p>
                            @error('max_discount') <p class="text-xs text-destructive mt-1">{{ $message }}</p> @enderror
                        </div>
                    </div>

                    <div class="h-px bg-border/40 w-full"></div>

                    {{-- Conditions --}}
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="space-y-2">
                            <label for="min_spend" class="text-[10px] font-black uppercase tracking-widest text-muted-foreground ml-1">Minimum Spend (₹)</label>
                            <input type="number" step="0.01" name="min_spend" id="min_spend" value="{{ old('min_spend', 0) }}" class="w-full h-11 px-4 rounded-xl border border-border bg-background/50 focus:bg-background text-sm font-bold text-foreground outline-none focus:ring-2 focus:ring-primary/20">
                            @error('min_spend') <p class="text-xs text-destructive mt-1">{{ $message }}</p> @enderror
                        </div>

                        <div class="space-y-2">
                            <label for="usage_limit" class="text-[10px] font-black uppercase tracking-widest text-muted-foreground ml-1">Usage Limit</label>
                            <input type="number" name="usage_limit" id="usage_limit" value="{{ old('usage_limit') }}" class="w-full h-11 px-4 rounded-xl border border-border bg-background/50 focus:bg-background text-sm font-bold text-foreground outline-none focus:ring-2 focus:ring-primary/20" placeholder="e.g. 100 (Optional)">
                            @error('usage_limit') <p class="text-xs text-destructive mt-1">{{ $message }}</p> @enderror
                        </div>

                        <div class="space-y-2">
                            <label for="expiry_date" class="text-[10px] font-black uppercase tracking-widest text-muted-foreground ml-1">Expiry Date</label>
                            <input type="date" name="expiry_date" id="expiry_date" value="{{ old('expiry_date') }}" class="w-full h-11 px-4 rounded-xl border border-border bg-background/50 focus:bg-background text-sm font-bold text-foreground outline-none focus:ring-2 focus:ring-primary/20">
                            @error('expiry_date') <p class="text-xs text-destructive mt-1">{{ $message }}</p> @enderror
                        </div>
                    </div>

                    <div class="space-y-2 flex items-center gap-3">
                        <input type="checkbox" name="is_active" id="is_active" value="1" {{ old('is_active', true) ? 'checked' : '' }} class="size-5 rounded border-border text-primary focus:ring-primary">
                        <label for="is_active" class="text-sm font-bold text-foreground cursor-pointer">Active</label>
                    </div>

                </x-ui.card-content>
                <div class="p-6 bg-muted/10 border-t border-border/40 flex justify-end gap-3 rounded-b-3xl">
                    <a href="{{ route('coupons.index') }}" class="h-11 px-6 rounded-xl border border-border bg-background text-sm font-bold flex items-center justify-center hover:bg-muted transition-colors">Cancel</a>
                    <button type="submit" class="h-11 px-8 rounded-xl bg-primary text-primary-foreground text-sm font-black uppercase tracking-widest hover:-translate-y-0.5 hover:shadow-lg hover:shadow-primary/30 transition-all duration-300">
                        Create Coupon
                    </button>
                </div>
            </x-ui.card>
        </form>
    </div>
</x-layouts.app>
