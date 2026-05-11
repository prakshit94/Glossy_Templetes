<x-layouts.app>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Dashboard') }}
        </h2>
    </x-slot>

    <div class="p-6 lg:p-10 space-y-10">
        <!-- Stats Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
            @php
                $stats = [
                    ['title' => 'Total Revenue', 'value' => '$45,231.89', 'change' => '+20.1%', 'icon' => 'dollar-sign', 'color' => 'primary', 'glow' => 'primary'],
                    ['title' => 'Active Users', 'value' => '2,350', 'change' => '+18.1%', 'icon' => 'users', 'color' => 'blue', 'glow' => 'blue-500'],
                    ['title' => 'New Sales', 'value' => '+12,234', 'change' => '+19%', 'icon' => 'shopping-cart', 'color' => 'emerald', 'glow' => 'emerald-500'],
                    ['title' => 'System Load', 'value' => '0.45ms', 'change' => '+201%', 'icon' => 'activity', 'color' => 'amber', 'glow' => 'amber-500'],
                ];
            @endphp

            @foreach($stats as $stat)
            <div class="group relative p-6 rounded-3xl bg-card/40 border border-border/60 backdrop-blur-xl hover:bg-{{ $stat['glow'] }}/5 transition-all duration-500 overflow-hidden shadow-2xl">
                <div class="absolute top-0 right-0 -mr-8 -mt-8 size-32 bg-{{ $stat['glow'] }}/10 blur-[50px] rounded-full group-hover:bg-{{ $stat['glow'] }}/20 transition-all duration-500"></div>
                <div class="flex items-center justify-between relative z-10">
                    <div>
                        <p class="text-[10px] font-black uppercase tracking-[0.2em] text-muted-foreground/60">{{ $stat['title'] }}</p>
                        <h3 class="text-3xl font-black mt-2 tracking-tight text-foreground">{{ $stat['value'] }}</h3>
                        <div class="flex items-center gap-2 mt-2">
                            <span class="px-1.5 py-0.5 rounded-lg bg-emerald-500/10 text-emerald-500 font-black text-[10px]">
                                {{ $stat['change'] }}
                            </span>
                            <span class="text-[10px] font-bold text-muted-foreground uppercase tracking-widest">vs last period</span>
                        </div>
                    </div>
                    <div class="size-14 rounded-2xl bg-gradient-to-tr from-{{ $stat['glow'] }}/20 to-{{ $stat['glow'] }}/5 border border-{{ $stat['glow'] }}/10 text-{{ $stat['color'] == 'primary' ? 'primary' : $stat['color'].'-500' }} flex items-center justify-center shadow-inner group-hover:scale-110 group-hover:rotate-6 transition-all duration-500">
                        <x-ui.icon name="{{ $stat['icon'] }}" size="6" />
                    </div>
                </div>
            </div>
            @endforeach
        </div>

        <!-- Main Content Area -->
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">
            <x-ui.card class="lg:col-span-8 overflow-hidden border-border/60 shadow-2xl bg-card/30 backdrop-blur-2xl rounded-3xl">
                <x-ui.card-header class="border-b border-border/40 bg-muted/10 p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <x-ui.card-title class="text-xl font-bold tracking-tight text-foreground">Analytics Overview</x-ui.card-title>
                            <x-ui.card-description class="text-muted-foreground">Real-time performance metrics across all channels.</x-ui.card-description>
                        </div>
                        <x-ui.button variant="outline" size="sm" class="rounded-xl font-bold uppercase tracking-widest text-[10px] border-border text-muted-foreground hover:bg-muted">
                            Export Data
                        </x-ui.button>
                    </div>
                </x-ui.card-header>
                <x-ui.card-content class="p-0">
                    <div class="h-[400px] w-full bg-muted/5 flex flex-col items-center justify-center relative overflow-hidden">
                        <div class="absolute inset-0 bg-[radial-gradient(circle_at_center,var(--primary)_0%,transparent_70%)] opacity-[0.03]"></div>
                        <x-ui.icon name="activity" size="12" class="text-primary/20 animate-pulse" />
                        <p class="text-muted-foreground font-bold uppercase tracking-[0.3em] text-xs mt-4">Synthesizing Chart Data</p>
                    </div>
                </x-ui.card-content>
            </x-ui.card>

            <x-ui.card class="lg:col-span-4 overflow-hidden border-border/60 shadow-2xl bg-card/30 backdrop-blur-2xl rounded-3xl">
                <x-ui.card-header class="border-b border-border/40 bg-muted/10 p-6">
                    <x-ui.card-title class="text-xl font-bold tracking-tight text-foreground">Recent Activity</x-ui.card-title>
                    <x-ui.card-description class="text-muted-foreground">Latest transactions and system events.</x-ui.card-description>
                </x-ui.card-header>
                <x-ui.card-content class="p-6">
                    <div class="space-y-8">
                        @foreach (range(1, 5) as $i)
                        <div class="flex items-start gap-4 group cursor-pointer">
                            <div class="relative">
                                <div class="size-12 rounded-2xl bg-gradient-to-tr from-primary/20 to-purple-500/20 flex items-center justify-center font-black text-primary border border-primary/10 group-hover:rotate-6 transition-all duration-300 shadow-inner">
                                    {{ substr(['JD', 'AS', 'BJ', 'AB', 'CG'][$i-1], 0, 2) }}
                                </div>
                                <div class="absolute -bottom-1 -right-1 size-4 rounded-full bg-emerald-500 border-2 border-background shadow-sm"></div>
                            </div>
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center justify-between">
                                    <p class="text-sm font-black truncate group-hover:text-primary transition-colors text-foreground">{{ ['John Doe', 'Anna Smith', 'Bob Johnson', 'Alice Brown', 'Charlie Green'][$i-1] }}</p>
                                    <span class="text-[10px] font-bold text-muted-foreground uppercase tracking-widest">2m ago</span>
                                </div>
                                <p class="text-xs text-muted-foreground mt-0.5 truncate">Purchased Premium Subscription Plan</p>
                                <div class="flex items-center gap-2 mt-2">
                                    <x-ui.badge variant="outline" class="text-[9px] uppercase tracking-tighter px-1 border-border text-muted-foreground">Invoice #{{ 2034 + $i }}</x-ui.badge>
                                    <span class="text-[10px] font-black text-foreground">+$1,299.00</span>
                                </div>
                            </div>
                        </div>
                        @if(!$loop->last)
                            <div class="h-px bg-gradient-to-r from-transparent via-border/50 to-transparent"></div>
                        @endif
                        @endforeach
                    </div>
                    <x-ui.button variant="ghost" class="w-full mt-6 text-xs font-bold uppercase tracking-widest text-muted-foreground hover:text-primary hover:bg-primary/5 rounded-xl">
                        View All Activity
                    </x-ui.button>
                </x-ui.card-content>
            </x-ui.card>
        </div>

        <!-- Table Section -->
        <x-ui.card class="overflow-hidden border-border/60 shadow-2xl bg-card/30 backdrop-blur-2xl rounded-3xl">
             <x-ui.card-header class="border-b border-border/40 bg-muted/10 p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <x-ui.card-title class="text-xl font-bold tracking-tight text-foreground">Transaction Ledger</x-ui.card-title>
                        <x-ui.card-description class="text-muted-foreground">Full history of system transactions for the current period.</x-ui.card-description>
                    </div>
                    <div class="flex items-center gap-2">
                        <x-ui.button variant="outline" size="sm" class="rounded-xl font-bold uppercase tracking-widest text-[10px] border-border text-muted-foreground hover:bg-muted">
                            <x-ui.icon name="external-link" size="3" class="mr-2" />
                            Report
                        </x-ui.button>
                        <x-ui.button size="sm" class="rounded-xl font-bold uppercase tracking-widest text-[10px] shadow-lg shadow-primary/20 hover:scale-[1.02] active:scale-[0.98] transition-all">
                            <x-ui.icon name="plus" size="3" class="mr-2" />
                            New Entry
                        </x-ui.button>
                    </div>
                </div>
             </x-ui.card-header>
             <x-ui.card-content class="p-0">
                <x-ui.table>
                    <x-ui.table-header class="bg-muted/5">
                        <x-ui.table-row class="border-b border-border/40 hover:bg-transparent">
                            <x-ui.table-head class="text-[10px] font-black uppercase tracking-widest text-muted-foreground/80 py-4 px-6">Transaction ID</x-ui.table-head>
                            <x-ui.table-head class="text-[10px] font-black uppercase tracking-widest text-muted-foreground/80 py-4 px-6">Recipient</x-ui.table-head>
                            <x-ui.table-head class="text-[10px] font-black uppercase tracking-widest text-muted-foreground/80 py-4 px-6">Status</x-ui.table-head>
                            <x-ui.table-head class="text-[10px] font-black uppercase tracking-widest text-muted-foreground/80 py-4 px-6">Method</x-ui.table-head>
                            <x-ui.table-head class="text-[10px] font-black uppercase tracking-widest text-muted-foreground/80 py-4 px-6 text-right">Amount</x-ui.table-head>
                        </x-ui.table-row>
                    </x-ui.table-header>
                    <x-ui.table-body>
                        @foreach(['INV001', 'INV002', 'INV003', 'INV004', 'INV005'] as $inv)
                        <x-ui.table-row class="border-b border-border/40 last:border-0 hover:bg-primary/[0.03] transition-colors group">
                            <x-ui.table-cell class="font-black text-primary py-4 px-6">{{ $inv }}</x-ui.table-cell>
                            <x-ui.table-cell class="py-4 px-6">
                                <div class="flex flex-col">
                                    <span class="font-bold text-foreground">Enterprise Client A</span>
                                    <span class="text-[10px] text-muted-foreground uppercase font-bold">Client Ref: #4059</span>
                                </div>
                            </x-ui.table-cell>
                            <x-ui.table-cell class="py-4 px-6">
                                <x-ui.badge variant="success" class="bg-emerald-500/10 text-emerald-500 border-none uppercase text-[9px] tracking-widest font-black">Successful</x-ui.badge>
                            </x-ui.table-cell>
                            <x-ui.table-cell class="py-4 px-6">
                                <div class="flex items-center gap-2">
                                    <div class="size-6 rounded bg-muted flex items-center justify-center text-muted-foreground border border-border/60 shadow-sm">
                                        <x-ui.icon name="credit-card" size="3" />
                                    </div>
                                    <span class="text-xs font-medium text-foreground">VISA **** 4242</span>
                                </div>
                            </x-ui.table-cell>
                            <x-ui.table-cell class="text-right font-black text-foreground py-4 px-6">$2,500.00</x-ui.table-cell>
                        </x-ui.table-row>
                        @endforeach
                    </x-ui.table-body>
                </x-ui.table>
             </x-ui.card-content>
        </x-ui.card>
    </div>
</x-layouts.app>
