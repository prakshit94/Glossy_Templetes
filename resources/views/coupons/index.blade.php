<x-layouts.app pageTitle="Coupons">
    <div class="p-6 lg:p-10 space-y-8" x-data="{ search: '' }">
        <div class="flex flex-col lg:flex-row lg:items-end justify-between gap-6">
            <div class="flex items-center gap-4">
                <div class="size-14 rounded-[1.25rem] bg-gradient-to-br from-primary/20 via-primary/10 to-background border border-primary/20 text-primary flex items-center justify-center shadow-inner shadow-primary/10">
                    <x-ui.icon name="gift" size="7" />
                </div>
                <div>
                    <p class="text-[10px] font-black uppercase tracking-[0.3em] text-primary/70 mb-2">Marketing Control</p>
                    <h1 class="text-3xl md:text-4xl font-black tracking-tighter text-foreground">Coupons</h1>
                    <p class="text-sm text-muted-foreground max-w-2xl">Manage promo codes, spending thresholds, expiration windows, and redemption limits from one place.</p>
                </div>
            </div>

            <a href="{{ route('coupons.create') }}" class="h-11 px-5 rounded-2xl bg-primary text-primary-foreground text-sm font-black uppercase tracking-[0.2em] hover:-translate-y-0.5 hover:shadow-lg hover:shadow-primary/30 transition-all duration-300 flex items-center justify-center gap-2">
                <x-ui.icon name="plus" size="4" />
                Create Coupon
            </a>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
            <div class="group relative p-6 rounded-[1.75rem] bg-card/40 border border-border/60 backdrop-blur-xl overflow-hidden shadow-xl">
                <div class="absolute inset-y-0 right-0 w-24 bg-primary/10 blur-3xl opacity-70 pointer-events-none"></div>
                <p class="text-[10px] font-black uppercase tracking-widest text-muted-foreground/70 mb-2">Total Coupons</p>
                <p class="text-3xl font-black tracking-tighter text-foreground">{{ number_format($coupons->total()) }}</p>
            </div>
            <div class="group relative p-6 rounded-[1.75rem] bg-card/40 border border-border/60 backdrop-blur-xl overflow-hidden shadow-xl">
                <div class="absolute inset-y-0 right-0 w-24 bg-emerald-500/10 blur-3xl opacity-70 pointer-events-none"></div>
                <p class="text-[10px] font-black uppercase tracking-widest text-muted-foreground/70 mb-2">Active Right Now</p>
                <p class="text-3xl font-black tracking-tighter text-emerald-500">{{ number_format($coupons->getCollection()->where('is_active', true)->count()) }}</p>
            </div>
            <div class="group relative p-6 rounded-[1.75rem] bg-card/40 border border-border/60 backdrop-blur-xl overflow-hidden shadow-xl">
                <div class="absolute inset-y-0 right-0 w-24 bg-orange-500/10 blur-3xl opacity-70 pointer-events-none"></div>
                <p class="text-[10px] font-black uppercase tracking-widest text-muted-foreground/70 mb-2">Used This Page</p>
                <p class="text-3xl font-black tracking-tighter text-orange-500">{{ number_format((int) $coupons->getCollection()->sum('used_count')) }}</p>
            </div>
        </div>

        <x-ui.card class="overflow-hidden border-border/60 shadow-2xl bg-card/30 backdrop-blur-2xl rounded-[2rem]">
            <x-ui.card-header class="border-b border-border/40 bg-gradient-to-r from-background via-muted/10 to-background p-8">
                <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4">
                    <div>
                        <p class="text-[10px] font-black uppercase tracking-[0.3em] text-muted-foreground/60 mb-2">Directory</p>
                        <h2 class="text-xl font-black tracking-tight text-foreground">Coupon Registry</h2>
                    </div>
                    <div class="relative group w-full lg:max-w-sm shrink-0">
                        <x-ui.icon name="search" size="4" class="absolute left-3 top-1/2 -translate-y-1/2 text-muted-foreground group-focus-within:text-primary transition-colors" />
                        <input type="text" x-model="search" placeholder="Search by promo code or type..."
                            class="pl-9 pr-4 h-11 rounded-2xl border border-border bg-background/50 focus:bg-background focus:ring-2 focus:ring-primary/20 transition-all w-full text-xs shadow-sm outline-none font-medium">
                    </div>
                </div>
            </x-ui.card-header>

            <x-ui.card-content class="p-0">
                <div x-show="search.trim().length > 0" class="px-6 py-3 border-b border-border/30 bg-muted/5 text-[11px] font-bold text-muted-foreground uppercase tracking-widest">
                    Client-side filter active
                </div>
                <div :class="search.trim().length > 0 ? '[&_tbody_tr]:hidden' : ''">
                    @include('coupons.partials.table', ['records' => $coupons])
                </div>
                <script>
                    document.addEventListener('alpine:init', () => {
                        Alpine.effect(() => {
                            const root = document.querySelector('[x-data]');
                            if (!root || !root.__x) return;
                            const query = String(root.__x.$data.search || '').trim().toLowerCase();
                            document.querySelectorAll('[data-coupon-row]').forEach((row) => {
                                const haystack = String(row.dataset.couponRow || '').toLowerCase();
                                row.style.display = !query || haystack.includes(query) ? '' : 'none';
                            });
                        });
                    });
                </script>
            </x-ui.card-content>
        </x-ui.card>
    </div>
</x-layouts.app>
