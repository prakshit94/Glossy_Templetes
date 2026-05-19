<x-layouts.app pageTitle="Dashboard">
    <!-- ChartJS (Include via CDN for the dashboard chart) -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <div class="p-6 lg:p-10" x-data="{ 
        filter: '{{ $filter }}',
        dateRangeString: '{{ $dateRangeString }}',
        currentTime: new Date().toLocaleString('en-US', { weekday: 'short', month: 'short', day: 'numeric', hour: '2-digit', minute: '2-digit', second: '2-digit', hour12: true }),
        isLoading: false,
        chartInstance: null,
        
        async updateDashboard() {
            this.isLoading = true;
            
            // Update URL without reload
            let params = new URLSearchParams({ filter: this.filter });
            window.history.replaceState({}, '', `${window.location.pathname}?${params.toString()}`);

            try {
                const res = await fetch(`{{ route('dashboard') }}?${params.toString()}`, {
                    headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
                });
                
                const data = await res.json();
                
                // Update HTML partials
                document.getElementById('metrics-container').innerHTML = data.html;
                this.dateRangeString = data.dateRangeString;
                
                // Update Chart
                if (this.chartInstance) {
                    this.chartInstance.data.labels = data.chartLabels;
                    this.chartInstance.data.datasets[0].data = data.salesData;
                    this.chartInstance.data.datasets[1].data = data.ordersData;
                    this.chartInstance.update();
                }
            } catch (error) {
                console.error('Error updating dashboard', error);
            }
            
            this.isLoading = false;
        },

        initChart() {
            // Live clock interval
            setInterval(() => {
                this.currentTime = new Date().toLocaleString('en-US', { weekday: 'short', month: 'short', day: 'numeric', hour: '2-digit', minute: '2-digit', second: '2-digit', hour12: true });
            }, 1000);

            const ctx = document.getElementById('mainChart').getContext('2d');
            
            // Primary colors based on CSS variables (or fallback to #10b981 / #3b82f6)
            const emeraldColor = '#10b981';
            const primaryColor = '#3b82f6';
            
            this.chartInstance = new Chart(ctx, {
                type: 'line',
                data: {
                    labels: @js($chartLabels),
                    datasets: [
                        {
                            label: 'Revenue (₹)',
                            data: @js($salesData),
                            borderColor: emeraldColor,
                            backgroundColor: emeraldColor + '20', // 20% opacity
                            borderWidth: 2,
                            tension: 0.4,
                            yAxisID: 'y'
                        },
                        {
                            label: 'Orders',
                            data: @js($ordersData),
                            borderColor: primaryColor,
                            backgroundColor: 'transparent',
                            borderWidth: 2,
                            borderDash: [5, 5],
                            tension: 0.4,
                            yAxisID: 'y1'
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    interaction: {
                        mode: 'index',
                        intersect: false,
                    },
                    plugins: {
                        legend: {
                            display: true,
                            position: 'top',
                            labels: {
                                color: '#a1a1aa',
                                usePointStyle: true,
                                font: {
                                    family: 'Plus Jakarta Sans',
                                    size: 11,
                                    weight: 'bold'
                                }
                            }
                        },
                        tooltip: {
                            backgroundColor: 'rgba(9, 9, 11, 0.9)',
                            titleFont: { family: 'Plus Jakarta Sans', size: 13 },
                            bodyFont: { family: 'Plus Jakarta Sans', size: 12 },
                            padding: 12,
                            cornerRadius: 12,
                            displayColors: true
                        }
                    },
                    scales: {
                        x: {
                            grid: { display: false, drawBorder: false },
                            ticks: { color: '#71717a', font: { family: 'Plus Jakarta Sans', size: 10 } }
                        },
                        y: {
                            type: 'linear',
                            display: true,
                            position: 'left',
                            grid: { color: 'rgba(161, 161, 170, 0.1)', drawBorder: false },
                            ticks: { color: '#71717a', font: { family: 'Plus Jakarta Sans', size: 10 } }
                        },
                        y1: {
                            type: 'linear',
                            display: true,
                            position: 'right',
                            grid: { drawOnChartArea: false },
                            ticks: { color: '#71717a', font: { family: 'Plus Jakarta Sans', size: 10 } }
                        }
                    }
                }
            });
        }
    }" x-init="initChart()">

        <!-- Header -->
        <div class="flex flex-col md:flex-row md:items-end justify-between gap-4 mb-8">
            <div>
                <h1 class="text-3xl font-black tracking-tight text-foreground">System Overview</h1>
                <div class="flex flex-col sm:flex-row sm:items-center gap-2 sm:gap-4 mt-2">
                    <p class="text-[11px] font-bold text-primary uppercase tracking-widest bg-primary/10 px-2.5 py-1 rounded-lg inline-flex items-center gap-2">
                        <x-ui.icon name="clock" size="3" /> <span x-text="currentTime"></span>
                    </p>
                    <p class="text-[11px] font-bold text-muted-foreground uppercase tracking-widest inline-flex items-center gap-1">
                        Viewing: <span x-text="dateRangeString" class="text-foreground"></span>
                    </p>
                </div>
            </div>
            
            <div class="flex items-center gap-3">
                <div class="relative">
                    <x-ui.icon name="calendar" size="4" class="absolute left-3 top-1/2 -translate-y-1/2 text-muted-foreground" />
                    <select x-model="filter" @change="updateDashboard()" 
                        class="h-12 pl-10 pr-10 rounded-2xl border border-border bg-card/50 backdrop-blur-xl focus:bg-background focus:ring-2 focus:ring-primary/20 transition-all text-sm font-bold shadow-xl outline-none appearance-none cursor-pointer">
                        <option value="today">Today</option>
                        <option value="yesterday">Yesterday</option>
                        <option value="this_week">This Week</option>
                        <option value="this_month">This Month</option>
                        <option value="this_year">This Year</option>
                        <option value="previous_year">Previous Year</option>
                    </select>
                    <x-ui.icon name="chevron-down" size="4" class="absolute right-4 top-1/2 -translate-y-1/2 text-muted-foreground pointer-events-none" />
                </div>
                
                <button @click="updateDashboard()" class="h-12 w-12 rounded-2xl bg-primary text-primary-foreground flex items-center justify-center shadow-lg shadow-primary/20 hover:scale-105 transition-transform">
                    <x-ui.icon name="refresh-cw" size="4" ::class="isLoading ? 'animate-spin' : ''" />
                </button>
            </div>
        </div>

        <div class="relative min-h-[150px]">
            <!-- Loading Overlay -->
            <div x-show="isLoading" x-cloak class="absolute inset-0 z-50 bg-background/50 backdrop-blur-md flex items-center justify-center rounded-3xl">
                <div class="flex items-center gap-3 bg-card/80 px-6 py-4 rounded-2xl border border-border/50 shadow-2xl">
                    <x-ui.icon name="refresh-cw" class="animate-spin text-primary" size="5" />
                    <span class="text-xs font-black uppercase tracking-widest text-foreground">Syncing Data...</span>
                </div>
            </div>

            <!-- Top Metrics -->
            <div id="metrics-container">
                @include('dashboard.partials.metrics')
            </div>
        </div>

        <!-- Main Content Grid -->
        <div class="grid grid-cols-1 xl:grid-cols-3 gap-6 mt-6">
            
            <!-- Chart Section -->
            <div class="xl:col-span-2">
                <x-ui.card class="h-full overflow-hidden border-border/60 shadow-2xl bg-card/30 backdrop-blur-2xl rounded-3xl">
                    <div class="p-6 border-b border-border/40 bg-muted/10 flex justify-between items-center">
                        <h3 class="text-xs font-black uppercase tracking-[0.2em] text-foreground flex items-center gap-2">
                            <x-ui.icon name="bar-chart-2" size="4" class="text-primary" /> Sales & Orders Timeline
                        </h3>
                    </div>
                    <div class="p-6 relative h-[400px]">
                        <canvas id="mainChart"></canvas>
                    </div>
                </x-ui.card>
            </div>

            <!-- Recent Activity Section -->
            <div class="space-y-6">
                
                <!-- Recent Orders -->
                <x-ui.card class="overflow-hidden border-border/60 shadow-xl bg-card/30 backdrop-blur-2xl rounded-3xl">
                    <div class="p-5 border-b border-border/40 bg-muted/10 flex justify-between items-center">
                        <h3 class="text-[10px] font-black uppercase tracking-[0.2em] text-foreground flex items-center gap-2">
                            <x-ui.icon name="shopping-bag" size="3.5" class="text-primary" /> Recent Orders
                        </h3>
                        <a href="{{ route('orders.index') }}" class="text-[9px] font-bold text-primary uppercase tracking-widest hover:underline">View All</a>
                    </div>
                    <div class="divide-y divide-border/40">
                        @forelse($recentOrders as $order)
                            <a href="{{ route('orders.show', $order) }}" class="block p-4 hover:bg-primary/5 transition-colors group">
                                <div class="flex justify-between items-center">
                                    <div>
                                        <p class="text-sm font-bold text-foreground group-hover:text-primary transition-colors">{{ $order->order_no }}</p>
                                        <p class="text-[10px] font-bold text-muted-foreground uppercase mt-0.5">
                                            {{ $order->party->company_name ?? ($order->party->firstname . ' ' . $order->party->lastname) }}
                                        </p>
                                    </div>
                                    <div class="text-right">
                                        <p class="text-sm font-black text-foreground">₹{{ number_format($order->net_amount, 2) }}</p>
                                        <x-ui.badge variant="outline" class="mt-1 text-[8px] font-black uppercase rounded-md">
                                            {{ $order->status }}
                                        </x-ui.badge>
                                    </div>
                                </div>
                            </a>
                        @empty
                            <div class="p-6 text-center text-sm font-medium text-muted-foreground">No recent orders.</div>
                        @endforelse
                    </div>
                </x-ui.card>

                <!-- Recent Returns -->
                <x-ui.card class="overflow-hidden border-border/60 shadow-xl bg-card/30 backdrop-blur-2xl rounded-3xl">
                    <div class="p-5 border-b border-border/40 bg-muted/10 flex justify-between items-center">
                        <h3 class="text-[10px] font-black uppercase tracking-[0.2em] text-foreground flex items-center gap-2">
                            <x-ui.icon name="corner-down-left" size="3.5" class="text-rose-500" /> Recent Returns
                        </h3>
                        <a href="{{ route('returns.index') }}" class="text-[9px] font-bold text-primary uppercase tracking-widest hover:underline">View All</a>
                    </div>
                    <div class="divide-y divide-border/40">
                        @forelse($recentReturns as $return)
                            <a href="{{ route('returns.show', $return) }}" class="block p-4 hover:bg-primary/5 transition-colors group">
                                <div class="flex justify-between items-center">
                                    <div>
                                        <p class="text-sm font-bold text-foreground group-hover:text-primary transition-colors">{{ $return->return_no }}</p>
                                        <p class="text-[10px] font-bold text-muted-foreground uppercase mt-0.5">Order: {{ $return->order->order_no }}</p>
                                    </div>
                                    <div class="text-right">
                                        <p class="text-sm font-black text-rose-500">₹{{ number_format($return->refund_amount, 2) }}</p>
                                        <x-ui.badge variant="outline" class="mt-1 text-[8px] font-black uppercase rounded-md text-amber-500">
                                            {{ $return->status }}
                                        </x-ui.badge>
                                    </div>
                                </div>
                            </a>
                        @empty
                            <div class="p-6 text-center text-sm font-medium text-muted-foreground">No recent returns.</div>
                        @endforelse
                    </div>
                </x-ui.card>

            </div>
        </div>

    </div>
</x-layouts.app>
