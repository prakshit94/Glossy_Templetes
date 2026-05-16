{{-- ══ TAB: Addresses ══ --}}
<div x-show="activeTab === 'addresses'" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-4" x-transition:enter-end="opacity-100 translate-y-0" x-cloak>
    <div class="flex items-center justify-between mb-8">
        <h3 class="text-xl font-black tracking-tight text-foreground">Registered Addresses</h3>
        <x-ui.button @click.prevent="openAddModal" class="rounded-xl h-10 px-5 gap-2 shadow-lg shadow-primary/20 text-xs font-bold uppercase tracking-widest">
            <x-ui.icon name="plus" size="4" /> Add Address
        </x-ui.button>
    </div>

    @if($customer->addresses->count())
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @foreach($customer->addresses as $address)
                <div class="group relative p-6 rounded-3xl bg-card border border-border/50 shadow-sm hover:shadow-2xl hover:border-primary/40 hover:-translate-y-1 transition-all duration-300 overflow-hidden">
                    @if($address->is_default)
                        <div class="absolute top-0 right-0 bg-primary text-primary-foreground text-[9px] font-black uppercase tracking-widest px-4 py-1.5 rounded-bl-xl shadow-md">
                            Default
                        </div>
                    @endif

                    <div class="flex items-center gap-3 mb-5">
                        <div class="size-10 rounded-2xl bg-purple-500/10 text-purple-500 flex items-center justify-center">
                            <x-ui.icon name="map-pin" size="5" />
                        </div>
                        <span class="text-sm font-black uppercase tracking-widest text-foreground">{{ $address->label ?: 'Address' }}</span>
                    </div>

                    <p class="text-sm font-bold text-foreground mb-1">{{ $address->address_line_1 }}</p>
                    @if($address->address_line_2)
                        <p class="text-xs text-muted-foreground mb-4">{{ $address->address_line_2 }}</p>
                    @endif

                    <div class="mt-4 pt-4 border-t border-border/40 space-y-2">
                        <div class="flex justify-between">
                            <span class="text-[10px] uppercase tracking-widest text-muted-foreground font-bold">Village</span>
                            <span class="text-xs font-bold text-foreground">{{ $address->village?->village_name ?? $address->village_name ?? '—' }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-[10px] uppercase tracking-widest text-muted-foreground font-bold">Post Office</span>
                            <span class="text-xs font-bold text-foreground">{{ $address->village?->post_so_name ?? $address->post_office ?? '—' }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-[10px] uppercase tracking-widest text-muted-foreground font-bold">Taluka</span>
                            <span class="text-xs font-bold text-foreground">{{ $address->village?->taluka_name ?? $address->taluka ?? '—' }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-[10px] uppercase tracking-widest text-muted-foreground font-bold">District</span>
                            <span class="text-xs font-bold text-foreground">{{ $address->village?->district_name ?? $address->city ?? '—' }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-[10px] uppercase tracking-widest text-muted-foreground font-bold">State</span>
                            <span class="text-xs font-bold text-foreground">{{ !empty($address->village?->state_name) ? $address->village->state_name : (!empty($address->state) ? $address->state : '—') }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-[10px] uppercase tracking-widest text-muted-foreground font-bold">Pincode</span>
                            <span class="text-xs font-bold font-mono text-foreground">{{ $address->village?->pincode ?? $address->pincode ?? '—' }}</span>
                        </div>
                    </div>

                    {{-- Floating Actions on Hover --}}
                    <div class="absolute bottom-4 right-4 flex items-center gap-2 opacity-0 translate-y-4 group-hover:opacity-100 group-hover:translate-y-0 transition-all duration-300">
                        <button @click="openEditModal({{ $address->toJson() }})" class="size-9 rounded-xl bg-card border border-border/80 text-foreground hover:text-primary hover:border-primary flex items-center justify-center shadow-lg transition-colors">
                            <x-ui.icon name="edit-3" size="4" />
                        </button>
                        <button @click="openDeleteModal({{ $address->toJson() }})" class="size-9 rounded-xl bg-card border border-border/80 text-destructive hover:bg-destructive hover:text-destructive-foreground hover:border-destructive flex items-center justify-center shadow-lg transition-colors">
                            <x-ui.icon name="trash-2" size="4" />
                        </button>
                    </div>
                </div>
            @endforeach
        </div>
    @else
        <div class="text-center py-24 px-4 rounded-3xl border-2 border-dashed border-border/60 bg-muted/5">
            <div class="size-20 rounded-3xl bg-muted flex items-center justify-center mx-auto mb-6">
                <x-ui.icon name="map" size="8" class="text-muted-foreground/50" />
            </div>
            <h4 class="text-lg font-black text-foreground">No Registered Addresses</h4>
            <p class="text-sm text-muted-foreground mt-2 max-w-sm mx-auto">This customer has no addresses on file. Add one to enable shipping and billing.</p>
            <x-ui.button @click.prevent="openAddModal" class="mt-8 h-12 px-6 rounded-xl gap-2 shadow-xl shadow-primary/20 text-xs font-bold uppercase tracking-widest">
                <x-ui.icon name="plus" size="4" /> Add First Address
            </x-ui.button>
        </div>
    @endif
</div>
