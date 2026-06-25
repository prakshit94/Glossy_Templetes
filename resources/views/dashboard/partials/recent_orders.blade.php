@php
    $roCounts = ['all' => 0, 'pending' => 0, 'future' => 0, 'cancelled' => 0];
    foreach ($recentOrders as $_o) {
        $roCounts['all']++;
        if ($_o->lifecycleStatus() === 'future_order') $roCounts['future']++;
        elseif ($_o->status === 'cancelled')            $roCounts['cancelled']++;
        else                                             $roCounts['pending']++;
    }
@endphp

<div class="rounded-2xl border border-border/60 bg-card/40 text-card-foreground shadow-xl backdrop-blur-xl overflow-hidden bg-card/30 backdrop-blur-2xl rounded-3xl">
    <div x-data="{ activeTab: 'all', counts: {{ json_encode($roCounts) }} }">

        {{-- Header --}}
        <div class="p-5 border-b border-border/40 bg-muted/10 flex justify-between items-center">
            <h3 class="text-[10px] font-black uppercase tracking-[0.2em] text-foreground flex items-center gap-2">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5 text-primary" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/></svg>
                Recent Orders
            </h3>
            <a href="{{ route('orders.index') }}" class="text-[9px] font-bold text-primary uppercase tracking-widest hover:underline">View All</a>
        </div>

        {{-- Tab Bar --}}
        <div class="flex gap-1.5 p-3 border-b border-border/40 bg-muted/5 overflow-x-auto">
            {{-- All --}}
            <button @click="activeTab = 'all'"
                :class="activeTab === 'all' ? 'bg-primary text-primary-foreground shadow-md shadow-primary/20' : 'bg-muted/40 text-muted-foreground hover:bg-muted/70'"
                class="flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-[9px] font-black uppercase tracking-wider transition-all whitespace-nowrap">
                All
                <span class="inline-flex items-center justify-center min-w-[16px] h-4 px-1 rounded-full text-[8px] font-black bg-black/10 dark:bg-white/20">{{ $roCounts['all'] }}</span>
            </button>
            {{-- Pending --}}
            <button @click="activeTab = 'pending'"
                :class="activeTab === 'pending' ? 'bg-emerald-500 text-white shadow-md shadow-emerald-500/25' : 'bg-muted/40 text-muted-foreground hover:bg-muted/70'"
                class="flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-[9px] font-black uppercase tracking-wider transition-all whitespace-nowrap">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-2.5 h-2.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><circle cx="12" cy="12" r="10"/><path d="M12 6v6l4 2"/></svg>
                Pending
                <span class="inline-flex items-center justify-center min-w-[16px] h-4 px-1 rounded-full text-[8px] font-black bg-black/10 dark:bg-white/20">{{ $roCounts['pending'] }}</span>
            </button>
            {{-- Future --}}
            <button @click="activeTab = 'future'"
                :class="activeTab === 'future' ? 'bg-violet-500 text-white shadow-md shadow-violet-500/25' : 'bg-muted/40 text-muted-foreground hover:bg-muted/70'"
                class="flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-[9px] font-black uppercase tracking-wider transition-all whitespace-nowrap">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-2.5 h-2.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                Future
                <span class="inline-flex items-center justify-center min-w-[16px] h-4 px-1 rounded-full text-[8px] font-black bg-black/10 dark:bg-white/20">{{ $roCounts['future'] }}</span>
            </button>
            {{-- Cancelled --}}
            <button @click="activeTab = 'cancelled'"
                :class="activeTab === 'cancelled' ? 'bg-rose-500 text-white shadow-md shadow-rose-500/25' : 'bg-muted/40 text-muted-foreground hover:bg-muted/70'"
                class="flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-[9px] font-black uppercase tracking-wider transition-all whitespace-nowrap">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-2.5 h-2.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><circle cx="12" cy="12" r="10"/><line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/></svg>
                Cancelled
                <span class="inline-flex items-center justify-center min-w-[16px] h-4 px-1 rounded-full text-[8px] font-black bg-black/10 dark:bg-white/20">{{ $roCounts['cancelled'] }}</span>
            </button>
        </div>

        {{-- Order Rows --}}
        <div class="divide-y divide-border/40">
            @forelse($recentOrders as $order)
                @php
                    $_ls  = $order->lifecycleStatus();
                    $_tab = $_ls === 'future_order' ? 'future' : ($order->status === 'cancelled' ? 'cancelled' : 'pending');
                @endphp
                <a href="{{ route('orders.show', $order) }}"
                    x-show="activeTab === 'all' || activeTab === '{{ $_tab }}'"
                    x-transition:enter="transition ease-out duration-150"
                    x-transition:enter-start="opacity-0 -translate-y-1"
                    x-transition:enter-end="opacity-100 translate-y-0"
                    class="block p-4 hover:bg-primary/5 transition-colors group {{ $_tab === 'future' ? 'border-l-2 border-l-violet-500/40' : '' }} {{ $_tab === 'cancelled' ? 'border-l-2 border-l-rose-500/40' : '' }}">
                    <div class="flex justify-between items-center">
                        <div>
                            <p class="text-sm font-bold text-foreground group-hover:text-primary transition-colors">{{ $order->order_no }}</p>
                            <p class="text-[10px] font-bold text-muted-foreground uppercase mt-0.5">
                                {{ $order->party->company_name ?? ($order->party->firstname . ' ' . $order->party->lastname) }}
                            </p>
                            <p class="text-[9px] text-muted-foreground mt-1 flex items-center gap-1">
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-2.5 h-2.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                                {{ $order->created_at->diffForHumans() }}
                            </p>
                        </div>
                        <div class="text-right">
                            <p class="text-sm font-black {{ $_tab === 'cancelled' ? 'text-rose-400' : 'text-foreground' }}">
                                ₹{{ number_format($order->net_amount, 2) }}
                            </p>
                            @if($_ls === 'future_order')
                                <span class="mt-1 inline-flex items-center gap-1 px-2 py-0.5 rounded-md text-[8px] font-black uppercase tracking-wider bg-violet-500/15 text-violet-400 border border-violet-500/30">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-2.5 h-2.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                                    Future Order
                                </span>
                            @elseif($order->status === 'cancelled')
                                <span class="mt-1 inline-flex items-center gap-1 px-2 py-0.5 rounded-md text-[8px] font-black uppercase tracking-wider bg-rose-500/10 text-rose-400 border border-rose-500/25">
                                    Cancelled
                                </span>
                            @else
                                <span class="mt-1 inline-flex items-center gap-1 px-2 py-0.5 rounded-md text-[8px] font-black uppercase tracking-wider border border-border/60 text-muted-foreground">
                                    {{ $order->statusLabel() }}
                                </span>
                            @endif
                        </div>
                    </div>
                </a>
            @empty
                <div class="p-6 text-center text-sm font-medium text-muted-foreground">No orders in this period.</div>
            @endforelse

            {{-- Per-tab empty state --}}
            <div x-show="activeTab !== 'all' && counts[activeTab] === 0"
                 x-cloak
                 class="p-8 text-center">
                <div class="inline-flex items-center justify-center w-10 h-10 rounded-2xl bg-muted/40 mb-3">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-muted-foreground" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0H4"/></svg>
                </div>
                <p class="text-xs font-bold text-muted-foreground">No orders in this category</p>
            </div>
        </div>

    </div>
</div>
