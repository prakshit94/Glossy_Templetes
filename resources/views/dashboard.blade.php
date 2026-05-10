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
                    ['title' => 'Total Revenue', 'value' => '$45,231.89', 'change' => '+20.1%', 'icon' => 'dollar-sign', 'color' => 'primary'],
                    ['title' => 'Active Users', 'value' => '2,350', 'change' => '+18.1%', 'icon' => 'users', 'color' => 'blue'],
                    ['title' => 'New Sales', 'value' => '+12,234', 'change' => '+19%', 'icon' => 'shopping-cart', 'color' => 'emerald'],
                    ['title' => 'System Load', 'value' => '0.45ms', 'change' => '+201%', 'icon' => 'activity', 'color' => 'amber'],
                ];
            @endphp

            @foreach($stats as $stat)
            <x-ui.card className="overflow-hidden group">
                <x-ui.card-content className="p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-[10px] font-black uppercase tracking-[0.2em] text-muted-foreground/60">{{ $stat['title'] }}</p>
                            <h3 class="text-3xl font-black mt-2 tracking-tight">{{ $stat['value'] }}</h3>
                            <div class="flex items-center gap-2 mt-2">
                                <x-ui.badge variant="success" className="bg-emerald-500/10 text-emerald-500 border-none px-1.5 py-0">
                                    {{ $stat['change'] }}
                                </x-ui.badge>
                                <span class="text-[10px] font-bold text-muted-foreground uppercase tracking-widest">vs last period</span>
                            </div>
                        </div>
                        <div class="size-14 rounded-[22px] bg-primary/5 flex items-center justify-center text-primary group-hover:scale-110 group-hover:rotate-6 transition-all duration-500 shadow-inner">
                            <x-ui.icon name="{{ $stat['icon'] }}" size="6" />
                        </div>
                    </div>
                </x-ui.card-content>
            </x-ui.card>
            @endforeach
        </div>

        <!-- Main Content Area -->
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">
            <x-ui.card className="lg:col-span-8 overflow-hidden">
                <x-ui.card-header className="border-b border-border/40 bg-muted/20 pb-4">
                    <div class="flex items-center justify-between">
                        <div>
                            <x-ui.card-title className="text-xl">Analytics Overview</x-ui.card-title>
                            <x-ui.card-description>Real-time performance metrics across all channels.</x-ui.card-description>
                        </div>
                        <x-ui.button variant="outline" size="sm" className="rounded-xl font-bold uppercase tracking-widest text-[10px]">
                            Export Data
                        </x-ui.button>
                    </div>
                </x-ui.card-header>
                <x-ui.card-content className="p-0">
                    <div class="h-[400px] w-full bg-[#fafafa] dark:bg-zinc-950/50 flex flex-col items-center justify-center relative overflow-hidden">
                        <div class="absolute inset-0 bg-[radial-gradient(circle_at_center,var(--primary)_0%,transparent_70%)] opacity-[0.03]"></div>
                        <x-ui.icon name="activity" size="12" className="text-primary/20 animate-pulse" />
                        <p class="text-muted-foreground font-bold uppercase tracking-[0.3em] text-xs mt-4">Synthesizing Chart Data</p>
                    </div>
                </x-ui.card-content>
            </x-ui.card>

            <x-ui.card className="lg:col-span-4 overflow-hidden">
                <x-ui.card-header className="border-b border-border/40 bg-muted/20 pb-4">
                    <x-ui.card-title className="text-xl">Recent Activity</x-ui.card-title>
                    <x-ui.card-description>Latest transactions and system events.</x-ui.card-description>
                </x-ui.card-header>
                <x-ui.card-content className="p-6">
                    <div class="space-y-8">
                        @foreach (range(1, 5) as $i)
                        <div class="flex items-start gap-4 group cursor-pointer">
                            <div class="relative">
                                <div class="size-12 rounded-2xl bg-gradient-to-tr from-primary/20 to-purple-500/20 flex items-center justify-center font-black text-primary border border-primary/10 group-hover:rotate-6 transition-all duration-300">
                                    {{ substr(['JD', 'AS', 'BJ', 'AB', 'CG'][$i-1], 0, 2) }}
                                </div>
                                <div class="absolute -bottom-1 -right-1 size-4 rounded-full bg-emerald-500 border-2 border-white dark:border-zinc-950"></div>
                            </div>
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center justify-between">
                                    <p class="text-sm font-black truncate group-hover:text-primary transition-colors">{{ ['John Doe', 'Anna Smith', 'Bob Johnson', 'Alice Brown', 'Charlie Green'][$i-1] }}</p>
                                    <span class="text-[10px] font-bold text-muted-foreground uppercase tracking-widest">2m ago</span>
                                </div>
                                <p class="text-xs text-muted-foreground mt-0.5 truncate">Purchased Premium Subscription Plan</p>
                                <div class="flex items-center gap-2 mt-2">
                                    <x-ui.badge variant="outline" className="text-[9px] uppercase tracking-tighter px-1">Invoice #{{ 2034 + $i }}</x-ui.badge>
                                    <span class="text-[10px] font-black text-foreground">+$1,299.00</span>
                                </div>
                            </div>
                        </div>
                        @if(!$loop->last)
                            <div class="h-px bg-gradient-to-r from-transparent via-border/50 to-transparent"></div>
                        @endif
                        @endforeach
                    </div>
                    <x-ui.button variant="ghost" className="w-full mt-6 text-xs font-bold uppercase tracking-widest text-muted-foreground hover:text-primary">
                        View All Activity
                    </x-ui.button>
                </x-ui.card-content>
            </x-ui.card>
        </div>

        <!-- Table Section -->
        <x-ui.card>
             <x-ui.card-header>
                <div class="flex items-center justify-between">
                    <div>
                        <x-ui.card-title>Transaction Ledger</x-ui.card-title>
                        <x-ui.card-description>Full history of system transactions for the current period.</x-ui.card-description>
                    </div>
                    <div class="flex items-center gap-2">
                        <x-ui.button variant="outline" size="sm" className="rounded-xl font-bold uppercase tracking-widest text-[10px]">
                            <x-ui.icon name="external-link" size="3" className="mr-2" />
                            Report
                        </x-ui.button>
                        <x-ui.button size="sm" className="rounded-xl font-bold uppercase tracking-widest text-[10px]">
                            <x-ui.icon name="plus" size="3" className="mr-2" />
                            New Entry
                        </x-ui.button>
                    </div>
                </div>
             </x-ui.card-header>
             <x-ui.card-content className="p-0">
                <x-ui.table>
                    <x-ui.table-header>
                        <x-ui.table-row>
                            <x-ui.table-head>Transaction ID</x-ui.table-head>
                            <x-ui.table-head>Recipient</x-ui.table-head>
                            <x-ui.table-head>Status</x-ui.table-head>
                            <x-ui.table-head>Method</x-ui.table-head>
                            <x-ui.table-head className="text-right">Amount</x-ui.table-head>
                        </x-ui.table-row>
                    </x-ui.table-header>
                    <x-ui.table-body>
                        @foreach(['INV001', 'INV002', 'INV003', 'INV004', 'INV005'] as $inv)
                        <x-ui.table-row>
                            <x-ui.table-cell className="font-black text-primary">{{ $inv }}</x-ui.table-cell>
                            <x-ui.table-cell>
                                <div class="flex flex-col">
                                    <span class="font-bold">Enterprise Client A</span>
                                    <span class="text-[10px] text-muted-foreground uppercase font-bold">Client Ref: #4059</span>
                                </div>
                            </x-ui.table-cell>
                            <x-ui.table-cell>
                                <x-ui.badge variant="success" className="bg-emerald-500/10 text-emerald-500 border-none uppercase text-[9px] tracking-widest">Successful</x-ui.badge>
                            </x-ui.table-cell>
                            <x-ui.table-cell>
                                <div class="flex items-center gap-2">
                                    <div class="size-6 rounded bg-secondary flex items-center justify-center">
                                        <x-ui.icon name="credit-card" size="3" />
                                    </div>
                                    <span class="text-xs font-medium">VISA **** 4242</span>
                                </div>
                            </x-ui.table-cell>
                            <x-ui.table-cell className="text-right font-black">$2,500.00</x-ui.table-cell>
                        </x-ui.table-row>
                        @endforeach
                    </x-ui.table-body>
                </x-ui.table>
             </x-ui.card-content>
        </x-ui.card>
    </div>
</x-layouts.app>
