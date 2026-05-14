{{-- Address Modal --}}
<x-ui.modal id="address-modal" maxWidth="2xl">
    <div class="p-8">
        <div class="flex items-center justify-between mb-6">
            <div class="flex items-center gap-3">
                <div class="size-10 rounded-2xl bg-primary/10 border border-primary/20 text-primary flex items-center justify-center shadow-inner">
                    <x-ui.icon name="map-pin" size="5" />
                </div>
                <div>
                    <h3 class="text-sm font-black text-foreground uppercase tracking-widest" x-text="editingAddress ? 'Edit Address' : 'Add New Address'"></h3>
                    <p class="text-[10px] text-muted-foreground font-bold tracking-tight">Manage customer registered addresses</p>
                </div>
            </div>
            <button type="button" @click="$dispatch('close-modal', { name: 'address-modal' })" class="size-8 rounded-lg hover:bg-muted flex items-center justify-center transition-colors">
                <x-ui.icon name="x" size="4" />
            </button>
        </div>

        <form :action="editingAddress ? `/customers/{{ $customer->id }}/addresses/${editingAddress.id}` : `/customers/{{ $customer->id }}/addresses`" method="POST" class="space-y-5">
            @csrf
            <template x-if="editingAddress">
                @method('PUT')
            </template>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                <div class="space-y-2">
                    <label for="label" class="text-[10px] font-black uppercase tracking-widest text-muted-foreground/80 ml-1">Address Label</label>
                    <input type="text" name="label" id="label" :value="editingAddress ? editingAddress.label : ''" placeholder="e.g. Home, Office" required 
                        class="w-full h-11 px-4 rounded-xl border border-border bg-background/50 focus:bg-background focus:ring-2 focus:ring-primary/20 transition-all text-sm font-medium outline-none">
                </div>

                <div class="space-y-2">
                    <label for="status" class="text-[10px] font-black uppercase tracking-widest text-muted-foreground/80 ml-1">Status</label>
                    <select name="status" id="status" class="w-full h-11 px-4 rounded-xl border border-border bg-background/50 text-[10px] font-black uppercase tracking-widest focus:bg-background focus:ring-2 focus:ring-primary/20 outline-none transition-all">
                        <option value="active" :selected="editingAddress && editingAddress.status == 'active'">Active</option>
                        <option value="inactive" :selected="editingAddress && editingAddress.status == 'inactive'">Inactive</option>
                    </select>
                </div>
            </div>

            <div class="space-y-2 relative">
                <label for="villageSearch" class="text-[10px] font-black uppercase tracking-widest text-muted-foreground/80 ml-1">Search Village / Area (Optional)</label>
                <div class="relative">
                    <x-ui.icon name="search" size="4" class="absolute left-3 top-1/2 -translate-y-1/2 text-muted-foreground" />
                    <input type="text" id="villageSearch" x-model="villageSearch" @input.debounce.500ms="searchVillages" placeholder="Search by village name or pincode..." autocomplete="off"
                        class="w-full h-11 pl-9 pr-4 rounded-xl border border-border bg-background/50 focus:bg-background focus:ring-2 focus:ring-primary/20 transition-all text-sm font-medium outline-none">
                    <div x-show="searchingVillages" x-cloak class="absolute right-3 top-1/2 -translate-y-1/2">
                        <x-ui.icon name="refresh-cw" size="4" class="animate-spin text-primary" />
                    </div>
                </div>
                
                <div x-show="villages.length > 0" x-cloak @click.away="villages = []" class="absolute z-50 w-full mt-1 bg-card border border-border rounded-xl shadow-lg shadow-primary/5 max-h-60 overflow-y-auto backdrop-blur-xl">
                    <template x-for="village in villages" :key="village.id">
                        <div @click="selectVillage(village)" class="p-3 border-b border-border/40 hover:bg-primary/5 cursor-pointer transition-colors last:border-0 group">
                            <p class="text-sm font-bold text-foreground group-hover:text-primary transition-colors" x-text="village.name"></p>
                            <p class="text-[10px] text-muted-foreground uppercase tracking-widest mt-0.5">
                                <span x-text="village.taluka"></span>, <span x-text="village.district"></span> - <span x-text="village.pincode"></span>
                            </p>
                        </div>
                    </template>
                </div>
            </div>
            
            <input type="hidden" name="village_id" id="village_id" :value="editingAddress ? editingAddress.village_id : ''">

            <div class="space-y-2">
                <label for="address_line_1" class="text-[10px] font-black uppercase tracking-widest text-muted-foreground/80 ml-1">Address Line 1</label>
                <input type="text" name="address_line_1" id="address_line_1" :value="editingAddress ? editingAddress.address_line_1 : ''" required 
                    class="w-full h-11 px-4 rounded-xl border border-border bg-background/50 focus:bg-background focus:ring-2 focus:ring-primary/20 transition-all text-sm font-medium outline-none">
            </div>

            <div class="space-y-2">
                <label for="address_line_2" class="text-[10px] font-black uppercase tracking-widest text-muted-foreground/80 ml-1">Address Line 2 (Optional)</label>
                <input type="text" name="address_line_2" id="address_line_2" :value="editingAddress ? editingAddress.address_line_2 : ''" 
                    class="w-full h-11 px-4 rounded-xl border border-border bg-background/50 focus:bg-background focus:ring-2 focus:ring-primary/20 transition-all text-sm font-medium outline-none">
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                <div class="space-y-2">
                    <label for="village_name" class="text-[10px] font-black uppercase tracking-widest text-muted-foreground/80 ml-1">Village</label>
                    <input type="text" name="village_name" id="village_name" :value="editingAddress ? editingAddress.village_name : ''" 
                        class="w-full h-11 px-4 rounded-xl border border-border bg-background/50 focus:bg-background focus:ring-2 focus:ring-primary/20 transition-all text-sm font-medium outline-none">
                </div>
                <div class="space-y-2">
                    <label for="post_office" class="text-[10px] font-black uppercase tracking-widest text-muted-foreground/80 ml-1">Post Office</label>
                    <input type="text" name="post_office" id="post_office" :value="editingAddress ? editingAddress.post_office : ''" 
                        class="w-full h-11 px-4 rounded-xl border border-border bg-background/50 focus:bg-background focus:ring-2 focus:ring-primary/20 transition-all text-sm font-medium outline-none">
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-4 gap-5">
                <div class="space-y-2">
                    <label for="taluka" class="text-[10px] font-black uppercase tracking-widest text-muted-foreground/80 ml-1">Taluka</label>
                    <input type="text" name="taluka" id="taluka" :value="editingAddress ? editingAddress.taluka : ''" 
                        class="w-full h-11 px-4 rounded-xl border border-border bg-background/50 focus:bg-background focus:ring-2 focus:ring-primary/20 transition-all text-sm font-medium outline-none">
                </div>
                <div class="space-y-2">
                    <label for="city" class="text-[10px] font-black uppercase tracking-widest text-muted-foreground/80 ml-1">District/City</label>
                    <input type="text" name="city" id="city" :value="editingAddress ? editingAddress.city : ''" required 
                        class="w-full h-11 px-4 rounded-xl border border-border bg-background/50 focus:bg-background focus:ring-2 focus:ring-primary/20 transition-all text-sm font-medium outline-none">
                </div>
                <div class="space-y-2">
                    <label for="state" class="text-[10px] font-black uppercase tracking-widest text-muted-foreground/80 ml-1">State</label>
                    <input type="text" name="state" id="state" :value="editingAddress ? editingAddress.state : ''" required 
                        class="w-full h-11 px-4 rounded-xl border border-border bg-background/50 focus:bg-background focus:ring-2 focus:ring-primary/20 transition-all text-sm font-medium outline-none">
                </div>
                <div class="space-y-2">
                    <label for="pincode" class="text-[10px] font-black uppercase tracking-widest text-muted-foreground/80 ml-1">Pincode</label>
                    <input type="text" name="pincode" id="pincode" :value="editingAddress ? editingAddress.pincode : ''" required 
                        class="w-full h-11 px-4 rounded-xl border border-border bg-background/50 focus:bg-background focus:ring-2 focus:ring-primary/20 transition-all text-sm font-medium outline-none">
                </div>
            </div>

            <div class="flex items-center gap-2 pt-2 ml-1">
                <input type="checkbox" name="is_default" id="is_default" value="1" :checked="editingAddress && editingAddress.is_default"
                    class="rounded border-border text-primary focus:ring-primary/20">
                <label for="is_default" class="text-xs font-medium text-foreground cursor-pointer">Set as default address</label>
            </div>

            <div class="flex items-center justify-end gap-3 pt-4 border-t border-border/40">
                <x-ui.button type="button" variant="outline" @click="$dispatch('close-modal', { name: 'address-modal' })" class="rounded-xl font-black uppercase tracking-widest text-[10px]">
                    Cancel
                </x-ui.button>
                <x-ui.button type="submit" class="rounded-xl font-black uppercase tracking-widest text-[10px] shadow-lg shadow-primary/20">
                    Save Address
                </x-ui.button>
            </div>
        </form>
    </div>
