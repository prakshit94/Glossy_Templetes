<x-layouts.app>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-foreground leading-tight">
            {{ __('Adjustment Details') }}
        </h2>
    </x-slot>

    <div class="p-6 lg:p-10">
        <div class="max-w-5xl mx-auto space-y-6">
            <!-- Header Card -->
            <x-ui.card class="overflow-hidden border-border/60 shadow-2xl bg-card/30 backdrop-blur-2xl rounded-3xl">
                <div class="p-8 flex flex-col md:flex-row md:items-center justify-between gap-6">
                    <div class="flex items-center gap-5">
                        <div class="size-16 rounded-2xl bg-primary/10 border border-primary/20 text-primary flex items-center justify-center shadow-inner">
                            <x-ui.icon name="sliders" size="8" />
                        </div>
                        <div>
                            <div class="flex items-center gap-3 mb-1">
                                <h3 class="text-2xl font-black text-foreground tracking-tight">{{ $adjustment->reference_no }}</h3>
                                <x-ui.badge variant="{{ $adjustment->status === 'approved' ? 'success' : ($adjustment->status === 'rejected' ? 'destructive' : 'warning') }}" class="rounded-lg px-2 py-0.5 text-[10px] font-black uppercase tracking-widest">
                                    {{ $adjustment->status }}
                                </x-ui.badge>
                            </div>
                            <p class="text-xs text-muted-foreground font-medium">Created on {{ $adjustment->created_at->format('M d, Y • h:i A') }}</p>
                        </div>
                    </div>

                    <div class="flex flex-wrap items-center gap-3">
                        <a href="{{ route('adjustments.index') }}">
                            <x-ui.button variant="outline" size="sm" class="rounded-xl font-bold uppercase tracking-widest text-[10px]">
                                <x-ui.icon name="arrow-left" size="3" class="mr-2" /> Back
                            </x-ui.button>
                        </a>
                        @if($adjustment->status === 'pending')
                            <form action="{{ route('adjustments.approve', $adjustment) }}" method="POST">
                                @csrf
                                <x-ui.button size="sm" class="rounded-xl font-bold uppercase tracking-widest text-[10px] bg-emerald-500 hover:bg-emerald-600 text-white shadow-lg shadow-emerald-500/20">
                                    <x-ui.icon name="check" size="3" class="mr-2" /> Approve
                                </x-ui.button>
                            </form>
                            <form action="{{ route('adjustments.reject', $adjustment) }}" method="POST">
                                @csrf
                                <x-ui.button size="sm" class="rounded-xl font-bold uppercase tracking-widest text-[10px] bg-red-500/10 text-red-500 hover:bg-red-500/20 shadow-none border-red-500/20">
                                    <x-ui.icon name="x" size="3" class="mr-2" /> Reject
                                </x-ui.button>
                            </form>
                        @endif
                    </div>
                </div>
            </x-ui.card>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <!-- Details -->
                <x-ui.card class="md:col-span-2 overflow-hidden border-border/60 shadow-xl bg-card/30 backdrop-blur-xl rounded-3xl">
                    <div class="p-6 border-b border-border/40 bg-muted/5">
                        <h4 class="text-[10px] font-black uppercase tracking-[0.2em] text-primary">Adjustment Details</h4>
                    </div>
                    <div class="p-8 grid grid-cols-2 gap-8">
                        <div>
                            <p class="text-[9px] font-black uppercase text-muted-foreground tracking-widest mb-2">Warehouse</p>
                            <p class="text-sm font-bold text-foreground">{{ $adjustment->warehouse->name }}</p>
                            <p class="text-[10px] text-muted-foreground">{{ $adjustment->warehouse->code }}</p>
                        </div>
                        <div>
                            <p class="text-[9px] font-black uppercase text-muted-foreground tracking-widest mb-2">Reason</p>
                            <p class="text-sm font-bold text-foreground">{{ $adjustment->reason }}</p>
                        </div>
                        <div class="col-span-2">
                            <p class="text-[9px] font-black uppercase text-muted-foreground tracking-widest mb-2">Performed By</p>
                            <div class="flex items-center gap-3">
                                <div class="size-8 rounded-full bg-muted flex items-center justify-center border border-border">
                                    <x-ui.icon name="user" size="4" class="text-muted-foreground" />
                                </div>
                                <span class="text-sm font-bold text-foreground">{{ $adjustment->user->name }}</span>
                            </div>
                        </div>
                    </div>
                </x-ui.card>

                <!-- Impact Summary -->
                <x-ui.card class="overflow-hidden border-border/60 shadow-xl bg-card/30 backdrop-blur-xl rounded-3xl">
                    <div class="p-6 border-b border-border/40 bg-muted/5">
                        <h4 class="text-[10px] font-black uppercase tracking-[0.2em] text-primary">Stock Impact</h4>
                    </div>
                    <div class="p-6 space-y-4">
                        @php
                            $totalDiff = $adjustment->items->sum('difference');
                        @endphp
                        <div class="flex justify-between items-center">
                            <span class="text-xs text-muted-foreground">Affected Items</span>
                            <span class="text-sm font-black text-foreground">{{ $adjustment->items->count() }}</span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-xs text-muted-foreground">Net Quantity Change</span>
                            <span class="text-sm font-black {{ $totalDiff > 0 ? 'text-emerald-500' : ($totalDiff < 0 ? 'text-red-500' : 'text-foreground') }}">
                                {{ $totalDiff > 0 ? '+' : '' }}{{ $totalDiff }}
                            </span>
                        </div>
                        <div class="pt-4 border-t border-border/40">
                            <p class="text-xs text-muted-foreground leading-relaxed">
                                Once approved, the central stock table for <strong>{{ $adjustment->warehouse->name }}</strong> will be updated to reflect the "New Quantity" values listed.
                            </p>
                        </div>
                    </div>
                </x-ui.card>
            </div>

            <!-- Items Table -->
            <x-ui.card class="overflow-hidden border-border/60 shadow-xl bg-card/30 backdrop-blur-xl rounded-3xl">
                <div class="p-6 border-b border-border/40 bg-muted/5">
                    <h4 class="text-[10px] font-black uppercase tracking-[0.2em] text-primary">Itemized Adjustments</h4>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="border-b border-border/40 bg-muted/5">
                                <th class="px-6 py-4 text-[10px] font-black uppercase tracking-widest text-muted-foreground">Product</th>
                                <th class="px-6 py-4 text-[10px] font-black uppercase tracking-widest text-muted-foreground text-center">Current Qty</th>
                                <th class="px-6 py-4 text-[10px] font-black uppercase tracking-widest text-muted-foreground text-center">New Qty</th>
                                <th class="px-6 py-4 text-[10px] font-black uppercase tracking-widest text-muted-foreground text-right">Difference</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-border/40">
                            @foreach($adjustment->items as $item)
                                <tr class="hover:bg-muted/10 transition-colors">
                                    <td class="px-6 py-4">
                                        <div class="flex flex-col">
                                            <span class="text-sm font-bold text-foreground">{{ $item->product->name }}</span>
                                            <span class="text-[10px] font-black uppercase tracking-widest text-muted-foreground/60">{{ $item->product->sku }}</span>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 text-center">
                                        <span class="text-xs font-bold text-muted-foreground">{{ $item->current_qty }}</span>
                                    </td>
                                    <td class="px-6 py-4 text-center">
                                        <span class="text-xs font-black text-foreground">{{ $item->new_qty }}</span>
                                    </td>
                                    <td class="px-6 py-4 text-right">
                                        <span class="inline-flex items-center px-3 py-1 rounded-lg {{ $item->difference > 0 ? 'bg-emerald-500/10 text-emerald-500' : ($item->difference < 0 ? 'bg-red-500/10 text-red-500' : 'bg-muted text-muted-foreground') }} text-xs font-black">
                                            {{ $item->difference > 0 ? '+' : '' }}{{ $item->difference }}
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
