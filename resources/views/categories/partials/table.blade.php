@if($categories->hasPages())
    <div class="p-4 border-b border-border/40">
        {{ $categories->links() }}
    </div>
@endif

<div class="overflow-x-auto">
    <table class="w-full text-left border-collapse">
        <thead>
            <tr class="bg-muted/5 border-b border-border/40">
                <th class="p-4 w-10">
                    <input type="checkbox" x-model="allSelected" @change="toggleAll" 
                        class="rounded border-border bg-background text-primary focus:ring-primary/20">
                </th>
                <th class="p-4 text-[10px] font-black uppercase tracking-widest text-muted-foreground/80">Category Name</th>
                <th class="p-4 text-[10px] font-black uppercase tracking-widest text-muted-foreground/80">Parent</th>
                <th class="p-4 text-[10px] font-black uppercase tracking-widest text-muted-foreground/80 text-center">Products</th>
                <th class="p-4 text-[10px] font-black uppercase tracking-widest text-muted-foreground/80 text-right">Actions</th>
            </tr>
        </thead>
        <tbody>
            @forelse($categories as $category)
                <tr x-bind:class="selectedCategories.includes({{ $category->id }}) ? 'bg-primary/5' : 'hover:bg-primary/[0.03] transition-colors'" class="border-b border-border/40 group">
                    <td class="p-4">
                        <input type="checkbox" name="category_ids[]" value="{{ $category->id }}" :checked="selectedCategories.includes({{ $category->id }})" @change="toggleCategory({{ $category->id }})"
                            class="rounded border-border bg-background text-primary focus:ring-primary/20">
                    </td>
                    <td class="p-4">
                        <div class="flex items-center gap-3">
                            @if($category->image)
                                <img src="{{ asset('storage/' . $category->image) }}" class="size-8 rounded-lg object-cover shadow-sm border border-border/40">
                            @else
                                <div class="size-8 rounded-lg bg-muted/20 flex items-center justify-center border border-border/40">
                                    <x-ui.icon name="folder" size="4" class="text-muted-foreground/40" />
                                </div>
                            @endif
                            <div class="text-sm font-bold text-foreground">{{ $category->name }}</div>
                        </div>
                    </td>
                    <td class="p-4 text-xs text-muted-foreground font-medium">
                        {{ $category->parent->name ?? '-' }}
                    </td>
                    <td class="p-4 text-center">
                        <span class="px-2 py-0.5 rounded-full bg-primary/10 border border-primary/20 text-[10px] font-black text-primary shadow-sm">
                            {{ $category->products_count }}
                        </span>
                    </td>
                    <td class="p-4 text-right">
                        <div class="flex items-center justify-end gap-2">
                            <button class="size-8 rounded-lg bg-background/50 border border-border/60 flex items-center justify-center text-muted-foreground hover:text-primary hover:border-primary/30 transition-all shadow-sm">
                                <x-ui.icon name="edit-3" size="3.5" />
                            </button>
                            <form action="{{ route('categories.destroy', $category) }}" method="POST" onsubmit="return confirm('Delete category?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="size-8 rounded-lg bg-background/50 border border-border/60 flex items-center justify-center text-muted-foreground hover:text-destructive hover:border-destructive/30 transition-all shadow-sm">
                                    <x-ui.icon name="trash-2" size="3.5" />
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" class="h-24 text-center text-muted-foreground text-sm">
                        No categories found.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

@if($categories->hasPages())
    <div class="p-4 border-t border-border/40">
        {{ $categories->links() }}
    </div>
@endif