</x-ui.modal>

{{-- Delete Address Modal --}}
<x-ui.modal id="delete-address-modal" maxWidth="sm">
    <div class="p-8 text-center">
        <div class="size-16 rounded-full bg-destructive/10 text-destructive flex items-center justify-center mx-auto mb-4">
            <x-ui.icon name="alert-triangle" size="8" />
        </div>
        <h3 class="text-lg font-black text-foreground mb-2">Delete Address?</h3>
        <p class="text-sm text-muted-foreground mb-6">Are you sure you want to delete <span class="font-bold text-foreground" x-text="deletingAddress?.label || 'this address'"></span>? This action cannot be undone.</p>
        
        <form :action="deletingAddress ? `/customers/{{ $customer->id }}/addresses/${deletingAddress.id}` : '#'" method="POST">
            @csrf
            @method('DELETE')
            <div class="flex flex-col sm:flex-row items-center justify-center gap-3">
                <x-ui.button type="button" variant="outline" @click="$dispatch('close-modal', { name: 'delete-address-modal' })" class="w-full sm:w-auto rounded-xl font-black uppercase tracking-widest text-[10px]">
                    Cancel
                </x-ui.button>
                <x-ui.button type="submit" variant="destructive" class="w-full sm:w-auto rounded-xl font-black uppercase tracking-widest text-[10px] shadow-lg shadow-destructive/20 bg-destructive text-destructive-foreground hover:bg-destructive/90">
                    Delete Address
                </x-ui.button>
            </div>
        </form>
    </div>
</x-ui.modal>
