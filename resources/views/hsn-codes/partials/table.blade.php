<div class="overflow-x-auto">
    <table class="w-full text-left border-collapse">
        <thead>
            <tr class="bg-muted/5 border-b border-border/40">
                <th class="p-5 text-[10px] font-black uppercase tracking-widest text-muted-foreground/80">HSN Code</th>
                <th class="p-5 text-[10px] font-black uppercase tracking-widest text-muted-foreground/80">Description</th>
                <th class="p-5 text-[10px] font-black uppercase tracking-widest text-muted-foreground/80">Status</th>
                <th class="p-5 text-[10px] font-black uppercase tracking-widest text-muted-foreground/80 text-right">Actions</th>
            </tr>
        </thead>
        <tbody>
            @forelse($hsnCodes as $hsn)
                <tr class="border-b border-border/40 hover:bg-primary/[0.02] transition-colors group">
                    <td class="p-5">
                        <div class="flex items-center gap-4">
                            <div class="size-10 rounded-xl bg-muted/20 flex items-center justify-center border border-border/40">
                                <x-ui.icon name="file-text" size="4" class="text-muted-foreground/30" />
                            </div>
                            <div class="text-sm font-black text-foreground">{{ $hsn->code }}</div>
                        </div>
                    </td>
                    <td class="p-5">
                        <div class="text-xs font-medium text-muted-foreground line-clamp-1 max-w-xs">{{ $hsn->description ?: 'No description provided' }}</div>
                    </td>
                    <td class="p-5">
                        @if($hsn->status == 'active')
                            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-emerald-500/10 border border-emerald-500/20 text-[10px] font-black uppercase tracking-widest text-emerald-500">
                                Active
                            </span>
                        @else
                            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-red-500/10 border border-red-500/20 text-[10px] font-black uppercase tracking-widest text-red-500">
                                Inactive
                            </span>
                        @endif
                    </td>
                    <td class="p-5 text-right">
                        <div class="flex items-center justify-end gap-2 opacity-0 group-hover:opacity-100 transition-opacity duration-300">
                            <button @click="openEditModal({{ json_encode($hsn) }})" class="size-9 rounded-xl bg-background/50 border border-border/60 flex items-center justify-center text-muted-foreground hover:text-primary hover:border-primary/40 transition-all shadow-sm">
                                <x-ui.icon name="edit-3" size="4" />
                            </button>
                            <form action="{{ route('hsn-codes.destroy', $hsn) }}" method="POST" onsubmit="return confirm('Delete HSN code?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="size-9 rounded-xl bg-background/50 border border-border/60 flex items-center justify-center text-muted-foreground hover:text-destructive hover:border-destructive/40 transition-all shadow-sm">
                                    <x-ui.icon name="trash-2" size="4" />
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="4" class="p-20 text-center">
                        <div class="flex flex-col items-center gap-3">
                            <div class="size-16 rounded-3xl bg-muted/10 flex items-center justify-center text-muted-foreground/20">
                                <x-ui.icon name="file-text" size="8" />
                            </div>
                            <div class="text-sm font-black text-muted-foreground uppercase tracking-widest">No HSN codes found</div>
                        </div>
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

@if($hsnCodes->hasPages())
    <div class="p-6 border-t border-border/40 bg-muted/5">
        {{ $hsnCodes->links() }}
    </div>
@endif
