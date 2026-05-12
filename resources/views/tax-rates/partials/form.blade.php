<x-ui.card class="overflow-hidden border-border/60 shadow-2xl bg-card/30 backdrop-blur-2xl rounded-3xl sticky top-6">
    <x-ui.card-header class="border-b border-border/40 bg-muted/10 p-6">
        <div class="flex items-center gap-3">
            <div class="size-10 rounded-2xl bg-emerald-500/10 border border-emerald-500/20 text-emerald-500 flex items-center justify-center shadow-inner">
                <x-ui.icon name="plus" size="5" />
            </div>
            <h3 class="text-sm font-black text-foreground uppercase tracking-widest" x-text="editingRate ? 'Edit Rate' : 'Add New Rate'"></h3>
        </div>
    </x-ui.card-header>
    <x-ui.card-content class="p-6">
        <form :action="editingRate ? `{{ url('tax-rates') }}/${editingRate.id}` : '{{ route('tax-rates.store') }}'" method="POST" class="space-y-4">
            @csrf
            <template x-if="editingRate">
                @method('PUT')
            </template>

            <div class="space-y-2">
                <label for="name" class="text-[10px] font-black uppercase tracking-widest text-muted-foreground/80 ml-1">Tax Name</label>
                <input type="text" name="name" id="name" :value="editingRate ? editingRate.name : ''" required placeholder="e.g. GST 18%, VAT 5%"
                    class="w-full h-11 px-4 rounded-xl border border-border bg-background/50 focus:bg-background focus:ring-2 focus:ring-primary/20 transition-all text-sm font-medium outline-none">
            </div>

            <div class="space-y-2">
                <label for="rate" class="text-[10px] font-black uppercase tracking-widest text-muted-foreground/80 ml-1">Rate (%)</label>
                <input type="number" step="0.01" name="rate" id="rate" :value="editingRate ? editingRate.rate : ''" required placeholder="e.g. 18.00"
                    class="w-full h-11 px-4 rounded-xl border border-border bg-background/50 focus:bg-background focus:ring-2 focus:ring-primary/20 transition-all text-sm font-black text-primary outline-none">
            </div>

            <div class="space-y-2">
                <label for="status" class="text-[10px] font-black uppercase tracking-widest text-muted-foreground/80 ml-1">Status</label>
                <select name="status" id="status" class="w-full h-11 px-4 rounded-xl border border-border bg-background/50 text-[10px] font-black uppercase tracking-widest focus:bg-background focus:ring-2 focus:ring-primary/20 transition-all">
                    <option value="active" :selected="editingRate && editingRate.status == 'active'">Active</option>
                    <option value="inactive" :selected="editingRate && editingRate.status == 'inactive'">Inactive</option>
                </select>
            </div>

            <div class="flex gap-3 pt-2">
                <template x-if="editingRate">
                    <x-ui.button type="button" variant="outline" @click="editingRate = null" class="flex-1 h-12 rounded-xl font-black uppercase tracking-widest text-[10px]">
                        Cancel
                    </x-ui.button>
                </template>
                <x-ui.button type="submit" class="flex-1 h-12 rounded-xl font-black uppercase tracking-widest text-[10px] shadow-lg shadow-primary/20">
                    Save Rate
                </x-ui.button>
            </div>
        </form>
    </x-ui.card-content>
</x-ui.card>
