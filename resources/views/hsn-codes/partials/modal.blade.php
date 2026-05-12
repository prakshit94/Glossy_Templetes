<!-- HSN Modal -->
<x-ui.modal id="hsn-modal" maxWidth="md">
    <div class="p-8">
        <div class="flex items-center justify-between mb-6">
            <div class="flex items-center gap-3">
                <div class="size-10 rounded-2xl bg-primary/10 border border-primary/20 text-primary flex items-center justify-center shadow-inner">
                    <x-ui.icon name="file-text" size="5" />
                </div>
                <div>
                    <h3 class="text-sm font-black text-foreground uppercase tracking-widest" x-text="editingCode ? 'Edit HSN Code' : 'Add New HSN Code'"></h3>
                    <p class="text-[10px] text-muted-foreground font-bold tracking-tight">Configure HSN/SAC details for tax compliance</p>
                </div>
            </div>
            <button type="button" @click="$dispatch('close-modal', { name: 'hsn-modal' })" class="size-8 rounded-lg hover:bg-muted flex items-center justify-center transition-colors">
                <x-ui.icon name="x" size="4" />
            </button>
        </div>

        <form :action="editingCode ? `{{ url('hsn-codes') }}/${editingCode.id}` : '{{ route('hsn-codes.store') }}'" method="POST" class="space-y-5">
            @csrf
            <template x-if="editingCode">
                @method('PUT')
            </template>

            <div class="space-y-2">
                <label for="code" class="text-[10px] font-black uppercase tracking-widest text-muted-foreground/80 ml-1">HSN Code</label>
                <input type="text" name="code" id="code" :value="editingCode ? editingCode.code : ''" required placeholder="e.g. 1001, 8517"
                    class="w-full h-11 px-4 rounded-xl border border-border bg-background/50 focus:bg-background focus:ring-2 focus:ring-primary/20 transition-all text-sm font-black outline-none">
            </div>

            <div class="space-y-2">
                <label for="description" class="text-[10px] font-black uppercase tracking-widest text-muted-foreground/80 ml-1">Description</label>
                <textarea name="description" id="description" rows="3" placeholder="Description of goods or services..."
                    class="w-full px-4 py-3 rounded-xl border border-border bg-background/50 focus:bg-background focus:ring-2 focus:ring-primary/20 transition-all text-sm font-medium outline-none" x-text="editingCode ? editingCode.description : ''"></textarea>
            </div>

            <div class="space-y-2">
                <label for="status" class="text-[10px] font-black uppercase tracking-widest text-muted-foreground/80 ml-1">Status</label>
                <select name="status" id="status" class="w-full h-11 px-4 rounded-xl border border-border bg-background/50 text-[10px] font-black uppercase tracking-widest focus:bg-background focus:ring-2 focus:ring-primary/20 outline-none transition-all">
                    <option value="active" :selected="editingCode && editingCode.status == 'active'">Active</option>
                    <option value="inactive" :selected="editingCode && editingCode.status == 'inactive'">Inactive</option>
                </select>
            </div>

            <div class="flex items-center justify-end gap-3 pt-4">
                <x-ui.button type="button" variant="outline" @click="$dispatch('close-modal', { name: 'hsn-modal' })" class="rounded-xl font-black uppercase tracking-widest text-[10px]">
                    Cancel
                </x-ui.button>
                <x-ui.button type="submit" class="rounded-xl font-black uppercase tracking-widest text-[10px] shadow-lg shadow-primary/20">
                    Save Code
                </x-ui.button>
            </div>
        </form>
    </div>
</x-ui.modal>
