<x-layouts.app pageTitle="Offers">
    <div class="p-6 lg:p-10 space-y-8">
        <div class="flex flex-col lg:flex-row lg:items-end justify-between gap-6">
            <div class="flex items-center gap-4">
                <div class="size-14 rounded-[1.25rem] bg-gradient-to-br from-primary/20 via-primary/10 to-background border border-primary/20 text-primary flex items-center justify-center shadow-inner shadow-primary/10">
                    <x-ui.icon name="tag" size="7" />
                </div>
                <div>
                    <p class="text-[10px] font-black uppercase tracking-[0.3em] text-primary/70 mb-2">Marketing Control</p>
                    <h1 class="text-3xl md:text-4xl font-black tracking-tighter text-foreground">Offers</h1>
                    <p class="text-sm text-muted-foreground max-w-2xl">Manage automatic order discounts and BOGO campaigns in the same visual language as the rest of the admin.</p>
                </div>
            </div>

            <a href="{{ route('offers.create') }}" class="h-11 px-5 rounded-2xl bg-primary text-primary-foreground text-sm font-black uppercase tracking-[0.2em] hover:-translate-y-0.5 hover:shadow-lg hover:shadow-primary/30 transition-all duration-300 flex items-center justify-center gap-2">
                <x-ui.icon name="plus" size="4" />
                Create Offer
            </a>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
            <div class="group relative p-6 rounded-[1.75rem] bg-card/40 border border-border/60 backdrop-blur-xl overflow-hidden shadow-xl">
                <div class="absolute inset-y-0 right-0 w-24 bg-primary/10 blur-3xl opacity-70 pointer-events-none"></div>
                <p class="text-[10px] font-black uppercase tracking-widest text-muted-foreground/70 mb-2">Total Offers</p>
                <p class="text-3xl font-black tracking-tighter text-foreground">{{ number_format($offers->total()) }}</p>
            </div>
            <div class="group relative p-6 rounded-[1.75rem] bg-card/40 border border-border/60 backdrop-blur-xl overflow-hidden shadow-xl">
                <div class="absolute inset-y-0 right-0 w-24 bg-emerald-500/10 blur-3xl opacity-70 pointer-events-none"></div>
                <p class="text-[10px] font-black uppercase tracking-widest text-muted-foreground/70 mb-2">Active On This Page</p>
                <p class="text-3xl font-black tracking-tighter text-emerald-500">{{ number_format($offers->getCollection()->where('is_active', true)->count()) }}</p>
            </div>
            <div class="group relative p-6 rounded-[1.75rem] bg-card/40 border border-border/60 backdrop-blur-xl overflow-hidden shadow-xl">
                <div class="absolute inset-y-0 right-0 w-24 bg-orange-500/10 blur-3xl opacity-70 pointer-events-none"></div>
                <p class="text-[10px] font-black uppercase tracking-widest text-muted-foreground/70 mb-2">BOGO Rules</p>
                <p class="text-3xl font-black tracking-tighter text-orange-500">{{ number_format($offers->getCollection()->where('type', 'bogo')->count()) }}</p>
            </div>
        </div>

        <x-ui.card class="overflow-hidden border-border/60 shadow-2xl bg-card/30 backdrop-blur-2xl rounded-[2rem]">
            <x-ui.card-header class="border-b border-border/40 bg-gradient-to-r from-background via-muted/10 to-background p-8">
                <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4">
                    <div>
                        <p class="text-[10px] font-black uppercase tracking-[0.3em] text-muted-foreground/60 mb-2">Directory</p>
                        <h2 class="text-xl font-black tracking-tight text-foreground">Offer Registry</h2>
                    </div>
                    <div class="text-[11px] font-bold uppercase tracking-widest text-muted-foreground">
                        Automatic pricing rules
                    </div>
                </div>
            </x-ui.card-header>

            <x-ui.card-content class="p-0">
                @if($offers->hasPages())
                    <div class="px-6 py-4 border-b border-border/40 bg-muted/10 flex flex-col md:flex-row md:items-center md:justify-between gap-3">
                        <p class="text-[11px] font-bold text-muted-foreground uppercase tracking-widest">
                            Showing {{ $offers->firstItem() ?? 0 }}-{{ $offers->lastItem() ?? 0 }} of {{ $offers->total() }}
                        </p>
                        {{ $offers->links() }}
                    </div>
                @endif

                <x-ui.table>
                    <x-ui.table-header class="bg-muted/20">
                        <x-ui.table-row class="border-b border-border/60">
                            <x-ui.table-head>Offer</x-ui.table-head>
                            <x-ui.table-head>Type</x-ui.table-head>
                            <x-ui.table-head>Rule</x-ui.table-head>
                            <x-ui.table-head>Schedule</x-ui.table-head>
                            <x-ui.table-head>Status</x-ui.table-head>
                            <x-ui.table-head class="text-right">Actions</x-ui.table-head>
                        </x-ui.table-row>
                    </x-ui.table-header>
                    <x-ui.table-body>
                        @forelse($offers as $offer)
                            <x-ui.table-row class="border-b border-border/40 group hover:bg-primary/[0.02] transition-colors">
                                <x-ui.table-cell>
                                    <div class="space-y-1">
                                        <p class="font-black text-foreground">{{ $offer->name }}</p>
                                        <p class="text-[10px] font-bold uppercase tracking-widest text-muted-foreground">Priority {{ $offer->priority }}</p>
                                    </div>
                                </x-ui.table-cell>
                                <x-ui.table-cell>
                                    <x-ui.badge :variant="$offer->type === 'order_discount' ? 'default' : 'success'" className="uppercase text-[9px] font-black tracking-[0.2em] px-3 py-1 rounded-xl">
                                        {{ $offer->type === 'order_discount' ? 'Order Discount' : 'BOGO' }}
                                    </x-ui.badge>
                                </x-ui.table-cell>
                                <x-ui.table-cell>
                                    <div class="space-y-1 text-sm text-foreground">
                                        @if($offer->type === 'order_discount')
                                            <p class="font-black">
                                                {{ $offer->discount_type === 'percentage' ? rtrim(rtrim(number_format((float) $offer->value, 2), '0'), '.') . '%' : '₹' . number_format((float) $offer->value, 2) }}
                                            </p>
                                            <p class="text-[10px] font-bold uppercase tracking-widest text-muted-foreground">
                                                @if((float) $offer->min_spend > 0)
                                                    Min spend ₹{{ number_format((float) $offer->min_spend, 2) }}
                                                @else
                                                    No minimum spend
                                                @endif
                                            </p>
                                        @else
                                            <p class="font-black">Buy {{ $offer->buy_qty }} Get {{ $offer->get_qty }}</p>
                                            <p class="text-[10px] font-bold uppercase tracking-widest text-muted-foreground">{{ $offer->product?->name ?? 'No product linked' }}</p>
                                        @endif
                                    </div>
                                </x-ui.table-cell>
                                <x-ui.table-cell>
                                    <div class="space-y-1">
                                        <p class="text-sm font-bold text-foreground">{{ $offer->starts_at?->format('d M Y h:i A') ?? 'Immediate' }}</p>
                                        <p class="text-[10px] font-bold uppercase tracking-widest text-muted-foreground">Until {{ $offer->ends_at?->format('d M Y h:i A') ?? 'No expiry' }}</p>
                                    </div>
                                </x-ui.table-cell>
                                <x-ui.table-cell>
                                    <x-ui.badge :variant="$offer->is_active ? 'success' : 'destructive'" className="uppercase text-[9px] font-black tracking-[0.2em] px-3 py-1 rounded-xl">
                                        {{ $offer->is_active ? 'Active' : 'Inactive' }}
                                    </x-ui.badge>
                                </x-ui.table-cell>
                                <x-ui.table-cell class="text-right">
                                    <div class="flex items-center justify-end gap-2 opacity-80 group-hover:opacity-100 transition-opacity">
                                        <a href="{{ route('offers.edit', $offer) }}" class="inline-flex items-center justify-center size-9 text-muted-foreground hover:text-primary hover:bg-primary/10 rounded-2xl border border-transparent hover:border-primary/20 transition-all">
                                            <x-ui.icon name="edit-3" size="4" />
                                        </a>
                                        <form action="{{ route('offers.destroy', $offer) }}" method="POST" onsubmit="return confirm('Delete this offer?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="inline-flex items-center justify-center size-9 text-muted-foreground hover:text-destructive hover:bg-destructive/10 rounded-2xl border border-transparent hover:border-destructive/20 transition-all">
                                                <x-ui.icon name="trash-2" size="4" />
                                            </button>
                                        </form>
                                    </div>
                                </x-ui.table-cell>
                            </x-ui.table-row>
                        @empty
                            <x-ui.table-row>
                                <x-ui.table-cell colspan="6" class="h-60 text-center">
                                    <div class="flex flex-col items-center justify-center gap-4 opacity-40">
                                        <div class="size-16 rounded-[1.5rem] bg-primary/5 border border-primary/10 flex items-center justify-center">
                                            <x-ui.icon name="tag" size="10" class="text-primary/40" />
                                        </div>
                                        <div class="space-y-1">
                                            <p class="text-sm font-black uppercase tracking-[0.2em]">No offers found</p>
                                            <p class="text-[10px] font-semibold text-muted-foreground uppercase tracking-widest">Create your first automatic pricing rule.</p>
                                        </div>
                                    </div>
                                </x-ui.table-cell>
                            </x-ui.table-row>
                        @endforelse
                    </x-ui.table-body>
                </x-ui.table>

                @if($offers->hasPages())
                    <div class="px-6 py-4 border-t border-border/40 bg-muted/10 flex flex-col md:flex-row md:items-center md:justify-between gap-3 rounded-b-[2rem]">
                        <p class="text-[11px] font-bold text-muted-foreground uppercase tracking-widest">
                            Page {{ $offers->currentPage() }} of {{ $offers->lastPage() }}
                        </p>
                        {{ $offers->links() }}
                    </div>
                @endif
            </x-ui.card-content>
        </x-ui.card>
    </div>
</x-layouts.app>
