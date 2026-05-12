<x-layouts.app>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Warehouse Details') }}
        </h2>
    </x-slot>

    <div class="p-6 lg:p-10">
        <div class="max-w-6xl mx-auto space-y-8">
            <!-- Header Card -->
            <x-ui.card class="overflow-hidden border-border/60 shadow-2xl bg-card/30 backdrop-blur-2xl rounded-3xl">
                <div class="p-8 flex flex-col md:flex-row md:items-center justify-between gap-6">
                    <div class="flex items-center gap-6">
                        <div class="size-20 rounded-3xl bg-primary/10 border border-primary/20 text-primary flex items-center justify-center shadow-inner">
                            <x-ui.icon name="warehouse" size="10" />
                        </div>
                        <div>
                            <div class="flex items-center gap-3 mb-2">
                                <h3 class="text-3xl font-black text-foreground tracking-tighter">{{ $warehouse->name }}</h3>
                                <x-ui.badge variant="{{ $warehouse->status === 'active' ? 'success' : 'outline' }}" class="rounded-lg px-2 py-0.5 text-[10px] font-black uppercase tracking-widest">
                                    {{ $warehouse->status }}
                                </x-ui.badge>
                            </div>
                            <div class="flex items-center gap-4 text-sm text-muted-foreground font-medium">
                                <span class="flex items-center gap-1.5"><x-ui.icon name="hash" size="3.5" /> {{ $warehouse->code }}</span>
                                <span class="flex items-center gap-1.5"><x-ui.icon name="map-pin" size="3.5" /> {{ $warehouse->location }}</span>
                            </div>
                        </div>
                    </div>

                    <div class="flex flex-wrap items-center gap-3">
                        <a href="{{ route('warehouses.index') }}">
                            <x-ui.button variant="outline" size="sm" class="rounded-xl font-bold uppercase tracking-widest text-[10px]">
                                <x-ui.icon name="arrow-left" size="3" class="mr-2" /> Back
                            </x-ui.button>
                        </a>
                        <a href="{{ route('warehouses.edit', $warehouse) }}">
                            <x-ui.button size="sm" class="rounded-xl font-bold uppercase tracking-widest text-[10px] shadow-lg shadow-primary/20">
                                <x-ui.icon name="edit-2" size="3" class="mr-2" /> Edit Details
                            </x-ui.button>
                        </a>
                    </div>
                </div>
            </x-ui.card>

            <div class="grid grid-cols-1 lg:grid-cols-4 gap-8">
                <!-- Sidebar Info -->
                <div class="lg:col-span-1 space-y-6">
                    <x-ui.card class="overflow-hidden border-border/60 shadow-xl bg-card/30 backdrop-blur-xl rounded-3xl p-6">
                        <h4 class="text-[10px] font-black uppercase tracking-[0.2em] text-primary mb-6">Warehouse Manager</h4>
                        <div class="flex items-center gap-4">
                            <div class="size-12 rounded-2xl bg-muted flex items-center justify-center border border-border shadow-sm">
                                <x-ui.icon name="user" size="6" class="text-muted-foreground" />
                            </div>
                            <div>
                                <p class="text-sm font-bold text-foreground">{{ $warehouse->manager?->name ?? 'Not Assigned' }}</p>
                                <p class="text-[10px] text-muted-foreground font-medium uppercase tracking-tight">{{ $warehouse->manager?->email ?? 'N/A' }}</p>
                            </div>
                        </div>
                    </x-ui.card>

                    <x-ui.card class="overflow-hidden border-border/60 shadow-xl bg-card/30 backdrop-blur-xl rounded-3xl p-6">
                        <h4 class="text-[10px] font-black uppercase tracking-[0.2em] text-primary mb-6">Inventory Summary</h4>
                        <div class="space-y-4">
                            <div class="flex justify-between items-center">
                                <span class="text-xs text-muted-foreground">Unique Products</span>
                                <span class="text-sm font-black text-foreground">{{ $warehouse->stocks->count() }}</span>
                            </div>
                            <div class="flex justify-between items-center">
                                <span class="text-xs text-muted-foreground">Total Stock Qty</span>
                                <span class="text-sm font-black text-foreground">{{ $warehouse->stocks->sum('quantity') }}</span>
                            </div>
                        </div>
                    </x-ui.card>
                </div>

                <!-- Stock Table -->
                <div class="lg:col-span-3">
                    <x-ui.card class="overflow-hidden border-border/60 shadow-xl bg-card/30 backdrop-blur-xl rounded-3xl">
                        <div class="p-6 border-b border-border/40 bg-muted/5 flex items-center justify-between">
                            <h4 class="text-[10px] font-black uppercase tracking-[0.2em] text-primary">Current Inventory Levels</h4>
                            <div class="relative w-64 group">
                                <x-ui.icon name="search" size="3.5" class="absolute left-3 top-1/2 -translate-y-1/2 text-muted-foreground group-focus-within:text-primary transition-colors" />
                                <input type="text" placeholder="Search stock..." class="w-full h-8 pl-9 pr-4 rounded-lg bg-background/50 border border-border text-[10px] focus:ring-2 focus:ring-primary/20 transition-all">
                            </div>
                        </div>
                        <div class="overflow-x-auto">
                            <table class="w-full text-left border-collapse">
                                <thead>
                                    <tr class="border-b border-border/40 bg-muted/5">
                                        <th class="px-6 py-4 text-[10px] font-black uppercase tracking-widest text-muted-foreground">Product Details</th>
                                        <th class="px-6 py-4 text-[10px] font-black uppercase tracking-widest text-muted-foreground text-center">SKU</th>
                                        <th class="px-6 py-4 text-[10px] font-black uppercase tracking-widest text-muted-foreground text-right">Available Qty</th>
                                        <th class="px-6 py-4 text-[10px] font-black uppercase tracking-widest text-muted-foreground text-right">In Transit</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-border/40">
                                    @forelse($warehouse->stocks as $stock)
                                        <tr class="hover:bg-muted/10 transition-colors">
                                            <td class="px-6 py-4">
                                                <span class="text-sm font-bold text-foreground">{{ $stock->product->name }}</span>
                                            </td>
                                            <td class="px-6 py-4 text-center">
                                                <span class="text-[10px] font-black uppercase tracking-widest text-muted-foreground/60">{{ $stock->product->sku }}</span>
                                            </td>
                                            <td class="px-6 py-4 text-right">
                                                <span class="inline-flex items-center px-3 py-1 rounded-lg bg-emerald-500/5 border border-emerald-500/10 text-sm font-black text-emerald-500">
                                                    {{ $stock->quantity }}
                                                </span>
                                            </td>
                                            <td class="px-6 py-4 text-right">
                                                <span class="text-xs font-bold text-muted-foreground">{{ $stock->in_transit_qty ?? 0 }}</span>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="4" class="px-6 py-12 text-center">
                                                <div class="flex flex-col items-center gap-2">
                                                    <x-ui.icon name="package-2" size="8" class="text-muted-foreground/20" />
                                                    <p class="text-xs text-muted-foreground font-medium uppercase tracking-widest">No stock found in this warehouse</p>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </x-ui.card>
                </div>
            </div>
        </div>
    </div>
</x-layouts.app>
