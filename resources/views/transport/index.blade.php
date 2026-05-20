<x-layouts.app pageTitle="Transport Fleet Management">

    <div class="p-6 lg:p-10" x-data="{ 
        search: '{{ request('search', '') }}',
        perPage: '{{ request('perPage', 10) }}',
        stats: @js($stats),
        isLoading: false,
        editingRecord: null,
        selectedRecords: [],
        allSelected: false,

        toggleAll() {
            const checkboxes = document.querySelectorAll('input[name=\'ids[]\']');
            if (this.allSelected) {
                this.selectedRecords = Array.from(checkboxes).map(el => parseInt(el.value));
            } else {
                this.selectedRecords = [];
            }
        },

        toggleRecord(id) {
            id = parseInt(id);
            if (this.selectedRecords.includes(id)) {
                this.selectedRecords = this.selectedRecords.filter(rId => rId !== id);
            } else {
                this.selectedRecords.push(id);
            }
        },

        async fetchTable(url) {
            if (this.isLoading) return;
            this.isLoading = true;
            try {
                const res = await fetch(url, {
                    headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
                });
                const data = await res.json();
                document.getElementById('table-container').innerHTML = data.table;
                this.stats = data.stats;
                this.selectedRecords = [];
                this.allSelected = false;
            } catch (error) {
                console.error('Fetch failed:', error);
            } finally {
                this.isLoading = false;
            }
        },

        async performSearch() {
            const params = new URLSearchParams({ search: this.search, perPage: this.perPage });
            const url = `{{ route('transport.index') }}?${params.toString()}`;
            window.history.replaceState({}, '', url);
            await this.fetchTable(url);
        },

        async handlePagination(event) {
            const link = event.target.closest('a');
            if (!link || !link.href || !link.href.includes('page=')) return;
            event.preventDefault();
            window.history.replaceState({}, '', link.href);
            await this.fetchTable(link.href);
        },

        openAddModal() {
            this.editingRecord = null;
            $dispatch('open-modal', { name: 'transport-modal' });
        },

        openEditModal(record) {
            this.editingRecord = record;
            $dispatch('open-modal', { name: 'transport-modal' });
        }
    }">

        <!-- Stats Widgets -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
            <x-ui.card class="p-6 bg-card/40 border-border/60 backdrop-blur-xl hover:bg-primary/5 transition-all duration-500 rounded-3xl group relative overflow-hidden">
                <div class="absolute top-0 right-0 -mr-8 -mt-8 size-32 bg-primary/10 blur-[50px] rounded-full group-hover:bg-primary/20 transition-all"></div>
                <div class="flex items-center gap-5 relative z-10">
                    <div class="size-14 rounded-2xl bg-gradient-to-tr from-primary/20 to-primary/5 border border-primary/10 text-primary flex items-center justify-center shadow-inner group-hover:scale-110 transition-transform duration-500">
                        <x-ui.icon name="truck" size="7" />
                    </div>
                    <div>
                        <p class="text-[10px] font-black uppercase tracking-widest text-muted-foreground/60 mb-1">Total Fleet</p>
                        <div class="text-3xl font-black tracking-tighter text-foreground" x-text="stats.total"></div>
                    </div>
                </div>
            </x-ui.card>

            <x-ui.card class="p-6 bg-card/40 border-border/60 backdrop-blur-xl hover:bg-emerald-500/5 transition-all duration-500 rounded-3xl group relative overflow-hidden">
                <div class="absolute top-0 right-0 -mr-8 -mt-8 size-32 bg-emerald-500/10 blur-[50px] rounded-full group-hover:bg-emerald-500/20 transition-all"></div>
                <div class="flex items-center gap-5 relative z-10">
                    <div class="size-14 rounded-2xl bg-gradient-to-tr from-emerald-500/20 to-emerald-500/5 border border-emerald-500/10 text-emerald-500 flex items-center justify-center shadow-inner group-hover:scale-110 transition-transform duration-500">
                        <x-ui.icon name="check-circle" size="7" />
                    </div>
                    <div>
                        <p class="text-[10px] font-black uppercase tracking-widest text-muted-foreground/60 mb-1">Available</p>
                        <div class="text-3xl font-black tracking-tighter text-foreground" x-text="stats.available"></div>
                    </div>
                </div>
            </x-ui.card>

            <x-ui.card class="p-6 bg-card/40 border-border/60 backdrop-blur-xl hover:bg-blue-500/5 transition-all duration-500 rounded-3xl group relative overflow-hidden">
                <div class="absolute top-0 right-0 -mr-8 -mt-8 size-32 bg-blue-500/10 blur-[50px] rounded-full group-hover:bg-blue-500/20 transition-all"></div>
                <div class="flex items-center gap-5 relative z-10">
                    <div class="size-14 rounded-2xl bg-gradient-to-tr from-blue-500/20 to-blue-500/5 border border-blue-500/10 text-blue-500 flex items-center justify-center shadow-inner group-hover:scale-110 transition-transform duration-500">
                        <x-ui.icon name="navigation" size="7" />
                    </div>
                    <div>
                        <p class="text-[10px] font-black uppercase tracking-widest text-muted-foreground/60 mb-1">On Delivery</p>
                        <div class="text-3xl font-black tracking-tighter text-foreground" x-text="stats.on_delivery"></div>
                    </div>
                </div>
            </x-ui.card>

            <x-ui.card class="p-6 bg-card/40 border-border/60 backdrop-blur-xl hover:bg-amber-500/5 transition-all duration-500 rounded-3xl group relative overflow-hidden">
                <div class="absolute top-0 right-0 -mr-8 -mt-8 size-32 bg-amber-500/10 blur-[50px] rounded-full group-hover:bg-amber-500/20 transition-all"></div>
                <div class="flex items-center gap-5 relative z-10">
                    <div class="size-14 rounded-2xl bg-gradient-to-tr from-amber-500/20 to-amber-500/5 border border-amber-500/10 text-amber-500 flex items-center justify-center shadow-inner group-hover:scale-110 transition-transform duration-500">
                        <x-ui.icon name="tool" size="7" />
                    </div>
                    <div>
                        <p class="text-[10px] font-black uppercase tracking-widest text-muted-foreground/60 mb-1">Maintenance</p>
                        <div class="text-3xl font-black tracking-tighter text-foreground" x-text="stats.maintenance"></div>
                    </div>
                </div>
            </x-ui.card>
        </div>

        <x-ui.card class="overflow-hidden border-border/60 shadow-2xl bg-card/30 backdrop-blur-2xl rounded-3xl">
            <x-ui.card-header class="border-b border-border/40 bg-muted/10 p-8">
                <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-6">
                    <div class="flex flex-wrap items-center gap-3">
                        <div class="flex bg-muted/50 px-4 py-1.5 rounded-xl border border-border/50 shadow-inner">
                            <span class="text-xs font-bold text-primary tracking-widest uppercase">Vehicles Fleet</span>
                        </div>
                        
                        <!-- Bulk Actions -->
                        <div x-show="selectedRecords.length > 0" x-cloak x-transition class="flex items-center gap-2">
                            <x-ui.dropdown>
                                <x-slot name="trigger">
                                    <x-ui.button variant="outline" size="sm" class="rounded-xl border-primary/20 bg-primary/5 text-primary font-bold shadow-sm whitespace-nowrap">
                                        <span x-text="selectedRecords.length"></span> Selected
                                        <x-ui.icon name="chevron-down" size="3" class="ml-2" />
                                    </x-ui.button>
                                </x-slot>
                                <x-slot name="content">
                                    <x-ui.dropdown-label>Bulk Actions</x-ui.dropdown-label>
                                    <form action="{{ route('transport.bulk-delete') }}" method="POST" onsubmit="return confirm('Delete selected vehicles?')">
                                        @csrf
                                        <input type="hidden" name="ids" :value="JSON.stringify(selectedRecords)">
                                        <button type="submit" class="w-full text-left px-2 py-1.5 text-xs hover:bg-muted rounded-md flex items-center text-destructive">
                                            <x-ui.icon name="trash" size="3" class="mr-2" /> Delete Selected
                                        </button>
                                    </form>
                                </x-slot>
                            </x-ui.dropdown>
                        </div>
                    </div>

                    <div class="flex flex-wrap items-center gap-3 w-full lg:w-auto">
                        <div class="flex items-center gap-2">
                            <span class="text-[10px] font-bold text-muted-foreground uppercase tracking-widest hidden sm:inline-block">Show</span>
                            <select x-model="perPage" @change="performSearch()" class="h-11 px-3 py-1.5 rounded-xl border border-border bg-background/50 focus:bg-background focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all text-xs font-medium shadow-sm">
                                <option value="5">5</option>
                                <option value="10">10</option>
                                <option value="20">20</option>
                                <option value="50">50</option>
                            </select>
                        </div>
                        <div class="relative group w-full lg:w-64 shrink-0">
                            <x-ui.icon name="search" size="4" class="absolute left-3 top-1/2 -translate-y-1/2 text-muted-foreground group-focus-within:text-primary transition-colors" />
                            <input type="text" x-model="search" @input.debounce.500ms="performSearch()" placeholder="Search vehicles..." 
                                class="pl-9 pr-4 py-2.5 rounded-xl border border-border bg-background/50 focus:bg-background focus:ring-2 focus:ring-primary/20 transition-all w-full text-xs shadow-sm outline-none">
                        </div>
                        <x-ui.button @click.stop="openAddModal" class="rounded-xl font-black uppercase tracking-widest text-[10px] h-11 px-6 shadow-lg shadow-primary/20 w-full lg:w-auto">
                            <x-ui.icon name="plus" size="3" class="mr-2" /> Add Vehicle
                        </x-ui.button>
                    </div>
                </div>
            </x-ui.card-header>

            <x-ui.card-content class="p-0 relative">
                <div x-show="isLoading" x-cloak class="absolute inset-0 z-50 bg-background/50 backdrop-blur-[2px] flex items-center justify-center">
                    <x-ui.icon name="refresh-cw" class="animate-spin text-primary" size="6" />
                </div>
                <div id="table-container" @click="handlePagination($event)">
                    @include('transport.partials.table')
                </div>
            </x-ui.card-content>
        </x-ui.card>

        <!-- Dynamic Modal Form -->
        <x-ui.modal id="transport-modal" maxWidth="md">
            <div class="p-8">
                <div class="flex items-center justify-between mb-6">
                    <div class="flex items-center gap-3">
                        <div class="size-10 rounded-2xl bg-primary/10 border border-primary/20 text-primary flex items-center justify-center shadow-inner">
                            <x-ui.icon name="truck" size="5" />
                        </div>
                        <div>
                            <h3 class="text-sm font-black text-foreground uppercase tracking-widest" x-text="editingRecord ? 'Edit Vehicle' : 'Register Vehicle'"></h3>
                            <p class="text-[10px] text-muted-foreground font-bold tracking-tight">Configure transport vehicle details</p>
                        </div>
                    </div>
                    <button type="button" @click="$dispatch('close-modal', { name: 'transport-modal' })" class="size-8 rounded-lg hover:bg-muted flex items-center justify-center transition-colors">
                        <x-ui.icon name="x" size="4" />
                    </button>
                </div>

                <form :action="editingRecord ? `{{ url('transport') }}/${editingRecord.id}` : `{{ route('transport.store') }}`" method="POST" class="space-y-5">
                    @csrf
                    <template x-if="editingRecord">
                        <input type="hidden" name="_method" value="PUT">
                    </template>

                    <div class="space-y-2">
                        <label for="name" class="text-[10px] font-black uppercase tracking-widest text-muted-foreground/80 ml-1">Vehicle Name</label>
                        <input type="text" id="name" name="name" :value="editingRecord ? editingRecord.name : ''" required placeholder="e.g. Tata Ultra Truck"
                            class="w-full h-11 px-4 rounded-xl border border-border bg-background/50 focus:bg-background focus:ring-2 focus:ring-primary/20 transition-all text-sm font-medium outline-none">
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="space-y-2">
                            <label for="vehicle_number" class="text-[10px] font-black uppercase tracking-widest text-muted-foreground/80 ml-1">Registration Plate</label>
                            <input type="text" id="vehicle_number" name="vehicle_number" :value="editingRecord ? editingRecord.vehicle_number : ''" required placeholder="e.g. MH-12-PQ-4567"
                                class="w-full h-11 px-4 rounded-xl border border-border bg-background/50 focus:bg-background focus:ring-2 focus:ring-primary/20 transition-all text-sm font-medium outline-none font-mono">
                        </div>

                        <div class="space-y-2">
                            <label for="type" class="text-[10px] font-black uppercase tracking-widest text-muted-foreground/80 ml-1">Vehicle Type</label>
                            <input type="text" id="type" name="type" :value="editingRecord ? editingRecord.type : ''" placeholder="e.g. Heavy Truck, Van"
                                class="w-full h-11 px-4 rounded-xl border border-border bg-background/50 focus:bg-background focus:ring-2 focus:ring-primary/20 transition-all text-sm font-medium outline-none">
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="space-y-2">
                            <label for="capacity_weight" class="text-[10px] font-black uppercase tracking-widest text-muted-foreground/80 ml-1">Weight Capacity (kg)</label>
                            <input type="number" id="capacity_weight" name="capacity_weight" :value="editingRecord ? editingRecord.capacity_weight : ''" placeholder="e.g. 5000"
                                class="w-full h-11 px-4 rounded-xl border border-border bg-background/50 focus:bg-background focus:ring-2 focus:ring-primary/20 transition-all text-sm font-medium outline-none">
                        </div>

                        <div class="space-y-2">
                            <label for="status" class="text-[10px] font-black uppercase tracking-widest text-muted-foreground/80 ml-1">Fleet Status</label>
                            <select id="status" name="status" class="w-full h-11 px-4 rounded-xl border border-border bg-background/50 text-[10px] font-black uppercase tracking-widest focus:bg-background focus:ring-2 focus:ring-primary/20 outline-none transition-all">
                                <option value="available" :selected="editingRecord && editingRecord.status === 'available'">Available</option>
                                <option value="on_delivery" :selected="editingRecord && editingRecord.status === 'on_delivery'">On Delivery</option>
                                <option value="maintenance" :selected="editingRecord && editingRecord.status === 'maintenance'">Maintenance</option>
                                <option value="inactive" :selected="editingRecord && editingRecord.status === 'inactive'">Inactive</option>
                            </select>
                        </div>
                    </div>

                    <div class="mt-8 flex justify-end gap-3 border-t border-border/30 pt-6">
                        <x-ui.button type="button" variant="outline" @click="$dispatch('close-modal', { name: 'transport-modal' })" class="rounded-xl font-black uppercase tracking-widest text-[10px]">Cancel</x-ui.button>
                        <x-ui.button type="submit" class="rounded-xl font-black uppercase tracking-widest text-[10px] shadow-lg shadow-primary/25" x-text="editingRecord ? 'Save Changes' : 'Register Vehicle'"></x-ui.button>
                    </div>
                </form>
            </div>
        </x-ui.modal>

    </div>
</x-layouts.app>
