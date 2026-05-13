<x-layouts.app>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-foreground leading-tight">
            {{ __('Product Categories') }}
        </h2>
    </x-slot>

    <div class="p-6 lg:p-10">
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            
            <!-- Left Side: Category List -->
            <div class="lg:col-span-2">
                <x-ui.card class="overflow-hidden border-border/60 shadow-2xl bg-card/30 backdrop-blur-2xl rounded-3xl">
                    <x-ui.card-header class="border-b border-border/40 bg-muted/10 p-6">
                        <div class="flex items-center gap-3">
                            <div class="size-10 rounded-2xl bg-primary/10 border border-primary/20 text-primary flex items-center justify-center shadow-inner">
                                <x-ui.icon name="list" size="5" />
                            </div>
                            <div>
                                <h3 class="text-sm font-black text-foreground uppercase tracking-widest">Existing Categories</h3>
                            </div>
                        </div>
                    </x-ui.card-header>
                    <x-ui.card-content class="p-0">
                        <div class="overflow-x-auto">
                            <table class="w-full text-left border-collapse">
                                <thead>
                                    <tr class="bg-muted/5 border-b border-border/40">
                                        <th class="p-4 text-[10px] font-black uppercase tracking-widest text-muted-foreground/80">Category Name</th>
                                        <th class="p-4 text-[10px] font-black uppercase tracking-widest text-muted-foreground/80">Parent</th>
                                        <th class="p-4 text-[10px] font-black uppercase tracking-widest text-muted-foreground/80 text-center">Products</th>
                                        <th class="p-4 text-[10px] font-black uppercase tracking-widest text-muted-foreground/80 text-right">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($categories as $category)
                                        <tr class="border-b border-border/40 hover:bg-primary/[0.03] transition-colors group">
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
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </x-ui.card-content>
                </x-ui.card>
            </div>

            <!-- Right Side: Add Category Form -->
            <div>
                <x-ui.card class="overflow-hidden border-border/60 shadow-2xl bg-card/30 backdrop-blur-2xl rounded-3xl sticky top-6">
                    <x-ui.card-header class="border-b border-border/40 bg-muted/10 p-6">
                        <div class="flex items-center gap-3">
                            <div class="size-10 rounded-2xl bg-emerald-500/10 border border-emerald-500/20 text-emerald-500 flex items-center justify-center shadow-inner">
                                <x-ui.icon name="plus" size="5" />
                            </div>
                            <div>
                                <h3 class="text-sm font-black text-foreground uppercase tracking-widest">Add New Category</h3>
                            </div>
                        </div>
                    </x-ui.card-header>
                    <x-ui.card-content class="p-6">
                        <form action="{{ route('categories.store') }}" method="POST" enctype="multipart/form-data" class="space-y-4">
                            @csrf
                            <div class="space-y-2">
                                <label for="name" class="text-[10px] font-black uppercase tracking-widest text-muted-foreground/80 ml-1">Category Name</label>
                                <input type="text" name="name" id="name" required class="w-full h-11 px-4 rounded-xl border border-border/60 bg-background/50 text-sm font-medium focus:bg-background focus:ring-2 focus:ring-primary/20 focus:border-primary outline-none transition-all shadow-inner text-foreground placeholder:text-muted-foreground/40">
                            </div>
                            <div class="space-y-2">
                                <label for="parent_id" class="text-[10px] font-black uppercase tracking-widest text-muted-foreground/80 ml-1">Parent Category</label>
                                <select name="parent_id" id="parent_id" class="w-full h-11 px-4 rounded-xl border border-border/60 bg-background/50 text-sm font-medium focus:bg-background focus:ring-2 focus:ring-primary/20 focus:border-primary outline-none transition-all shadow-inner text-foreground">
                                    <option value="">None</option>
                                    @foreach($categories as $cat)
                                        <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="space-y-2">
                                <label for="status" class="text-[10px] font-black uppercase tracking-widest text-muted-foreground/80 ml-1">Status</label>
                                <select name="status" id="status" class="w-full h-11 px-4 rounded-xl border border-border/60 bg-background/50 text-[10px] font-black uppercase tracking-widest focus:bg-background focus:ring-2 focus:ring-primary/20 focus:border-primary outline-none transition-all shadow-inner text-foreground">
                                    <option value="active">Active</option>
                                    <option value="inactive">Inactive</option>
                                </select>
                            </div>
                            <x-ui.button type="submit" class="w-full h-12 rounded-xl font-black uppercase tracking-widest text-[10px] mt-2 shadow-lg shadow-primary/20 hover:scale-[1.02] active:scale-[0.98] transition-all">
                                Save Category
                            </x-ui.button>
                        </form>
                    </x-ui.card-content>
                </x-ui.card>
            </div>

        </div>
    </div>
</x-layouts.app>
