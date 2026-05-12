<x-layouts.app pageTitle="Customer: {{ $customer->name }}">

    <div class="p-6 lg:p-10">
        <div class="max-w-5xl mx-auto space-y-6">

            {{-- Header --}}
            <div class="flex items-center justify-between">
                <a href="{{ route('customers.index') }}">
                    <x-ui.button variant="outline" size="sm" class="rounded-xl border-border text-muted-foreground hover:bg-muted">
                        <x-ui.icon name="arrow-left" size="3" class="mr-2" />
                        Back to Customers
                    </x-ui.button>
                </a>
                <a href="{{ route('customers.edit', $customer) }}">
                    <x-ui.button size="sm" class="rounded-xl shadow-lg shadow-primary/20">
                        <x-ui.icon name="edit-3" size="3" class="mr-2" />
                        Edit Customer
                    </x-ui.button>
                </a>
            </div>

            {{-- Profile Card --}}
            <x-ui.card class="overflow-hidden border-border/60 shadow-2xl bg-card/30 backdrop-blur-2xl rounded-3xl">
                <div class="p-8 border-b border-border/40 bg-muted/10">
                    <div class="flex items-center gap-6">
                        <div class="size-20 rounded-3xl bg-gradient-to-br from-primary/20 to-primary/5 border border-primary/10 flex items-center justify-center font-black text-primary text-2xl shadow-inner">
                            {{ $customer->initials() }}
                        </div>
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center gap-3 flex-wrap">
                                <h1 class="text-2xl font-black tracking-tight text-foreground uppercase">{{ $customer->name }}</h1>
                                <span class="text-[10px] font-mono font-bold text-muted-foreground/40">#{{ sprintf('%04d', $customer->id) }}</span>
                                @php
                                    $statusClass = match($customer->status) {
                                        'active'    => 'bg-emerald-500/10 text-emerald-500 border-emerald-500/20',
                                        'suspended' => 'bg-red-500/10 text-red-500 border-red-500/20',
                                        default     => 'bg-orange-500/10 text-orange-500 border-orange-500/20',
                                    };
                                @endphp
                                <span class="text-[9px] font-black uppercase tracking-widest px-2.5 py-1 rounded-lg border {{ $statusClass }}">
                                    {{ $customer->status }}
                                </span>
                            </div>
                            <div class="flex items-center gap-4 mt-2 flex-wrap">
                                @if($customer->email)
                                    <span class="text-sm text-muted-foreground flex items-center gap-1.5">
                                        <x-ui.icon name="mail" size="3.5" class="text-muted-foreground/40" />
                                        {{ $customer->email }}
                                    </span>
                                @endif
                                @if($customer->phone)
                                    <span class="text-sm text-muted-foreground flex items-center gap-1.5">
                                        <x-ui.icon name="phone" size="3.5" class="text-muted-foreground/40" />
                                        {{ $customer->phone }}
                                    </span>
                                @endif
                                <span class="text-xs text-muted-foreground/40 flex items-center gap-1.5">
                                    <x-ui.icon name="clock" size="3" class="text-muted-foreground/30" />
                                    Registered {{ $customer->created_at->diffForHumans() }}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 divide-y md:divide-y-0 md:divide-x divide-border/40">
                    <div class="p-6">
                        <p class="text-[10px] font-black uppercase tracking-widest text-muted-foreground/60 mb-3 flex items-center gap-2">
                            <x-ui.icon name="credit-card" size="3.5" class="text-primary" />
                            Credit Terms
                        </p>
                        <div class="space-y-2">
                            <div class="flex items-center justify-between">
                                <span class="text-xs text-muted-foreground">Credit Limit</span>
                                <span class="text-sm font-black text-foreground">₹{{ number_format($customer->credit_limit, 0) }}</span>
                            </div>
                            <div class="flex items-center justify-between">
                                <span class="text-xs text-muted-foreground">Credit Days</span>
                                <span class="text-sm font-black text-foreground">{{ $customer->credit_days }} days</span>
                            </div>
                        </div>
                    </div>

                    <div class="p-6">
                        <p class="text-[10px] font-black uppercase tracking-widest text-muted-foreground/60 mb-3 flex items-center gap-2">
                            <x-ui.icon name="hash" size="3.5" class="text-primary" />
                            Tax Information
                        </p>
                        <div class="space-y-2">
                            <div class="flex items-center justify-between">
                                <span class="text-xs text-muted-foreground">GST No</span>
                                <span class="text-xs font-black font-mono text-foreground">{{ $customer->gst_no ?: '—' }}</span>
                            </div>
                            <div class="flex items-center justify-between">
                                <span class="text-xs text-muted-foreground">PAN No</span>
                                <span class="text-xs font-black font-mono text-foreground">{{ $customer->pan_no ?: '—' }}</span>
                            </div>
                        </div>
                    </div>

                    <div class="p-6">
                        <p class="text-[10px] font-black uppercase tracking-widest text-muted-foreground/60 mb-3 flex items-center gap-2">
                            <x-ui.icon name="calendar" size="3.5" class="text-primary" />
                            Timeline
                        </p>
                        <div class="space-y-2">
                            <div class="flex items-center justify-between">
                                <span class="text-xs text-muted-foreground">Created</span>
                                <span class="text-xs font-black text-foreground">{{ $customer->created_at->format('M d, Y') }}</span>
                            </div>
                            <div class="flex items-center justify-between">
                                <span class="text-xs text-muted-foreground">Last Updated</span>
                                <span class="text-xs font-black text-foreground">{{ $customer->updated_at->format('M d, Y') }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </x-ui.card>

            {{-- Addresses --}}
            @if($customer->addresses->count())
            <x-ui.card class="overflow-hidden border-border/60 shadow-2xl bg-card/30 backdrop-blur-2xl rounded-3xl">
                <div class="p-6 border-b border-border/40 bg-muted/10">
                    <h3 class="text-sm font-bold text-foreground flex items-center gap-2">
                        <x-ui.icon name="map-pin" size="4" class="text-primary" />
                        Registered Addresses
                    </h3>
                </div>
                <div class="p-6 grid grid-cols-1 md:grid-cols-2 gap-4">
                    @foreach($customer->addresses as $address)
                    <div class="p-4 rounded-2xl bg-muted/10 border border-border/40 space-y-1">
                        <div class="flex items-center justify-between">
                            <span class="text-[10px] font-black uppercase tracking-widest text-primary">{{ $address->label }}</span>
                            @if($address->is_default)
                                <span class="text-[8px] font-black uppercase tracking-widest px-1.5 py-0.5 rounded-md bg-primary/10 text-primary border border-primary/20">Default</span>
                            @endif
                        </div>
                        <p class="text-xs font-bold text-foreground">{{ $address->address_line_1 }}</p>
                        @if($address->address_line_2)
                            <p class="text-xs text-muted-foreground">{{ $address->address_line_2 }}</p>
                        @endif
                        <p class="text-xs text-muted-foreground">
                            {{ collect([$address->city, $address->state, $address->pincode])->filter()->implode(', ') }}
                        </p>
                    </div>
                    @endforeach
                </div>
            </x-ui.card>
            @endif

        </div>
    </div>
</x-layouts.app>
