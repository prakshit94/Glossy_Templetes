<div class="overflow-x-auto">
    <table class="w-full text-left border-collapse">
        <thead>
            <tr class="bg-muted/5 border-b border-border/40">
                <th class="p-5 text-[10px] font-black uppercase tracking-widest text-muted-foreground/80">Attribute</th>
                <th class="p-5 text-[10px] font-black uppercase tracking-widest text-muted-foreground/80">Type</th>
                <th class="p-5 text-[10px] font-black uppercase tracking-widest text-muted-foreground/80">Values</th>
                <th class="p-5 text-[10px] font-black uppercase tracking-widest text-muted-foreground/80">Filterable</th>
                <th class="p-5 text-[10px] font-black uppercase tracking-widest text-muted-foreground/80 text-right">Actions</th>
            </tr>
        </thead>
        <tbody>
            @forelse($attributes as $attribute)
                <tr class="border-b border-border/40 hover:bg-primary/[0.02] transition-colors group">
                    <td class="p-5">
                        <div class="flex items-center gap-4">
                            <div class="size-10 rounded-xl bg-muted/20 flex items-center justify-center border border-border/40">
                                <x-ui.icon name="list" size="4" class="text-muted-foreground/30" />
                            </div>
                            <div>
                                <div class="text-sm font-black text-foreground">{{ $attribute->name }}</div>
                                <div class="text-[10px] font-bold text-muted-foreground tracking-tight uppercase">ID: AT-{{ str_pad($attribute->id, 4, '0', STR_PAD_LEFT) }}</div>
                            </div>
                        </div>
                    </td>
                    <td class="p-5">
                        <span class="px-2 py-1 rounded-lg bg-muted/30 text-[10px] font-black text-muted-foreground uppercase tracking-widest">{{ $attribute->type }}</span>
                    </td>
                    <td class="p-5">
                        <div class="flex flex-wrap gap-1 max-w-[200px]">
                            @forelse($attribute->values->take(3) as $value)
                                <span class="px-2 py-0.5 rounded-full bg-violet-500/10 border border-violet-500/20 text-[9px] font-black text-violet-500">
                                    {{ $value->value }}
                                </span>
                            @empty
                                <span class="text-[10px] text-muted-foreground italic font-medium">No values</span>
                            @endforelse
                            @if($attribute->values->count() > 3)
                                <span class="text-[9px] font-black text-muted-foreground/60">+{{ $attribute->values->count() - 3 }} more</span>
                            @endif
                        </div>
                    </td>
                    <td class="p-5">
                        @if($attribute->is_filterable)
                            <span class="px-2 py-1 rounded-full bg-emerald-500/10 border border-emerald-500/20 text-[10px] font-black uppercase tracking-widest text-emerald-500">Yes</span>
                        @else
                            <span class="px-2 py-1 rounded-full bg-muted/20 border border-border/40 text-[10px] font-black uppercase tracking-widest text-muted-foreground/60">No</span>
                        @endif
                    </td>
                    <td class="p-5 text-right">
                        <div class="flex items-center justify-end gap-2 opacity-0 group-hover:opacity-100 transition-opacity duration-300">
                            <button @click.stop="openValueModal({{ json_encode($attribute->load('values')) }})" class="px-3 h-9 rounded-xl bg-violet-500/10 border border-violet-500/20 text-violet-600 flex items-center justify-center text-[10px] font-black uppercase tracking-widest hover:bg-violet-500/20 transition-all shadow-sm">
                                <x-ui.icon name="tag" size="3.5" class="mr-2" /> Values
                            </button>
                            <button @click.stop="openEditModal({{ json_encode($attribute) }})" class="size-9 rounded-xl bg-background/50 border border-border/60 flex items-center justify-center text-muted-foreground hover:text-primary hover:border-primary/40 transition-all shadow-sm hover:scale-105 active:scale-95">
                                <x-ui.icon name="edit-3" size="4" />
                            </button>
                            <form action="{{ route('attributes.destroy', $attribute) }}" method="POST" onsubmit="return confirm('Permanently delete this attribute?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="size-9 rounded-xl bg-background/50 border border-border/60 flex items-center justify-center text-muted-foreground hover:text-destructive hover:border-destructive/40 transition-all shadow-sm hover:scale-105 active:scale-95">
                                    <x-ui.icon name="trash-2" size="4" />
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" class="p-20 text-center">
                        <div class="flex flex-col items-center gap-3">
                            <div class="size-16 rounded-3xl bg-muted/10 flex items-center justify-center text-muted-foreground/20">
                                <x-ui.icon name="list" size="8" />
                            </div>
                            <div class="text-sm font-black text-muted-foreground uppercase tracking-widest">No attributes found</div>
                            <p class="text-xs text-muted-foreground/60 max-w-[200px]">Define properties like Color, Size or Material.</p>
                        </div>
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

@if($attributes->hasPages())
    <div class="p-6 border-t border-border/40 bg-muted/5">
        {{ $attributes->links() }}
    </div>
@endif
