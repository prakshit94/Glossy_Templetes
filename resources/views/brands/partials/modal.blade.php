<!-- Brand Modal -->
<x-ui.modal id="brand-modal" maxWidth="md">
    <div class="p-8">
        <div class="flex items-center justify-between mb-6">
            <div class="flex items-center gap-3">
                <div class="size-10 rounded-2xl bg-primary/10 border border-primary/20 text-primary flex items-center justify-center shadow-inner">
                    <x-ui.icon name="award" size="5" />
                </div>
                <div>
                    <h3 class="text-sm font-black text-foreground uppercase tracking-widest" x-text="editingBrand ? 'Edit Brand' : 'Add New Brand'"></h3>
                    <p class="text-[10px] text-muted-foreground font-bold tracking-tight">Configure brand details and logo</p>
                </div>
            </div>
            <button type="button" @click="$dispatch('close-modal', { name: 'brand-modal' })" class="size-8 rounded-lg hover:bg-muted flex items-center justify-center transition-colors">
                <x-ui.icon name="x" size="4" />
            </button>
        </div>

        <form :action="editingBrand ? `{{ url('brands') }}/${editingBrand.id}` : '{{ route('brands.store') }}'" method="POST" enctype="multipart/form-data" class="space-y-5">
            @csrf
            <template x-if="editingBrand">
                @method('PUT')
            </template>

            <div class="space-y-2">
                <label for="name" class="text-[10px] font-black uppercase tracking-widest text-muted-foreground/80 ml-1">Brand Name</label>
                <input type="text" name="name" id="name" :value="editingBrand ? editingBrand.name : ''" required 
                    class="w-full h-11 px-4 rounded-xl border border-border bg-background/50 focus:bg-background focus:ring-2 focus:ring-primary/20 transition-all text-sm font-medium outline-none">
            </div>

            <div class="space-y-2">
                <label for="status" class="text-[10px] font-black uppercase tracking-widest text-muted-foreground/80 ml-1">Status</label>
                <select name="status" id="status" class="w-full h-11 px-4 rounded-xl border border-border bg-background/50 text-[10px] font-black uppercase tracking-widest focus:bg-background focus:ring-2 focus:ring-primary/20 outline-none transition-all">
                    <option value="active" :selected="editingBrand && editingBrand.status == 'active'">Active</option>
                    <option value="inactive" :selected="editingBrand && editingBrand.status == 'inactive'">Inactive</option>
                </select>
            </div>

            <div class="space-y-2">
                <label for="image" class="text-[10px] font-black uppercase tracking-widest text-muted-foreground/80 ml-1">Brand Logo</label>
                <input type="file" name="image" id="image" class="w-full text-xs text-muted-foreground file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-[10px] file:font-black file:uppercase file:tracking-widest file:bg-primary/10 file:text-primary hover:file:bg-primary/20">
            </div>

            <div class="flex items-center justify-end gap-3 pt-4">
                <x-ui.button type="button" variant="outline" @click="$dispatch('close-modal', { name: 'brand-modal' })" class="rounded-xl font-black uppercase tracking-widest text-[10px]">
                    Cancel
                </x-ui.button>
                <x-ui.button type="submit" class="rounded-xl font-black uppercase tracking-widest text-[10px] shadow-lg shadow-primary/20">
                    Save Brand
                </x-ui.button>
            </div>
        </form>
    </div>
</x-ui.modal>
