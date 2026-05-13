<x-layouts.app pageTitle="Customer Profile: {{ $customer->name }}">

    <div class="p-6 lg:p-10" x-data="{ 
        editingAddress: null,
        deletingAddress: null,
        villageSearch: '',
        villages: [],
        searchingVillages: false,
        openAddModal() {
            this.editingAddress = null;
            this.resetVillageSearch();
            $dispatch('open-modal', { name: 'address-modal' });
        },
        openEditModal(address) {
            this.editingAddress = address;
            this.resetVillageSearch();
            if (address && address.village) {
                this.editingAddress.village_name = address.village.village_name;
                this.editingAddress.post_office = address.village.post_so_name;
                this.editingAddress.taluka = address.village.taluka_name;
            } else {
                if (this.editingAddress) {
                    this.editingAddress.village_name = '';
                    this.editingAddress.post_office = '';
                    this.editingAddress.taluka = '';
                }
            }
            $dispatch('open-modal', { name: 'address-modal' });
        },
        openDeleteModal(address) {
            this.deletingAddress = address;
            $dispatch('open-modal', { name: 'delete-address-modal' });
        },
        resetVillageSearch() {
            this.villageSearch = '';
            this.villages = [];
        },
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
                if (!res.ok) throw new Error('Network response was not ok');
                const data = await res.json();
                this.villages = data.data || [];
            } catch (e) {
                console.error('Search failed:', e);
            } finally {
                this.searchingVillages = false;
            }
        },
        selectVillage(village) {
            this.villageSearch = village.name;
            this.villages = [];
            
            const villageIdInput = document.getElementById('village_id');
            if (villageIdInput) villageIdInput.value = village.id;
            
            const villageNameInput = document.getElementById('village_name');
            if (villageNameInput) villageNameInput.value = village.name || '';

            const cityInput = document.getElementById('city');
            if (cityInput) cityInput.value = village.district || '';
            
            const stateInput = document.getElementById('state');
            if (stateInput) stateInput.value = village.state || '';
            
            const pincodeInput = document.getElementById('pincode');
            if (pincodeInput) pincodeInput.value = village.pincode || '';
            
            const talukaInput = document.getElementById('taluka');
            if (talukaInput) talukaInput.value = village.taluka || '';

            const postOfficeInput = document.getElementById('post_office');
            if (postOfficeInput) postOfficeInput.value = village.post_office || '';
            
            if (this.editingAddress) {
                this.editingAddress.village_id = village.id;
                this.editingAddress.village_name = village.name;
                this.editingAddress.city = village.district;
                this.editingAddress.state = village.state;
                this.editingAddress.pincode = village.pincode;
                this.editingAddress.taluka = village.taluka;
                this.editingAddress.post_office = village.post_office;
            }
        }
    }">
        <div class="max-w-6xl mx-auto">
            
            {{-- Header Actions --}}
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
                <div>
                    <h2 class="text-2xl font-black tracking-tight text-foreground">Customer Profile</h2>
                    <p class="text-sm text-muted-foreground mt-1">Detailed view of customer record and associated information.</p>
                </div>
                <div class="flex items-center gap-3">
                    <a href="{{ route('customers.index') }}">
                        <x-ui.button variant="outline" size="sm" class="rounded-xl border-border text-muted-foreground hover:bg-muted">
                            <x-ui.icon name="arrow-left" size="4" class="mr-2" />
                            Back to Customers
                        </x-ui.button>
                    </a>
                    <a href="{{ route('customers.edit', $customer) }}">
                        <x-ui.button size="sm" class="rounded-xl shadow-lg shadow-primary/20">
                            <x-ui.icon name="edit-3" size="4" class="mr-2" />
                            Edit Profile
                        </x-ui.button>
                    </a>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                
                {{-- Left Sidebar: Customer Summary Card --}}
                <div class="lg:col-span-1 space-y-6">
                    <x-ui.card class="overflow-hidden border-border/60 shadow-2xl bg-card/30 backdrop-blur-2xl rounded-3xl relative">
                        {{-- Background Accent --}}
                        <div class="absolute inset-0 bg-gradient-to-br from-primary/5 via-transparent to-transparent pointer-events-none"></div>
                        
                        <div class="p-8 flex flex-col items-center text-center border-b border-border/40 relative">
                            <div class="size-24 rounded-3xl bg-gradient-to-tr from-primary/20 to-primary/5 border border-primary/10 text-primary flex items-center justify-center shadow-inner font-black text-3xl mb-4">
                                {{ $customer->initials() }}
                            </div>
                            <h3 class="text-xl font-black tracking-tight text-foreground">{{ $customer->name }}</h3>
                            <p class="text-sm font-mono text-muted-foreground mt-1">#{{ sprintf('%04d', $customer->id) }}</p>
                            
                            @php
                                $statusClass = match($customer->status) {
                                    'active'    => 'bg-emerald-500/10 text-emerald-500 border-emerald-500/20',
                                    'suspended' => 'bg-red-500/10 text-red-500 border-red-500/20',
                                    default     => 'bg-orange-500/10 text-orange-500 border-orange-500/20',
                                };
                            @endphp
                            <div class="mt-4">
                                <span class="text-[10px] font-black uppercase tracking-widest px-3 py-1.5 rounded-xl border {{ $statusClass }}">
                                    {{ $customer->status }}
                                </span>
                            </div>
                        </div>

                        <div class="p-6 bg-muted/5 space-y-4">
                            <div class="flex items-center gap-3">
                                <div class="size-8 rounded-xl bg-primary/10 text-primary flex items-center justify-center shrink-0">
                                    <x-ui.icon name="mail" size="4" />
                                </div>
                                <div class="min-w-0 flex-1">
                                    <p class="text-[10px] font-black uppercase tracking-widest text-muted-foreground">Email</p>
                                    <p class="text-sm font-medium text-foreground truncate">{{ $customer->email ?: 'Not Provided' }}</p>
                                </div>
                            </div>
                            <div class="flex items-center gap-3">
                                <div class="size-8 rounded-xl bg-primary/10 text-primary flex items-center justify-center shrink-0">
                                    <x-ui.icon name="phone" size="4" />
                                </div>
                                <div class="min-w-0 flex-1">
                                    <p class="text-[10px] font-black uppercase tracking-widest text-muted-foreground">Phone</p>
                                    <p class="text-sm font-medium text-foreground">{{ $customer->phone ?: 'Not Provided' }}</p>
                                </div>
                            </div>
                            <div class="flex items-center gap-3">
                                <div class="size-8 rounded-xl bg-primary/10 text-primary flex items-center justify-center shrink-0">
                                    <x-ui.icon name="calendar" size="4" />
                                </div>
                                <div class="min-w-0 flex-1">
                                    <p class="text-[10px] font-black uppercase tracking-widest text-muted-foreground">Registered</p>
                                    <p class="text-sm font-medium text-foreground">{{ $customer->created_at->format('M d, Y') }}</p>
                                </div>
                            </div>
                        </div>
                    </x-ui.card>

                    {{-- Customer Analytics/Mini Stats --}}
                    <div class="grid grid-cols-2 gap-4">
                        <x-ui.card class="p-5 border-border/60 bg-card/30 backdrop-blur-xl rounded-3xl shadow-sm text-center">
                            <x-ui.icon name="shopping-cart" size="5" class="text-primary mx-auto mb-2 opacity-80" />
                            <p class="text-[10px] font-black uppercase tracking-widest text-muted-foreground mb-1">Total Orders</p>
                            <p class="text-xl font-black text-foreground">{{ $customer->orders()->count() ?? 0 }}</p>
                        </x-ui.card>
                        <x-ui.card class="p-5 border-border/60 bg-card/30 backdrop-blur-xl rounded-3xl shadow-sm text-center">
                            <x-ui.icon name="map-pin" size="5" class="text-primary mx-auto mb-2 opacity-80" />
                            <p class="text-[10px] font-black uppercase tracking-widest text-muted-foreground mb-1">Addresses</p>
                            <p class="text-xl font-black text-foreground">{{ $customer->addresses->count() }}</p>
                        </x-ui.card>
                    </div>
                </div>

                {{-- Right Main Area: Details Tabs/Sections --}}
                <div class="lg:col-span-2 space-y-8">
                    
                    <x-ui.card class="overflow-hidden border-border/60 shadow-xl bg-card/30 backdrop-blur-2xl rounded-3xl">
                        <div class="p-6 border-b border-border/40 bg-muted/10">
                            <div class="flex items-center gap-3">
                                <div class="size-10 rounded-xl bg-blue-500/10 text-blue-500 flex items-center justify-center">
                                    <x-ui.icon name="hash" size="5" />
                                </div>
                                <h3 class="text-lg font-bold tracking-tight text-foreground">Tax & Financial Details</h3>
                            </div>
                        </div>
                        <div class="p-8 grid grid-cols-1 md:grid-cols-2 gap-8">
                            {{-- Tax Info --}}
                            <div class="space-y-6">
                                <div>
                                    <h4 class="text-[10px] font-black uppercase tracking-[0.2em] text-muted-foreground mb-4 border-b border-border/40 pb-2">Tax Compliance</h4>
                                    <div class="space-y-4">
                                        <div class="group">
                                            <p class="text-[10px] font-black uppercase tracking-widest text-muted-foreground group-hover:text-primary transition-colors">GST Number</p>
                                            <div class="flex items-center gap-2 mt-1">
                                                <p class="text-sm font-mono font-bold text-foreground bg-muted/30 px-3 py-1.5 rounded-lg border border-border/40 w-full">{{ $customer->gst_no ?: '—' }}</p>
                                            </div>
                                        </div>
                                        <div class="group">
                                            <p class="text-[10px] font-black uppercase tracking-widest text-muted-foreground group-hover:text-primary transition-colors">PAN Number</p>
                                            <div class="flex items-center gap-2 mt-1">
                                                <p class="text-sm font-mono font-bold text-foreground bg-muted/30 px-3 py-1.5 rounded-lg border border-border/40 w-full">{{ $customer->pan_no ?: '—' }}</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            {{-- Credit Terms --}}
                            <div class="space-y-6">
                                <div>
                                    <h4 class="text-[10px] font-black uppercase tracking-[0.2em] text-muted-foreground mb-4 border-b border-border/40 pb-2">Credit Policy</h4>
                                    <div class="space-y-4">
                                        <div class="group">
                                            <p class="text-[10px] font-black uppercase tracking-widest text-muted-foreground group-hover:text-primary transition-colors">Credit Limit</p>
                                            <div class="flex items-center gap-2 mt-1">
                                                <p class="text-sm font-bold text-foreground bg-muted/30 px-3 py-1.5 rounded-lg border border-border/40 w-full text-emerald-500">
                                                    ₹{{ number_format($customer->credit_limit, 2) }}
                                                </p>
                                            </div>
                                        </div>
                                        <div class="group">
                                            <p class="text-[10px] font-black uppercase tracking-widest text-muted-foreground group-hover:text-primary transition-colors">Credit Days</p>
                                            <div class="flex items-center gap-2 mt-1">
                                                <p class="text-sm font-bold text-foreground bg-muted/30 px-3 py-1.5 rounded-lg border border-border/40 w-full">
                                                    {{ $customer->credit_days ?: 0 }} Days
                                                </p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </x-ui.card>

                    {{-- Addresses Section --}}
                    <x-ui.card class="overflow-hidden border-border/60 shadow-xl bg-card/30 backdrop-blur-2xl rounded-3xl">
                        <div class="p-6 border-b border-border/40 bg-muted/10 flex items-center justify-between">
                            <div class="flex items-center gap-3">
                                <div class="size-10 rounded-xl bg-purple-500/10 text-purple-500 flex items-center justify-center">
                                    <x-ui.icon name="map" size="5" />
                                </div>
                                <h3 class="text-lg font-bold tracking-tight text-foreground">Registered Addresses</h3>
                            </div>
                            <x-ui.button @click.prevent="openAddModal" size="sm" class="rounded-xl shadow-lg shadow-primary/20">
                                <x-ui.icon name="plus" size="4" class="mr-2" />
                                Add Address
                            </x-ui.button>
                        </div>
                        <div class="p-6 bg-background/30">
                            @if($customer->addresses->count())
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                    @foreach($customer->addresses as $address)
                                    <div class="p-5 rounded-2xl bg-card border border-border/60 shadow-sm hover:shadow-md hover:border-primary/30 transition-all relative group">
                                        @if($address->is_default)
                                            <div class="absolute -top-2.5 -right-2.5 bg-primary text-primary-foreground text-[9px] font-black uppercase tracking-widest px-2 py-1 rounded-lg shadow-lg">
                                                Default
                                            </div>
                                        @endif
                                        <div class="absolute top-4 right-4 flex items-center gap-2 opacity-0 group-hover:opacity-100 transition-opacity">
                                            <button @click="openEditModal({{ $address->toJson() }})" class="size-7 rounded-lg bg-primary/10 text-primary hover:bg-primary/20 flex items-center justify-center transition-colors" title="Edit Address">
                                                <x-ui.icon name="edit-3" size="3" />
                                            </button>
                                            <button @click="openDeleteModal({{ $address->toJson() }})" class="size-7 rounded-lg bg-destructive/10 text-destructive hover:bg-destructive/20 flex items-center justify-center transition-colors" title="Delete Address">
                                                <x-ui.icon name="trash-2" size="3" />
                                            </button>
                                        </div>
                                        <div class="flex items-center gap-2 mb-3 pr-16">
                                            <x-ui.icon name="home" size="4" class="text-muted-foreground group-hover:text-primary transition-colors" />
                                            <span class="text-xs font-black uppercase tracking-widest text-foreground">{{ $address->label ?: 'Address' }}</span>
                                        </div>
                                        <div class="space-y-1 text-sm text-muted-foreground">
                                            <p class="font-bold text-foreground">{{ $address->address_line_1 }}</p>
                                            @if($address->address_line_2)
                                                <p>{{ $address->address_line_2 }}</p>
                                            @endif
                                            <div class="pt-3 mt-3 border-t border-border/40 grid grid-cols-2 gap-y-3 gap-x-4">
                                                @if($address->village)
                                                    <div>
                                                        <span class="text-[9px] font-black uppercase tracking-widest text-muted-foreground block mb-0.5">Village</span>
                                                        <span class="text-xs font-medium text-foreground">{{ $address->village->village_name ?: '—' }}</span>
                                                    </div>
                                                    <div>
                                                        <span class="text-[9px] font-black uppercase tracking-widest text-muted-foreground block mb-0.5">Post Office</span>
                                                        <span class="text-xs font-medium text-foreground">{{ $address->village->post_so_name ?: '—' }}</span>
                                                    </div>
                                                    <div>
                                                        <span class="text-[9px] font-black uppercase tracking-widest text-muted-foreground block mb-0.5">Taluka</span>
                                                        <span class="text-xs font-medium text-foreground">{{ $address->village->taluka_name ?: '—' }}</span>
                                                    </div>
                                                @endif
                                                <div>
                                                    <span class="text-[9px] font-black uppercase tracking-widest text-muted-foreground block mb-0.5">District / City</span>
                                                    <span class="text-xs font-medium text-foreground">{{ $address->city ?: '—' }}</span>
                                                </div>
                                                <div>
                                                    <span class="text-[9px] font-black uppercase tracking-widest text-muted-foreground block mb-0.5">State</span>
                                                    <span class="text-xs font-medium text-foreground">{{ $address->state ?: '—' }}</span>
                                                </div>
                                                <div>
                                                    <span class="text-[9px] font-black uppercase tracking-widest text-muted-foreground block mb-0.5">Pincode</span>
                                                    <span class="text-xs font-medium text-foreground">{{ $address->pincode ?: '—' }}</span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    @endforeach
                                </div>
                            @else
                                <div class="text-center py-10 px-4 rounded-2xl border-2 border-dashed border-border/60 bg-muted/5">
                                    <div class="size-12 rounded-full bg-muted flex items-center justify-center mx-auto mb-3">
                                        <x-ui.icon name="map-pin" size="5" class="text-muted-foreground" />
                                    </div>
                                    <h4 class="text-sm font-bold text-foreground">No Addresses Found</h4>
                                    <p class="text-xs text-muted-foreground mt-1 max-w-sm mx-auto">This customer does not have any registered addresses yet.</p>
                                </div>
                            @endif
                        </div>
                    </x-ui.card>

                    {{-- Activity / Metadata Section --}}
                    <x-ui.card class="overflow-hidden border-border/60 shadow-xl bg-card/30 backdrop-blur-2xl rounded-3xl">
                        <div class="p-6 border-b border-border/40 bg-muted/10">
                            <div class="flex items-center gap-3">
                                <div class="size-10 rounded-xl bg-orange-500/10 text-orange-500 flex items-center justify-center">
                                    <x-ui.icon name="clock" size="5" />
                                </div>
                                <h3 class="text-lg font-bold tracking-tight text-foreground">System Metadata</h3>
                            </div>
                        </div>
                        <div class="p-6 grid grid-cols-1 sm:grid-cols-3 gap-4">
                            <div class="p-4 rounded-2xl bg-muted/10 border border-border/40 space-y-1 text-center">
                                <p class="text-[10px] font-black uppercase tracking-widest text-muted-foreground">Created On</p>
                                <p class="text-sm font-bold text-foreground">{{ $customer->created_at->format('M d, Y h:i A') }}</p>
                            </div>
                            <div class="p-4 rounded-2xl bg-muted/10 border border-border/40 space-y-1 text-center">
                                <p class="text-[10px] font-black uppercase tracking-widest text-muted-foreground">Last Updated</p>
                                <p class="text-sm font-bold text-foreground">{{ $customer->updated_at->format('M d, Y h:i A') }}</p>
                                <p class="text-[9px] text-muted-foreground/60">{{ $customer->updated_at->diffForHumans() }}</p>
                            </div>
                            <div class="p-4 rounded-2xl bg-muted/10 border border-border/40 space-y-1 text-center flex flex-col justify-center items-center">
                                <p class="text-[10px] font-black uppercase tracking-widest text-muted-foreground mb-2">Record Actions</p>
                                <form action="{{ route('customers.destroy', $customer) }}" method="POST" onsubmit="return confirm('Archive this customer?')" class="w-full">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="w-full py-1.5 px-3 rounded-lg bg-destructive/10 text-destructive text-xs font-bold hover:bg-destructive hover:text-destructive-foreground transition-colors">
                                        Archive Record
                                    </button>
                                </form>
                            </div>
                        </div>
                    </x-ui.card>

                </div>
            </div>
            
        </div>

        <!-- Address Modal -->
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
                        
                        <!-- Search Results Dropdown -->
                        <div x-show="villages.length > 0" x-cloak @click.away="villages = []" class="absolute z-50 w-full mt-1 bg-card border border-border rounded-xl shadow-lg shadow-primary/5 max-h-60 overflow-y-auto backdrop-blur-xl">
                            <template x-for="village in villages" :key="village.id">
                                <div @click="selectVillage(village)" class="p-3 border-b border-border/40 hover:bg-primary/5 cursor-pointer transition-colors last:border-0 group">
                                    <p class="text-sm font-bold text-foreground group-hover:text-primary transition-colors" x-text="village.name"></p>
                                    <p class="text-[10px] text-muted-foreground uppercase tracking-widest mt-0.5">
                                        <span x-text="village.taluka"></span>, <span x-text="village.district"></span> - <span x-text="village.pincode"></span>
                                        <template x-if="village.post_office">
                                            <span> | PO: <span x-text="village.post_office"></span></span>
                                        </template>
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
                            <label for="city" class="text-[10px] font-black uppercase tracking-widest text-muted-foreground/80 ml-1">District</label>
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

        <!-- Delete Address Modal -->
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

    </div>
</x-layouts.app>
