<x-layouts.app pageTitle="Customer Profile: {{ $customer->name }}" :hideSidebar="true">

    @include('customers.partials.scripts')

    <div x-data="@include('customers.partials.alpine-state')">
        {{-- ── Header Section ── --}}
        @include('customers.partials.header_top')

        {{-- ── Main Layout: Vertical Tabs + Content ── --}}
        <div class="max-w-7xl mx-auto px-6 lg:px-10 pb-20">
            <div class="flex flex-col lg:flex-row gap-8">
                
                {{-- Sidebar: Vertical Navigation --}}
                <aside class="w-full lg:w-64 shrink-0">
                    <div class="sticky top-6 space-y-2">
                        <div class="bg-card/40 backdrop-blur-xl border border-border/50 rounded-3xl p-3 shadow-xl">
                            <nav class="flex flex-col gap-1">
                                <template x-for="tab in [
                                    { id: 'overview', icon: 'user',         label: 'Profile'        },
                                    { id: 'addresses',icon: 'map-pin',      label: 'Addresses'      },
                                    { id: 'order',    icon: 'shopping-bag', label: 'Order Products' },
                                    { id: 'history',  icon: 'clock',        label: 'Order History'  },
                                    { id: 'finance',  icon: 'hash',         label: 'Finance'        },
                                    { id: 'system',   icon: 'settings',     label: 'System'         },
                                    { id: 'review',   icon: 'check-square', label: 'Order Review'   },
                                    { id: 'close',    icon: 'x-circle',     label: 'Tag & Close Profile' }
                                ].filter(t => t.id !== 'review' || activeTab === 'review')" :key="tab.id">
                                    <button
                                        type="button"
                                        @click="tab.id === 'close' ? closeCustomerProfile() : activeTab = tab.id"
                                        :class="activeTab === tab.id
                                            ? 'bg-primary text-primary-foreground shadow-lg shadow-primary/30'
                                            : 'text-muted-foreground hover:bg-card hover:text-foreground'"
                                        class="flex items-center gap-3 px-4 py-3.5 rounded-2xl font-black text-xs uppercase tracking-widest transition-all duration-200"
                                    >
                                        <x-ui.icon x-bind:name="tab.icon" size="4" />
                                        <span x-text="tab.label"></span>
                                        <template x-if="activeTab === tab.id">
                                            <x-ui.icon name="chevron-right" size="3" class="ml-auto" />
                                        </template>
                                    </button>
                                </template>
                            </nav>
                        </div>

                        {{-- Quick Info Card in Sidebar --}}
                        <div class="p-5 rounded-3xl bg-gradient-to-br from-primary/10 to-transparent border border-primary/5 hidden lg:block">
                            <p class="text-[10px] font-black uppercase tracking-widest text-primary mb-3">Customer Since</p>
                            <p class="text-xs font-bold text-foreground">{{ $customer->created_at->format('M Y') }}</p>
                            <div class="mt-4 pt-4 border-t border-primary/10">
                                <p class="text-[10px] font-black uppercase tracking-widest text-primary mb-1">Loyalty Points</p>
                                <p class="text-lg font-black text-foreground">1,250</p>
                            </div>
                        </div>
                    </div>
                </aside>

                {{-- Main Content Area --}}
                <main class="flex-1 min-w-0">
                    @include('customers.partials.tab-overview')
                    @include('customers.partials.tab-addresses')
                    @include('customers.partials.tab-order')
                    @include('customers.partials.tab-history')
                    @include('customers.partials.tab-finance')
                    @include('customers.partials.tab-system')
                    @include('customers.partials.tab-review')
                </main>
            </div>
        </div>

        @include('customers.partials.cart-sidebar')
        @include('customers.partials.modals')

        <x-ui.toaster />

    </div>

</x-layouts.app>