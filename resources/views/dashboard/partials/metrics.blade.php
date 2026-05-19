@php
    if (!function_exists('renderTrend')) {
        function renderTrend($diff) {
            $isPositive = $diff > 0;
            $isNeutral = $diff == 0;
            $color = $isPositive ? 'text-emerald-500' : ($isNeutral ? 'text-muted-foreground' : 'text-rose-500');
            $icon = $isPositive ? 'trending-up' : ($isNeutral ? 'minus' : 'trending-down');
            $sign = $isPositive ? '+' : '';
            
            return '
            <div class="flex items-center gap-1 mt-2">
                <div class="flex items-center justify-center p-0.5 rounded-md bg-current/10 ' . $color . '">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="size-3"><polyline points="' . ($isPositive ? '22 7 13.5 15.5 8.5 10.5 2 17' : ($isNeutral ? '5 12 19 12' : '22 17 13.5 8.5 8.5 13.5 2 7')) . '"></polyline>'.($isNeutral ? '' : '<polyline points="' . ($isPositive ? '16 7 22 7 22 13' : '16 17 22 17 22 11') . '"></polyline>').'</svg>
                </div>
                <span class="text-[10px] font-bold ' . $color . '">' . $sign . $diff . '% <span class="text-muted-foreground font-medium ml-1">vs prev period</span></span>
            </div>';
        }
    }
@endphp

