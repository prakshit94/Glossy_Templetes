<x-ui.table>
    <x-ui.table-header class="bg-muted/30">
        <x-ui.table-row class="border-b border-border/60">
            <x-ui.table-head>Reference & Date</x-ui.table-head>
            <x-ui.table-head>Warehouse</x-ui.table-head>
            <x-ui.table-head>Adjusted By</x-ui.table-head>
            <x-ui.table-head class="text-center">Items</x-ui.table-head>
            <x-ui.table-head class="text-center">Status</x-ui.table-head>
            <x-ui.table-head class="text-right">Actions</x-ui.table-head>
        </x-ui.table-row>
    </x-ui.table-header>

    <x-ui.table-body>
        @forelse($adjustments as $adjustment)
            <x-ui.table-row class="hover:bg-muted/20 transition-colors group">
                <x-ui.table-cell>
                <div class="flex items-center gap-4">
                    <div class="size-12 rounded-2xl bg-gradient-to-br from-primary/20 to-primary/5 border border-primary/10 flex items-center justify-center shadow-inner group-hover:scale-110 transition-transform duration-500">
                        <x-ui.icon name="sliders" size="5" class="text-primary/60" />
                    </div>
                    <div class="flex flex-col min-w-0">
                        <span class="text-sm font-bold text-foreground truncate">{{ $adjustment->reference_no }}</span>
                        <span class="text-[9px] font-bold text-muted-foreground uppercase tracking-tight">{{ $adjustment->created_at->format('M d, Y H:i') }}</span>
                    </div>
                </div>
            </x-ui.table-cell>
            
            <x-ui.table-cell>
                <div class="flex items-center gap-2">
                    <div class="size-2 rounded-full bg-blue-500/40"></div>
                    <span class="text-xs font-bold text-muted-foreground uppercase tracking-widest">{{ $adjustment->warehouse->name }}</span>
                </div>
            </x-ui.table-cell>

            <x-ui.table-cell>
                <div class="flex items-center gap-2">
                    <x-ui.icon name="user" size="3" class="text-muted-foreground/40" />
                    <span class="text-xs font-bold text-muted-foreground">{{ $adjustment->user->name }}</span>
                </div>
            </x-ui.table-cell>

            <x-ui.table-cell class="text-center">
                <div class="inline-flex items-center px-3 py-1 rounded-xl bg-muted/10 border border-border/40 text-[10px] font-black tracking-tight">
                    {{ number_format($adjustment->items_count) }}
                </div>
            </x-ui.table-cell>

            <x-ui.table-cell class="text-center">
                <x-ui.badge variant="{{ $adjustment->status === 'approved' ? 'success' : ($adjustment->status === 'rejected' ? 'destructive' : 'outline') }}" class="rounded-lg px-2 py-0.5 text-[10px] font-black uppercase tracking-widest">
                    {{ $adjustment->status }}
                </x-ui.badge>
            </x-ui.table-cell>

            <x-ui.table-cell class="text-right">
                <div class="flex justify-end gap-1.5">
                    <a href="{{ route('adjustments.show', $adjustment) }}">
                        <x-ui.button variant="ghost" size="sm" class="h-8 w-8 p-0 rounded-xl hover:bg-primary/10 hover:text-primary transition-colors" title="View Details">
                            <x-ui.icon name="eye" size="4" />
                        </x-ui.button>
                    </a>
                    @if($adjustment->status === 'pending')
                        <a href="{{ route('adjustments.edit', $adjustment) }}">
                            <x-ui.button variant="ghost" size="sm" class="h-8 w-8 p-0 rounded-xl hover:bg-primary/10 hover:text-primary transition-colors" title="Edit">
                                <x-ui.icon name="edit-2" size="4" />
                            </x-ui.button>
                        </a>
                        <form action="{{ route('adjustments.approve', $adjustment) }}" method="POST" class="inline">
                            @csrf
                            <x-ui.button type="submit" variant="ghost" size="sm" class="h-8 w-8 p-0 rounded-xl hover:bg-emerald-500/10 hover:text-emerald-500 transition-colors" title="Approve">
                                <x-ui.icon name="check" size="4" />
                            </x-ui.button>
                        </form>
                        <form action="{{ route('adjustments.reject', $adjustment) }}" method="POST" class="inline">
                            @csrf
                            <x-ui.button type="submit" variant="ghost" size="sm" class="h-8 w-8 p-0 rounded-xl hover:bg-red-500/10 hover:text-red-500 transition-colors" title="Reject">
                                <x-ui.icon name="x" size="4" />
                            </x-ui.button>
                        </form>
                        <form action="{{ route('adjustments.destroy', $adjustment) }}" method="POST" class="inline" onsubmit="return confirm('Delete this record?')">
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
                        <p class="text-sm font-black uppercase tracking-widest">No adjustments found</p>
                    </div>
                </x-ui.table-cell>
            </x-ui.table-row>
        @endforelse
    </x-ui.table-body>
</x-ui.table>

@if($adjustments->hasPages())
    <div class="p-4 border-t border-border/40 bg-muted/5 flex justify-end items-center">
        {{ $adjustments->links() }}
    </div>
@endif
