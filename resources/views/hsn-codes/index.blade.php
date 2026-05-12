<x-layouts.app pageTitle="HSN Codes">

    <div class="p-6 lg:p-10" x-data="{ 
        search: '{{ request('search', '') }}',
        stats: @js($stats),
        editingCode: null,
        isLoading: false,

        async performSearch() {
            this.isLoading = true;
            let params = new URLSearchParams({ search: this.search });
            window.history.replaceState({}, '', `${window.location.pathname}?${params.toString()}`);

            try {
                const res = await fetch(`{{ route('hsn-codes.index') }}?${params.toString()}`, {
                    headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
                });
                const data = await res.json();
                document.getElementById('table-container').innerHTML = data.table;
                this.stats = data.stats;
            } catch (error) {
                console.error('Search failed:', error);
            } finally {
                this.isLoading = false;
            }
        },

        openAddModal() {
            this.editingCode = null;
            $dispatch('open-modal', { name: 'hsn-modal' });
        },

        openEditModal(code) {
            this.editingCode = code;
            $dispatch('open-modal', { name: 'hsn-modal' });
        }
    }">

        <!-- Stats Widgets -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
            <x-ui.card class="p-6 bg-card/40 border-border/60 backdrop-blur-xl hover:bg-primary/5 transition-all duration-500 rounded-3xl group relative overflow-hidden">
                <div class="absolute top-0 right-0 -mr-8 -mt-8 size-32 bg-primary/10 blur-[50px] rounded-full group-hover:bg-primary/20 transition-all"></div>
                <div class="flex items-center gap-5 relative z-10">
                    <div class="size-14 rounded-2xl bg-gradient-to-tr from-primary/20 to-primary/5 border border-primary/10 text-primary flex items-center justify-center shadow-inner group-hover:scale-110 transition-transform duration-500">
                        <x-ui.icon name="file-text" size="7" />
                    </div>
                    <div>
                        <p class="text-[10px] font-black uppercase tracking-widest text-muted-foreground/60 mb-1">Total HSN Codes</p>
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
                        <p class="text-[10px] font-black uppercase tracking-widest text-muted-foreground/60 mb-1">Active Codes</p>
                        <div class="text-3xl font-black tracking-tighter text-foreground" x-text="stats.active"></div>
                    </div>
                </div>
            </x-ui.card>
        </div>

        <x-ui.card class="overflow-hidden border-border/60 shadow-2xl bg-card/30 backdrop-blur-2xl rounded-3xl">
            <x-ui.card-header class="border-b border-border/40 bg-muted/10 p-8">
                <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-6">
                    <div class="relative group w-full lg:max-w-md">
                        <x-ui.icon name="search" size="4" class="absolute left-3 top-1/2 -translate-y-1/2 text-muted-foreground group-focus-within:text-primary transition-colors" />
                        <input type="text" x-model="search" @input.debounce.500ms="performSearch" placeholder="Search codes or descriptions..." 
                            class="pl-9 pr-4 py-2.5 rounded-xl border border-border bg-background/50 focus:bg-background focus:ring-2 focus:ring-primary/20 transition-all w-full text-xs shadow-sm outline-none">
                    </div>
                    <x-ui.button @click="openAddModal" class="rounded-xl font-black uppercase tracking-widest text-[10px] h-11 px-6 shadow-lg shadow-primary/20">
                        <x-ui.icon name="plus" size="3" class="mr-2" /> Add HSN Code
                    </x-ui.button>
                </div>
            </x-ui.card-header>

            <x-ui.card-content class="p-0 relative">
                <div x-show="isLoading" x-cloak class="absolute inset-0 z-50 bg-background/50 backdrop-blur-[2px] flex items-center justify-center">
                    <x-ui.icon name="refresh-cw" class="animate-spin text-primary" size="6" />
                </div>
                <div id="table-container">
                    @include('hsn-codes.partials.table')
                </div>
            </x-ui.card-content>
        </x-ui.card>

        @include('hsn-codes.partials.modal')
    </div>
</x-layouts.app>
