<x-layouts.app pageTitle="Coupons">
    <div class="p-6 lg:p-10" x-data="{ search: '', perPage: '10' }">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-8">
            <div class="flex items-center gap-4">
                <div class="size-14 rounded-2xl bg-gradient-to-tr from-primary/20 to-primary/5 border border-primary/10 text-primary flex items-center justify-center shadow-inner">
                    <x-ui.icon name="gift" size="7" />
                </div>
                <div>
                    <h1 class="text-3xl font-black tracking-tighter text-foreground">Coupons</h1>
                    <p class="text-xs font-bold text-muted-foreground uppercase tracking-widest">Manage promo codes and discounts</p>
                </div>
            </div>
            <a href="{{ route('coupons.create') }}" class="h-11 px-5 rounded-xl bg-primary text-primary-foreground text-sm font-black uppercase tracking-widest hover:-translate-y-0.5 hover:shadow-lg hover:shadow-primary/30 transition-all duration-300 flex items-center justify-center gap-2">
                <x-ui.icon name="plus" size="4" />
                Create Coupon
            </a>
        </div>

        <x-ui.card class="overflow-hidden border-border/60 shadow-2xl bg-card/30 backdrop-blur-2xl rounded-3xl">
            <x-ui.card-header class="border-b border-border/40 bg-muted/10 p-6">
                <div class="flex justify-end gap-4">
                    <div class="relative group w-full lg:max-w-xs shrink-0">
                        <x-ui.icon name="search" size="4" class="absolute left-3 top-1/2 -translate-y-1/2 text-muted-foreground group-focus-within:text-primary transition-colors" />
                        <input type="text" x-model="search" placeholder="Search coupons..."
                            class="pl-9 pr-4 py-2 rounded-xl border border-border bg-background/50 focus:bg-background focus:ring-2 focus:ring-primary/20 transition-all w-full text-xs shadow-sm outline-none">
                    </div>
                </div>
            </x-ui.card-header>

            <x-ui.card-content class="p-0">
                @include('coupons.partials.table', ['records' => $coupons])
            </x-ui.card-content>
        </x-ui.card>
    </div>
</x-layouts.app>
