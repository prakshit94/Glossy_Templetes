<!-- Attribute Modal -->
<x-ui.modal id="attribute-modal" maxWidth="md">
    <div class="p-8">
        <div class="flex items-center justify-between mb-6">
            <div class="flex items-center gap-3">
                <div class="size-10 rounded-2xl bg-primary/10 border border-primary/20 text-primary flex items-center justify-center shadow-inner">
                    <x-ui.icon name="list" size="5" />
                </div>
                <div>
                    <h3 class="text-sm font-black text-foreground uppercase tracking-widest" x-text="editingAttribute ? 'Edit Attribute' : 'Add New Attribute'"></h3>
                    <p class="text-[10px] text-muted-foreground font-bold tracking-tight">Define attribute properties and behavior</p>
                </div>
            </div>
            <button type="button" @click="$dispatch('close-modal', { name: 'attribute-modal' })" class="size-8 rounded-lg hover:bg-muted flex items-center justify-center transition-colors">
                <x-ui.icon name="x" size="4" />
            </button>
        </div>

        <form :action="editingAttribute ? `{{ url('attributes') }}/${editingAttribute.id}` : '{{ route('attributes.store') }}'" method="POST" class="space-y-5">
            @csrf
            <template x-if="editingAttribute">
                @method('PUT')
            </template>

            <div class="space-y-2">
                <label for="name" class="text-[10px] font-black uppercase tracking-widest text-muted-foreground/80 ml-1">Attribute Name</label>
                <input type="text" name="name" id="name" :value="editingAttribute ? editingAttribute.name : ''" required placeholder="e.g. Color, Size, Material"
                    class="w-full h-11 px-4 rounded-xl border border-border bg-background/50 focus:bg-background focus:ring-2 focus:ring-primary/20 transition-all text-sm font-medium outline-none">
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div class="space-y-2">
                    <label for="type" class="text-[10px] font-black uppercase tracking-widest text-muted-foreground/80 ml-1">Input Type</label>
                    <select name="type" id="type" class="w-full h-11 px-4 rounded-xl border border-border bg-background/50 text-[10px] font-black uppercase tracking-widest focus:bg-background focus:ring-2 focus:ring-primary/20 outline-none transition-all">
                        <option value="text" :selected="editingAttribute && editingAttribute.type == 'text'">Text</option>
                        <option value="color" :selected="editingAttribute && editingAttribute.type == 'color'">Color</option>
                        <option value="select" :selected="editingAttribute && editingAttribute.type == 'select'">Select</option>
                    </select>
                </div>
                <div class="space-y-2">
                    <label for="status" class="text-[10px] font-black uppercase tracking-widest text-muted-foreground/80 ml-1">Status</label>
                    <select name="status" id="status" class="w-full h-11 px-4 rounded-xl border border-border bg-background/50 text-[10px] font-black uppercase tracking-widest focus:bg-background focus:ring-2 focus:ring-primary/20 outline-none transition-all">
                        <option value="active" :selected="editingAttribute && editingAttribute.status == 'active'">Active</option>
                        <option value="inactive" :selected="editingAttribute && editingAttribute.status == 'inactive'">Inactive</option>
                    </select>
                </div>
            </div>

            <div class="flex items-center justify-between p-4 rounded-2xl bg-muted/20 border border-border/40">
                <div>
                    <p class="text-xs font-black text-foreground uppercase tracking-widest">Filterable</p>
                    <p class="text-[10px] text-muted-foreground font-bold tracking-tight">Show in product filters</p>
                </div>
                <label class="relative inline-flex items-center cursor-pointer">
                    <input type="checkbox" name="is_filterable" value="1" :checked="editingAttribute && editingAttribute.is_filterable" class="sr-only peer">
                    <div class="w-11 h-6 bg-muted/40 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full rtl:peer-checked:after:-translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:start-[2px] after:bg-white after:border-border/40 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-primary"></div>
                </label>
            </div>

            <div class="flex items-center justify-end gap-3 pt-4">
                <x-ui.button type="button" variant="outline" @click="$dispatch('close-modal', { name: 'attribute-modal' })" class="rounded-xl font-black uppercase tracking-widest text-[10px]">
                    Cancel
                </x-ui.button>
                <x-ui.button type="submit" class="rounded-xl font-black uppercase tracking-widest text-[10px] shadow-lg shadow-primary/20">
                    Save Attribute
                </x-ui.button>
            </div>
        </form>
    </div>
