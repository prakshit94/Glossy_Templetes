<x-layouts.app pageTitle="Delivery Success Dashboard">
    <div class="p-6 lg:p-10 max-w-[1920px] mx-auto w-full space-y-8">
        <div class="flex flex-col lg:flex-row lg:items-end justify-between gap-4">
            <div>
                <h1 class="text-3xl font-black tracking-tight text-foreground">Delivery Success Dashboard</h1>
                <p class="text-[11px] font-bold uppercase tracking-widest text-muted-foreground mt-2">Partner, courier and order tracking analytics</p>
            </div>
            <a href="{{ route('order.tracking.index') }}">
                <x-ui.button variant="outline" class="rounded-xl font-black uppercase tracking-widest text-[10px]">
                    <x-ui.icon name="arrow-left" size="4" class="mr-2" /> Tracking Screen
                </x-ui.button>
            </a>
        </div>

        <form method="GET" class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-7 gap-3">
            <input name="search" value="{{ request('search') }}" placeholder="Order / tracking / customer" class="h-11 px-4 rounded-xl border border-border bg-background/50 text-xs font-bold outline-none xl:col-span-2">
            <input type="date" name="date_from" value="{{ request('date_from') }}" class="h-11 px-4 rounded-xl border border-border bg-background/50 text-xs font-bold outline-none">
            <input type="date" name="date_to" value="{{ request('date_to') }}" class="h-11 px-4 rounded-xl border border-border bg-background/50 text-xs font-bold outline-none">
            <select name="dispatch_type" class="h-11 px-4 rounded-xl border border-border bg-background/50 text-xs font-bold outline-none">
                <option value="">All Types</option>
                <option value="courier" @selected(request('dispatch_type') === 'courier')>Courier</option>
                <option value="lmd" @selected(request('dispatch_type') === 'lmd')>LMD</option>
            </select>
            <select name="status" class="h-11 px-4 rounded-xl border border-border bg-background/50 text-xs font-bold outline-none">
                <option value="">All Statuses</option>
                @foreach($statuses as $status)
                    <option value="{{ $status->code }}" @selected(request('status') === $status->code)>{{ $status->name }}</option>
                @endforeach
            </select>
            <x-ui.button class="h-11 rounded-xl text-[10px] font-black uppercase tracking-widest">Apply</x-ui.button>
        </form>

        <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-7 gap-4">
            @foreach([
                ['Total Orders', $metrics['total'], 'package'],
                ['Delivered', $metrics['delivered'], 'check-circle'],
                ['Returned', $metrics['returned'], 'return'],
                ['Pending', $metrics['pending'], 'clock'],
                ['Failed', $metrics['failed'], 'alert-circle'],
                ['Success %', $metrics['success_rate'] . '%', 'trending-up'],
                ['Return %', $metrics['return_rate'] . '%', 'refresh-cw'],
            ] as $metric)
                <x-ui.card class="p-5 rounded-2xl">
                    <div class="flex items-center gap-3">
                        <div class="size-10 rounded-xl bg-primary/10 text-primary flex items-center justify-center">
                            <x-ui.icon :name="$metric[2]" size="5" />
                        </div>
                        <div>
                            <p class="text-[9px] font-black uppercase tracking-widest text-muted-foreground">{{ $metric[0] }}</p>
                            <p class="text-2xl font-black">{{ $metric[1] }}</p>
                        </div>
                    </div>
                </x-ui.card>
            @endforeach
        </div>

        <div class="grid grid-cols-1 xl:grid-cols-2 gap-6">
            <x-ui.card class="overflow-hidden rounded-3xl">
                <div class="p-6 border-b border-border/40 flex items-center justify-between">
                    <h2 class="text-sm font-black uppercase tracking-widest">Partner Performance Report</h2>
                </div>
                <div class="overflow-x-auto relative">
                    <x-ui.table>
                        <x-ui.table-body>
                            @forelse($partnerPerformance as $row)
                                @php $success = $row->total_orders > 0 ? round(($row->delivered_orders / $row->total_orders) * 100, 2) : 0; @endphp
                                <x-ui.table-row class="hover:bg-primary/[0.03] transition-colors duration-200 border-b border-border/40">
                                    <x-ui.table-cell class="pl-6 py-4 align-middle">
                                        <a href="{{ route('delivery.performance.partner', $row->driver_id) }}" class="text-sm font-black hover:text-primary">{{ $row->driver?->name ?? 'Partner #' . $row->driver_id }}</a>
                                    </x-ui.table-cell>
                                    <x-ui.table-cell class="px-6 py-4 text-xs font-bold align-middle">Total {{ $row->total_orders }}</x-ui.table-cell>
                                    <x-ui.table-cell class="px-6 py-4 text-xs font-bold text-emerald-500 align-middle">Delivered {{ $row->delivered_orders }}</x-ui.table-cell>
                                    <x-ui.table-cell class="px-6 py-4 text-xs font-bold text-red-500 align-middle">Returned {{ $row->returned_orders }}</x-ui.table-cell>
                                    <x-ui.table-cell class="pr-6 pl-6 py-4 text-xs font-black align-middle text-right">{{ $success }}%</x-ui.table-cell>
                                </x-ui.table-row>
                            @empty
                                <x-ui.table-row>
                                    <x-ui.table-cell colspan="5" class="py-12 text-center text-sm text-muted-foreground">No partner data yet.</x-ui.table-cell>
                                </x-ui.table-row>
                            @endforelse
                        </x-ui.table-body>
                    </x-ui.table>
                </div>
            </x-ui.card>

            <x-ui.card class="overflow-hidden rounded-3xl">
                <div class="p-6 border-b border-border/40">
                    <h2 class="text-sm font-black uppercase tracking-widest">Courier Performance Report</h2>
                </div>
                <div class="overflow-x-auto relative">
                    <x-ui.table>
                        <x-ui.table-body>
                            @forelse($courierPerformance as $row)
                                @php $success = $row->total_orders > 0 ? round(($row->delivered_orders / $row->total_orders) * 100, 2) : 0; @endphp
                                <x-ui.table-row class="hover:bg-primary/[0.03] transition-colors duration-200 border-b border-border/40">
                                    <x-ui.table-cell class="pl-6 py-4 align-middle">
                                        <a href="{{ route('delivery.performance.courier', urlencode($row->courier_provider_name)) }}" class="text-sm font-black hover:text-primary">{{ $row->courier_provider_name }}</a>
                                    </x-ui.table-cell>
                                    <x-ui.table-cell class="px-6 py-4 text-xs font-bold align-middle">Total {{ $row->total_orders }}</x-ui.table-cell>
                                    <x-ui.table-cell class="px-6 py-4 text-xs font-bold text-emerald-500 align-middle">Delivered {{ $row->delivered_orders }}</x-ui.table-cell>
                                    <x-ui.table-cell class="px-6 py-4 text-xs font-bold text-red-500 align-middle">Returned {{ $row->returned_orders }}</x-ui.table-cell>
                                    <x-ui.table-cell class="pr-6 pl-6 py-4 text-xs font-black align-middle text-right">{{ $success }}%</x-ui.table-cell>
                                </x-ui.table-row>
                            @empty
                                <x-ui.table-row>
                                    <x-ui.table-cell colspan="5" class="py-12 text-center text-sm text-muted-foreground">No courier data yet.</x-ui.table-cell>
                                </x-ui.table-row>
                            @endforelse
                        </x-ui.table-body>
                    </x-ui.table>
                </div>
            </x-ui.card>
        </div>

        <x-ui.card class="overflow-hidden rounded-3xl">
            <div class="p-6 border-b border-border/40">
                <h2 class="text-sm font-black uppercase tracking-widest">Order Tracking Report</h2>
            </div>
            @include('delivery-performance.partials.tracking-table', ['trackings' => $trackings])
        </x-ui.card>

        <x-ui.card class="overflow-hidden rounded-3xl">
            <div class="p-6 border-b border-border/40">
                <h2 class="text-sm font-black uppercase tracking-widest">Monthly Delivery Report</h2>
            </div>
            <div class="overflow-x-auto relative">
                <x-ui.table>
                    <x-ui.table-header class="bg-muted/30">
                        <x-ui.table-row class="border-b border-border/60">
                            <x-ui.table-head class="text-[10px] font-black uppercase tracking-widest text-muted-foreground/70 whitespace-nowrap pl-6">Month</x-ui.table-head>
                            <x-ui.table-head class="text-[10px] font-black uppercase tracking-widest text-muted-foreground/70 whitespace-nowrap">Type</x-ui.table-head>
                            <x-ui.table-head class="text-[10px] font-black uppercase tracking-widest text-muted-foreground/70 whitespace-nowrap">Assigned</x-ui.table-head>
                            <x-ui.table-head class="text-[10px] font-black uppercase tracking-widest text-muted-foreground/70 whitespace-nowrap">Delivered</x-ui.table-head>
                            <x-ui.table-head class="text-[10px] font-black uppercase tracking-widest text-muted-foreground/70 whitespace-nowrap">Returned</x-ui.table-head>
                            <x-ui.table-head class="text-[10px] font-black uppercase tracking-widest text-muted-foreground/70 whitespace-nowrap pr-6">Pending</x-ui.table-head>
                        </x-ui.table-row>
                    </x-ui.table-header>
                    <x-ui.table-body>
                        @forelse($monthlyPerformance as $row)
                            <x-ui.table-row class="hover:bg-primary/[0.03] transition-colors duration-200 border-b border-border/40">
                                <x-ui.table-cell class="pl-6 py-4 text-xs font-black align-middle">{{ $row->month }}</x-ui.table-cell>
                                <x-ui.table-cell class="px-6 py-4 text-xs font-bold uppercase align-middle">{{ $row->dispatch_type }}</x-ui.table-cell>
                                <x-ui.table-cell class="px-6 py-4 text-xs font-bold align-middle">{{ $row->assigned_orders }}</x-ui.table-cell>
                                <x-ui.table-cell class="px-6 py-4 text-xs font-bold text-emerald-500 align-middle">{{ $row->delivered_orders }}</x-ui.table-cell>
                                <x-ui.table-cell class="px-6 py-4 text-xs font-bold text-red-500 align-middle">{{ $row->returned_orders }}</x-ui.table-cell>
                                <x-ui.table-cell class="px-6 py-4 text-xs font-bold align-middle pr-6">{{ $row->pending_orders }}</x-ui.table-cell>
                            </x-ui.table-row>
                        @empty
                            <x-ui.table-row>
                                <x-ui.table-cell colspan="6" class="py-12 text-center text-sm text-muted-foreground">No monthly data yet.</x-ui.table-cell>
                            </x-ui.table-row>
                        @endforelse
                    </x-ui.table-body>
                </x-ui.table>
            </div>
        </x-ui.card>
    </div>
</x-layouts.app>
