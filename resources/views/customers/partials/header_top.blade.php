{{-- ── Header Section ── --}}
<div class="relative overflow-hidden mb-4">
    <div class="absolute -top-32 -right-32 size-96 bg-primary/10 rounded-full blur-[100px] pointer-events-none"></div>
    <div class="absolute -bottom-32 -left-32 size-96 bg-blue-500/10 rounded-full blur-[100px] pointer-events-none"></div>
    <div class="absolute inset-0 bg-gradient-to-r from-primary/5 via-transparent to-purple-500/5 pointer-events-none"></div>

    <div class="max-w-7xl mx-auto px-6 lg:px-10 pt-12 pb-4 relative z-10">
        
        {{-- Header Content --}}
        <div class="flex flex-col md:flex-row md:items-end justify-between gap-6 mb-6">
            <div class="flex items-center gap-5">
                {{-- Avatar --}}
                <div class="relative shrink-0 group">
                    <div class="size-16 rounded-2xl bg-gradient-to-br from-primary to-primary/80 text-primary-foreground flex items-center justify-center font-black text-2xl shadow-2xl shadow-primary/40 ring-4 ring-primary/10 transition-transform duration-300 group-hover:scale-105 group-hover:-rotate-3">
                        {{ $customer->initials() }}
                    </div>
                    @php
                        $dotClass = match($customer->status) {
                            'active'    => 'bg-emerald-500 shadow-emerald-500/50',
                            'suspended' => 'bg-red-500 shadow-red-500/50',
                            default     => 'bg-orange-400 shadow-orange-500/50',
                        };
                    @endphp
                    <span class="absolute -bottom-1 -right-1 size-4 {{ $dotClass }} rounded-full border-4 border-background shadow-lg"></span>
                </div>
                
                <div>
                    <div class="flex items-center gap-3 mb-1">
                        <h1 class="text-2xl font-black tracking-tight text-foreground bg-clip-text">{{ $customer->name }}</h1>
                        @php
                            $badgeClass = match($customer->status) {
                                'active'    => 'bg-emerald-500/15 text-emerald-500 border-emerald-500/30',
                                'suspended' => 'bg-red-500/15 text-red-500 border-red-500/30',
                                default     => 'bg-orange-500/15 text-orange-500 border-orange-500/30',
                            };
                        @endphp
                        <span class="text-[9px] font-black uppercase tracking-[0.2em] px-2.5 py-1 rounded-xl border shadow-sm {{ $badgeClass }}">
                            {{ $customer->status }}
                        </span>
                    </div>
                    <p class="text-xs text-muted-foreground/80 font-medium flex items-center gap-3">
                        <span class="font-mono bg-muted/50 px-2 py-0.5 rounded-md text-foreground">#{{ sprintf('%04d', $customer->id) }}</span>
                        <span class="size-1 rounded-full bg-border/80"></span>
                        Registered {{ $customer->created_at->format('F d, Y') }}
                    </p>
                </div>
            </div>

            <div class="flex items-center gap-3">
                {{-- Editing Order Info Widget --}}
                <template x-if="editingOrderDetails">
                    <div class="hidden lg:flex flex-col items-end mr-4 bg-amber-500/10 border border-amber-500/20 rounded-2xl p-3 text-amber-700 shadow-sm backdrop-blur-md transition-all duration-500 animate-in fade-in slide-in-from-right-4">
                        <div class="flex items-center gap-2 mb-1.5">
                            <span class="size-2 rounded-full bg-amber-500 animate-pulse shadow-[0_0_8px_rgba(245,158,11,0.6)]"></span>
                            <h4 class="text-[10px] font-black uppercase tracking-widest text-amber-600">Editing <span x-text="editingOrderDetails.order_no"></span></h4>
                        </div>
                        <div class="flex items-center gap-4 text-[9px] font-medium opacity-90 mt-0.5">
                            <div class="flex items-center gap-1.5" title="Placed At">
                                <x-ui.icon name="calendar" size="3" class="opacity-70" /> 
                                <span x-text="new Date(editingOrderDetails.created_at).toLocaleString('en-US', { day: '2-digit', month: 'short', year: 'numeric', hour: 'numeric', minute:'2-digit' })"></span>
                            </div>
                            <template x-if="editingOrderDetails.creator">
                                <div class="flex items-center gap-1.5" title="Placed By">
                                    <x-ui.icon name="user" size="3" class="opacity-70" /> <span x-text="editingOrderDetails.creator.name"></span>
                                </div>
                            </template>
                            <template x-if="editingOrderDetails.updater && editingOrderDetails.updated_by !== editingOrderDetails.created_by">
                                <div class="flex items-center gap-1.5 text-amber-800 font-bold" title="Last Updated By">
                                    <x-ui.icon name="edit-3" size="3" class="opacity-70" /> <span x-text="editingOrderDetails.updater.name"></span>
                                </div>
                            </template>
                        </div>
                    </div>
                </template>

                <button type="button" @click.prevent="isCartOpen = true" style="z-index: 50;" class="group relative flex items-center justify-center h-9 px-4 rounded-xl bg-card border border-border/80 text-[10px] font-black uppercase tracking-widest text-foreground shadow-sm hover:shadow-md hover:border-border transition-all duration-300 hover:-translate-y-0.5">
                    <x-ui.icon name="shopping-cart" size="3.5" class="mr-2 opacity-70 group-hover:opacity-100 transition-opacity" /> Cart
                    <span x-show="cart && cart.length > 0" class="absolute -top-2 -right-2 flex size-5 items-center justify-center rounded-full bg-primary text-[9px] font-black text-primary-foreground shadow-lg" x-text="cart ? cart.length : 0" x-cloak></span>
                </button>
                <a href="{{ route('customers.index') }}" class="group relative flex items-center justify-center h-9 px-4 rounded-xl bg-card border border-border/80 text-[10px] font-black uppercase tracking-widest text-foreground shadow-sm hover:shadow-md hover:border-border transition-all duration-300 hover:-translate-y-0.5">
                    <x-ui.icon name="arrow-left" size="3.5" class="mr-2 opacity-70 group-hover:opacity-100 transition-opacity" /> Back
                </a>
                <a href="{{ route('customers.edit', $customer) }}" class="group relative flex items-center justify-center h-9 px-4 rounded-xl bg-gradient-to-r from-primary to-primary/90 text-primary-foreground text-[10px] font-black uppercase tracking-widest shadow-lg shadow-primary/25 hover:shadow-primary/40 transition-all duration-300 hover:-translate-y-0.5">
                    <x-ui.icon name="edit-3" size="3.5" class="mr-2 opacity-90" /> Edit Profile
                </a>
            </div>
        </div>
    </div>
</div>