<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-6 gap-6">
    <!-- Revenue -->
    <div class="group relative p-6 rounded-3xl bg-card/40 border border-border/60 backdrop-blur-xl hover:bg-emerald-500/5 transition-all duration-500 overflow-hidden shadow-2xl xl:col-span-2">
        <div class="absolute top-0 right-0 -mr-8 -mt-8 size-32 bg-emerald-500/10 blur-[50px] rounded-full group-hover:bg-emerald-500/20 transition-all duration-500"></div>
        <div class="flex flex-col gap-4 relative z-10">
            <div class="flex items-center justify-between">
                <div class="size-12 rounded-2xl bg-gradient-to-tr from-emerald-500/20 to-emerald-500/5 border border-emerald-500/10 text-emerald-500 flex items-center justify-center shadow-inner group-hover:scale-110 transition-transform duration-500">
                    <x-ui.icon name="dollar-sign" size="6" />
                </div>
            </div>
            <div>
                <p class="text-[10px] font-black uppercase tracking-widest text-muted-foreground/60 mb-1">Total Revenue</p>
                <div class="text-3xl font-black tracking-tighter text-foreground">₹{{ number_format($revenue, 2) }}</div>
                {!! renderTrend($diffs['revenue']) !!}
            </div>
        </div>
    </div>

    <!-- Orders -->
    <div class="group relative p-6 rounded-3xl bg-card/40 border border-border/60 backdrop-blur-xl hover:bg-primary/5 transition-all duration-500 overflow-hidden shadow-2xl">
        <div class="absolute top-0 right-0 -mr-8 -mt-8 size-32 bg-primary/10 blur-[50px] rounded-full group-hover:bg-primary/20 transition-all duration-500"></div>
        <div class="flex flex-col gap-4 relative z-10">
            <div class="flex items-center justify-between">
                <div class="size-12 rounded-2xl bg-gradient-to-tr from-primary/20 to-primary/5 border border-primary/10 text-primary flex items-center justify-center shadow-inner group-hover:scale-110 transition-transform duration-500">
                    <x-ui.icon name="shopping-bag" size="6" />
                </div>
            </div>
            <div>
                <p class="text-[10px] font-black uppercase tracking-widest text-muted-foreground/60 mb-1">Orders</p>
                <div class="text-3xl font-black tracking-tighter text-foreground">{{ number_format($ordersCount) }}</div>
                {!! renderTrend($diffs['orders']) !!}
            </div>
        </div>
    </div>

    <!-- Cancelled Orders -->
    <div class="group relative p-6 rounded-3xl bg-card/40 border border-border/60 backdrop-blur-xl hover:bg-rose-500/5 transition-all duration-500 overflow-hidden shadow-2xl">
        <div class="absolute top-0 right-0 -mr-8 -mt-8 size-32 bg-rose-500/10 blur-[50px] rounded-full group-hover:bg-rose-500/20 transition-all duration-500"></div>
        <div class="flex flex-col gap-4 relative z-10">
            <div class="flex items-center justify-between">
                <div class="size-12 rounded-2xl bg-gradient-to-tr from-rose-500/20 to-rose-500/5 border border-rose-500/10 text-rose-500 flex items-center justify-center shadow-inner group-hover:scale-110 transition-transform duration-500">
                    <x-ui.icon name="x-octagon" size="6" />
                </div>
            </div>
            <div>
                <p class="text-[10px] font-black uppercase tracking-widest text-muted-foreground/60 mb-1">Cancelled</p>
                <div class="text-3xl font-black tracking-tighter text-foreground">{{ number_format($cancelledOrdersCount) }}</div>
                <!-- Lower cancellations are good, so we invert the diff visual logic here natively -->
                @php
                    $isPositive = $diffs['cancelled'] < 0; // Negative diff is good for cancellations
                    $isNeutral = $diffs['cancelled'] == 0;
                    $color = $isPositive ? 'text-emerald-500' : ($isNeutral ? 'text-muted-foreground' : 'text-rose-500');
                    $sign = $diffs['cancelled'] > 0 ? '+' : '';
                @endphp
                <div class="flex items-center gap-1 mt-2">
                    <div class="flex items-center justify-center p-0.5 rounded-md bg-current/10 {{ $color }}">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="size-3"><polyline points="{{ $isPositive ? '22 7 13.5 15.5 8.5 10.5 2 17' : ($isNeutral ? '5 12 19 12' : '22 17 13.5 8.5 8.5 13.5 2 7') }}"></polyline>{{ $isNeutral ? '' : '<polyline points="' . ($isPositive ? '16 7 22 7 22 13' : '16 17 22 17 22 11') . '"></polyline>' }}</svg>
                    </div>
                    <span class="text-[10px] font-bold {{ $color }}">{{ $sign }}{{ $diffs['cancelled'] }}% <span class="text-muted-foreground font-medium ml-1">vs prev</span></span>
                </div>
            </div>
        </div>
    </div>

    <!-- Refunds -->
    <div class="group relative p-6 rounded-3xl bg-card/40 border border-border/60 backdrop-blur-xl hover:bg-amber-500/5 transition-all duration-500 overflow-hidden shadow-2xl">
        <div class="absolute top-0 right-0 -mr-8 -mt-8 size-32 bg-amber-500/10 blur-[50px] rounded-full group-hover:bg-amber-500/20 transition-all duration-500"></div>
        <div class="flex flex-col gap-4 relative z-10">
            <div class="flex items-center justify-between">
                <div class="size-12 rounded-2xl bg-gradient-to-tr from-amber-500/20 to-amber-500/5 border border-amber-500/10 text-amber-500 flex items-center justify-center shadow-inner group-hover:scale-110 transition-transform duration-500">
                    <x-ui.icon name="refresh-cw" size="6" />
                </div>
            </div>
            <div>
                <p class="text-[10px] font-black uppercase tracking-widest text-muted-foreground/60 mb-1">Refunds</p>
                <div class="text-3xl font-black tracking-tighter text-foreground">₹{{ number_format($refundsAmount, 2) }}</div>
                @php
                    $isPositive = $diffs['refunds'] < 0; 
                    $isNeutral = $diffs['refunds'] == 0;
                    $color = $isPositive ? 'text-emerald-500' : ($isNeutral ? 'text-muted-foreground' : 'text-amber-500');
                    $sign = $diffs['refunds'] > 0 ? '+' : '';
                @endphp
                <div class="flex items-center gap-1 mt-2">
                    <div class="flex items-center justify-center p-0.5 rounded-md bg-current/10 {{ $color }}">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="size-3"><polyline points="{{ $isPositive ? '22 7 13.5 15.5 8.5 10.5 2 17' : ($isNeutral ? '5 12 19 12' : '22 17 13.5 8.5 8.5 13.5 2 7') }}"></polyline>{{ $isNeutral ? '' : '<polyline points="' . ($isPositive ? '16 7 22 7 22 13' : '16 17 22 17 22 11') . '"></polyline>' }}</svg>
                    </div>
                    <span class="text-[10px] font-bold {{ $color }}">{{ $sign }}{{ $diffs['refunds'] }}% <span class="text-muted-foreground font-medium ml-1">vs prev</span></span>
                </div>
            </div>
        </div>
    </div>

    <!-- Active Returns -->
    <div class="group relative p-6 rounded-3xl bg-card/40 border border-border/60 backdrop-blur-xl hover:bg-fuchsia-500/5 transition-all duration-500 overflow-hidden shadow-2xl">
        <div class="absolute top-0 right-0 -mr-8 -mt-8 size-32 bg-fuchsia-500/10 blur-[50px] rounded-full group-hover:bg-fuchsia-500/20 transition-all duration-500"></div>
        <div class="flex flex-col gap-4 relative z-10">
            <div class="flex items-center justify-between">
                <div class="size-12 rounded-2xl bg-gradient-to-tr from-fuchsia-500/20 to-fuchsia-500/5 border border-fuchsia-500/10 text-fuchsia-500 flex items-center justify-center shadow-inner group-hover:scale-110 transition-transform duration-500">
                    <x-ui.icon name="corner-down-left" size="6" />
                </div>
            </div>
            <div>
                <p class="text-[10px] font-black uppercase tracking-widest text-muted-foreground/60 mb-1">Returns</p>
                <div class="text-3xl font-black tracking-tighter text-foreground">{{ number_format($activeReturns) }}</div>
                @php
                    $isPositive = $diffs['returns'] < 0; 
                    $isNeutral = $diffs['returns'] == 0;
                    $color = $isPositive ? 'text-emerald-500' : ($isNeutral ? 'text-muted-foreground' : 'text-fuchsia-500');
                    $sign = $diffs['returns'] > 0 ? '+' : '';
                @endphp
                <div class="flex items-center gap-1 mt-2">
                    <div class="flex items-center justify-center p-0.5 rounded-md bg-current/10 {{ $color }}">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="size-3"><polyline points="{{ $isPositive ? '22 7 13.5 15.5 8.5 10.5 2 17' : ($isNeutral ? '5 12 19 12' : '22 17 13.5 8.5 8.5 13.5 2 7') }}"></polyline>{{ $isNeutral ? '' : '<polyline points="' . ($isPositive ? '16 7 22 7 22 13' : '16 17 22 17 22 11') . '"></polyline>' }}</svg>
                    </div>
                    <span class="text-[10px] font-bold {{ $color }}">{{ $sign }}{{ $diffs['returns'] }}% <span class="text-muted-foreground font-medium ml-1">vs prev</span></span>
                </div>
            </div>
        </div>
    </div>

    <!-- Customers -->
    <div class="group relative p-6 rounded-3xl bg-card/40 border border-border/60 backdrop-blur-xl hover:bg-blue-500/5 transition-all duration-500 overflow-hidden shadow-2xl">
        <div class="absolute top-0 right-0 -mr-8 -mt-8 size-32 bg-blue-500/10 blur-[50px] rounded-full group-hover:bg-blue-500/20 transition-all duration-500"></div>
        <div class="flex flex-col gap-4 relative z-10">
            <div class="flex items-center justify-between">
                <div class="size-12 rounded-2xl bg-gradient-to-tr from-blue-500/20 to-blue-500/5 border border-blue-500/10 text-blue-500 flex items-center justify-center shadow-inner group-hover:scale-110 transition-transform duration-500">
                    <x-ui.icon name="users" size="6" />
                </div>
            </div>
            <div>
                <p class="text-[10px] font-black uppercase tracking-widest text-muted-foreground/60 mb-1">New Customers</p>
                <div class="text-3xl font-black tracking-tighter text-foreground">{{ number_format($newCustomers) }}</div>
                {!! renderTrend($diffs['customers']) !!}
            </div>
        </div>
    </div>
</div>