</x-ui.modal>

<!-- Values Modal -->
<x-ui.modal id="values-modal" maxWidth="lg">
    <div class="p-8">
        <div class="flex items-center justify-between mb-6">
            <div class="flex items-center gap-3">
                <div class="size-10 rounded-2xl bg-violet-500/10 border border-violet-500/20 text-violet-500 flex items-center justify-center shadow-inner">
                    <x-ui.icon name="tag" size="5" />
                </div>
                <div>
                    <h3 class="text-sm font-black text-foreground uppercase tracking-widest" x-text="managingValues ? `Values for ${managingValues.name}` : 'Manage Values'"></h3>
                    <p class="text-[10px] text-muted-foreground font-bold tracking-tight">Add and remove attribute values</p>
                </div>
            </div>
            <button type="button" @click="$dispatch('close-modal', { name: 'values-modal' })" class="size-8 rounded-lg hover:bg-muted flex items-center justify-center transition-colors">
                <x-ui.icon name="x" size="4" />
            </button>
        </div>

        <div class="space-y-6">
            <!-- Add Value Form -->
            <form :action="managingValues ? `{{ url('attributes') }}/${managingValues.id}/values` : '#'" method="POST" class="p-4 rounded-2xl bg-muted/10 border border-border/40 flex items-end gap-3">
                @csrf
                <div class="flex-1 space-y-2">
                    <label class="text-[10px] font-black uppercase tracking-widest text-muted-foreground/80 ml-1">New Value</label>
                    <input type="text" name="value" required placeholder="e.g. Red, XL, Cotton"
                        class="w-full h-10 px-4 rounded-xl border border-border bg-background/50 focus:bg-background text-sm font-medium outline-none">
                </div>
                <template x-if="managingValues && managingValues.type == 'color'">
                    <div class="w-24 space-y-2">
                        <label class="text-[10px] font-black uppercase tracking-widest text-muted-foreground/80 ml-1">Color</label>
                        <input type="color" name="color_code" class="w-full h-10 p-1 rounded-xl border border-border bg-background/50 outline-none">
                    </div>
                </template>
                <input type="hidden" name="status" value="active">
                <x-ui.button type="submit" class="h-10 rounded-xl px-4 font-black uppercase tracking-widest text-[10px]">
                    Add
                </x-ui.button>
            </form>

            <!-- Values List -->
            <div class="max-h-[300px] overflow-y-auto custom-scrollbar pr-2">
                <div class="grid grid-cols-1 gap-2">
                    <template x-for="val in (managingValues ? managingValues.values : [])" :key="val.id">
                        <div class="flex items-center justify-between p-3 rounded-xl bg-card border border-border/40 hover:border-primary/20 transition-all group">
                            <div class="flex items-center gap-3">
                                <template x-if="managingValues.type == 'color'">
                                    <div class="size-5 rounded-full border border-border/50 shadow-sm" :style="`background-color: ${val.color_code}`"></div>
                                </template>
                                <span class="text-sm font-bold text-foreground" x-text="val.value"></span>
                            </div>
                            <form :action="`{{ url('attribute-values') }}/${val.id}`" method="POST" onsubmit="return confirm('Remove this value?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="size-8 rounded-lg bg-red-500/5 text-red-500 opacity-0 group-hover:opacity-100 transition-all hover:bg-red-500/10 flex items-center justify-center">
                                    <x-ui.icon name="trash-2" size="3.5" />
                                </button>
                            </form>
                        </div>
                    </template>
                </div>
            </div>
        </div>
    </div>
</x-ui.modal>
