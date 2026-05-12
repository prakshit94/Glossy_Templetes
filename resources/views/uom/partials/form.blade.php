<x-ui.card class="overflow-hidden border-border/60 shadow-2xl bg-card/30 backdrop-blur-2xl rounded-3xl sticky top-6">
    <x-ui.card-header class="border-b border-border/40 bg-muted/10 p-6">
        <div class="flex items-center gap-3">
            <div class="size-10 rounded-2xl bg-emerald-500/10 border border-emerald-500/20 text-emerald-500 flex items-center justify-center shadow-inner">
                <x-ui.icon name="plus" size="5" />
            </div>
            <h3 class="text-sm font-black text-foreground uppercase tracking-widest" x-text="editingUom ? 'Edit Unit' : 'Add New Unit'"></h3>
        </div>
    </x-ui.card-header>
    <x-ui.card-content class="p-6">
        <form :action="editingUom ? `{{ url('uoms') }}/${editingUom.id}` : '{{ route('uoms.store') }}'" method="POST" class="space-y-4">
            @csrf
            <template x-if="editingUom">
                @method('PUT')
            </template>

            <div class="space-y-2">
                <label for="name" class="text-[10px] font-black uppercase tracking-widest text-muted-foreground/80 ml-1">Unit Name</label>
                <input type="text" name="name" id="name" :value="editingUom ? editingUom.name : ''" required placeholder="e.g. Kilogram, Piece"
                    class="w-full h-11 px-4 rounded-xl border border-border bg-background/50 focus:bg-background focus:ring-2 focus:ring-primary/20 transition-all text-sm font-medium outline-none">
            </div>

            <div class="space-y-2">
                <label for="code" class="text-[10px] font-black uppercase tracking-widest text-muted-foreground/80 ml-1">Unit Code</label>
                <input type="text" name="code" id="code" :value="editingUom ? editingUom.code : ''" required placeholder="e.g. kg, pcs"
                    class="w-full h-11 px-4 rounded-xl border border-border bg-background/50 focus:bg-background focus:ring-2 focus:ring-primary/20 transition-all text-sm font-medium outline-none">
            </div>

            <div class="space-y-2">
                <label for="status" class="text-[10px] font-black uppercase tracking-widest text-muted-foreground/80 ml-1">Status</label>
                <select name="status" id="status" class="w-full h-11 px-4 rounded-xl border border-border bg-background/50 text-[10px] font-black uppercase tracking-widest focus:bg-background focus:ring-2 focus:ring-primary/20 transition-all">
                    <option value="active" :selected="editingUom && editingUom.status == 'active'">Active</option>
                    <option value="inactive" :selected="editingUom && editingUom.status == 'inactive'">Inactive</option>
                </select>
            </div>

            <div class="flex items-center justify-between p-3 rounded-xl bg-muted/20 border border-border/40">
                <span class="text-[10px] font-black uppercase tracking-widest text-foreground">Base Unit</span>
                <label class="relative inline-flex items-center cursor-pointer">
                    <input type="checkbox" name="is_base_unit" value="1" :checked="editingUom && editingUom.is_base_unit" class="sr-only peer">
                    <div class="w-11 h-6 bg-muted/40 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full rtl:peer-checked:after:-translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:start-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-primary"></div>
                </label>
            </div>

            <div class="flex gap-3 pt-2">
                <template x-if="editingUom">
                    <x-ui.button type="button" variant="outline" @click="editingUom = null" class="flex-1 h-12 rounded-xl font-black uppercase tracking-widest text-[10px]">
                        Cancel
                    </x-ui.button>
                </template>
                <x-ui.button type="submit" class="flex-1 h-12 rounded-xl font-black uppercase tracking-widest text-[10px] shadow-lg shadow-primary/20">
                    Save Unit
                </x-ui.button>
            </div>
        </form>
    </x-ui.card-content>
</x-ui.card>
