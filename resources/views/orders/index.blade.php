<x-layouts.app pageTitle="Orders">

    <div class="p-6 lg:p-10">
        <div class="max-w-7xl mx-auto space-y-8">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                <div class="group relative p-6 rounded-3xl bg-card/40 border border-border/60 backdrop-blur-xl hover:bg-primary/5 transition-all duration-500 overflow-hidden shadow-2xl">
                    <div class="absolute top-0 right-0 -mr-8 -mt-8 size-32 bg-primary/10 blur-[50px] rounded-full group-hover:bg-primary/20 transition-all duration-500"></div>
                    <div class="flex items-center gap-5 relative z-10">
                        <div class="size-14 rounded-2xl bg-gradient-to-tr from-primary/20 to-primary/5 border border-primary/10 text-primary flex items-center justify-center shadow-inner">
                            <x-ui.icon name="shopping-cart" size="7" />
                        </div>
                        <div>
                            <p class="text-[10px] font-black uppercase tracking-widest text-muted-foreground/60 mb-1">Total Orders</p>
                            <div class="text-3xl font-black tracking-tighter text-foreground">{{ number_format($stats['total'] ?? 0) }}</div>
                        </div>
                    </div>
                </div>

                <div class="group relative p-6 rounded-3xl bg-card/40 border border-border/60 backdrop-blur-xl hover:bg-orange-500/5 transition-all duration-500 overflow-hidden shadow-2xl">
                    <div class="absolute top-0 right-0 -mr-8 -mt-8 size-32 bg-orange-500/10 blur-[50px] rounded-full group-hover:bg-orange-500/20 transition-all duration-500"></div>
                    <div class="flex items-center gap-5 relative z-10">
                        <div class="size-14 rounded-2xl bg-gradient-to-tr from-orange-500/20 to-orange-500/5 border border-orange-500/10 text-orange-500 flex items-center justify-center shadow-inner">
                            <x-ui.icon name="clock" size="7" />
                        </div>
                        <div>
                            <p class="text-[10px] font-black uppercase tracking-widest text-muted-foreground/60 mb-1">Pending</p>
                            <div class="text-3xl font-black tracking-tighter text-orange-500">{{ number_format($stats['pending'] ?? 0) }}</div>
                        </div>
                    </div>
                </div>

                <div class="group relative p-6 rounded-3xl bg-card/40 border border-border/60 backdrop-blur-xl hover:bg-blue-500/5 transition-all duration-500 overflow-hidden shadow-2xl">
                    <div class="absolute top-0 right-0 -mr-8 -mt-8 size-32 bg-blue-500/10 blur-[50px] rounded-full group-hover:bg-blue-500/20 transition-all duration-500"></div>
                    <div class="flex items-center gap-5 relative z-10">
                        <div class="size-14 rounded-2xl bg-gradient-to-tr from-blue-500/20 to-blue-500/5 border border-blue-500/10 text-blue-500 flex items-center justify-center shadow-inner">
                            <x-ui.icon name="settings" size="7" />
                        </div>
                        <div>
                            <p class="text-[10px] font-black uppercase tracking-widest text-muted-foreground/60 mb-1">Processing</p>
                            <div class="text-3xl font-black tracking-tighter text-blue-500">{{ number_format($stats['processing'] ?? 0) }}</div>
                        </div>
                    </div>
                </div>

                <div class="group relative p-6 rounded-3xl bg-card/40 border border-border/60 backdrop-blur-xl hover:bg-emerald-500/5 transition-all duration-500 overflow-hidden shadow-2xl">
                    <div class="absolute top-0 right-0 -mr-8 -mt-8 size-32 bg-emerald-500/10 blur-[50px] rounded-full group-hover:bg-emerald-500/20 transition-all duration-500"></div>
                    <div class="flex items-center gap-5 relative z-10">
                        <div class="size-14 rounded-2xl bg-gradient-to-tr from-emerald-500/20 to-emerald-500/5 border border-emerald-500/10 text-emerald-500 flex items-center justify-center shadow-inner">
                            <x-ui.icon name="truck" size="7" />
                        </div>
                        <div>
                            <p class="text-[10px] font-black uppercase tracking-widest text-muted-foreground/60 mb-1">Shipped</p>
                            <div class="text-3xl font-black tracking-tighter text-emerald-500">{{ number_format($stats['shipped'] ?? 0) }}</div>
                        </div>
                    </div>
                </div>
            </div>

            <x-ui.card class="overflow-hidden border-border/60 shadow-2xl bg-card/30 backdrop-blur-2xl rounded-3xl">
                <x-ui.card-header class="border-b border-border/40 bg-muted/10 p-6">
                    <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4">
                        <form method="GET" action="{{ route('orders.index') }}" class="flex flex-col sm:flex-row gap-2 w-full lg:max-w-2xl">
                            <input type="text" name="search" value="{{ request('search') }}" placeholder="Search by order number..."
                                class="h-10 px-4 rounded-xl border border-border bg-background/50 focus:bg-background focus:ring-2 focus:ring-primary/20 transition-all text-sm w-full">
                            <select name="status" class="h-10 px-3 rounded-xl border border-border bg-background/50 text-sm min-w-[160px]">
                                <option value="">All Statuses</option>
                                @foreach(['pending', 'confirmed', 'processing', 'shipped', 'delivered', 'cancelled', 'returned'] as $status)
                                    <option value="{{ $status }}" {{ request('status') === $status ? 'selected' : '' }}>{{ ucfirst($status) }}</option>
                                @endforeach
                            </select>
                            <x-ui.button type="submit" size="sm" class="h-10 rounded-xl font-bold uppercase tracking-widest text-[10px] px-5">
                                Filter
                            </x-ui.button>
                        </form>

                        <a href="{{ route('orders.create') }}">
                            <x-ui.button size="sm" class="rounded-xl font-bold uppercase tracking-widest text-[10px] h-10 shadow-lg shadow-primary/20">
                                <x-ui.icon name="plus" size="3" class="mr-2" /> New Order
                            </x-ui.button>
                        </a>
                    </div>
                </x-ui.card-header>

                <x-ui.card-content class="p-0">
                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse">
                            <thead>
                                <tr class="border-b border-border/40 bg-muted/5">
                                    <th class="px-6 py-4 text-[10px] font-black uppercase tracking-widest text-muted-foreground">Order #</th>
                                    <th class="px-6 py-4 text-[10px] font-black uppercase tracking-widest text-muted-foreground">Type</th>
                                    <th class="px-6 py-4 text-[10px] font-black uppercase tracking-widest text-muted-foreground">Party</th>
                                    <th class="px-6 py-4 text-[10px] font-black uppercase tracking-widest text-muted-foreground">Warehouse</th>
                                    <th class="px-6 py-4 text-[10px] font-black uppercase tracking-widest text-muted-foreground">Status</th>
                                    <th class="px-6 py-4 text-[10px] font-black uppercase tracking-widest text-muted-foreground text-right">Net Amount</th>
                                    <th class="px-6 py-4 text-[10px] font-black uppercase tracking-widest text-muted-foreground text-right">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-border/40">
                                @forelse($orders as $order)
                                    <tr class="hover:bg-muted/10 transition-colors">
                                        <td class="px-6 py-4">
                                            <p class="text-sm font-bold text-foreground">{{ $order->order_no }}</p>
                                            <p class="text-[10px] text-muted-foreground">{{ optional($order->order_date)->format('M d, Y h:i A') }}</p>
                                        </td>
                                        <td class="px-6 py-4">
                                            <x-ui.badge variant="{{ $order->type === 'sale' ? 'default' : 'outline' }}" class="uppercase text-[10px] font-black tracking-widest">
                                                {{ $order->type }}
                                            </x-ui.badge>
                                        </td>
                                        <td class="px-6 py-4 text-sm font-semibold text-foreground">{{ $order->party?->name ?? 'N/A' }}</td>
                                        <td class="px-6 py-4 text-sm font-semibold text-foreground">{{ $order->warehouse?->name ?? 'N/A' }}</td>
                                        <td class="px-6 py-4">
                                            @php
                                                $statusVariant = match($order->status) {
                                                    'shipped', 'delivered' => 'success',
                                                    'cancelled', 'returned' => 'destructive',
                                                    'pending' => 'warning',
                                                    default => 'outline'
                                                };
                                            @endphp
                                            <x-ui.badge variant="{{ $statusVariant }}" class="uppercase text-[10px] font-black tracking-widest">
                                                {{ $order->status }}
                                            </x-ui.badge>
                                        </td>
                                        <td class="px-6 py-4 text-right text-sm font-black text-foreground">{{ number_format((float) $order->net_amount, 2) }}</td>
                                        <td class="px-6 py-4 text-right">
                                            <a href="{{ route('orders.show', $order) }}">
                                                <x-ui.button variant="ghost" size="sm" class="h-8 px-3 rounded-xl text-[10px] font-bold uppercase tracking-widest">
                                                    View
                                                </x-ui.button>
                                            </a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="px-6 py-14 text-center text-muted-foreground font-semibold">
                                            No orders found.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    @if($orders->hasPages())
                        <div class="p-4 border-t border-border/40 bg-muted/5">
                            {{ $orders->links() }}
                        </div>
                    @endif
                </x-ui.card-content>
            </x-ui.card>
        </div>
    </div>
</x-layouts.app>
