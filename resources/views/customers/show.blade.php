<x-layouts.app pageTitle="Customer Profile: {{ $customer->name }}">

    <div class="p-6 lg:p-10">
        <div class="max-w-6xl mx-auto">
            
            {{-- Header Actions --}}
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
                <div>
                    <h2 class="text-2xl font-black tracking-tight text-foreground">Customer Profile</h2>
                    <p class="text-sm text-muted-foreground mt-1">Detailed view of customer record and associated information.</p>
                </div>
                <div class="flex items-center gap-3">
                    <a href="{{ route('customers.index') }}">
                        <x-ui.button variant="outline" size="sm" class="rounded-xl border-border text-muted-foreground hover:bg-muted">
                            <x-ui.icon name="arrow-left" size="4" class="mr-2" />
                            Back to Customers
                        </x-ui.button>
                    </a>
                    <a href="{{ route('customers.edit', $customer) }}">
                        <x-ui.button size="sm" class="rounded-xl shadow-lg shadow-primary/20">
                            <x-ui.icon name="edit-3" size="4" class="mr-2" />
                            Edit Profile
                        </x-ui.button>
                    </a>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                
                {{-- Left Sidebar: Customer Summary Card --}}
                <div class="lg:col-span-1 space-y-6">
                    <x-ui.card class="overflow-hidden border-border/60 shadow-2xl bg-card/30 backdrop-blur-2xl rounded-3xl relative">
                        {{-- Background Accent --}}
                        <div class="absolute inset-0 bg-gradient-to-br from-primary/5 via-transparent to-transparent pointer-events-none"></div>
                        
                        <div class="p-8 flex flex-col items-center text-center border-b border-border/40 relative">
                            <div class="size-24 rounded-3xl bg-gradient-to-tr from-primary/20 to-primary/5 border border-primary/10 text-primary flex items-center justify-center shadow-inner font-black text-3xl mb-4">
                                {{ $customer->initials() }}
                            </div>
                            <h3 class="text-xl font-black tracking-tight text-foreground">{{ $customer->name }}</h3>
                            <p class="text-sm font-mono text-muted-foreground mt-1">#{{ sprintf('%04d', $customer->id) }}</p>
                            
                            @php
                                $statusClass = match($customer->status) {
                                    'active'    => 'bg-emerald-500/10 text-emerald-500 border-emerald-500/20',
                                    'suspended' => 'bg-red-500/10 text-red-500 border-red-500/20',
                                    default     => 'bg-orange-500/10 text-orange-500 border-orange-500/20',
                                };
                            @endphp
                            <div class="mt-4">
                                <span class="text-[10px] font-black uppercase tracking-widest px-3 py-1.5 rounded-xl border {{ $statusClass }}">
                                    {{ $customer->status }}
                                </span>
                            </div>
                        </div>

                        <div class="p-6 bg-muted/5 space-y-4">
                            <div class="flex items-center gap-3">
                                <div class="size-8 rounded-xl bg-primary/10 text-primary flex items-center justify-center shrink-0">
                                    <x-ui.icon name="mail" size="4" />
                                </div>
                                <div class="min-w-0 flex-1">
                                    <p class="text-[10px] font-black uppercase tracking-widest text-muted-foreground">Email</p>
                                    <p class="text-sm font-medium text-foreground truncate">{{ $customer->email ?: 'Not Provided' }}</p>
                                </div>
                            </div>
                            <div class="flex items-center gap-3">
                                <div class="size-8 rounded-xl bg-primary/10 text-primary flex items-center justify-center shrink-0">
                                    <x-ui.icon name="phone" size="4" />
                                </div>
                                <div class="min-w-0 flex-1">
                                    <p class="text-[10px] font-black uppercase tracking-widest text-muted-foreground">Phone</p>
                                    <p class="text-sm font-medium text-foreground">{{ $customer->phone ?: 'Not Provided' }}</p>
                                </div>
                            </div>
                            <div class="flex items-center gap-3">
                                <div class="size-8 rounded-xl bg-primary/10 text-primary flex items-center justify-center shrink-0">
                                    <x-ui.icon name="calendar" size="4" />
                                </div>
                                <div class="min-w-0 flex-1">
                                    <p class="text-[10px] font-black uppercase tracking-widest text-muted-foreground">Registered</p>
                                    <p class="text-sm font-medium text-foreground">{{ $customer->created_at->format('M d, Y') }}</p>
                                </div>
                            </div>
                        </div>
                    </x-ui.card>

                    {{-- Customer Analytics/Mini Stats --}}
                    <div class="grid grid-cols-2 gap-4">
                        <x-ui.card class="p-5 border-border/60 bg-card/30 backdrop-blur-xl rounded-3xl shadow-sm text-center">
                            <x-ui.icon name="shopping-cart" size="5" class="text-primary mx-auto mb-2 opacity-80" />
                            <p class="text-[10px] font-black uppercase tracking-widest text-muted-foreground mb-1">Total Orders</p>
                            <p class="text-xl font-black text-foreground">{{ $customer->orders()->count() ?? 0 }}</p>
                        </x-ui.card>
                        <x-ui.card class="p-5 border-border/60 bg-card/30 backdrop-blur-xl rounded-3xl shadow-sm text-center">
                            <x-ui.icon name="map-pin" size="5" class="text-primary mx-auto mb-2 opacity-80" />
                            <p class="text-[10px] font-black uppercase tracking-widest text-muted-foreground mb-1">Addresses</p>
                            <p class="text-xl font-black text-foreground">{{ $customer->addresses->count() }}</p>
                        </x-ui.card>
                    </div>
                </div>

                {{-- Right Main Area: Details Tabs/Sections --}}
                <div class="lg:col-span-2 space-y-8">
                    
                    <x-ui.card class="overflow-hidden border-border/60 shadow-xl bg-card/30 backdrop-blur-2xl rounded-3xl">
                        <div class="p-6 border-b border-border/40 bg-muted/10">
                            <div class="flex items-center gap-3">
                                <div class="size-10 rounded-xl bg-blue-500/10 text-blue-500 flex items-center justify-center">
                                    <x-ui.icon name="hash" size="5" />
                                </div>
                                <h3 class="text-lg font-bold tracking-tight text-foreground">Tax & Financial Details</h3>
                            </div>
                        </div>
                        <div class="p-8 grid grid-cols-1 md:grid-cols-2 gap-8">
                            {{-- Tax Info --}}
                            <div class="space-y-6">
                                <div>
                                    <h4 class="text-[10px] font-black uppercase tracking-[0.2em] text-muted-foreground mb-4 border-b border-border/40 pb-2">Tax Compliance</h4>
                                    <div class="space-y-4">
                                        <div class="group">
                                            <p class="text-[10px] font-black uppercase tracking-widest text-muted-foreground group-hover:text-primary transition-colors">GST Number</p>
                                            <div class="flex items-center gap-2 mt-1">
                                                <p class="text-sm font-mono font-bold text-foreground bg-muted/30 px-3 py-1.5 rounded-lg border border-border/40 w-full">{{ $customer->gst_no ?: '—' }}</p>
                                            </div>
                                        </div>
                                        <div class="group">
                                            <p class="text-[10px] font-black uppercase tracking-widest text-muted-foreground group-hover:text-primary transition-colors">PAN Number</p>
                                            <div class="flex items-center gap-2 mt-1">
                                                <p class="text-sm font-mono font-bold text-foreground bg-muted/30 px-3 py-1.5 rounded-lg border border-border/40 w-full">{{ $customer->pan_no ?: '—' }}</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            {{-- Credit Terms --}}
                            <div class="space-y-6">
                                <div>
                                    <h4 class="text-[10px] font-black uppercase tracking-[0.2em] text-muted-foreground mb-4 border-b border-border/40 pb-2">Credit Policy</h4>
                                    <div class="space-y-4">
                                        <div class="group">
                                            <p class="text-[10px] font-black uppercase tracking-widest text-muted-foreground group-hover:text-primary transition-colors">Credit Limit</p>
                                            <div class="flex items-center gap-2 mt-1">
                                                <p class="text-sm font-bold text-foreground bg-muted/30 px-3 py-1.5 rounded-lg border border-border/40 w-full text-emerald-500">
                                                    ₹{{ number_format($customer->credit_limit, 2) }}
                                                </p>
                                            </div>
                                        </div>
                                        <div class="group">
                                            <p class="text-[10px] font-black uppercase tracking-widest text-muted-foreground group-hover:text-primary transition-colors">Credit Days</p>
                                            <div class="flex items-center gap-2 mt-1">
                                                <p class="text-sm font-bold text-foreground bg-muted/30 px-3 py-1.5 rounded-lg border border-border/40 w-full">
                                                    {{ $customer->credit_days ?: 0 }} Days
                                                </p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </x-ui.card>

                    {{-- Addresses Section --}}
                    <x-ui.card class="overflow-hidden border-border/60 shadow-xl bg-card/30 backdrop-blur-2xl rounded-3xl">
                        <div class="p-6 border-b border-border/40 bg-muted/10 flex items-center justify-between">
                            <div class="flex items-center gap-3">
                                <div class="size-10 rounded-xl bg-purple-500/10 text-purple-500 flex items-center justify-center">
                                    <x-ui.icon name="map" size="5" />
                                </div>
                                <h3 class="text-lg font-bold tracking-tight text-foreground">Registered Addresses</h3>
                            </div>
                            {{-- Optional: Add new address button could go here --}}
                        </div>
                        <div class="p-6 bg-background/30">
                            @if($customer->addresses->count())
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                    @foreach($customer->addresses as $address)
                                    <div class="p-5 rounded-2xl bg-card border border-border/60 shadow-sm hover:shadow-md hover:border-primary/30 transition-all relative group">
                                        @if($address->is_default)
                                            <div class="absolute -top-2.5 -right-2.5 bg-primary text-primary-foreground text-[9px] font-black uppercase tracking-widest px-2 py-1 rounded-lg shadow-lg">
                                                Default
                                            </div>
                                        @endif
                                        <div class="flex items-center gap-2 mb-3">
                                            <x-ui.icon name="home" size="4" class="text-muted-foreground group-hover:text-primary transition-colors" />
                                            <span class="text-xs font-black uppercase tracking-widest text-foreground">{{ $address->label ?: 'Address' }}</span>
                                        </div>
                                        <div class="space-y-1 text-sm text-muted-foreground">
                                            <p class="font-bold text-foreground">{{ $address->address_line_1 }}</p>
                                            @if($address->address_line_2)
                                                <p>{{ $address->address_line_2 }}</p>
                                            @endif
                                            <p class="pt-2 border-t border-border/40 mt-2 flex items-center gap-1.5">
                                                <x-ui.icon name="map-pin" size="3" class="opacity-50" />
                                                {{ collect([$address->city, $address->state, $address->pincode])->filter()->implode(', ') }}
                                            </p>
                                        </div>
                                    </div>
                                    @endforeach
                                </div>
                            @else
                                <div class="text-center py-10 px-4 rounded-2xl border-2 border-dashed border-border/60 bg-muted/5">
                                    <div class="size-12 rounded-full bg-muted flex items-center justify-center mx-auto mb-3">
                                        <x-ui.icon name="map-pin" size="5" class="text-muted-foreground" />
                                    </div>
                                    <h4 class="text-sm font-bold text-foreground">No Addresses Found</h4>
                                    <p class="text-xs text-muted-foreground mt-1 max-w-sm mx-auto">This customer does not have any registered addresses yet.</p>
                                </div>
                            @endif
                        </div>
                    </x-ui.card>

                    {{-- Activity / Metadata Section --}}
                    <x-ui.card class="overflow-hidden border-border/60 shadow-xl bg-card/30 backdrop-blur-2xl rounded-3xl">
                        <div class="p-6 border-b border-border/40 bg-muted/10">
                            <div class="flex items-center gap-3">
                                <div class="size-10 rounded-xl bg-orange-500/10 text-orange-500 flex items-center justify-center">
                                    <x-ui.icon name="clock" size="5" />
                                </div>
                                <h3 class="text-lg font-bold tracking-tight text-foreground">System Metadata</h3>
                            </div>
                        </div>
                        <div class="p-6 grid grid-cols-1 sm:grid-cols-3 gap-4">
                            <div class="p-4 rounded-2xl bg-muted/10 border border-border/40 space-y-1 text-center">
                                <p class="text-[10px] font-black uppercase tracking-widest text-muted-foreground">Created On</p>
                                <p class="text-sm font-bold text-foreground">{{ $customer->created_at->format('M d, Y h:i A') }}</p>
                            </div>
                            <div class="p-4 rounded-2xl bg-muted/10 border border-border/40 space-y-1 text-center">
                                <p class="text-[10px] font-black uppercase tracking-widest text-muted-foreground">Last Updated</p>
                                <p class="text-sm font-bold text-foreground">{{ $customer->updated_at->format('M d, Y h:i A') }}</p>
                                <p class="text-[9px] text-muted-foreground/60">{{ $customer->updated_at->diffForHumans() }}</p>
                            </div>
                            <div class="p-4 rounded-2xl bg-muted/10 border border-border/40 space-y-1 text-center flex flex-col justify-center items-center">
                                <p class="text-[10px] font-black uppercase tracking-widest text-muted-foreground mb-2">Record Actions</p>
                                <form action="{{ route('customers.destroy', $customer) }}" method="POST" onsubmit="return confirm('Archive this customer?')" class="w-full">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="w-full py-1.5 px-3 rounded-lg bg-destructive/10 text-destructive text-xs font-bold hover:bg-destructive hover:text-destructive-foreground transition-colors">
                                        Archive Record
                                    </button>
                                </form>
                            </div>
                        </div>
                    </x-ui.card>

                </div>
            </div>
            
        </div>
    </div>
</x-layouts.app>
