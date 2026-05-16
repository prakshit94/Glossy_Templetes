<div class="overflow-x-auto">
    <table class="w-full text-left border-collapse">
        <thead>
            <tr class="bg-muted/5 border-b border-border/40">
                <th class="p-4 text-[10px] font-black uppercase tracking-widest text-muted-foreground/60">Reference & Date</th>
                <th class="p-4 text-[10px] font-black uppercase tracking-widest text-muted-foreground/60">Warehouse</th>
                <th class="p-4 text-[10px] font-black uppercase tracking-widest text-muted-foreground/60">Performed By</th>
                <th class="p-4 text-[10px] font-black uppercase tracking-widest text-muted-foreground/60 text-center">Items</th>
                <th class="p-4 text-[10px] font-black uppercase tracking-widest text-muted-foreground/60 text-center">Status</th>
                <th class="p-4 text-[10px] font-black uppercase tracking-widest text-muted-foreground/60 text-right">Actions</th>
            </tr>
        </thead>
        <tbody>
            @forelse($adjustments as $adjustment)
                <tr class="border-b border-border/30 hover:bg-muted/10 transition-colors group">
                    <td class="p-4">
                        <div class="flex items-center gap-4">
                            <div class="size-11 rounded-2xl bg-gradient-to-tr from-primary/20 to-primary/5 border border-primary/10 flex items-center justify-center shadow-inner group-hover:scale-110 transition-transform duration-500">
                                <x-ui.icon name="sliders" size="5" class="text-primary/60" />
                            </div>
                            <div class="flex flex-col min-w-0">
                                <span class="text-xs font-black text-foreground truncate uppercase tracking-tight">{{ $adjustment->reference_no }}</span>
                                <span class="text-[9px] font-bold text-muted-foreground uppercase tracking-[0.1em] mt-0.5">{{ $adjustment->created_at->format('M d, Y • h:i A') }}</span>
                            </div>
                        </div>
                    </td>
                    
                    <td class="p-4">
                        <div class="flex items-center gap-2">
                            <div class="size-2 rounded-full bg-blue-500/40"></div>
                            <span class="text-[10px] font-black text-foreground uppercase tracking-widest">{{ $adjustment->warehouse->name }}</span>
                        </div>
                    </td>

                    <td class="p-4">
                        <div class="flex items-center gap-2">
                            <div class="size-7 rounded-full bg-muted border border-border/40 flex items-center justify-center">
                                <x-ui.icon name="user" size="3" class="text-muted-foreground/40" />
                            </div>
                            <span class="text-[10px] font-bold text-muted-foreground uppercase tracking-tight">{{ $adjustment->user->name }}</span>
                        </div>
                    </td>

                    <td class="p-4 text-center">
                        <div class="inline-flex items-center px-3 py-1 rounded-xl bg-muted/10 border border-border/40 text-[10px] font-black tracking-tight">
                            {{ number_format($adjustment->items_count) }}
                        </div>
                    </td>

                    <td class="p-4 text-center">
                        @php
                            $statusMap = [
                                'approved' => ['variant' => 'success', 'icon' => 'check-circle'],
                                'rejected' => ['variant' => 'destructive', 'icon' => 'x-circle'],
                                'pending'  => ['variant' => 'warning', 'icon' => 'clock'],
                            ];
                            $st = $statusMap[$adjustment->status] ?? $statusMap['pending'];
                        @endphp
                        <x-ui.badge variant="{{ $st['variant'] }}" class="rounded-lg px-2.5 py-1 text-[9px] font-black uppercase tracking-widest inline-flex items-center gap-1.5 shadow-sm">
                            <x-ui.icon name="{{ $st['icon'] }}" size="3" />
                            {{ $adjustment->status }}
                        </x-ui.badge>
                    </td>

                    <td class="p-4 text-right">
                        <div class="flex justify-end gap-1">
                            <a href="{{ route('adjustments.show', $adjustment) }}">
                                <x-ui.button variant="ghost" size="sm" class="h-9 w-9 p-0 rounded-xl hover:bg-primary/10 hover:text-primary transition-all duration-300" title="View Details">
                                    <x-ui.icon name="eye" size="4.5" />
                                </x-ui.button>
                            </a>
                            @if($adjustment->status === 'pending')
                                <a href="{{ route('adjustments.edit', $adjustment) }}">
                                    <x-ui.button variant="ghost" size="sm" class="h-9 w-9 p-0 rounded-xl hover:bg-primary/10 hover:text-primary transition-all duration-300" title="Edit Draft">
                                        <x-ui.icon name="edit-2" size="4.5" />
                                    </x-ui.button>
                                </a>
                                
                                <div class="w-px h-6 bg-border/40 mx-1"></div>

                                <form action="{{ route('adjustments.approve', $adjustment) }}" method="POST" class="inline">
                                    @csrf
                                    <x-ui.button type="submit" variant="ghost" size="sm" class="h-9 w-9 p-0 rounded-xl hover:bg-emerald-500/10 hover:text-emerald-600 transition-all duration-300" title="Approve Adjustment" onclick="return confirm('Approve this adjustment and update stock?')">
                                        <x-ui.icon name="check" size="4.5" />
                                    </x-ui.button>
                                </form>
                                <form action="{{ route('adjustments.reject', $adjustment) }}" method="POST" class="inline">
                                    @csrf
                                    <x-ui.button type="submit" variant="ghost" size="sm" class="h-9 w-9 p-0 rounded-xl hover:bg-red-500/10 hover:text-red-600 transition-all duration-300" title="Reject Adjustment" onclick="return confirm('Reject this adjustment?')">
                                        <x-ui.icon name="x" size="4.5" />
                                    </x-ui.button>
                                </form>
                                <form action="{{ route('adjustments.destroy', $adjustment) }}" method="POST" class="inline">
                                    @csrf
                                    @method('DELETE')
                                    <x-ui.button type="submit" variant="ghost" size="sm" class="h-9 w-9 p-0 rounded-xl hover:bg-red-500/10 hover:text-red-600 transition-all duration-300" title="Delete Permanent" onclick="return confirm('Delete this record?')">
                                        <x-ui.icon name="trash-2" size="4.5" />
                                    </x-ui.button>
                                </form>
                            @endif
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" class="p-20 text-center">
                        <div class="flex flex-col items-center gap-4 opacity-20">
                            <x-ui.icon name="inbox" size="12" />
                            <p class="text-sm font-black uppercase tracking-widest">No adjustments recorded</p>
                        </div>
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

@if($adjustments->hasPages())
    <div class="p-6 border-t border-border/30 bg-muted/5">
        {{ $adjustments->links() }}
    </div>
@endif
