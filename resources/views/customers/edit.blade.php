<x-layouts.app pageTitle="Edit Customer: {{ $customer->name }}">
    <div class="p-6 lg:p-10">
        <div class="max-w-5xl mx-auto">
            <x-ui.card class="overflow-hidden border-border/60 shadow-2xl bg-card/30 backdrop-blur-2xl rounded-3xl">
                <div class="p-6 border-b border-border/40 bg-muted/10">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-4">
                            <div class="size-12 rounded-2xl bg-gradient-to-tr from-primary/20 to-primary/5 border border-primary/10 text-primary flex items-center justify-center shadow-inner font-black text-lg">
                                {{ $customer->initials() }}
                            </div>
                            <div>
                                <h3 class="text-lg font-bold tracking-tight text-foreground">{{ $customer->name }}</h3>
                                <p class="text-xs text-muted-foreground mt-0.5">Customer #{{ sprintf('%04d', $customer->id) }} · Registered {{ $customer->created_at->format('M d, Y') }}</p>
                            </div>
                        </div>
                        <a href="{{ route('customers.index') }}">
                            <x-ui.button variant="outline" size="sm" class="rounded-xl border-border text-muted-foreground hover:bg-muted">
                                <x-ui.icon name="arrow-left" size="3" class="mr-2" />
                                Back to List
                            </x-ui.button>
                        </a>
                    </div>
                </div>

                <form action="{{ route('customers.update', $customer) }}" method="POST">
                    @csrf
                    @method('PUT')
                    <x-ui.card-content class="p-8 space-y-12">

                        {{-- ─── Basic Identity ───────────────────────────────────────── --}}
                        <div class="relative">
                            <div class="flex items-center gap-3 pb-4 mb-8 border-b border-border/40">
                                <div class="size-8 rounded-lg bg-primary/10 flex items-center justify-center text-primary">
                                    <x-ui.icon name="user" size="4" />
                                </div>
                                <div>
                                    <h4 class="text-xs font-black uppercase tracking-[0.2em] text-foreground">Basic Identity</h4>
                                    <p class="text-[10px] text-muted-foreground uppercase tracking-wider font-bold mt-0.5">Personal and categorization details</p>
                                </div>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                                {{-- First Name --}}
                                <div class="space-y-2 group">
                                    <label class="text-[10px] font-black uppercase tracking-[0.15em] text-muted-foreground group-focus-within:text-primary transition-colors">First Name <span class="text-destructive">*</span></label>
                                    <div class="relative">
                                        <x-ui.icon name="user" size="4" class="absolute left-4 top-1/2 -translate-y-1/2 text-muted-foreground group-focus-within:text-primary transition-colors" />
                                        <input type="text" name="firstname" value="{{ old('firstname', $customer->firstname) }}" required placeholder="e.g. Rahul"
                                            class="w-full pl-11 pr-4 py-3 rounded-2xl bg-background/40 border border-border/60 focus:ring-4 focus:ring-primary/10 focus:border-primary outline-none transition-all shadow-sm text-sm font-medium">
                                    </div>
                                    @error('firstname') <p class="text-[10px] font-bold text-destructive uppercase tracking-widest mt-1">{{ $message }}</p> @enderror
                                </div>

                                {{-- Middle Name --}}
                                <div class="space-y-2 group">
                                    <label class="text-[10px] font-black uppercase tracking-[0.15em] text-muted-foreground group-focus-within:text-primary transition-colors">Middle Name</label>
                                    <input type="text" name="middlename" value="{{ old('middlename', $customer->middlename) }}" placeholder="e.g. Kumar"
                                        class="w-full px-5 py-3 rounded-2xl bg-background/40 border border-border/60 focus:ring-4 focus:ring-primary/10 focus:border-primary outline-none transition-all shadow-sm text-sm font-medium">
                                </div>

                                {{-- Last Name --}}
                                <div class="space-y-2 group">
                                    <label class="text-[10px] font-black uppercase tracking-[0.15em] text-muted-foreground group-focus-within:text-primary transition-colors">Last Name <span class="text-destructive">*</span></label>
                                    <input type="text" name="lastname" value="{{ old('lastname', $customer->lastname) }}" required placeholder="e.g. Sharma"
                                        class="w-full px-5 py-3 rounded-2xl bg-background/40 border border-border/60 focus:ring-4 focus:ring-primary/10 focus:border-primary outline-none transition-all shadow-sm text-sm font-medium">
                                    @error('lastname') <p class="text-[10px] font-bold text-destructive uppercase tracking-widest mt-1">{{ $message }}</p> @enderror
                                </div>

                                {{-- Status --}}
                                <div class="space-y-2 group">
                                    <label class="text-[10px] font-black uppercase tracking-[0.15em] text-muted-foreground group-focus-within:text-primary transition-colors">Account Status <span class="text-destructive">*</span></label>
                                    <div class="relative">
                                        <x-ui.icon name="activity" size="4" class="absolute left-4 top-1/2 -translate-y-1/2 text-muted-foreground z-10" />
                                        <select name="status" class="w-full pl-11 pr-10 py-3 rounded-2xl bg-background/40 border border-border/60 focus:ring-4 focus:ring-primary/10 focus:border-primary outline-none transition-all shadow-sm appearance-none text-sm font-bold text-foreground">
                                            <option value="active"    {{ old('status', $customer->status) === 'active'    ? 'selected' : '' }}>Active</option>
                                            <option value="inactive"  {{ old('status', $customer->status) === 'inactive'  ? 'selected' : '' }}>Inactive</option>
                                            <option value="suspended" {{ old('status', $customer->status) === 'suspended' ? 'selected' : '' }}>Suspended</option>
                                        </select>
                                        <x-ui.icon name="chevron-down" size="4" class="absolute right-4 top-1/2 -translate-y-1/2 text-muted-foreground pointer-events-none" />
                                    </div>
                                </div>

                                {{-- Category --}}
                                <div class="space-y-2 group">
                                    <label class="text-[10px] font-black uppercase tracking-[0.15em] text-muted-foreground group-focus-within:text-primary transition-colors">Client Category</label>
                                    <div class="relative">
                                        <select name="category" class="w-full px-5 py-3 rounded-2xl bg-background/40 border border-border/60 focus:ring-4 focus:ring-primary/10 focus:border-primary outline-none transition-all shadow-sm appearance-none text-sm font-bold">
                                            <option value="">— Select —</option>
                                            <option value="individual" {{ old('category', $customer->category) === 'individual' ? 'selected' : '' }}>Individual</option>
                                            <option value="business"   {{ old('category', $customer->category) === 'business'   ? 'selected' : '' }}>Business</option>
                                        </select>
                                        <x-ui.icon name="chevron-down" size="4" class="absolute right-4 top-1/2 -translate-y-1/2 text-muted-foreground pointer-events-none" />
                                    </div>
                                </div>

                                {{-- Lead Source Multi-Select --}}
                                <div class="space-y-2 group" x-data="{ 
                                    selectedSources: {{ json_encode(old('source', is_array($customer->source) ? $customer->source : [])) }},
                                    showSourceDropdown: false,
                                    sources: ['Referral', 'Walk-in', 'Social Media', 'Website', 'Advertisement', 'Event', 'Cold Call', 'Other'],
                                    toggleSource(name) {
                                        if(this.selectedSources.includes(name)) {
                                            this.selectedSources = this.selectedSources.filter(s => s !== name);
                                        } else {
                                            this.selectedSources.push(name);
                                        }
                                    }
                                }" @click.away="showSourceDropdown = false">
                                    <label class="text-[10px] font-black uppercase tracking-[0.15em] text-muted-foreground group-focus-within:text-primary transition-colors">Lead Source</label>
                                    <div class="relative">
                                        {{-- Multi-Select Trigger with Badges --}}
                                        <div @click="showSourceDropdown = !showSourceDropdown" 
                                            class="w-full min-h-[3.5rem] p-2.5 rounded-2xl bg-background/40 border border-border/60 focus-within:ring-4 focus-within:ring-primary/10 focus-within:border-primary outline-none transition-all shadow-sm flex flex-wrap gap-2 items-center cursor-pointer">
                                            <template x-if="selectedSources.length === 0">
                                                <span class="text-sm text-muted-foreground/60 px-2">Select sources...</span>
                                            </template>
                                            <template x-for="source in selectedSources" :key="source">
                                                <div class="inline-flex items-center gap-2 px-3 py-1.5 rounded-xl bg-indigo-500/10 border border-indigo-500/20 text-indigo-500 text-[10px] font-black uppercase tracking-wider animate-in zoom-in-95">
                                                    <span x-text="source"></span>
                                                    <x-ui.icon name="x" size="3" @click.stop="toggleSource(source)" class="hover:text-destructive transition-colors" />
                                                    <input type="hidden" name="source[]" :value="source">
                                                </div>
                                            </template>
                                            <div class="ml-auto pr-2">
                                                <x-ui.icon name="chevron-down" size="4" class="text-muted-foreground" />
                                            </div>
                                        </div>
                                        
                                        {{-- Dropdown --}}
                                        <div x-show="showSourceDropdown" 
                                            class="absolute z-50 left-0 right-0 mt-2 py-3 bg-card border border-border/60 rounded-2xl shadow-2xl max-h-60 overflow-y-auto backdrop-blur-xl"
                                            x-transition:enter="transition ease-out duration-200"
                                            x-transition:enter-start="opacity-0 translate-y-2"
                                            x-transition:enter-end="opacity-100 translate-y-0">
                                            <template x-for="source in sources" :key="source">
                                                <label class="flex items-center gap-3 px-5 py-2.5 hover:bg-primary/10 cursor-pointer transition-colors group/item">
                                                    <input type="checkbox" :value="source" 
                                                        :checked="selectedSources.includes(source)"
                                                        @change="toggleSource(source)"
                                                        class="size-4 rounded border-border text-primary focus:ring-primary/20 transition-all">
                                                    <span class="text-sm font-medium text-muted-foreground group-hover/item:text-foreground transition-colors" x-text="source"></span>
                                                </label>
                                            </template>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- ─── Contact Details ──────────────────────────────────────── --}}
                        <div>
                            <div class="flex items-center gap-3 pb-4 mb-8 border-b border-border/40">
                                <div class="size-8 rounded-lg bg-indigo-500/10 flex items-center justify-center text-indigo-500">
                                    <x-ui.icon name="phone" size="4" />
                                </div>
                                <div>
                                    <h4 class="text-xs font-black uppercase tracking-[0.2em] text-foreground">Contact Channels</h4>
                                    <p class="text-[10px] text-muted-foreground uppercase tracking-wider font-bold mt-0.5">Primary and emergency contact info</p>
                                </div>
                            </div>
                            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                                <div class="space-y-2 group">
                                    <label class="text-[10px] font-black uppercase tracking-[0.15em] text-muted-foreground group-focus-within:text-primary transition-colors">Email Address</label>
                                    <div class="relative">
                                        <x-ui.icon name="mail" size="4" class="absolute left-4 top-1/2 -translate-y-1/2 text-muted-foreground" />
                                        <input type="email" name="email" value="{{ old('email', $customer->email) }}" placeholder="customer@example.com"
                                            class="w-full pl-11 pr-4 py-3 rounded-2xl bg-background/40 border border-border/60 focus:ring-4 focus:ring-primary/10 focus:border-primary outline-none transition-all shadow-sm text-sm font-medium">
                                    </div>
                                </div>
                                <div class="space-y-2 group">
                                    <label class="text-[10px] font-black uppercase tracking-[0.15em] text-muted-foreground group-focus-within:text-primary transition-colors">Primary Phone</label>
                                    <div class="relative">
                                        <x-ui.icon name="phone" size="4" class="absolute left-4 top-1/2 -translate-y-1/2 text-muted-foreground" />
                                        <input type="text" name="phone" value="{{ old('phone', $customer->phone) }}" placeholder="+91 98765 43210"
                                            class="w-full pl-11 pr-4 py-3 rounded-2xl bg-background/40 border border-border/60 focus:ring-4 focus:ring-primary/10 focus:border-primary outline-none transition-all shadow-sm text-sm font-medium">
                                    </div>
                                    @error('phone') <p class="text-[10px] font-bold text-destructive uppercase tracking-widest mt-1">{{ $message }}</p> @enderror
                                </div>
                                <div class="space-y-2 group">
                                    <label class="text-[10px] font-black uppercase tracking-[0.15em] text-muted-foreground group-focus-within:text-primary transition-colors">Alternate Mobile</label>
                                    <input type="text" name="alternatemobile" value="{{ old('alternatemobile', $customer->alternatemobile) }}" placeholder="Secondary mobile"
                                        class="w-full px-5 py-3 rounded-2xl bg-background/40 border border-border/60 focus:ring-4 focus:ring-primary/10 focus:border-primary outline-none transition-all shadow-sm text-sm font-medium">
                                </div>
                                <div class="space-y-2 group">
                                    <label class="text-[10px] font-black uppercase tracking-[0.15em] text-muted-foreground group-focus-within:text-primary transition-colors">Secondary Landline</label>
                                    <input type="text" name="phone_number_2" value="{{ old('phone_number_2', $customer->phone_number_2) }}" placeholder="Office/Home phone"
                                        class="w-full px-5 py-3 rounded-2xl bg-background/40 border border-border/60 focus:ring-4 focus:ring-primary/10 focus:border-primary outline-none transition-all shadow-sm text-sm font-medium">
                                </div>
                                <div class="space-y-2 group">
                                    <label class="text-[10px] font-black uppercase tracking-[0.15em] text-muted-foreground group-focus-within:text-primary transition-colors">Relative Contact Name</label>
                                    <input type="text" name="relative_mobile" value="{{ old('relative_mobile', $customer->relative_mobile) }}" placeholder="Emergency contact name"
                                        class="w-full px-5 py-3 rounded-2xl bg-background/40 border border-border/60 focus:ring-4 focus:ring-primary/10 focus:border-primary outline-none transition-all shadow-sm text-sm font-medium">
                                </div>
                                <div class="space-y-2 group">
                                    <label class="text-[10px] font-black uppercase tracking-[0.15em] text-muted-foreground group-focus-within:text-primary transition-colors">Relative Contact Phone</label>
                                    <input type="text" name="relative_phone" value="{{ old('relative_phone', $customer->relative_phone) }}" placeholder="Emergency contact phone"
                                        class="w-full px-5 py-3 rounded-2xl bg-background/40 border border-border/60 focus:ring-4 focus:ring-primary/10 focus:border-primary outline-none transition-all shadow-sm text-sm font-medium">
                                </div>
                            </div>
                        </div>

                        {{-- ─── Business & Compliance ────────────────────────────────── --}}
                        <div>
                            <div class="flex items-center gap-3 pb-4 mb-8 border-b border-border/40">
                                <div class="size-8 rounded-lg bg-emerald-500/10 flex items-center justify-center text-emerald-500">
                                    <x-ui.icon name="briefcase" size="4" />
                                </div>
                                <div>
                                    <h4 class="text-xs font-black uppercase tracking-[0.2em] text-foreground">Business & Compliance</h4>
                                    <p class="text-[10px] text-muted-foreground uppercase tracking-wider font-bold mt-0.5">Corporate identity and tax details</p>
                                </div>
                            </div>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                                <div class="space-y-2 group md:col-span-2">
                                    <label class="text-[10px] font-black uppercase tracking-[0.15em] text-muted-foreground group-focus-within:text-primary transition-colors">Registered Company Name</label>
                                    <div class="relative">
                                        <x-ui.icon name="building" size="4" class="absolute left-4 top-1/2 -translate-y-1/2 text-muted-foreground" />
                                        <input type="text" name="company_name" value="{{ old('company_name', $customer->company_name) }}" placeholder="Full legal company name"
                                            class="w-full pl-11 pr-4 py-3 rounded-2xl bg-background/40 border border-border/60 focus:ring-4 focus:ring-primary/10 focus:border-primary outline-none transition-all shadow-sm text-sm font-medium">
                                    </div>
                                </div>
                                <div class="space-y-2 group">
                                    <label class="text-[10px] font-black uppercase tracking-[0.15em] text-muted-foreground group-focus-within:text-primary transition-colors">GST Number</label>
                                    <input type="text" name="gst_no" value="{{ old('gst_no', $customer->gst_no) }}" placeholder="22AAAAA0000A1Z5"
                                        class="w-full px-5 py-3 rounded-2xl bg-background/40 border border-border/60 focus:ring-4 focus:ring-primary/10 focus:border-primary outline-none transition-all shadow-sm text-sm font-mono uppercase tracking-widest">
                                </div>
                                <div class="space-y-2 group">
                                    <label class="text-[10px] font-black uppercase tracking-[0.15em] text-muted-foreground group-focus-within:text-primary transition-colors">PAN Number</label>
                                    <input type="text" name="pan_no" value="{{ old('pan_no', $customer->pan_no) }}" placeholder="AAAAA0000A"
                                        class="w-full px-5 py-3 rounded-2xl bg-background/40 border border-border/60 focus:ring-4 focus:ring-primary/10 focus:border-primary outline-none transition-all shadow-sm text-sm font-mono uppercase tracking-widest">
                                </div>
                                <div class="space-y-2 group">
                                    <label class="text-[10px] font-black uppercase tracking-[0.15em] text-muted-foreground group-focus-within:text-primary transition-colors">Aadhaar (Last 4)</label>
                                    <input type="text" name="aadhaar_last4" value="{{ old('aadhaar_last4', $customer->aadhaar_last4) }}" maxlength="4" placeholder="XXXX"
                                        class="w-full px-5 py-3 rounded-2xl bg-background/40 border border-border/60 focus:ring-4 focus:ring-primary/10 focus:border-primary outline-none transition-all shadow-sm text-sm font-mono tracking-[0.3em]">
                                </div>
                                <div class="flex items-center gap-6 pt-4">
                                    <label class="flex items-center gap-3 cursor-pointer group/toggle">
                                        <input type="hidden" name="kyc_completed" value="0">
                                        <input type="checkbox" name="kyc_completed" value="1" {{ old('kyc_completed', $customer->kyc_completed) ? 'checked' : '' }}
                                            class="size-5 rounded-lg border-border text-primary focus:ring-primary/20 transition-all">
                                        <span class="text-[10px] font-black uppercase tracking-widest text-muted-foreground group-hover/toggle:text-foreground transition-colors">KYC Verified</span>
                                    </label>
                                    <label class="flex items-center gap-3 cursor-pointer group/toggle">
                                        <input type="hidden" name="is_blacklisted" value="0">
                                        <input type="checkbox" name="is_blacklisted" value="1" {{ old('is_blacklisted', $customer->is_blacklisted) ? 'checked' : '' }}
                                            class="size-5 rounded-lg border-destructive text-destructive focus:ring-destructive/20 transition-all">
                                        <span class="text-[10px] font-black uppercase tracking-widest text-destructive group-hover/toggle:text-destructive/80 transition-colors">Blacklisted</span>
                                    </label>
                                </div>
                            </div>
                        </div>

                        {{-- ─── Agriculture Profile ──────────────────────────────────── --}}
                        <div>
                            <div class="flex items-center gap-3 pb-4 mb-8 border-b border-border/40">
                                <div class="size-8 rounded-lg bg-amber-500/10 flex items-center justify-center text-amber-500">
                                    <x-ui.icon name="sun" size="4" />
                                </div>
                                <div>
                                    <h4 class="text-xs font-black uppercase tracking-[0.2em] text-foreground">Agriculture Profile</h4>
                                    <p class="text-[10px] text-muted-foreground uppercase tracking-wider font-bold mt-0.5">Land and cultivation details</p>
                                </div>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                                <div class="space-y-2 group">
                                    <label class="text-[10px] font-black uppercase tracking-[0.15em] text-muted-foreground group-focus-within:text-primary transition-colors">Land Area & Unit</label>
                                    <div class="flex gap-3">
                                        <input type="number" name="land_area" value="{{ old('land_area', $customer->land_area) }}" min="0" step="0.01" placeholder="0.00"
                                            class="w-2/3 px-5 py-3 rounded-2xl bg-background/40 border border-border/60 focus:ring-4 focus:ring-primary/10 focus:border-primary outline-none transition-all shadow-sm text-sm font-medium">
                                        <select name="land_unit" class="w-1/3 px-4 py-3 rounded-2xl bg-background/40 border border-border/60 focus:ring-4 focus:ring-primary/10 focus:border-primary outline-none transition-all shadow-sm text-xs font-bold text-foreground">
                                            <option value="">Unit</option>
                                            @foreach($landUnits as $unit)
                                                <option value="{{ $unit->name }}" {{ old('land_unit', $customer->land_unit) === $unit->name ? 'selected' : '' }}>{{ $unit->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>

                                {{-- Irrigation Type Multi-Select --}}
                                <div class="space-y-2 group" x-data="{ 
                                    selectedIrrigation: {{ json_encode(old('irrigation_type', is_array($customer->irrigation_type) ? $customer->irrigation_type : [])) }},
                                    showIrrigationDropdown: false,
                                    types: {{ json_encode($irrigationTypes->map(fn($t) => $t->name)) }},
                                    toggleIrrigation(name) {
                                        if(this.selectedIrrigation.includes(name)) {
                                            this.selectedIrrigation = this.selectedIrrigation.filter(t => t !== name);
                                        } else {
                                            this.selectedIrrigation.push(name);
                                        }
                                    }
                                }" @click.away="showIrrigationDropdown = false">
                                    <label class="text-[10px] font-black uppercase tracking-[0.15em] text-muted-foreground group-focus-within:text-primary transition-colors">Irrigation Type</label>
                                    <div class="relative">
                                        {{-- Multi-Select Trigger with Badges --}}
                                        <div @click="showIrrigationDropdown = !showIrrigationDropdown" 
                                            class="w-full min-h-[3.5rem] p-2.5 rounded-2xl bg-background/40 border border-border/60 focus-within:ring-4 focus-within:ring-primary/10 focus-within:border-primary outline-none transition-all shadow-sm flex flex-wrap gap-2 items-center cursor-pointer">
                                            <template x-if="selectedIrrigation.length === 0">
                                                <span class="text-sm text-muted-foreground/60 px-2 font-medium">Select types...</span>
                                            </template>
                                            <template x-for="type in selectedIrrigation" :key="type">
                                                <div class="inline-flex items-center gap-2 px-3 py-1.5 rounded-xl bg-amber-500/10 border border-amber-500/20 text-amber-600 text-[10px] font-black uppercase tracking-wider animate-in zoom-in-95">
                                                    <span x-text="type"></span>
                                                    <x-ui.icon name="x" size="3" @click.stop="toggleIrrigation(type)" class="hover:text-destructive transition-colors" />
                                                    <input type="hidden" name="irrigation_type[]" :value="type">
                                                </div>
                                            </template>
                                            <div class="ml-auto pr-2">
                                                <x-ui.icon name="chevron-down" size="4" class="text-muted-foreground" />
                                            </div>
                                        </div>
                                        
                                        <div x-show="showIrrigationDropdown" 
                                            class="absolute z-50 left-0 right-0 mt-2 py-3 bg-card border border-border/60 rounded-2xl shadow-2xl max-h-60 overflow-y-auto backdrop-blur-xl"
                                            x-transition:enter="transition ease-out duration-200"
                                            x-transition:enter-start="opacity-0 translate-y-2"
                                            x-transition:enter-end="opacity-100 translate-y-0">
                                            <template x-for="type in types" :key="type">
                                                <label class="flex items-center gap-3 px-5 py-2.5 hover:bg-primary/10 cursor-pointer transition-colors group/item">
                                                    <input type="checkbox" :value="type" 
                                                        :checked="selectedIrrigation.includes(type)"
                                                        @change="toggleIrrigation(type)"
                                                        class="size-4 rounded border-border text-primary focus:ring-primary/20 transition-all">
                                                    <span class="text-sm font-medium text-muted-foreground group-hover/item:text-foreground transition-colors" x-text="type"></span>
                                                </label>
                                            </template>
                                        </div>
                                    </div>
                                </div>

                                {{-- Cultivated Major Crops Multi-Select --}}
                                <div class="space-y-3 md:col-span-2" x-data="{ 
                                    selectedCrops: {{ json_encode(old('crops', is_array($customer->crops) ? $customer->crops : [])) }},
                                    showCropsDropdown: false,
                                    allCrops: {{ json_encode($crops->map(fn($c) => $c->name)) }},
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
                                }" @click.away="showCropsDropdown = false">
                                    <label class="text-[10px] font-black uppercase tracking-[0.15em] text-muted-foreground">Cultivated Major Crops</label>
                                    
                                    <div class="relative">
                                        {{-- Trigger Container with Badges + Search Input --}}
                                        <div class="w-full min-h-[4rem] p-2.5 rounded-2xl bg-background/40 border border-border/60 focus-within:ring-4 focus-within:ring-primary/10 focus-within:border-primary outline-none transition-all shadow-sm flex flex-wrap gap-2 items-center cursor-pointer"
                                            @click="showCropsDropdown = true">
                                            
                                            <template x-for="crop in selectedCrops" :key="crop">
                                                <div class="inline-flex items-center gap-2 px-3 py-2 rounded-xl bg-emerald-500/10 border border-emerald-500/20 text-emerald-600 text-[10px] font-black uppercase tracking-wider animate-in zoom-in-95">
                                                    <span x-text="crop"></span>
                                                    <x-ui.icon name="x" size="3" @click.stop="toggleCrop(crop)" class="hover:text-destructive transition-colors" />
                                                    <input type="hidden" name="crops[]" :value="crop">
                                                </div>
                                            </template>

                                            <div class="flex-1 min-w-[12rem] relative group/input">
                                                <x-ui.icon name="search" size="3" class="absolute left-2 top-1/2 -translate-y-1/2 text-muted-foreground group-focus-within/input:text-primary transition-colors" />
                                                <input type="text" 
                                                    x-model="cropSearch"
                                                    @focus="showCropsDropdown = true"
                                                    placeholder="Search crops..."
                                                    class="w-full pl-8 pr-2 py-1 bg-transparent border-none outline-none focus:ring-0 text-sm font-medium placeholder:text-muted-foreground/60">
                                            </div>

                                            <div class="ml-auto pr-2">
                                                <x-ui.icon name="chevron-down" size="4" class="text-muted-foreground" />
                                            </div>
                                        </div>

                                        {{-- Dropdown --}}
                                        <div x-show="showCropsDropdown && filteredCrops.length > 0" 
                                            class="absolute z-50 left-0 right-0 mt-2 py-2 bg-card border border-border/60 rounded-2xl shadow-2xl max-h-60 overflow-y-auto backdrop-blur-xl"
                                            x-transition:enter="transition ease-out duration-200"
                                            x-transition:enter-start="opacity-0 translate-y-2"
                                            x-transition:enter-end="opacity-100 translate-y-0">
                                            <template x-for="crop in filteredCrops" :key="crop">
                                                <label class="flex items-center gap-3 px-5 py-2.5 hover:bg-primary/10 cursor-pointer transition-colors group/item">
                                                    <input type="checkbox" :value="crop" 
                                                        :checked="selectedCrops.includes(crop)"
                                                        @change="toggleCrop(crop)"
                                                        class="size-4 rounded border-border text-primary focus:ring-primary/20 transition-all">
                                                    <span class="text-sm font-medium text-muted-foreground group-hover/item:text-foreground transition-colors" x-text="crop"></span>
                                                </label>
                                            </template>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- ─── Financial Terms ──────────────────────────────────────── --}}
                        <div>
                            <div class="flex items-center gap-3 pb-4 mb-8 border-b border-border/40">
                                <div class="size-8 rounded-lg bg-emerald-500/10 flex items-center justify-center text-emerald-500">
                                    <x-ui.icon name="credit-card" size="4" />
                                </div>
                                <div>
                                    <h4 class="text-xs font-black uppercase tracking-[0.2em] text-foreground">Financial Terms</h4>
                                    <p class="text-[10px] text-muted-foreground uppercase tracking-wider font-bold mt-0.5">Credit limits and payment cycles</p>
                                </div>
                            </div>
                            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-8">
                                <div class="space-y-2 group">
                                    <label class="text-[10px] font-black uppercase tracking-[0.15em] text-muted-foreground group-focus-within:text-primary transition-colors">Credit Limit (₹)</label>
                                    <input type="number" name="credit_limit" value="{{ old('credit_limit', $customer->credit_limit) }}" min="0" step="0.01"
                                        class="w-full px-5 py-3 rounded-2xl bg-background/40 border border-border/60 focus:ring-4 focus:ring-primary/10 focus:border-primary outline-none transition-all shadow-sm text-sm font-medium">
                                </div>
                                <div class="space-y-2 group">
                                    <label class="text-[10px] font-black uppercase tracking-[0.15em] text-muted-foreground group-focus-within:text-primary transition-colors">Credit Days</label>
                                    <input type="number" name="credit_days" value="{{ old('credit_days', $customer->credit_days) }}" min="0"
                                        class="w-full px-5 py-3 rounded-2xl bg-background/40 border border-border/60 focus:ring-4 focus:ring-primary/10 focus:border-primary outline-none transition-all shadow-sm text-sm font-medium">
                                </div>
                                <div class="space-y-2 group">
                                    <label class="text-[10px] font-black uppercase tracking-[0.15em] text-muted-foreground group-focus-within:text-primary transition-colors">Current Balance (₹)</label>
                                    <input type="number" name="outstanding_balance" value="{{ old('outstanding_balance', $customer->outstanding_balance) }}" step="0.01"
                                        class="w-full px-5 py-3 rounded-2xl bg-background/40 border border-border/60 focus:ring-4 focus:ring-primary/10 focus:border-primary outline-none transition-all shadow-sm text-sm font-medium">
                                </div>
                                <div class="space-y-2 group">
                                    <label class="text-[10px] font-black uppercase tracking-[0.15em] text-muted-foreground group-focus-within:text-primary transition-colors">Validity Period</label>
                                    <input type="date" name="credit_valid_till" value="{{ old('credit_valid_till', $customer->credit_valid_till?->format('Y-m-d')) }}"
                                        class="w-full px-5 py-3 rounded-2xl bg-background/40 border border-border/60 focus:ring-4 focus:ring-primary/10 focus:border-primary outline-none transition-all shadow-sm text-sm font-medium">
                                </div>
                            </div>
                        </div>

                        {{-- ─── Internal Notes ───────────────────────────────────────── --}}
                        <div class="pt-4">
                            <div class="flex items-center gap-3 pb-4 mb-6 border-b border-border/40">
                                <div class="size-8 rounded-lg bg-slate-500/10 flex items-center justify-center text-slate-500">
                                    <x-ui.icon name="file-text" size="4" />
                                </div>
                                <h4 class="text-xs font-black uppercase tracking-[0.2em] text-foreground">Internal Documentation</h4>
                            </div>
                            <textarea name="internal_notes" rows="4" placeholder="Add private administrative notes..."
                                class="w-full px-6 py-4 rounded-3xl bg-background/40 border border-border/60 focus:ring-4 focus:ring-primary/10 focus:border-primary outline-none transition-all shadow-sm text-sm font-medium resize-none">{{ old('internal_notes', $customer->internal_notes) }}</textarea>
                        </div>
                    </x-ui.card-content>

                    <div class="p-8 bg-muted/20 border-t border-border/40 flex items-center justify-between">
                        <button type="button" onclick="if(confirm('Archive this customer?')) document.getElementById('delete-customer-form').submit();" 
                                class="text-xs font-bold uppercase tracking-widest text-destructive hover:text-destructive/80 transition-colors flex items-center gap-2">
                            <x-ui.icon name="trash" size="3" />
                            Archive Profile
                        </button>
                        <div class="flex gap-4">
                            <a href="{{ route('customers.index') }}" class="flex items-center px-6 text-xs font-bold uppercase tracking-widest text-muted-foreground hover:text-foreground transition-colors">Cancel</a>
                            <x-ui.button type="submit" class="rounded-2xl px-10 py-6 shadow-xl shadow-primary/20">
                                Update Customer Profile
                            </x-ui.button>
                        </div>
                    </div>
                </form>

                <form id="delete-customer-form" action="{{ route('customers.destroy', $customer) }}" method="POST" class="hidden">
                    @csrf
                    @method('DELETE')
                </form>
            </x-ui.card>
        </div>
    </div>
</x-layouts.app>
