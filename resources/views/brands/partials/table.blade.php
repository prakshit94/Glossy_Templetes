<div class="overflow-x-auto">
    <table class="w-full text-left border-collapse">
        <thead>
            <tr class="bg-muted/5 border-b border-border/40">
                <th class="p-5 text-[10px] font-black uppercase tracking-widest text-muted-foreground/80">Brand</th>
                <th class="p-5 text-[10px] font-black uppercase tracking-widest text-muted-foreground/80">Slug</th>
                <th class="p-5 text-[10px] font-black uppercase tracking-widest text-muted-foreground/80">Status</th>
                <th class="p-5 text-[10px] font-black uppercase tracking-widest text-muted-foreground/80 text-right">Actions</th>
            </tr>
        </thead>
        <tbody>
            @forelse($brands as $brand)
                <tr class="border-b border-border/40 hover:bg-primary/[0.02] transition-colors group">
                    <td class="p-5">
                        <div class="flex items-center gap-4">
                            @if($brand->image)
                                <img src="{{ asset('storage/' . $brand->image) }}" class="size-10 rounded-xl object-cover shadow-sm border border-border/40">
                            @else
                                <div class="size-10 rounded-xl bg-muted/20 flex items-center justify-center border border-border/40">
                                    <x-ui.icon name="image" size="4" class="text-muted-foreground/30" />
                                </div>
                            @endif
                            <div>
                                <div class="text-sm font-black text-foreground">{{ $brand->name }}</div>
                                <div class="text-[10px] font-bold text-muted-foreground tracking-tight uppercase">ID: BR-{{ str_pad($brand->id, 4, '0', STR_PAD_LEFT) }}</div>
                            </div>
                        </div>
                    </td>
                    <td class="p-5">
                        <code class="px-2 py-1 rounded-lg bg-muted/30 text-[10px] font-bold text-muted-foreground">{{ $brand->slug }}</code>
                    </td>
                    <td class="p-5">
                        @if($brand->status == 'active')
                            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-emerald-500/10 border border-emerald-500/20 text-[10px] font-black uppercase tracking-widest text-emerald-500 shadow-sm shadow-emerald-500/5">
                                <span class="size-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                                Active
                            </span>
                        @else
                            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-red-500/10 border border-red-500/20 text-[10px] font-black uppercase tracking-widest text-red-500 shadow-sm shadow-red-500/5">
                                <span class="size-1.5 rounded-full bg-red-500"></span>
                                Inactive
                            </span>
                        @endif
                    </td>
                    <td class="p-5 text-right">
                        <div class="flex items-center justify-end gap-2 opacity-0 group-hover:opacity-100 transition-opacity duration-300">
                            <button @click.stop="openEditModal({{ json_encode($brand) }})" class="size-9 rounded-xl bg-background/50 border border-border/60 flex items-center justify-center text-muted-foreground hover:text-primary hover:border-primary/40 transition-all shadow-sm hover:scale-105 active:scale-95">
                                <x-ui.icon name="edit-3" size="4" />
                            </button>
                            <form action="{{ route('brands.destroy', $brand) }}" method="POST" onsubmit="return confirm('Permanently delete this brand?')">
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
                    <td colspan="4" class="p-20 text-center">
                        <div class="flex flex-col items-center gap-3">
                            <div class="size-16 rounded-3xl bg-muted/10 flex items-center justify-center text-muted-foreground/20">
                                <x-ui.icon name="award" size="8" />
                            </div>
                            <div class="text-sm font-black text-muted-foreground uppercase tracking-widest">No brands found</div>
                            <p class="text-xs text-muted-foreground/60 max-w-[200px]">Start by adding your first brand to the catalog.</p>
                        </div>
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

@if($brands->hasPages())
    <div class="p-6 border-t border-border/40 bg-muted/5">
        {{ $brands->links() }}
    </div>
@endif
