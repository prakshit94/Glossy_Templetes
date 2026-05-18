<x-layouts.app>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-foreground leading-tight">
            {{ __('Edit Warehouse') }}
        </h2>
    </x-slot>

    <div class="p-6 lg:p-10" x-data="{
        form: {
            name: @js(old('name', $warehouse->name)),
            company_name: @js(old('company_name', $warehouse->company_name)),
            gstin: @js(old('gstin', $warehouse->gstin)),
            phone: @js(old('phone', $warehouse->phone)),
            code: @js(old('code', $warehouse->code)),
            address_line_1: @js(old('address_line_1', $warehouse->address_line_1 ?? $warehouse->address)),
            address_line_2: @js(old('address_line_2', $warehouse->address_line_2)),
            village_id: @js(old('village_id', $warehouse->village_id)),
            village_name: @js(old('village_name', $warehouse->village?->village_name ?? $warehouse->village_name)),
            post_office: @js(old('post_office', $warehouse->village?->post_so_name ?? $warehouse->post_office)),
            taluka: @js(old('taluka', $warehouse->village?->taluka_name ?? $warehouse->taluka)),
            city: @js(old('city', $warehouse->village?->district_name ?? $warehouse->city)),
            state: @js(old('state', $warehouse->village?->state_name ?? $warehouse->state)),
            pincode: @js(old('pincode', $warehouse->village?->pincode ?? $warehouse->pincode)),
            status: @js(old('status', $warehouse->status)),
            is_default: {{ old('is_default', $warehouse->is_default) ? 'true' : 'false' }}
        },
        villageSearch: @js(old('village_name', $warehouse->village?->village_name ?? $warehouse->village_name ?? '')),
        villages: [],
        searchingVillages: false,
        async searchVillages() {
            if (this.villageSearch.length < 3) {
                this.villages = [];
                return;
            }
            this.searchingVillages = true;
            try {
                const res = await fetch(`/villages-search?q=${this.villageSearch}`, {
                    headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
                });
                if (!res.ok) throw new Error('Network error');
                const data = await res.json();
                this.villages = data.data || [];
            } catch (e) {
                console.error(e);
            } finally {
                this.searchingVillages = false;
            }
        },
        selectVillage(v) {
            this.form.village_id = v.id;
            this.form.village_name = v.name || v.village_name || '';
            this.form.post_office = v.post_office || v.post_so_name || '';
            this.form.taluka = v.taluka || v.taluka_name || '';
            this.form.city = v.district || v.district_name || '';
            this.form.state = v.state || v.state_name || '';
            this.form.pincode = v.pincode || '';
            this.villages = [];
            this.villageSearch = v.name || v.village_name || '';
        }
    }">
        <div class="max-w-4xl mx-auto space-y-8">
            <form action="{{ route('warehouses.update', $warehouse) }}" method="POST" class="space-y-8">
                @csrf
                @method('PUT')
                <input type="hidden" name="village_id" x-model="form.village_id">
                <input type="hidden" name="is_default" value="0">

                <!-- Card 1: Basic Identity -->
                <x-ui.card class="overflow-hidden border-border/60 shadow-2xl bg-card/30 backdrop-blur-2xl rounded-3xl">
                    <x-ui.card-header class="border-b border-border/40 bg-muted/10 p-6">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-3">
                                <div class="size-12 rounded-2xl bg-primary/10 border border-primary/20 text-primary flex items-center justify-center shadow-inner">
                                    <x-ui.icon name="edit" size="6" />
                                </div>
                                <div>
                                    <h3 class="text-lg font-black text-foreground tracking-tight">Edit Warehouse: {{ $warehouse->name }}</h3>
                                    <p class="text-[10px] uppercase font-bold text-muted-foreground tracking-widest">Update storage location details</p>
                                </div>
                            </div>
                            <a href="{{ route('warehouses.index') }}">
                                <x-ui.button type="button" variant="outline" size="sm" class="rounded-xl font-bold uppercase tracking-widest text-[10px] border-border hover:bg-muted transition-colors">
                                    <x-ui.icon name="arrow-left" size="3" class="mr-2" /> Back to list
                                </x-ui.button>
                            </a>
                        </div>
                    </x-ui.card-header>

                    <x-ui.card-content class="p-8 space-y-8">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                            <div class="space-y-2">
                                <label for="name" class="text-[10px] font-black uppercase tracking-widest text-muted-foreground/80 ml-1">Warehouse Name *</label>
                                <input type="text" name="name" id="name" x-model="form.name" required 
                                    class="w-full h-12 px-4 rounded-2xl border border-border bg-background/50 focus:bg-background focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all text-sm font-medium" placeholder="e.g. Central Hub">
                                @error('name') <p class="text-[10px] text-destructive font-bold mt-1 ml-1">{{ $message }}</p> @enderror
                            </div>

                            <div class="space-y-2">
                                <label for="code" class="text-[10px] font-black uppercase tracking-widest text-muted-foreground/80 ml-1">Warehouse Code *</label>
                                <input type="text" name="code" id="code" x-model="form.code" required 
                                    class="w-full h-12 px-4 rounded-2xl border border-border bg-background/50 focus:bg-background focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all text-sm font-medium" placeholder="e.g. WH-001">
                                @error('code') <p class="text-[10px] text-destructive font-bold mt-1 ml-1">{{ $message }}</p> @enderror
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-8 pt-4 border-t border-border/40">
                            <div class="space-y-2">
                                <label for="status" class="text-[10px] font-black uppercase tracking-widest text-muted-foreground/80 ml-1">Status</label>
                                <select name="status" id="status" x-model="form.status" required 
                                    class="w-full h-12 px-4 rounded-2xl border border-border bg-background/50 focus:bg-background focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all text-sm font-black uppercase tracking-widest text-foreground">
                                    <option value="active">Active</option>
                                    <option value="inactive">Inactive</option>
                                </select>
                            </div>

                            <div class="space-y-2 flex flex-col justify-center pt-2">
                                <label class="text-[10px] font-black uppercase tracking-widest text-muted-foreground/80 ml-1 block mb-2">Default Warehouse</label>
                                <label class="relative inline-flex items-center cursor-pointer">
                                    <input type="checkbox" name="is_default" value="1" x-model="form.is_default" class="sr-only peer">
                                    <div class="w-11 h-6 bg-muted peer-focus:outline-none peer-focus:ring-2 peer-focus:ring-ring rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-border/40 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-primary"></div>
                                    <span class="ml-3 text-sm font-medium text-foreground">Set as Default Warehouse</span>
                                </label>
                            </div>
                        </div>
                    </x-ui.card-content>
                </x-ui.card>

                <!-- Card 1.5: Company Details -->
                <x-ui.card class="overflow-hidden border-border/60 shadow-2xl bg-card/30 backdrop-blur-2xl rounded-3xl">
                    <x-ui.card-header class="border-b border-border/40 bg-muted/10 p-6">
                        <div class="flex items-center gap-3">
                            <div class="size-12 rounded-2xl bg-amber-500/10 border border-amber-500/20 text-amber-500 flex items-center justify-center shadow-inner">
                                <x-ui.icon name="shield" size="6" />
                            </div>
                            <div>
                                <h3 class="text-base font-black text-foreground tracking-tight">Company & Tax Details (Sender Hub)</h3>
                                <p class="text-[10px] uppercase font-bold text-muted-foreground tracking-widest">Official details displayed on GST invoices and dispatch receipts</p>
                            </div>
                        </div>
                    </x-ui.card-header>

                    <x-ui.card-content class="p-8 space-y-8">
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                            <div class="space-y-2">
                                <label for="company_name" class="text-[10px] font-black uppercase tracking-widest text-muted-foreground/80 ml-1">Company / Entity Name</label>
                                <input type="text" name="company_name" id="company_name" x-model="form.company_name" 
                                    class="w-full h-12 px-4 rounded-2xl border border-border bg-background/50 focus:bg-background focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all text-sm font-medium" placeholder="e.g. ABC Pvt. Ltd.">
                                @error('company_name') <p class="text-[10px] text-destructive font-bold mt-1 ml-1">{{ $message }}</p> @enderror
                            </div>

                            <div class="space-y-2">
                                <label for="gstin" class="text-[10px] font-black uppercase tracking-widest text-muted-foreground/80 ml-1">GSTIN Number</label>
                                <input type="text" name="gstin" id="gstin" x-model="form.gstin" 
                                    class="w-full h-12 px-4 rounded-2xl border border-border bg-background/50 focus:bg-background focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all text-sm font-mono uppercase font-bold text-primary" placeholder="e.g. 24AAMCK0386L1Z6">
                                @error('gstin') <p class="text-[10px] text-destructive font-bold mt-1 ml-1">{{ $message }}</p> @enderror
                            </div>

                            <div class="space-y-2">
                                <label for="phone" class="text-[10px] font-black uppercase tracking-widest text-muted-foreground/80 ml-1">Contact Phone / Mobile</label>
                                <input type="text" name="phone" id="phone" x-model="form.phone" 
                                    class="w-full h-12 px-4 rounded-2xl border border-border bg-background/50 focus:bg-background focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all text-sm font-medium" placeholder="e.g. +91 9199125925">
                                @error('phone') <p class="text-[10px] text-destructive font-bold mt-1 ml-1">{{ $message }}</p> @enderror
                            </div>
                        </div>
                    </x-ui.card-content>
                </x-ui.card>

                <!-- Card 2: Complete Address & Location -->
                <x-ui.card class="overflow-hidden border-border/60 shadow-2xl bg-card/30 backdrop-blur-2xl rounded-3xl">
                    <x-ui.card-header class="border-b border-border/40 bg-muted/10 p-6">
                        <div class="flex items-center gap-3">
                            <div class="size-10 rounded-2xl bg-purple-500/10 border border-purple-500/20 text-purple-500 flex items-center justify-center shadow-inner">
                                <x-ui.icon name="map-pin" size="5" />
                            </div>
                            <div>
                                <h3 class="text-base font-black text-foreground tracking-tight">Location & Complete Address</h3>
                                <p class="text-[10px] uppercase font-bold text-muted-foreground tracking-widest">Village, district, post office and PIN details</p>
                            </div>
                        </div>
                    </x-ui.card-header>

                    <x-ui.card-content class="p-8 space-y-8">
                        <div class="space-y-2 relative">
                            <label for="villageSearch" class="text-[10px] font-black uppercase tracking-widest text-muted-foreground/80 ml-1">Search Village / Area (Optional)</label>
                            <div class="relative">
                                <x-ui.icon name="search" size="4" class="absolute left-4 top-1/2 -translate-y-1/2 text-muted-foreground" />
                                <input type="text" id="villageSearch" x-model="villageSearch" @input.debounce.500ms="searchVillages" placeholder="Search by village name or pincode to auto-fill..." autocomplete="off"
                                    class="w-full h-12 pl-11 pr-4 rounded-2xl border border-border bg-background/50 focus:bg-background focus:ring-2 focus:ring-primary/20 transition-all text-sm font-medium outline-none">
                                <div x-show="searchingVillages" x-cloak class="absolute right-4 top-1/2 -translate-y-1/2">
                                    <x-ui.icon name="refresh-cw" size="4" class="animate-spin text-primary" />
                                </div>
                            </div>
                            
                            <div x-show="villages.length > 0" x-cloak @click.away="villages = []" class="absolute z-50 w-full mt-1 bg-card border border-border rounded-2xl shadow-2xl max-h-60 overflow-y-auto backdrop-blur-xl">
                                <template x-for="village in villages" :key="village.id">
                                    <div @click="selectVillage(village)" class="p-4 border-b border-border/40 hover:bg-primary/5 cursor-pointer transition-colors last:border-0 group">
                                        <p class="text-sm font-bold text-foreground group-hover:text-primary transition-colors" x-text="village.name"></p>
                                        <p class="text-[10px] text-muted-foreground uppercase tracking-widest mt-0.5">
                                            <span x-text="village.post_office"></span>, <span x-text="village.taluka"></span>, <span x-text="village.district"></span> - <span x-text="village.pincode"></span>
                                        </p>
                                    </div>
                                </template>
                            </div>
                        </div>

                        <div class="space-y-2">
                            <label for="address_line_1" class="text-[10px] font-black uppercase tracking-widest text-muted-foreground/80 ml-1">Address Line 1 *</label>
                            <input type="text" name="address_line_1" id="address_line_1" x-model="form.address_line_1" required 
                                class="w-full h-12 px-4 rounded-2xl border border-border bg-background/50 focus:bg-background focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all text-sm font-medium" placeholder="Street address, building, warehouse number">
                            @error('address_line_1') <p class="text-[10px] text-destructive font-bold mt-1 ml-1">{{ $message }}</p> @enderror
                        </div>

                        <div class="space-y-2">
                            <label for="address_line_2" class="text-[10px] font-black uppercase tracking-widest text-muted-foreground/80 ml-1">Address Line 2 (Optional)</label>
                            <input type="text" name="address_line_2" id="address_line_2" x-model="form.address_line_2" 
                                class="w-full h-12 px-4 rounded-2xl border border-border bg-background/50 focus:bg-background focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all text-sm font-medium" placeholder="Landmark, nearby area">
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                            <div class="space-y-2">
                                <label for="village_name" class="text-[10px] font-black uppercase tracking-widest text-muted-foreground/80 ml-1">Village</label>
                                <input type="text" name="village_name" id="village_name" x-model="form.village_name" 
                                    class="w-full h-12 px-4 rounded-2xl border border-border bg-background/50 focus:bg-background focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all text-sm font-medium" placeholder="Village name">
                            </div>

                            <div class="space-y-2">
                                <label for="post_office" class="text-[10px] font-black uppercase tracking-widest text-muted-foreground/80 ml-1">Post Office</label>
                                <input type="text" name="post_office" id="post_office" x-model="form.post_office" 
                                    class="w-full h-12 px-4 rounded-2xl border border-border bg-background/50 focus:bg-background focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all text-sm font-medium" placeholder="Post office">
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-4 gap-8">
                            <div class="space-y-2">
                                <label for="taluka" class="text-[10px] font-black uppercase tracking-widest text-muted-foreground/80 ml-1">Taluka</label>
                                <input type="text" name="taluka" id="taluka" x-model="form.taluka" 
                                    class="w-full h-12 px-4 rounded-2xl border border-border bg-background/50 focus:bg-background focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all text-sm font-medium" placeholder="Taluka">
                            </div>

                            <div class="space-y-2">
                                <label for="city" class="text-[10px] font-black uppercase tracking-widest text-muted-foreground/80 ml-1">District/City *</label>
                                <input type="text" name="city" id="city" x-model="form.city" required 
                                    class="w-full h-12 px-4 rounded-2xl border border-border bg-background/50 focus:bg-background focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all text-sm font-medium" placeholder="District or city">
                                @error('city') <p class="text-[10px] text-destructive font-bold mt-1 ml-1">{{ $message }}</p> @enderror
                            </div>

                            <div class="space-y-2">
                                <label for="state" class="text-[10px] font-black uppercase tracking-widest text-muted-foreground/80 ml-1">State *</label>
                                <input type="text" name="state" id="state" x-model="form.state" required 
                                    class="w-full h-12 px-4 rounded-2xl border border-border bg-background/50 focus:bg-background focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all text-sm font-medium" placeholder="State">
                                @error('state') <p class="text-[10px] text-destructive font-bold mt-1 ml-1">{{ $message }}</p> @enderror
                            </div>

                            <div class="space-y-2">
                                <label for="pincode" class="text-[10px] font-black uppercase tracking-widest text-muted-foreground/80 ml-1">Pincode *</label>
                                <input type="text" name="pincode" id="pincode" x-model="form.pincode" required 
                                    class="w-full h-12 px-4 rounded-2xl border border-border bg-background/50 focus:bg-background focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all text-sm font-medium" placeholder="Pincode">
                                @error('pincode') <p class="text-[10px] text-destructive font-bold mt-1 ml-1">{{ $message }}</p> @enderror
                            </div>
                        </div>
                    </x-ui.card-content>
                </x-ui.card>

                <div class="flex justify-end pt-4">
                    <x-ui.button type="submit" class="h-14 px-12 rounded-2xl font-black uppercase tracking-[0.2em] text-xs shadow-xl shadow-primary/20 hover:scale-[1.02] active:scale-[0.98] transition-all">
                        Update Warehouse
                    </x-ui.button>
                </div>
            </form>
        </div>
    </div>
</x-layouts.app>
