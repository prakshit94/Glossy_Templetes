<x-layouts.app pageTitle="Coupons">

    <div class="p-6 lg:p-10"
        x-data="{
            selectedCoupons: [],
            allSelected: false,

            search: '{{ request('search', '') }}',
            statusFilter: '{{ request('status', '') }}',
            perPage: '{{ request('perPage', 10) }}',

            isLoading: false,

            toggleAll() {
                if (this.allSelected) {
                    this.selectedCoupons = Array.from(
                        document.querySelectorAll('input[name=\'coupon_ids[]\']')
                    ).map(el => parseInt(el.value));
                } else {
                    this.selectedCoupons = [];
                }
            },

            toggleCoupon(id) {
                if (this.selectedCoupons.includes(id)) {
                    this.selectedCoupons = this.selectedCoupons.filter(c => c !== id);
                } else {
                    this.selectedCoupons.push(id);
                }
            },

            async performSearch(page = 1) {

                this.isLoading = true;

                const params = new URLSearchParams({
                    search: this.search,
                    status: this.statusFilter,
                    perPage: this.perPage,
                    page: page
                });

                const res = await fetch(
                    `{{ route('coupons.index') }}?${params.toString()}`,
                    {
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest'
                        }
                    }
                );

                const html = await res.text();

                document
                    .getElementById('coupons-table-container')
                    .innerHTML = html;

                this.isLoading = false;

                this.selectedCoupons = [];
                this.allSelected = false;
            }
        }">

        <!-- Stats -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">

            <!-- Total -->
            <div
                class="group relative p-6 rounded-3xl bg-card/40 border border-border/60 backdrop-blur-xl hover:bg-primary/5 transition-all duration-500 overflow-hidden shadow-2xl">
                <div
                    class="absolute top-0 right-0 -mr-8 -mt-8 size-32 bg-primary/10 blur-[50px] rounded-full group-hover:bg-primary/20 transition-all duration-500">
                </div>

                <div class="flex items-center gap-5 relative z-10">

                    <div
                        class="size-14 rounded-2xl bg-gradient-to-tr from-primary/20 to-primary/5 border border-primary/10 text-primary flex items-center justify-center shadow-inner group-hover:scale-110 transition-transform duration-500">
                        <x-ui.icon name="gift" size="7" />
                    </div>

                    <div>
                        <p class="text-[10px] font-black uppercase tracking-widest text-muted-foreground/60 mb-1">
                            Total Coupons
                        </p>

                        <div class="text-3xl font-black tracking-tighter text-foreground">
                            {{ number_format($stats['total'] ?? 0) }}
                        </div>
                    </div>

                </div>
            </div>

            <!-- Active -->
            <div
                class="group relative p-6 rounded-3xl bg-card/40 border border-border/60 backdrop-blur-xl hover:bg-emerald-500/5 transition-all duration-500 overflow-hidden shadow-2xl">
                <div
                    class="absolute top-0 right-0 -mr-8 -mt-8 size-32 bg-emerald-500/10 blur-[50px] rounded-full group-hover:bg-emerald-500/20 transition-all duration-500">
                </div>

                <div class="flex items-center gap-5 relative z-10">

                    <div
                        class="size-14 rounded-2xl bg-gradient-to-tr from-emerald-500/20 to-emerald-500/5 border border-emerald-500/10 text-emerald-500 flex items-center justify-center shadow-inner group-hover:scale-110 transition-transform duration-500">
                        <x-ui.icon name="check-circle" size="7" />
                    </div>

                    <div>
                        <p class="text-[10px] font-black uppercase tracking-widest text-muted-foreground/60 mb-1">
                            Active Coupons
                        </p>

                        <div class="text-3xl font-black tracking-tighter text-foreground">
                            {{ number_format($stats['active'] ?? 0) }}
                        </div>
                    </div>

                </div>
            </div>

            <!-- Expired -->
            <div
                class="group relative p-6 rounded-3xl bg-card/40 border border-border/60 backdrop-blur-xl hover:bg-orange-500/5 transition-all duration-500 overflow-hidden shadow-2xl">
                <div
                    class="absolute top-0 right-0 -mr-8 -mt-8 size-32 bg-orange-500/10 blur-[50px] rounded-full group-hover:bg-orange-500/20 transition-all duration-500">
                </div>

                <div class="flex items-center gap-5 relative z-10">

                    <div
                        class="size-14 rounded-2xl bg-gradient-to-tr from-orange-500/20 to-orange-500/5 border border-orange-500/10 text-orange-500 flex items-center justify-center shadow-inner group-hover:scale-110 transition-transform duration-500">
                        <x-ui.icon name="clock" size="7" />
                    </div>

                    <div>
                        <p class="text-[10px] font-black uppercase tracking-widest text-muted-foreground/60 mb-1">
                            Expired
                        </p>

                        <div class="text-3xl font-black tracking-tighter text-foreground">
                            {{ number_format($stats['expired'] ?? 0) }}
                        </div>
                    </div>

                </div>
            </div>

            <!-- Usage -->
            <div
                class="group relative p-6 rounded-3xl bg-card/40 border border-border/60 backdrop-blur-xl hover:bg-blue-500/5 transition-all duration-500 overflow-hidden shadow-2xl">
                <div
                    class="absolute top-0 right-0 -mr-8 -mt-8 size-32 bg-blue-500/10 blur-[50px] rounded-full group-hover:bg-blue-500/20 transition-all duration-500">
                </div>

                <div class="flex items-center gap-5 relative z-10">

                    <div
                        class="size-14 rounded-2xl bg-gradient-to-tr from-blue-500/20 to-blue-500/5 border border-blue-500/10 text-blue-500 flex items-center justify-center shadow-inner group-hover:scale-110 transition-transform duration-500">
                        <x-ui.icon name="activity" size="7" />
                    </div>

                    <div>
                        <p class="text-[10px] font-black uppercase tracking-widest text-muted-foreground/60 mb-1">
                            Total Uses
                        </p>

                        <div class="text-3xl font-black tracking-tighter text-foreground">
                            {{ number_format($stats['used'] ?? 0) }}
                        </div>
                    </div>

                </div>
            </div>

        </div>

        <!-- Main Card -->
        <x-ui.card
            class="overflow-hidden border-border/60 shadow-2xl bg-card/30 backdrop-blur-2xl rounded-3xl">

            <!-- Header -->
            <x-ui.card-header class="p-8 border-b border-border/40 bg-muted/10">

                <div class="flex flex-col gap-8">

                    <!-- Top Row -->
                    <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-6">

                        <!-- Left -->
                        <div class="flex flex-wrap items-center gap-3">

                            <div
                                class="flex bg-muted/50 px-4 py-1.5 rounded-xl border border-border/50 shadow-inner">
                                <span
                                    class="text-xs font-bold text-primary tracking-widest uppercase">
                                    Coupon Registry
                                </span>
                            </div>

                            <!-- Status Tabs -->
                            <div
                                class="flex bg-muted/20 p-1 rounded-xl border border-border/60 shadow-inner">

                                <button
                                    @click="statusFilter=''; performSearch()"
                                    :class="statusFilter === '' ? 'bg-card shadow-sm text-primary ring-1 ring-border/20' : 'text-muted-foreground/60 hover:text-foreground'"
                                    class="px-4 py-1.5 rounded-lg text-[10px] font-black transition-all uppercase tracking-widest">
                                    All
                                </button>

                                <button
                                    @click="statusFilter='active'; performSearch()"
                                    :class="statusFilter === 'active' ? 'bg-card shadow-sm text-emerald-600 ring-1 ring-border/20' : 'text-muted-foreground/60 hover:text-foreground'"
                                    class="px-4 py-1.5 rounded-lg text-[10px] font-black transition-all uppercase tracking-widest">
                                    Active
                                </button>

                                <button
                                    @click="statusFilter='expired'; performSearch()"
                                    :class="statusFilter === 'expired' ? 'bg-card shadow-sm text-orange-600 ring-1 ring-border/20' : 'text-muted-foreground/60 hover:text-foreground'"
                                    class="px-4 py-1.5 rounded-lg text-[10px] font-black transition-all uppercase tracking-widest">
                                    Expired
                                </button>

                                <button
                                    @click="statusFilter='inactive'; performSearch()"
                                    :class="statusFilter === 'inactive' ? 'bg-card shadow-sm text-destructive ring-1 ring-border/20' : 'text-muted-foreground/60 hover:text-foreground'"
                                    class="px-4 py-1.5 rounded-lg text-[10px] font-black transition-all uppercase tracking-widest">
                                    Disabled
                                </button>

                            </div>

                            <!-- Bulk Actions -->
                            <div x-show="selectedCoupons.length > 0"
                                x-cloak
                                x-transition
                                class="flex items-center gap-2 animate-in fade-in slide-in-from-left-4 duration-300">

                                <x-ui.dropdown>

                                    <x-slot name="trigger">

                                        <x-ui.button
                                            variant="outline"
                                            size="sm"
                                            class="rounded-xl border-primary/20 bg-primary/5 text-primary font-bold shadow-sm whitespace-nowrap">

                                            <span x-text="selectedCoupons.length"></span>
                                            Selected

                                            <x-ui.icon
                                                name="chevron-down"
                                                size="3"
                                                class="ml-2" />
                                        </x-ui.button>

                                    </x-slot>

                                    <x-slot name="content">

                                        <x-ui.dropdown-label>
                                            Bulk Actions
                                        </x-ui.dropdown-label>

                                        <div class="p-1 space-y-1">

                                            <form
                                                action="{{ route('coupons.bulk-status') }}"
                                                method="POST">

                                                @csrf

                                                <input
                                                    type="hidden"
                                                    name="ids"
                                                    :value="JSON.stringify(selectedCoupons)">

                                                <input
                                                    type="hidden"
                                                    name="status"
                                                    value="active">

                                                <button
                                                    type="submit"
                                                    class="w-full text-left px-3 py-2 text-[10px] font-black hover:bg-emerald-500/10 rounded-xl flex items-center text-emerald-600 uppercase tracking-widest transition-colors">

                                                    <x-ui.icon
                                                        name="check-circle"
                                                        size="3.5"
                                                        class="mr-2" />

                                                    Activate

                                                </button>

                                            </form>

                                            <form
                                                action="{{ route('coupons.bulk-status') }}"
                                                method="POST">

                                                @csrf

                                                <input
                                                    type="hidden"
                                                    name="ids"
                                                    :value="JSON.stringify(selectedCoupons)">

                                                <input
                                                    type="hidden"
                                                    name="status"
                                                    value="inactive">

                                                <button
                                                    type="submit"
                                                    class="w-full text-left px-3 py-2 text-[10px] font-black hover:bg-orange-500/10 rounded-xl flex items-center text-orange-600 uppercase tracking-widest transition-colors">

                                                    <x-ui.icon
                                                        name="slash"
                                                        size="3.5"
                                                        class="mr-2" />

                                                    Disable

                                                </button>

                                            </form>

                                            <x-ui.separator class="my-1 opacity-40" />

                                            <form
                                                action="{{ route('coupons.bulk-delete') }}"
                                                method="POST"
                                                onsubmit="return confirm('Delete selected coupons?')">

                                                @csrf

                                                <input
                                                    type="hidden"
                                                    name="ids"
                                                    :value="JSON.stringify(selectedCoupons)">

                                                <button
                                                    type="submit"
                                                    class="w-full text-left px-3 py-2 text-[10px] font-black hover:bg-destructive/10 rounded-xl flex items-center text-destructive uppercase tracking-widest transition-colors">

                                                    <x-ui.icon
                                                        name="trash"
                                                        size="3.5"
                                                        class="mr-2" />

                                                    Delete

                                                </button>

                                            </form>

                                        </div>

                                    </x-slot>

                                </x-ui.dropdown>

                            </div>

                        </div>

                        <!-- Right -->
                        <div class="flex flex-wrap items-center gap-2 w-full lg:w-auto">

                            <x-ui.button
                                variant="outline"
                                size="sm"
                                class="flex-1 sm:flex-none rounded-xl font-bold uppercase tracking-widest text-[10px] h-9 shadow-sm"
                                onclick="alert('Export feature coming soon!')">

                                <x-ui.icon
                                    name="external-link"
                                    size="3"
                                    class="mr-2" />

                                Export

                            </x-ui.button>

                            <a href="{{ route('coupons.create') }}"
                                class="w-full sm:w-auto mt-2 sm:mt-0">

                                <x-ui.button
                                    size="sm"
                                    class="w-full rounded-xl font-bold uppercase tracking-widest text-[10px] h-9 shadow-lg shadow-primary/20">

                                    <x-ui.icon
                                        name="plus"
                                        size="3"
                                        class="mr-2" />

                                    Create Coupon

                                </x-ui.button>

                            </a>

                        </div>

                    </div>

                    <!-- Bottom Row -->
                    <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4 pt-2">

                        <!-- Left -->
                        <div class="flex flex-wrap items-center gap-2 w-full lg:w-auto">

                            <div class="flex items-center gap-2">

                                <span
                                    class="text-[10px] font-bold text-muted-foreground uppercase tracking-widest hidden sm:inline-block">
                                    Show
                                </span>

                                <select
                                    x-model="perPage"
                                    @change="performSearch()"
                                    class="h-9 px-3 py-1.5 rounded-xl border border-border bg-background/50 focus:bg-background focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all text-xs font-medium shadow-sm">

                                    <option value="5">5</option>
                                    <option value="10">10</option>
                                    <option value="15">15</option>
                                    <option value="20">20</option>
                                    <option value="50">50</option>

                                </select>

                            </div>

                        </div>

                        <!-- Search -->
                        <div class="relative group w-full lg:max-w-xs shrink-0">

                            <x-ui.icon
                                name="search"
                                size="4"
                                class="absolute left-3 top-1/2 -translate-y-1/2 text-muted-foreground group-focus-within:text-primary transition-colors" />

                            <input
                                type="text"
                                x-model="search"
                                @input.debounce.500ms="performSearch()"
                                placeholder="Search coupons..."
                                class="pl-9 pr-4 py-2 rounded-xl border border-border bg-background/50 focus:bg-background focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all w-full text-xs shadow-sm">

                            <div
                                x-show="isLoading"
                                class="absolute right-3 top-1/2 -translate-y-1/2">

                                <svg
                                    class="animate-spin h-3 w-3 text-primary"
                                    xmlns="http://www.w3.org/2000/svg"
                                    fill="none"
                                    viewBox="0 0 24 24">

                                    <circle
                                        class="opacity-25"
                                        cx="12"
                                        cy="12"
                                        r="10"
                                        stroke="currentColor"
                                        stroke-width="4">
                                    </circle>

                                    <path
                                        class="opacity-75"
                                        fill="currentColor"
                                        d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                                    </path>

                                </svg>

                            </div>

                        </div>

                    </div>

                </div>

            </x-ui.card-header>

            <!-- Table -->
            <x-ui.card-content class="p-0 relative min-h-[400px]">

                <!-- Loading Overlay -->
                <div
                    x-show="isLoading"
                    x-cloak
                    class="absolute inset-0 z-50 bg-background/40 backdrop-blur-sm flex items-center justify-center animate-in fade-in duration-300">

                    <div class="flex flex-col items-center gap-4">

                        <x-ui.icon
                            name="refresh-cw"
                            class="animate-spin text-primary"
                            size="8" />

                        <span
                            class="text-[10px] font-black uppercase tracking-[0.2em] text-primary">
                            Syncing Coupon Data
                        </span>

                    </div>

                </div>

                <!-- Table -->
                <div id="coupons-table-container">
                    @include('coupons.partials.table', [
                        'records' => $coupons
                    ])
                </div>

            </x-ui.card-content>

        </x-ui.card>

    </div>

    <style>
        [x-cloak] {
            display: none !important;
        }
    </style>

</x-layouts.app>