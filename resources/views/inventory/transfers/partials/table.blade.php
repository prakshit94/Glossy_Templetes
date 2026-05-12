<x-ui.table>
    <x-ui.table-header class="bg-muted/30">
        <x-ui.table-row class="border-b border-border/60">
            <x-ui.table-head>Transfer No & Date</x-ui.table-head>
            <x-ui.table-head>From Warehouse</x-ui.table-head>
            <x-ui.table-head>To Warehouse</x-ui.table-head>
            <x-ui.table-head class="text-center">Items</x-ui.table-head>
            <x-ui.table-head class="text-center">Status</x-ui.table-head>
            <x-ui.table-head class="text-right">Actions</x-ui.table-head>
        </x-ui.table-row>
    </x-ui.table-header>

    <x-ui.table-body>
        @forelse($transfers as $transfer)
            <x-ui.table-row class="hover:bg-muted/20 transition-colors group">
                <x-ui.table-cell>
                    <div class="flex items-center gap-4">
                        <div class="size-12 rounded-2xl bg-gradient-to-br from-primary/20 to-primary/5 border border-primary/10 flex items-center justify-center shadow-inner group-hover:scale-110 transition-transform duration-500">
                            <x-ui.icon name="repeat" size="5" class="text-primary/60" />
                        </div>
                        <div class="flex flex-col min-w-0">
                            <span class="text-sm font-bold text-foreground truncate">{{ $transfer->transfer_no }}</span>
                            <span class="text-[9px] font-bold text-muted-foreground uppercase tracking-tight">{{ $transfer->created_at->format('M d, Y H:i') }}</span>
                        </div>
                    </div>
                </x-ui.table-cell>
                
                <x-ui.table-cell>
                    <div class="flex items-center gap-2">
                        <div class="size-2 rounded-full bg-orange-500/40"></div>
                        <span class="text-xs font-bold text-muted-foreground uppercase tracking-widest">{{ $transfer->fromWarehouse->name }}</span>
                    </div>
                </x-ui.table-cell>

                <x-ui.table-cell>
                    <div class="flex items-center gap-2">
                        <div class="size-2 rounded-full bg-blue-500/40"></div>
                        <span class="text-xs font-bold text-muted-foreground uppercase tracking-widest">{{ $transfer->toWarehouse->name }}</span>
                    </div>
                </x-ui.table-cell>

                <x-ui.table-cell class="text-center">
                    <div class="inline-flex items-center px-3 py-1 rounded-xl bg-muted/10 border border-border/40 text-[10px] font-black tracking-tight">
                        {{ number_format($transfer->items_count) }}
                    </div>
                </x-ui.table-cell>

                <x-ui.table-cell class="text-center">
                    @php
                        $variant = match($transfer->status) {
                            'received' => 'default',
                            'sent' => 'warning',
                            'cancelled' => 'destructive',
                            default => 'secondary'
                        };
                        $colorClass = match($transfer->status) {
                            'received' => 'bg-emerald-500/10 text-emerald-500 border-emerald-500/20',
                            'sent' => 'bg-orange-500/10 text-orange-500 border-orange-500/20',
                            'cancelled' => 'bg-red-500/10 text-red-500 border-red-500/20',
                            default => 'bg-muted/10 text-muted-foreground/40 border-border/40'
                        };
                    @endphp
                    <x-ui.badge variant="outline" className="text-[8px] px-2 py-0.5 rounded-lg font-black uppercase tracking-widest {{ $colorClass }}">
                        {{ $transfer->status }}
                    </x-ui.badge>
                </x-ui.table-cell>

                <x-ui.table-cell class="text-right">
                    <div class="flex justify-end gap-1.5">
                        <a href="{{ route('transfers.show', $transfer) }}">
                            <x-ui.button variant="ghost" size="sm" class="h-8 w-8 p-0 rounded-xl hover:bg-primary/10 hover:text-primary transition-colors" title="View Details">
                                <x-ui.icon name="eye" size="4" />
                            </x-ui.button>
                        </a>
                        @if($transfer->status === 'draft')
                            <form action="{{ route('transfers.send', $transfer) }}" method="POST" class="inline">
                                @csrf
                                <x-ui.button type="submit" variant="ghost" size="sm" class="h-8 w-8 p-0 rounded-xl hover:bg-emerald-500/10 hover:text-emerald-500 transition-colors" title="Mark as Sent">
                                    <x-ui.icon name="send" size="4" />
                                </x-ui.button>
                            </form>
                            <a href="{{ route('transfers.edit', $transfer) }}">
                                <x-ui.button variant="ghost" size="sm" class="h-8 w-8 p-0 rounded-xl hover:bg-primary/10 hover:text-primary transition-colors" title="Edit">
                                    <x-ui.icon name="edit-2" size="4" />
                                </x-ui.button>
                            </a>
                        @endif

                        @if($transfer->status === 'sent')
                            <form action="{{ route('transfers.receive', $transfer) }}" method="POST" class="inline">
                                @csrf
                                <x-ui.button type="submit" variant="ghost" size="sm" class="h-8 w-8 p-0 rounded-xl hover:bg-emerald-500/10 hover:text-emerald-500 transition-colors" title="Receive Stock">
                                    <x-ui.icon name="check-circle" size="4" />
                                </x-ui.button>
                            </form>
                        @endif

                        @if(in_array($transfer->status, ['draft', 'sent']))
                            <form action="{{ route('transfers.cancel', $transfer) }}" method="POST" class="inline" onsubmit="return confirm('Cancel this transfer?')">
                                @csrf
                                <x-ui.button type="submit" variant="ghost" size="sm" class="h-8 w-8 p-0 rounded-xl hover:bg-red-500/10 hover:text-red-500 transition-colors" title="Cancel">
                                    <x-ui.icon name="x-circle" size="4" />
                                </x-ui.button>
                            </form>
                        @endif

                        @if($transfer->status === 'draft')
                            <form action="{{ route('transfers.destroy', $transfer) }}" method="POST" class="inline" onsubmit="return confirm('Delete this record?')">
                                @csrf
                                @method('DELETE')
                                <x-ui.button type="submit" variant="ghost" size="sm" class="h-8 w-8 p-0 rounded-xl hover:bg-red-500/10 hover:text-red-500 transition-colors" title="Delete">
                                    <x-ui.icon name="trash-2" size="4" />
                                </x-ui.button>
                            </form>
                        @endif
                    </div>
                </x-ui.table-cell>
            </x-ui.table-row>
        @empty
            <x-ui.table-row>
                <x-ui.table-cell colspan="6" class="h-40 text-center">
                    <div class="flex flex-col items-center justify-center gap-2 opacity-50">
                        <x-ui.icon name="inbox" size="10" />
                        <p class="text-sm font-black uppercase tracking-widest">No transfers found</p>
                    </div>
                </x-ui.table-cell>
            </x-ui.table-row>
        @endforelse
    </x-ui.table-body>
</x-ui.table>

@if($transfers->hasPages())
    <div class="p-4 border-t border-border/40 bg-muted/5 flex justify-end items-center">
        {{ $transfers->links() }}
    </div>
@endif
