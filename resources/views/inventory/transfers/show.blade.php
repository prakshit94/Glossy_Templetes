<x-layouts.app pageTitle="Transfer Details: {{ $transfer->transfer_no }}">

    <div class="p-6 lg:p-10">
        <div class="max-w-5xl mx-auto space-y-6">
            <!-- Header Card -->
            <x-ui.card class="overflow-hidden border-border/60 shadow-2xl bg-card/30 backdrop-blur-2xl rounded-3xl">
                <div class="p-8 flex flex-col md:flex-row md:items-center justify-between gap-6">
                    <div class="flex items-center gap-5">
                        <div class="size-16 rounded-2xl bg-primary/10 border border-primary/20 text-primary flex items-center justify-center shadow-inner">
                            <x-ui.icon name="repeat" size="8" />
                        </div>
                        <div>
                            <div class="flex items-center gap-3 mb-1">
                                <h3 class="text-2xl font-black text-foreground tracking-tight">{{ $transfer->transfer_no }}</h3>
                                <x-ui.badge variant="{{ $transfer->status === 'received' ? 'success' : ($transfer->status === 'cancelled' ? 'destructive' : ($transfer->status === 'sent' ? 'warning' : 'outline')) }}" class="rounded-lg px-2 py-0.5 text-[10px] font-black uppercase tracking-widest">
                                    {{ $transfer->status }}
                                </x-ui.badge>
                            </div>
                            <p class="text-xs text-muted-foreground font-medium">Created on {{ $transfer->created_at->format('M d, Y • h:i A') }}</p>
                        </div>
                    </div>

                    <div class="flex flex-wrap items-center gap-3">
                        <a href="{{ route('transfers.index') }}">
                            <x-ui.button variant="outline" size="sm" class="rounded-xl font-bold uppercase tracking-widest text-[10px]">
                                <x-ui.icon name="arrow-left" size="3" class="mr-2" /> Back
                            </x-ui.button>
                        </a>
                        @if($transfer->status === 'draft')
                            <form action="{{ route('transfers.send', $transfer) }}" method="POST">
                                @csrf
                                <x-ui.button size="sm" class="rounded-xl font-bold uppercase tracking-widest text-[10px] bg-emerald-500 hover:bg-emerald-600 text-white shadow-lg shadow-emerald-500/20">
                                    <x-ui.icon name="send" size="3" class="mr-2" /> Send Transfer
                                </x-ui.button>
                            </form>
                        @endif
                        @if($transfer->status === 'sent')
                            <form action="{{ route('transfers.receive', $transfer) }}" method="POST">
                                @csrf
                                <x-ui.button size="sm" class="rounded-xl font-bold uppercase tracking-widest text-[10px] bg-primary shadow-lg shadow-primary/20">
                                    <x-ui.icon name="check-circle" size="3" class="mr-2" /> Receive Stock
                                </x-ui.button>
                            </form>
                        @endif
                    </div>
                </div>
            </x-ui.card>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <!-- Route Details -->
                <x-ui.card class="md:col-span-2 overflow-hidden border-border/60 shadow-xl bg-card/30 backdrop-blur-xl rounded-3xl">
                    <div class="p-6 border-b border-border/40 bg-muted/5">
                        <h4 class="text-[10px] font-black uppercase tracking-[0.2em] text-primary">Transfer Route</h4>
                    </div>
                    <div class="p-8 flex items-center justify-between relative">
                        <div class="flex flex-col items-center gap-3 z-10">
                            <div class="size-12 rounded-xl bg-background border border-border flex items-center justify-center shadow-sm">
                                <x-ui.icon name="home" size="5" class="text-muted-foreground" />
                            </div>
                            <div class="text-center">
                                <p class="text-[10px] font-black uppercase text-muted-foreground tracking-widest mb-1">Source</p>
                                <p class="text-sm font-bold text-foreground">{{ $transfer->fromWarehouse->name }}</p>
                                <p class="text-[10px] text-muted-foreground">{{ $transfer->fromWarehouse->code }}</p>
                            </div>
                        </div>

                        <div class="flex-1 flex flex-col items-center gap-2 px-10">
                            <div class="w-full h-px bg-gradient-to-r from-transparent via-primary/30 to-transparent relative">
                                <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 size-8 rounded-full bg-primary/10 border border-primary/20 flex items-center justify-center">
                                    <x-ui.icon name="arrow-right" size="4" class="text-primary" />
                                </div>
                            </div>
                            <span class="text-[9px] font-black uppercase tracking-widest text-primary/60">In Transit</span>
                        </div>

                        <div class="flex flex-col items-center gap-3 z-10">
                            <div class="size-12 rounded-xl bg-background border border-border flex items-center justify-center shadow-sm">
                                <x-ui.icon name="map-pin" size="5" class="text-primary" />
                            </div>
                            <div class="text-center">
                                <p class="text-[10px] font-black uppercase text-muted-foreground tracking-widest mb-1">Destination</p>
                                <p class="text-sm font-bold text-foreground">{{ $transfer->toWarehouse->name }}</p>
                                <p class="text-[10px] text-muted-foreground">{{ $transfer->toWarehouse->code }}</p>
                            </div>
                        </div>
                    </div>
                </x-ui.card>

                <!-- Summary Stats -->
                <x-ui.card class="overflow-hidden border-border/60 shadow-xl bg-card/30 backdrop-blur-xl rounded-3xl">
                    <div class="p-6 border-b border-border/40 bg-muted/5">
                        <h4 class="text-[10px] font-black uppercase tracking-[0.2em] text-primary">Summary</h4>
                    </div>
                    <div class="p-6 space-y-4">
                        <div class="flex justify-between items-center">
                            <span class="text-xs text-muted-foreground">Total Items</span>
                            <span class="text-sm font-black text-foreground">{{ $transfer->items->count() }}</span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-xs text-muted-foreground">Total Quantity</span>
                            <span class="text-sm font-black text-foreground">{{ $transfer->items->sum('quantity') }}</span>
                        </div>
                        <div class="pt-4 border-t border-border/40">
                            <p class="text-[9px] font-bold text-muted-foreground uppercase tracking-widest mb-2">Internal Note</p>
                            <p class="text-xs text-foreground italic">No notes provided for this transfer.</p>
                        </div>
                    </div>
                </x-ui.card>
            </div>

            <!-- Items Table -->
            <x-ui.card class="overflow-hidden border-border/60 shadow-xl bg-card/30 backdrop-blur-xl rounded-3xl">
                <div class="p-6 border-b border-border/40 bg-muted/5 flex items-center justify-between">
                    <h4 class="text-[10px] font-black uppercase tracking-[0.2em] text-primary">Itemized List</h4>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="border-b border-border/40 bg-muted/5">
                                <th class="px-6 py-4 text-[10px] font-black uppercase tracking-widest text-muted-foreground">#</th>
                                <th class="px-6 py-4 text-[10px] font-black uppercase tracking-widest text-muted-foreground">Product Details</th>
                                <th class="px-6 py-4 text-[10px] font-black uppercase tracking-widest text-muted-foreground text-right">Transfer Qty</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-border/40">
                            @foreach($transfer->items as $index => $item)
                                <tr class="hover:bg-muted/10 transition-colors">
                                    <td class="px-6 py-4 text-xs font-bold text-muted-foreground">{{ $index + 1 }}</td>
                                    <td class="px-6 py-4">
                                        <div class="flex flex-col">
                                            <span class="text-sm font-bold text-foreground">{{ $item->product->name }}</span>
                                            <span class="text-[10px] font-black uppercase tracking-widest text-muted-foreground/60">{{ $item->product->sku }}</span>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 text-right">
                                        <span class="inline-flex items-center px-3 py-1 rounded-lg bg-primary/5 border border-primary/10 text-sm font-black text-primary">
                                            {{ $item->quantity }}
                                        </span>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </x-ui.card>
        </div>
    </div>
</x-layouts.app>
