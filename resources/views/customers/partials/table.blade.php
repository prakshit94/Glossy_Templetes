@if($customers->hasPages())
    <div class="p-4 border-b border-border/40 flex justify-end items-center">
        {{ $customers->links() }}
    </div>
@endif

<x-ui.table>
    <x-ui.table-header class="bg-muted/20">
        <x-ui.table-row class="border-b border-border/60">
            <x-ui.table-head class="w-10">
                <input type="checkbox" x-model="allSelected" @change="toggleAll"
                    class="rounded border-border bg-background text-primary focus:ring-primary/20">
            </x-ui.table-head>
            <x-ui.table-head class="text-[10px] font-black uppercase tracking-widest text-muted-foreground/60">Customer Identity</x-ui.table-head>
            <x-ui.table-head class="text-[10px] font-black uppercase tracking-widest text-muted-foreground/60">Account Status</x-ui.table-head>
            <x-ui.table-head class="text-[10px] font-black uppercase tracking-widest text-muted-foreground/60 text-center">Credit Profile</x-ui.table-head>
            <x-ui.table-head class="text-[10px] font-black uppercase tracking-widest text-muted-foreground/60">Contact & Tax</x-ui.table-head>
            <x-ui.table-head class="text-[10px] font-black uppercase tracking-widest text-muted-foreground/60">Registration Ledger</x-ui.table-head>
            <x-ui.table-head class="text-right text-[10px] font-black uppercase tracking-widest text-muted-foreground/60">Actions</x-ui.table-head>
        </x-ui.table-row>
    </x-ui.table-header>
    <x-ui.table-body>
        @forelse($customers as $customer)
        <x-ui.table-row x-bind:class="selectedCustomers.includes({{ $customer->id }}) ? 'bg-primary/5' : 'hover:bg-primary/[0.02] transition-colors'" class="border-b border-border/40 group">
            <!-- Selection -->
            <x-ui.table-cell>
                <input type="checkbox" name="customer_ids[]" value="{{ $customer->id }}" :checked="selectedCustomers.includes({{ $customer->id }})" @change="toggleCustomer({{ $customer->id }})"
                    class="rounded border-border bg-background text-primary focus:ring-primary/20">
            </x-ui.table-cell>

            <!-- Customer Identity -->
            <x-ui.table-cell>
                <div class="flex items-center gap-4">
                    <div class="relative shrink-0">
                        <div class="size-12 rounded-2xl bg-gradient-to-br from-primary/20 to-primary/5 border border-primary/10 flex items-center justify-center font-black text-primary shadow-inner group-hover:scale-110 transition-transform duration-500 overflow-hidden">
                            <span class="text-lg">{{ $customer->initials() }}</span>
                        </div>
                    </div>
                    <div class="flex flex-col min-w-0">
                        <div class="flex items-center gap-2">
                            <span class="text-sm font-black tracking-tight text-foreground truncate uppercase">{{ $customer->name }}</span>
                            <span class="text-[9px] font-mono font-bold text-muted-foreground/30">#{{ sprintf('%04d', $customer->id) }}</span>
                        </div>
                        <span class="text-[11px] font-medium text-muted-foreground/60 truncate lowercase select-all italic">{{ $customer->email ?? '—' }}</span>
                    </div>
                </div>
            </x-ui.table-cell>

            <!-- Status -->
            <x-ui.table-cell>
                @if($customer->trashed())
                    <x-ui.badge variant="destructive" className="uppercase text-[9px] font-black tracking-[0.1em] px-2.5 py-1 rounded-lg border-red-500/20 bg-red-500/10 text-red-500 shadow-sm">Archived</x-ui.badge>
                @else
                    @php
                        $statusClass = match($customer->status) {
                            'active'    => 'border-emerald-500/20 bg-emerald-500/10 text-emerald-600',
                            'suspended' => 'border-red-500/20 bg-red-500/10 text-red-600',
                            default     => 'border-orange-500/20 bg-orange-500/10 text-orange-600',
                        };
                    @endphp
                    <x-ui.badge variant="outline" className="uppercase text-[9px] font-black tracking-[0.1em] px-2.5 py-1 rounded-lg shadow-sm {{ $statusClass }}">
                        {{ $customer->status }}
                    </x-ui.badge>
                @endif
            </x-ui.table-cell>

            <!-- Credit Profile -->
            <x-ui.table-cell>
                <div class="flex flex-col items-center gap-2">
                    <div class="flex items-center gap-1.5">
                        <div class="size-6 rounded-lg flex items-center justify-center transition-colors {{ $customer->credit_limit > 0 ? 'bg-blue-500/10 text-blue-500 border border-blue-500/20' : 'bg-muted/10 text-muted-foreground/20' }}">
                            <x-ui.icon name="credit-card" size="3.5" />
                        </div>
                        <div class="size-6 rounded-lg flex items-center justify-center transition-colors {{ $customer->credit_days > 0 ? 'bg-emerald-500/10 text-emerald-500 border border-emerald-500/20' : 'bg-muted/10 text-muted-foreground/20' }}">
                            <x-ui.icon name="calendar" size="3.5" />
                        </div>
                    </div>
                    @if($customer->credit_limit > 0)
                        <span class="text-[8px] font-black text-blue-500 uppercase tracking-widest">₹{{ number_format($customer->credit_limit, 0) }}</span>
                    @else
                        <span class="text-[8px] font-black text-muted-foreground/30 uppercase tracking-widest">No Credit</span>
                    @endif
                </div>
            </x-ui.table-cell>

            <!-- Contact & Tax -->
            <x-ui.table-cell>
                <div class="flex flex-col gap-2 max-w-[180px]">
                    @if($customer->phone)
                        <div class="flex items-center gap-2 px-2 py-1 rounded-lg bg-primary/5 border border-primary/10 w-fit">
                            <x-ui.icon name="phone" size="3" class="text-primary/60" />
                            <span class="text-[10px] font-black text-primary/80 uppercase tracking-tighter">{{ $customer->phone }}</span>
                        </div>
                    @endif
                    <div class="flex flex-wrap gap-1.5">
                        @if($customer->gst_no)
                            <span class="text-[8px] font-black px-1.5 py-0.5 rounded-md bg-muted/20 border border-border/40 text-muted-foreground uppercase tracking-tight font-mono">GST: {{ $customer->gst_no }}</span>
                        @endif
                        @if($customer->pan_no)
                            <span class="text-[8px] font-black px-1.5 py-0.5 rounded-md bg-muted/20 border border-border/40 text-muted-foreground uppercase tracking-tight font-mono">PAN: {{ $customer->pan_no }}</span>
                        @endif
                        @if(!$customer->phone && !$customer->gst_no && !$customer->pan_no)
                            <span class="text-[9px] text-muted-foreground/30 italic">No contact info</span>
                        @endif
                    </div>
                </div>
            </x-ui.table-cell>

            <!-- Registration Ledger -->
            <x-ui.table-cell>
                <div class="flex flex-col gap-1">
                    <div class="flex items-center gap-1.5">
                        <x-ui.icon name="clock" size="3" class="text-muted-foreground/40" />
                        <span class="text-[10px] font-bold text-foreground/80 tracking-tight">{{ $customer->created_at->diffForHumans() }}</span>
                    </div>
                    <div class="flex items-center gap-1.5">
                        <x-ui.icon name="hash" size="3" class="text-muted-foreground/30" />
                        <span class="text-[9px] font-mono font-bold text-muted-foreground/40 tracking-tighter">{{ $customer->created_at->format('M d, Y') }}</span>
                    </div>
                </div>
            </x-ui.table-cell>

            <!-- Actions -->
            <x-ui.table-cell class="text-right">
                <div class="flex justify-end gap-1.5">
                    @if($customer->trashed())
                        <form action="{{ route('customers.restore', $customer->id) }}" method="POST">
                            @csrf
                            <x-ui.button variant="ghost" size="sm" type="submit" className="h-8 px-3 text-[10px] font-black uppercase tracking-widest text-emerald-600 hover:bg-emerald-500/10 rounded-xl border border-transparent hover:border-emerald-500/20">
                                Restore
                            </x-ui.button>
                        </form>
                        <form action="{{ route('customers.force-delete', $customer->id) }}" method="POST" onsubmit="return confirm('PERMANENTLY delete this customer?')">
                            @csrf
                            @method('DELETE')
                            <x-ui.button variant="ghost" size="icon" type="submit" className="size-8 text-destructive hover:bg-destructive/10 rounded-xl border border-transparent hover:border-destructive/20">
                                <x-ui.icon name="trash-2" size="4" />
                            </x-ui.button>
                        </form>
                    @else
                        <a href="{{ route('customers.edit', $customer) }}">
                            <x-ui.button variant="ghost" size="icon" className="size-8 text-muted-foreground hover:text-primary hover:bg-primary/10 rounded-xl border border-transparent hover:border-primary/20 transition-all">
                                <x-ui.icon name="edit-3" size="4" />
                            </x-ui.button>
                        </a>
                        <form action="{{ route('customers.destroy', $customer) }}" method="POST" onsubmit="return confirm('Move this customer to archive?')">
                            @csrf
                            @method('DELETE')
                            <x-ui.button variant="ghost" size="icon" type="submit" className="size-8 text-muted-foreground hover:text-destructive hover:bg-destructive/10 rounded-xl border border-transparent hover:border-destructive/20 transition-all">
                                <x-ui.icon name="trash" size="4" />
                            </x-ui.button>
                        </form>
                    @endif
                </div>
            </x-ui.table-cell>
        </x-ui.table-row>
        @empty
        <x-ui.table-row>
            <x-ui.table-cell colspan="7" class="h-60 text-center">
                <div class="flex flex-col items-center justify-center gap-4 opacity-30">
                    <x-ui.icon name="users" size="16" stroke-width="1" />
                    <p class="text-sm font-black uppercase tracking-[0.2em]">No customers matching criteria</p>
                    <x-ui.button variant="outline" size="sm" onclick="location.reload()" class="rounded-xl border-border">Reset View</x-ui.button>
                </div>
            </x-ui.table-cell>
        </x-ui.table-row>
        @endforelse
    </x-ui.table-body>
</x-ui.table>

@if($customers->hasPages())
    <div class="p-4 border-t border-border/40 bg-muted/5 flex justify-end items-center rounded-b-3xl">
        {{ $customers->links() }}
    </div>
@endif
