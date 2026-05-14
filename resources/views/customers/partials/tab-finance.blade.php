{{-- ══ TAB: Finance ══ --}}
<div x-show="activeTab === 'finance'" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-4" x-transition:enter-end="opacity-100 translate-y-0" x-cloak>
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <x-ui.card class="p-8 rounded-3xl border-border/40 shadow-xl bg-card/60 backdrop-blur-xl relative overflow-hidden">
            <div class="absolute top-0 right-0 p-8 opacity-5 pointer-events-none">
                <x-ui.icon name="file-text" size="24" />
            </div>
            <h4 class="text-[11px] font-black uppercase tracking-[0.2em] text-muted-foreground mb-8 flex items-center gap-2">
                <span class="size-2 rounded-full bg-blue-500 inline-block shadow-lg shadow-blue-500/50"></span> Tax Details
            </h4>
            <div class="space-y-6">
                <div>
                    <p class="text-[10px] font-black uppercase tracking-widest text-muted-foreground mb-2">GST Number</p>
                    <p class="text-lg font-mono font-bold text-foreground">{{ $customer->gst_no ?: 'Not Provided' }}</p>
                </div>
                <div>
                    <p class="text-[10px] font-black uppercase tracking-widest text-muted-foreground mb-2">PAN Number</p>
                    <p class="text-lg font-mono font-bold text-foreground">{{ $customer->pan_no ?: 'Not Provided' }}</p>
                </div>
            </div>
        </x-ui.card>

        <x-ui.card class="p-8 rounded-3xl border-border/40 shadow-xl bg-card/60 backdrop-blur-xl relative overflow-hidden">
            <div class="absolute top-0 right-0 p-8 opacity-5 pointer-events-none">
                <x-ui.icon name="credit-card" size="24" />
            </div>
            <h4 class="text-[11px] font-black uppercase tracking-[0.2em] text-muted-foreground mb-8 flex items-center gap-2">
                <span class="size-2 rounded-full bg-emerald-500 inline-block shadow-lg shadow-emerald-500/50"></span> Credit Policy
            </h4>
            <div class="space-y-6">
                <div>
                    <p class="text-[10px] font-black uppercase tracking-widest text-muted-foreground mb-2">Credit Limit</p>
                    <p class="text-3xl font-black text-emerald-500">₹{{ number_format($customer->credit_limit, 2) }}</p>
                </div>
                <div>
                    <p class="text-[10px] font-black uppercase tracking-widest text-muted-foreground mb-2">Payment Terms</p>
                    <p class="text-xl font-bold text-foreground">{{ $customer->credit_days ?: 0 }} <span class="text-sm text-muted-foreground">Days</span></p>
                </div>
            </div>
        </x-ui.card>
    </div>
</div>
