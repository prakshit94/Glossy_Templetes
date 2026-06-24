@props(['globalCrops' => collect(), 'globalIrrigationTypes' => collect(), 'globalLandUnits' => collect()])

<x-ui.modal id="global-add-customer-modal" maxWidth="6xl">
    <div class="p-0 overflow-hidden" x-data="{
        selectedSources: [],
        showSourceDropdown: false,
        sources: ['Referral', 'Walk-in', 'Social Media', 'Website', 'Advertisement', 'Event', 'Cold Call', 'Other'],
        toggleSource(name) {
            if(this.selectedSources.includes(name)) {
                this.selectedSources = this.selectedSources.filter(s => s !== name);
            } else {
                this.selectedSources.push(name);
            }
        },
        selectedIrrigation: [],
        showIrrigationDropdown: false,
        types: {{ json_encode($globalIrrigationTypes->map(fn($t) => $t->name)) }},
        toggleIrrigation(name) {
            if(this.selectedIrrigation.includes(name)) {
                this.selectedIrrigation = this.selectedIrrigation.filter(t => t !== name);
            } else {
                this.selectedIrrigation.push(name);
            }
        },
        selectedCrops: [],
        showCropsDropdown: false,
        allCrops: {{ json_encode($globalCrops->map(fn($c) => $c->name)) }},
        cropSearch: '',
        get filteredCrops() {
            if (!this.cropSearch) return this.allCrops;
            return this.allCrops.filter(c => c.toLowerCase().includes(this.cropSearch.toLowerCase()));
        },
        toggleCrop(name) {
            if(this.selectedCrops.includes(name)) {
                this.selectedCrops = this.selectedCrops.filter(c => c !== name);
            } else {
                this.selectedCrops.push(name);
            }
        }
    }">
        <div class="p-4 border-b border-border/40 bg-muted/10 flex items-center justify-between">
            <div class="flex items-center gap-4">
                <div class="size-12 rounded-2xl bg-gradient-to-tr from-primary/20 to-primary/5 border border-primary/10 text-primary flex items-center justify-center shadow-inner font-black text-lg">
                    <x-ui.icon name="user-plus" size="6" />
                </div>
                <div>
                    <h3 class="text-lg font-bold tracking-tight text-foreground">Add New Customer</h3>
                    <p class="text-xs text-muted-foreground mt-0.5">Register a new customer profile</p>
                </div>
            </div>
            <button type="button" @click="$dispatch('close-modal', { name: 'global-add-customer-modal' })" class="size-10 rounded-xl hover:bg-muted flex items-center justify-center transition-colors">
                <x-ui.icon name="x" size="5" />
            </button>
        </div>

        <form action="{{ route('customers.store') }}" method="POST">
            @csrf
            <div class="p-3 max-h-[90vh] overflow-y-auto custom-scrollbar">
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-3">
                    
                    {{-- ─── LEFT COLUMN ───────────────────────────────────────── --}}
                    <div class="space-y-4">
                        
                        {{-- Basic Identity --}}
                        <div class="bg-muted/10 rounded-2xl border border-border/50 p-3 shadow-sm">
                            <div class="flex items-center gap-3 pb-2 mb-2 border-b border-border/40">
                                <div class="size-6 rounded-md bg-blue-500/10 flex items-center justify-center text-blue-500">
                                    <x-ui.icon name="user" size="3.5" />
                                </div>
                                <h4 class="text-[11px] font-black uppercase tracking-[0.15em] text-foreground">Basic Identity</h4>
                            </div>
                            <div class="grid grid-cols-1 sm:grid-cols-3 gap-2">
                                <div class="space-y-1 group">
                                    <label class="text-[9px] font-black uppercase tracking-[0.1em] text-muted-foreground">First Name *</label>
                                    <div class="relative">
                                        <x-ui.icon name="user" size="3.5" class="absolute left-3 top-1/2 -translate-y-1/2 text-muted-foreground" />
                                        <input type="text" name="firstname" required class="w-full pl-9 pr-3 py-1.5 rounded-xl bg-background/50 border border-border/60 focus:ring-2 focus:ring-primary/20 focus:border-primary outline-none transition-all text-xs font-semibold">
                                    </div>
                                </div>
                                <div class="space-y-1 group">
                                    <label class="text-[9px] font-black uppercase tracking-[0.1em] text-muted-foreground">Middle Name</label>
                                    <input type="text" name="middlename" class="w-full px-3 py-1.5 rounded-xl bg-background/50 border border-border/60 focus:ring-2 focus:ring-primary/20 focus:border-primary outline-none transition-all text-xs font-semibold">
                                </div>
                                <div class="space-y-1 group">
                                    <label class="text-[9px] font-black uppercase tracking-[0.1em] text-muted-foreground">Last Name *</label>
                                    <input type="text" name="lastname" required class="w-full px-3 py-1.5 rounded-xl bg-background/50 border border-border/60 focus:ring-2 focus:ring-primary/20 focus:border-primary outline-none transition-all text-xs font-semibold">
                                </div>
                                <div class="space-y-1 group">
                                    <label class="text-[9px] font-black uppercase tracking-[0.1em] text-muted-foreground">Status *</label>
                                    <select name="status" class="w-full px-3 py-1.5 rounded-xl bg-background/50 border border-border/60 focus:ring-2 focus:ring-primary/20 focus:border-primary outline-none transition-all text-xs font-semibold">
                                        <option value="active" selected>Active</option>
                                        <option value="inactive">Inactive</option>
                                        <option value="suspended">Suspended</option>
                                    </select>
                                </div>
                                <div class="space-y-1 group">
                                    <label class="text-[9px] font-black uppercase tracking-[0.1em] text-muted-foreground">Category</label>
                                    <select name="category" class="w-full px-3 py-1.5 rounded-xl bg-background/50 border border-border/60 focus:ring-2 focus:ring-primary/20 focus:border-primary outline-none transition-all text-xs font-semibold">
                                        <option value="">— Select —</option>
                                        <option value="individual">Individual</option>
                                        <option value="business">Business</option>
                                    </select>
                                </div>
                                <div class="space-y-1 group relative" @click.away="showSourceDropdown = false">
                                    <label class="text-[9px] font-black uppercase tracking-[0.1em] text-muted-foreground">Lead Source</label>
                                    <div @click="showSourceDropdown = !showSourceDropdown" class="w-full min-h-[1.875rem] px-2 py-1 rounded-xl bg-background/50 border border-border/60 cursor-pointer flex items-center flex-wrap gap-1">
                                        <template x-if="selectedSources.length === 0">
                                            <span class="text-xs text-muted-foreground/60 px-1">Select...</span>
                                        </template>
                                        <template x-for="source in selectedSources" :key="source">
                                            <div class="inline-flex items-center gap-1 px-1.5 py-0.5 rounded-md bg-indigo-500/10 text-indigo-500 text-[9px] font-bold">
                                                <span x-text="source"></span>
                                                <x-ui.icon name="x" size="2.5" @click.stop="toggleSource(source)" class="hover:text-destructive cursor-pointer" />
                                                <input type="hidden" name="source[]" :value="source">
                                            </div>
                                        </template>
                                    </div>
                                    <div x-show="showSourceDropdown" class="absolute z-[100] left-0 right-0 mt-1 py-1 bg-card border border-border/60 rounded-xl shadow-xl max-h-40 overflow-y-auto">
                                        <template x-for="source in sources" :key="source">
                                            <label class="flex items-center gap-2 px-3 py-1.5 hover:bg-primary/10 cursor-pointer">
                                                <input type="checkbox" :value="source" :checked="selectedSources.includes(source)" @change="toggleSource(source)" class="size-3 rounded border-border">
                                                <span class="text-xs font-medium text-muted-foreground" x-text="source"></span>
                                            </label>
                                        </template>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Contact Channels --}}
                        <div class="bg-muted/10 rounded-2xl border border-border/50 p-3 shadow-sm">
                            <div class="flex items-center gap-3 pb-2 mb-2 border-b border-border/40">
                                <div class="size-6 rounded-md bg-indigo-500/10 flex items-center justify-center text-indigo-500">
                                    <x-ui.icon name="phone" size="3.5" />
                                </div>
                                <h4 class="text-[11px] font-black uppercase tracking-[0.15em] text-foreground">Contact Channels</h4>
                            </div>
                            <div class="grid grid-cols-1 sm:grid-cols-3 gap-2">
                                <div class="space-y-1 group">
                                    <label class="text-[9px] font-black uppercase tracking-[0.1em] text-muted-foreground">Email Address</label>
                                    <div class="relative">
                                        <x-ui.icon name="mail" size="3.5" class="absolute left-3 top-1/2 -translate-y-1/2 text-muted-foreground" />
                                        <input type="email" name="email" class="w-full pl-9 pr-3 py-1.5 rounded-xl bg-background/50 border border-border/60 focus:ring-2 focus:ring-primary/20 outline-none transition-all text-xs font-semibold">
                                    </div>
                                </div>
                                <div class="space-y-1 group">
                                    <label class="text-[9px] font-black uppercase tracking-[0.1em] text-muted-foreground">Primary Phone</label>
                                    <div class="relative">
                                        <x-ui.icon name="phone" size="3.5" class="absolute left-3 top-1/2 -translate-y-1/2 text-muted-foreground" />
                                        <input type="text" name="phone" x-model="globalSearchPhone" class="w-full pl-9 pr-3 py-1.5 rounded-xl bg-background/50 border border-border/60 focus:ring-2 focus:ring-primary/20 outline-none transition-all text-xs font-semibold">
                                    </div>
                                    @error('phone') <p class="text-[9px] font-bold text-destructive uppercase tracking-widest mt-0.5">{{ $message }}</p> @enderror
                                </div>
                                <div class="space-y-1 group">
                                    <label class="text-[9px] font-black uppercase tracking-[0.1em] text-muted-foreground">Alternate Mobile</label>
                                    <input type="text" name="alternatemobile" class="w-full px-3 py-1.5 rounded-xl bg-background/50 border border-border/60 focus:ring-2 focus:ring-primary/20 outline-none transition-all text-xs font-semibold">
                                </div>
                                <div class="space-y-1 group">
                                    <label class="text-[9px] font-black uppercase tracking-[0.1em] text-muted-foreground">Landline</label>
                                    <input type="text" name="phone_number_2" class="w-full px-3 py-1.5 rounded-xl bg-background/50 border border-border/60 focus:ring-2 focus:ring-primary/20 outline-none transition-all text-xs font-semibold">
                                </div>
                                <div class="space-y-1 group">
                                    <label class="text-[9px] font-black uppercase tracking-[0.1em] text-muted-foreground">Relative Name</label>
                                    <input type="text" name="relative_mobile" class="w-full px-3 py-1.5 rounded-xl bg-background/50 border border-border/60 focus:ring-2 focus:ring-primary/20 outline-none transition-all text-xs font-semibold">
                                </div>
                                <div class="space-y-1 group">
                                    <label class="text-[9px] font-black uppercase tracking-[0.1em] text-muted-foreground">Relative Phone</label>
                                    <input type="text" name="relative_phone" class="w-full px-3 py-1.5 rounded-xl bg-background/50 border border-border/60 focus:ring-2 focus:ring-primary/20 outline-none transition-all text-xs font-semibold">
                                </div>
                            </div>
                        </div>
                        
                        {{-- Internal Notes --}}
                        <div class="bg-muted/10 rounded-2xl border border-border/50 p-3 shadow-sm">
                            <div class="flex items-center gap-3 pb-2 mb-2 border-b border-border/40">
                                <div class="size-6 rounded-md bg-slate-500/10 flex items-center justify-center text-slate-500">
                                    <x-ui.icon name="file-text" size="3.5" />
                                </div>
                                <h4 class="text-[11px] font-black uppercase tracking-[0.15em] text-foreground">Internal Notes</h4>
                            </div>
                            <textarea name="internal_notes" rows="1" placeholder="Administrative notes..." class="w-full px-4 py-2 rounded-xl bg-background/50 border border-border/60 focus:ring-2 focus:ring-primary/20 outline-none transition-all text-xs font-semibold resize-none"></textarea>
                        </div>

                    </div>

                    {{-- ─── RIGHT COLUMN ───────────────────────────────────────── --}}
                    <div class="space-y-4">

                        {{-- Business & Compliance --}}
                        <div class="bg-muted/10 rounded-2xl border border-border/50 p-3 shadow-sm">
                            <div class="flex items-center gap-3 pb-2 mb-2 border-b border-border/40">
                                <div class="size-6 rounded-md bg-emerald-500/10 flex items-center justify-center text-emerald-500">
                                    <x-ui.icon name="briefcase" size="3.5" />
                                </div>
                                <h4 class="text-[11px] font-black uppercase tracking-[0.15em] text-foreground">Business & Compliance</h4>
                            </div>
                            <div class="grid grid-cols-1 sm:grid-cols-3 gap-2">
                                <div class="space-y-1 group sm:col-span-3">
                                    <label class="text-[9px] font-black uppercase tracking-[0.1em] text-muted-foreground">Company Name</label>
                                    <div class="relative">
                                        <x-ui.icon name="building" size="3.5" class="absolute left-3 top-1/2 -translate-y-1/2 text-muted-foreground" />
                                        <input type="text" name="company_name" class="w-full pl-9 pr-3 py-1.5 rounded-xl bg-background/50 border border-border/60 focus:ring-2 focus:ring-primary/20 outline-none transition-all text-xs font-semibold">
                                    </div>
                                </div>
                                <div class="space-y-1 group">
                                    <label class="text-[9px] font-black uppercase tracking-[0.1em] text-muted-foreground">GST Number</label>
                                    <input type="text" name="gst_no" class="w-full px-3 py-1.5 rounded-xl bg-background/50 border border-border/60 focus:ring-2 focus:ring-primary/20 outline-none transition-all text-xs font-mono uppercase">
                                </div>
                                <div class="space-y-1 group">
                                    <label class="text-[9px] font-black uppercase tracking-[0.1em] text-muted-foreground">PAN Number</label>
                                    <input type="text" name="pan_no" class="w-full px-3 py-1.5 rounded-xl bg-background/50 border border-border/60 focus:ring-2 focus:ring-primary/20 outline-none transition-all text-xs font-mono uppercase">
                                </div>
                                <div class="space-y-1 group">
                                    <label class="text-[9px] font-black uppercase tracking-[0.1em] text-muted-foreground">Aadhaar (Last 4)</label>
                                    <input type="text" name="aadhaar_last4" maxlength="4" class="w-full px-3 py-1.5 rounded-xl bg-background/50 border border-border/60 focus:ring-2 focus:ring-primary/20 outline-none transition-all text-xs font-mono tracking-widest">
                                </div>
                                <div class="flex flex-row items-center justify-start gap-6 pt-4 sm:col-span-3">
                                    <label class="flex items-center gap-2 cursor-pointer group/toggle">
                                        <input type="hidden" name="kyc_completed" value="0">
                                        <input type="checkbox" name="kyc_completed" value="1" class="size-4 rounded-md border-border text-primary focus:ring-primary/20">
                                        <span class="text-[9px] font-black uppercase tracking-widest text-muted-foreground group-hover/toggle:text-foreground">KYC Verified</span>
                                    </label>
                                    <label class="flex items-center gap-2 cursor-pointer group/toggle">
                                        <input type="hidden" name="is_blacklisted" value="0">
                                        <input type="checkbox" name="is_blacklisted" value="1" class="size-4 rounded-md border-destructive text-destructive focus:ring-destructive/20">
                                        <span class="text-[9px] font-black uppercase tracking-widest text-destructive group-hover/toggle:text-destructive/80">Blacklisted</span>
                                    </label>
                                </div>
                            </div>
                        </div>

                        {{-- Agriculture Profile --}}
                        <div class="bg-muted/10 rounded-2xl border border-border/50 p-3 shadow-sm">
                            <div class="flex items-center gap-3 pb-2 mb-2 border-b border-border/40">
                                <div class="size-6 rounded-md bg-amber-500/10 flex items-center justify-center text-amber-500">
                                    <x-ui.icon name="sun" size="3.5" />
                                </div>
                                <h4 class="text-[11px] font-black uppercase tracking-[0.15em] text-foreground">Agriculture Profile</h4>
                            </div>
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                                <div class="space-y-1 group">
                                    <label class="text-[9px] font-black uppercase tracking-[0.1em] text-muted-foreground">Land Area & Unit</label>
                                    <div class="flex gap-2">
                                        <input type="number" name="land_area" step="0.01" class="w-3/5 px-3 py-1.5 rounded-xl bg-background/50 border border-border/60 focus:ring-2 focus:ring-primary/20 outline-none transition-all text-xs font-semibold">
                                        <select name="land_unit" class="w-2/5 px-2 py-1.5 rounded-xl bg-background/50 border border-border/60 focus:ring-2 focus:ring-primary/20 outline-none transition-all text-xs font-semibold">
                                            <option value="">Unit</option>
                                            @foreach($globalLandUnits as $unit)
                                                <option value="{{ $unit->name }}">{{ $unit->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>

                                <div class="space-y-1 group relative" @click.away="showIrrigationDropdown = false">
                                    <label class="text-[9px] font-black uppercase tracking-[0.1em] text-muted-foreground">Irrigation</label>
                                    <div @click="showIrrigationDropdown = !showIrrigationDropdown" class="w-full min-h-[1.875rem] px-2 py-1 rounded-xl bg-background/50 border border-border/60 cursor-pointer flex items-center flex-wrap gap-1">
                                        <template x-if="selectedIrrigation.length === 0">
                                            <span class="text-xs text-muted-foreground/60 px-1">Select...</span>
                                        </template>
                                        <template x-for="type in selectedIrrigation" :key="type">
                                            <div class="inline-flex items-center gap-1 px-1.5 py-0.5 rounded-md bg-amber-500/10 text-amber-600 text-[9px] font-bold">
                                                <span x-text="type"></span>
                                                <x-ui.icon name="x" size="2.5" @click.stop="toggleIrrigation(type)" class="hover:text-destructive cursor-pointer" />
                                                <input type="hidden" name="irrigation_type[]" :value="type">
                                            </div>
                                        </template>
                                    </div>
                                    <div x-show="showIrrigationDropdown" class="absolute z-[100] left-0 right-0 mt-1 py-1 bg-card border border-border/60 rounded-xl shadow-xl max-h-40 overflow-y-auto">
                                        <template x-for="type in types" :key="type">
                                            <label class="flex items-center gap-2 px-3 py-1.5 hover:bg-primary/10 cursor-pointer">
                                                <input type="checkbox" :value="type" :checked="selectedIrrigation.includes(type)" @change="toggleIrrigation(type)" class="size-3 rounded border-border">
                                                <span class="text-xs font-medium text-muted-foreground" x-text="type"></span>
                                            </label>
                                        </template>
                                    </div>
                                </div>

                                <div class="space-y-1 sm:col-span-2 relative" @click.away="showCropsDropdown = false">
                                    <label class="text-[9px] font-black uppercase tracking-[0.1em] text-muted-foreground">Cultivated Major Crops</label>
                                    <div class="w-full min-h-[1.875rem] px-2 py-1 rounded-xl bg-background/50 border border-border/60 flex items-center flex-wrap gap-1 cursor-text" @click="showCropsDropdown = true; $refs.cropSearch.focus()">
                                        <template x-for="crop in selectedCrops" :key="crop">
                                            <div class="inline-flex items-center gap-1 px-1.5 py-0.5 rounded-md bg-emerald-500/10 text-emerald-600 text-[9px] font-bold">
                                                <span x-text="crop"></span>
                                                <x-ui.icon name="x" size="2.5" @click.stop="toggleCrop(crop)" class="hover:text-destructive cursor-pointer" />
                                                <input type="hidden" name="crops[]" :value="crop">
                                            </div>
                                        </template>
                                        <div class="flex-1 min-w-[6rem] relative">
                                            <x-ui.icon name="search" size="3" class="absolute left-1 top-1/2 -translate-y-1/2 text-muted-foreground" />
                                            <input x-ref="cropSearch" type="text" x-model="cropSearch" @focus="showCropsDropdown = true" placeholder="Search..." class="w-full pl-5 pr-1 py-0.5 bg-transparent border-none outline-none focus:ring-0 text-xs font-semibold">
                                        </div>
                                    </div>
                                    <div x-show="showCropsDropdown && filteredCrops.length > 0" class="absolute z-[100] left-0 right-0 mt-1 py-1 bg-card border border-border/60 rounded-xl shadow-xl max-h-40 overflow-y-auto">
                                        <template x-for="crop in filteredCrops" :key="crop">
                                            <label class="flex items-center gap-2 px-3 py-1.5 hover:bg-primary/10 cursor-pointer">
                                                <input type="checkbox" :value="crop" :checked="selectedCrops.includes(crop)" @change="toggleCrop(crop)" class="size-3 rounded border-border">
                                                <span class="text-xs font-medium text-muted-foreground" x-text="crop"></span>
                                            </label>
                                        </template>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Financial Terms --}}
                        <div class="bg-muted/10 rounded-2xl border border-border/50 p-3 shadow-sm">
                            <div class="flex items-center gap-3 pb-2 mb-2 border-b border-border/40">
                                <div class="size-6 rounded-md bg-rose-500/10 flex items-center justify-center text-rose-500">
                                    <x-ui.icon name="credit-card" size="3.5" />
                                </div>
                                <h4 class="text-[11px] font-black uppercase tracking-[0.15em] text-foreground">Financial Terms</h4>
                            </div>
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                                <div class="space-y-1 group">
                                    <label class="text-[9px] font-black uppercase tracking-[0.1em] text-muted-foreground">Credit Limit (₹)</label>
                                    <input type="number" name="credit_limit" step="0.01" class="w-full px-3 py-1.5 rounded-xl bg-background/50 border border-border/60 focus:ring-2 focus:ring-primary/20 outline-none transition-all text-xs font-semibold">
                                </div>
                                <div class="space-y-1 group">
                                    <label class="text-[9px] font-black uppercase tracking-[0.1em] text-muted-foreground">Credit Days</label>
                                    <input type="number" name="credit_days" class="w-full px-3 py-1.5 rounded-xl bg-background/50 border border-border/60 focus:ring-2 focus:ring-primary/20 outline-none transition-all text-xs font-semibold">
                                </div>
                                <div class="space-y-1 group">
                                    <label class="text-[9px] font-black uppercase tracking-[0.1em] text-muted-foreground">Current Balance</label>
                                    <input type="number" name="outstanding_balance" step="0.01" class="w-full px-3 py-1.5 rounded-xl bg-background/50 border border-border/60 focus:ring-2 focus:ring-primary/20 outline-none transition-all text-xs font-semibold">
                                </div>
                                <div class="space-y-1 group">
                                    <label class="text-[9px] font-black uppercase tracking-[0.1em] text-muted-foreground">Validity Period</label>
                                    <input type="date" name="credit_valid_till" class="w-full px-3 py-1.5 rounded-xl bg-background/50 border border-border/60 focus:ring-2 focus:ring-primary/20 outline-none transition-all text-xs font-semibold">
                                </div>
                            </div>
                        </div>

                    </div>
                </div>
            </div>

            <div class="p-4 bg-muted/20 border-t border-border/40 flex items-center justify-end gap-4">
                <button type="button" @click="$dispatch('close-modal', { name: 'global-add-customer-modal' })" class="flex items-center px-6 text-xs font-bold uppercase tracking-widest text-muted-foreground hover:text-foreground transition-colors">Cancel</button>
                <x-ui.button type="submit" class="rounded-2xl px-6 py-2 shadow-xl shadow-primary/20">
                    Create Customer Profile
                </x-ui.button>
            </div>
        </form>
    </div>
</x-ui.modal>
