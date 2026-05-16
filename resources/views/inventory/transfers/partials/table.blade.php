<div class="overflow-x-auto">
    <table class="w-full text-left border-collapse">
        <thead>
            <tr class="bg-muted/5 border-b border-border/40">
                <th class="p-4 text-[10px] font-black uppercase tracking-widest text-muted-foreground/60">Transfer No & Date</th>
                <th class="p-4 text-[10px] font-black uppercase tracking-widest text-muted-foreground/60">Movement Path</th>
                <th class="p-4 text-[10px] font-black uppercase tracking-widest text-muted-foreground/60 text-center">Items</th>
                <th class="p-4 text-[10px] font-black uppercase tracking-widest text-muted-foreground/60 text-center">Status</th>
                <th class="p-4 text-[10px] font-black uppercase tracking-widest text-muted-foreground/60 text-right">Actions</th>
            </tr>
        </thead>
        <tbody>
            @forelse($transfers as $transfer)
                <tr class="border-b border-border/30 hover:bg-muted/10 transition-colors group">
                    <td class="p-4">
                        <div class="flex items-center gap-4">
                            <div class="size-11 rounded-2xl bg-gradient-to-tr from-primary/20 to-primary/5 border border-primary/10 flex items-center justify-center shadow-inner group-hover:scale-110 transition-transform duration-500">
                                <x-ui.icon name="repeat" size="5" class="text-primary/60" />
                            </div>
                            <div class="flex flex-col min-w-0">
                                <span class="text-xs font-black text-foreground truncate uppercase tracking-tight">{{ $transfer->transfer_no }}</span>
                                <span class="text-[9px] font-bold text-muted-foreground uppercase tracking-[0.1em] mt-0.5">{{ $transfer->created_at->format('M d, Y • h:i A') }}</span>
                            </div>
                        </div>
                    </td>
                    
                    <td class="p-4 min-w-[320px]">
                        <div class="flex items-center gap-3">
                            <div class="flex flex-col items-end">
                                <span class="text-[9px] font-black text-muted-foreground uppercase tracking-widest">Source</span>
                                <span class="text-[10px] font-black text-foreground uppercase tracking-tight">{{ $transfer->fromWarehouse->name }}</span>
                            </div>
                            <div class="flex items-center gap-1 opacity-40">
                                <div class="size-1 bg-primary rounded-full animate-pulse"></div>
                                <div class="w-8 h-[2px] bg-gradient-to-r from-primary to-transparent"></div>
                                <x-ui.icon name="truck" size="4" class="text-primary group-hover:translate-x-2 transition-transform duration-700" />
                                <div class="w-8 h-[2px] bg-gradient-to-l from-primary to-transparent"></div>
                                <div class="size-1 bg-primary rounded-full animate-pulse"></div>
                            </div>
                            <div class="flex flex-col items-start">
                                <span class="text-[9px] font-black text-muted-foreground uppercase tracking-widest">Destination</span>
                                <span class="text-[10px] font-black text-foreground uppercase tracking-tight">{{ $transfer->toWarehouse->name }}</span>
                            </div>
                        </div>
                    </td>

                    <td class="p-4 text-center">
                        <div class="inline-flex items-center px-3 py-1 rounded-xl bg-muted/10 border border-border/40 text-[10px] font-black tracking-tight">
                            {{ number_format($transfer->items_count) }}
                        </div>
                    </td>

                    <td class="p-4 text-center">
                        @php
                            $statusMap = [
                                'received' => ['variant' => 'success', 'icon' => 'check-circle'],
                                'sent'     => ['variant' => 'warning', 'icon' => 'truck'],
                                'cancelled' => ['variant' => 'destructive', 'icon' => 'x-circle'],
                                'draft'     => ['variant' => 'outline', 'icon' => 'file-text'],
                            ];
                            $st = $statusMap[$transfer->status] ?? $statusMap['draft'];
                        @endphp
                        <x-ui.badge variant="{{ $st['variant'] }}" class="rounded-lg px-2.5 py-1 text-[9px] font-black uppercase tracking-widest inline-flex items-center gap-1.5 shadow-sm">
                            <x-ui.icon name="{{ $st['icon'] }}" size="3" />
                            {{ $transfer->status }}
                        </x-ui.badge>
                    </td>

                    <td class="p-4 text-right">
                        <div class="flex justify-end gap-1">
                            <a href="{{ route('transfers.show', $transfer) }}">
                                <x-ui.button variant="ghost" size="sm" class="h-9 w-9 p-0 rounded-xl hover:bg-primary/10 hover:text-primary transition-all duration-300" title="View Details">
                                    <x-ui.icon name="eye" size="4.5" />
                                </x-ui.button>
                            </a>
                            
                            @if($transfer->status === 'draft')
                                <form action="{{ route('transfers.send', $transfer) }}" method="POST" class="inline">
                                    @csrf
                                    <x-ui.button type="submit" variant="ghost" size="sm" class="h-9 w-9 p-0 rounded-xl hover:bg-emerald-500/10 hover:text-emerald-600 transition-all duration-300" title="Ship/Send Transfer" onclick="return confirm('Mark as sent? This will move stock to In-Transit.')">
                                        <x-ui.icon name="send" size="4.5" />
                                    </x-ui.button>
                                </form>
                                <a href="{{ route('transfers.edit', $transfer) }}">
                                    <x-ui.button variant="ghost" size="sm" class="h-9 w-9 p-0 rounded-xl hover:bg-primary/10 hover:text-primary transition-all duration-300" title="Edit Draft">
                                        <x-ui.icon name="edit-2" size="4.5" />
                                    </x-ui.button>
                                </a>
                            @endif

                            @if($transfer->status === 'sent')
                                <form action="{{ route('transfers.receive', $transfer) }}" method="POST" class="inline">
                                    @csrf
                                    <x-ui.button type="submit" variant="ghost" size="sm" class="h-9 w-9 p-0 rounded-xl hover:bg-emerald-500/10 hover:text-emerald-600 transition-all duration-300" title="Receive at Destination" onclick="return confirm('Complete this transfer?')">
                                        <x-ui.icon name="check-circle" size="4.5" />
                                    </x-ui.button>
                                </form>
                            @endif

                            @if(in_array($transfer->status, ['draft', 'sent']))
                                <div class="w-px h-6 bg-border/40 mx-1"></div>
                                <form action="{{ route('transfers.cancel', $transfer) }}" method="POST" class="inline">
                                    @csrf
                                    <x-ui.button type="submit" variant="ghost" size="sm" class="h-9 w-9 p-0 rounded-xl hover:bg-red-500/10 hover:text-red-600 transition-all duration-300" title="Cancel Transfer" onclick="return confirm('Cancel this transfer? Stock will be released/restored.')">
                                        <x-ui.icon name="x-circle" size="4.5" />
                                    </x-ui.button>
                                </form>
                            @endif

                            @if($transfer->status === 'draft')
                                <form action="{{ route('transfers.destroy', $transfer) }}" method="POST" class="inline">
                                    @csrf
                                    @method('DELETE')
                                    <x-ui.button type="submit" variant="ghost" size="sm" class="h-9 w-9 p-0 rounded-xl hover:bg-red-500/10 hover:text-red-600 transition-all duration-300" title="Delete Draft" onclick="return confirm('Delete this record permanently?')">
                                        <x-ui.icon name="trash-2" size="4.5" />
                                    </x-ui.button>
                                </form>
                            @endif
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" class="p-20 text-center">
                        <div class="flex flex-col items-center gap-4 opacity-20">
                            <x-ui.icon name="repeat" size="12" />
                            <p class="text-sm font-black uppercase tracking-widest">No stock transfers found</p>
                        </div>
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

@if($transfers->hasPages())
    <div class="p-6 border-t border-border/30 bg-muted/5">
        {{ $transfers->links() }}
    </div>
@endif
