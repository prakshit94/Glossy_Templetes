<x-layouts.app pageTitle="Order Details">

    <div class="p-6 lg:p-10">
        <div class="max-w-5xl mx-auto space-y-6">
            <x-ui.card class="overflow-hidden border-border/60 shadow-2xl bg-card/30 backdrop-blur-2xl rounded-3xl">
                <div class="p-8 flex flex-col md:flex-row md:items-center justify-between gap-6">
                    <div class="flex items-center gap-5">
                        <div class="size-16 rounded-2xl bg-primary/10 border border-primary/20 text-primary flex items-center justify-center shadow-inner">
                            <x-ui.icon name="shopping-cart" size="8" />
                        </div>
                        <div>
                            <div class="flex items-center gap-3 mb-1">
                                <h3 class="text-2xl font-black text-foreground tracking-tight">{{ $order->order_no }}</h3>
                                @php
                                    $statusVariant = match($order->status) {
                                        'shipped', 'delivered' => 'success',
                                        'cancelled', 'returned' => 'destructive',
                                        'pending' => 'warning',
                                        default => 'outline'
                                    };
                                @endphp
                                <x-ui.badge variant="{{ $statusVariant }}" class="rounded-lg px-2 py-0.5 text-[10px] font-black uppercase tracking-widest">
                                    {{ $order->status }}
                                </x-ui.badge>
                            </div>
                            <p class="text-xs text-muted-foreground font-medium">
                                {{ strtoupper($order->type) }} ORDER • {{ optional($order->order_date)->format('M d, Y • h:i A') }}
                            </p>
                        </div>
                    </div>

                    <div class="flex flex-wrap items-center gap-3">
                        <a href="{{ route('orders.index') }}">
                            <x-ui.button variant="outline" size="sm" class="rounded-xl font-bold uppercase tracking-widest text-[10px]">
                                <x-ui.icon name="arrow-left" size="3" class="mr-2" /> Back
                            </x-ui.button>
                        </a>
                        @if($order->status === 'pending')
                            <form action="{{ route('orders.confirm', $order) }}" method="POST">
                                @csrf
                                <x-ui.button size="sm" class="rounded-xl font-bold uppercase tracking-widest text-[10px] bg-primary shadow-lg shadow-primary/20">
                                    <x-ui.icon name="check-circle" size="3" class="mr-2" /> Confirm
                                </x-ui.button>
                            </form>
                        @endif
                        @if($order->status === 'confirmed')
                            <form action="{{ route('orders.ship', $order) }}" method="POST">
                                @csrf
                                <x-ui.button size="sm" class="rounded-xl font-bold uppercase tracking-widest text-[10px] bg-emerald-500 hover:bg-emerald-600 text-white shadow-lg shadow-emerald-500/20">
                                    <x-ui.icon name="truck" size="3" class="mr-2" /> Ship
                                </x-ui.button>
                            </form>
                        @endif
                    </div>
                </div>
            </x-ui.card>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <x-ui.card class="md:col-span-2 overflow-hidden border-border/60 shadow-xl bg-card/30 backdrop-blur-xl rounded-3xl">
                    <div class="p-6 border-b border-border/40 bg-muted/5">
                        <h4 class="text-[10px] font-black uppercase tracking-[0.2em] text-primary">Order Meta</h4>
                    </div>
                    <div class="p-8 grid grid-cols-2 gap-8">
                        <div>
                            <p class="text-[9px] font-black uppercase text-muted-foreground tracking-widest mb-2">Party</p>
                            <p class="text-sm font-bold text-foreground">{{ $order->party?->name ?? 'N/A' }}</p>
                            <p class="text-[10px] text-muted-foreground uppercase">{{ $order->party?->type ?? 'N/A' }}</p>
                        </div>
                        <div>
                            <p class="text-[9px] font-black uppercase text-muted-foreground tracking-widest mb-2">Warehouse</p>
                            <p class="text-sm font-bold text-foreground">{{ $order->warehouse?->name ?? 'N/A' }}</p>
                        </div>
                    </div>
                </x-ui.card>

                <x-ui.card class="overflow-hidden border-border/60 shadow-xl bg-card/30 backdrop-blur-xl rounded-3xl">
                    <div class="p-6 border-b border-border/40 bg-muted/5">
                        <h4 class="text-[10px] font-black uppercase tracking-[0.2em] text-primary">Summary</h4>
                    </div>
                    <div class="p-6 space-y-4">
                        <div class="flex justify-between items-center">
                            <span class="text-xs text-muted-foreground">Line Items</span>
                            <span class="text-sm font-black text-foreground">{{ $order->items->count() }}</span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-xs text-muted-foreground">Net Amount</span>
                            <span class="text-sm font-black text-primary">{{ number_format((float) $order->net_amount, 2) }}</span>
                        </div>
                    </div>
                </x-ui.card>
            </div>

            <x-ui.card class="overflow-hidden border-border/60 shadow-xl bg-card/30 backdrop-blur-xl rounded-3xl">
                <div class="p-6 border-b border-border/40 bg-muted/5">
                    <h4 class="text-[10px] font-black uppercase tracking-[0.2em] text-primary">Itemized Order</h4>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="border-b border-border/40 bg-muted/5">
                                <th class="px-6 py-4 text-[10px] font-black uppercase tracking-widest text-muted-foreground">Product</th>
                                <th class="px-6 py-4 text-[10px] font-black uppercase tracking-widest text-muted-foreground text-right">Qty</th>
                                <th class="px-6 py-4 text-[10px] font-black uppercase tracking-widest text-muted-foreground text-right">Unit Price</th>
                                <th class="px-6 py-4 text-[10px] font-black uppercase tracking-widest text-muted-foreground text-right">Total</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-border/40">
                            @foreach($order->items as $item)
                                <tr class="hover:bg-muted/10 transition-colors">
                                    <td class="px-6 py-4">
                                        <span class="text-sm font-bold text-foreground">{{ $item->product?->name ?? 'Unknown Product' }}</span>
                                    </td>
                                    <td class="px-6 py-4 text-right text-sm font-semibold text-foreground">{{ $item->quantity }}</td>
                                    <td class="px-6 py-4 text-right text-sm font-semibold text-foreground">{{ number_format((float) $item->unit_price, 2) }}</td>
                                    <td class="px-6 py-4 text-right text-sm font-black text-primary">{{ number_format((float) $item->total_amount, 2) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </x-ui.card>
        </div>
    </div>
</x-layouts.app>
