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
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <tbody class="divide-y divide-border/20">
                            @forelse($partnerPerformance as $row)
                                @php $success = $row->total_orders > 0 ? round(($row->delivered_orders / $row->total_orders) * 100, 2) : 0; @endphp
                                <tr>
                                    <td class="px-6 py-4">
                                        <a href="{{ route('delivery.performance.partner', $row->driver_id) }}" class="text-sm font-black hover:text-primary">{{ $row->driver?->name ?? 'Partner #' . $row->driver_id }}</a>
                                    </td>
                                    <td class="px-6 py-4 text-xs font-bold">Total {{ $row->total_orders }}</td>
                                    <td class="px-6 py-4 text-xs font-bold text-emerald-500">Delivered {{ $row->delivered_orders }}</td>
                                    <td class="px-6 py-4 text-xs font-bold text-red-500">Returned {{ $row->returned_orders }}</td>
                                    <td class="px-6 py-4 text-xs font-black">{{ $success }}%</td>
                                </tr>
                            @empty
                                <tr><td class="px-6 py-12 text-center text-sm text-muted-foreground">No partner data yet.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </x-ui.card>

            <x-ui.card class="overflow-hidden rounded-3xl">
                <div class="p-6 border-b border-border/40">
                    <h2 class="text-sm font-black uppercase tracking-widest">Courier Performance Report</h2>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <tbody class="divide-y divide-border/20">
                            @forelse($courierPerformance as $row)
                                @php $success = $row->total_orders > 0 ? round(($row->delivered_orders / $row->total_orders) * 100, 2) : 0; @endphp
                                <tr>
                                    <td class="px-6 py-4">
                                        <a href="{{ route('delivery.performance.courier', urlencode($row->courier_provider_name)) }}" class="text-sm font-black hover:text-primary">{{ $row->courier_provider_name }}</a>
                                    </td>
                                    <td class="px-6 py-4 text-xs font-bold">Total {{ $row->total_orders }}</td>
                                    <td class="px-6 py-4 text-xs font-bold text-emerald-500">Delivered {{ $row->delivered_orders }}</td>
                                    <td class="px-6 py-4 text-xs font-bold text-red-500">Returned {{ $row->returned_orders }}</td>
                                    <td class="px-6 py-4 text-xs font-black">{{ $success }}%</td>
                                </tr>
                            @empty
                                <tr><td class="px-6 py-12 text-center text-sm text-muted-foreground">No courier data yet.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
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
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead><tr class="border-b border-border/40"><th class="px-6 py-4 text-left text-[10px] uppercase tracking-widest">Month</th><th class="px-6 py-4 text-left text-[10px] uppercase tracking-widest">Type</th><th class="px-6 py-4 text-left text-[10px] uppercase tracking-widest">Assigned</th><th class="px-6 py-4 text-left text-[10px] uppercase tracking-widest">Delivered</th><th class="px-6 py-4 text-left text-[10px] uppercase tracking-widest">Returned</th><th class="px-6 py-4 text-left text-[10px] uppercase tracking-widest">Pending</th></tr></thead>
                    <tbody class="divide-y divide-border/20">
                        @forelse($monthlyPerformance as $row)
                            <tr>
                                <td class="px-6 py-4 text-xs font-black">{{ $row->month }}</td>
                                <td class="px-6 py-4 text-xs font-bold uppercase">{{ $row->dispatch_type }}</td>
                                <td class="px-6 py-4 text-xs font-bold">{{ $row->assigned_orders }}</td>
                                <td class="px-6 py-4 text-xs font-bold text-emerald-500">{{ $row->delivered_orders }}</td>
                                <td class="px-6 py-4 text-xs font-bold text-red-500">{{ $row->returned_orders }}</td>
                                <td class="px-6 py-4 text-xs font-bold">{{ $row->pending_orders }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="px-6 py-12 text-center text-sm text-muted-foreground">No monthly data yet.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </x-ui.card>
    </div>
</x-layouts.app>
