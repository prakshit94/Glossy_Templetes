<x-ui.table>
    <x-ui.table-header class="bg-muted/30">
        <x-ui.table-row>
            @canany(['services.edit', 'services.delete'])
            <x-ui.table-head class="w-10 text-left">
                <input type="checkbox" x-model="allSelected" @change="toggleAll" class="rounded border-border text-primary focus:ring-primary/20">
            </x-ui.table-head>
            @endcanany
            <x-ui.table-head>Service</x-ui.table-head>
            <x-ui.table-head>Coverage</x-ui.table-head>
            <x-ui.table-head>Total Reach</x-ui.table-head>
            <x-ui.table-head>Status</x-ui.table-head>
            @canany(['services.edit', 'services.delete'])
            <x-ui.table-head class="text-right">Actions</x-ui.table-head>
            @endcanany
        </x-ui.table-row>
    </x-ui.table-header>
    <x-ui.table-body>
        @forelse($services as $service)
            <x-ui.table-row class="group">
                @canany(['services.edit', 'services.delete'])
                <x-ui.table-cell>
                    <input type="checkbox" name="item_ids[]" value="{{ $service->id }}" x-model="selectedItems" class="rounded border-border text-primary focus:ring-primary/20">
                </x-ui.table-cell>
                @endcanany
                
                <x-ui.table-cell>
                    <div class="flex flex-col">
                        <span class="text-sm font-bold text-foreground">{{ $service->name }}</span>
                        <span class="text-[9px] font-bold text-muted-foreground uppercase">{{ $service->code }}</span>
                    </div>
                </x-ui.table-cell>

                <x-ui.table-cell>
                    <div class="flex items-center gap-1.5">
                        <x-ui.badge variant="outline" class="bg-blue-500/10 text-blue-500 border-none text-[8px] font-bold px-1.5 py-0">
                            {{ $service->geography->states_count ?? 0 }} S
                        </x-ui.badge>
                        <x-ui.badge variant="outline" class="bg-emerald-500/10 text-emerald-500 border-none text-[8px] font-bold px-1.5 py-0">
                            {{ $service->geography->districts_count ?? 0 }} D
                        </x-ui.badge>
                    </div>
                </x-ui.table-cell>

                <x-ui.table-cell>
                    <div class="relative inline-block" x-data="{ 
                        open: false, 
                        villages: [], 
                        search: '', 
                        loading: false,
                        async loadVillages() {
                            if(this.villages.length > 0) return;
                            this.loading = true;
                            const res = await fetch(`{{ route('services.villages', $service) }}`);
                            this.villages = await res.json();
                            this.loading = false;
                        },
                        get filteredVillages() {
                            return this.villages.filter(v => 
                                v.village_name.toLowerCase().includes(this.search.toLowerCase()) ||
                                v.pincode.includes(this.search)
                            );
                        }
                    }">
                        <button 
                            @click="open = !open; if(open) loadVillages();" 
                            class="flex items-center gap-1.5 px-2 py-1 rounded-md hover:bg-muted transition-colors text-left"
                        >
                            <span class="text-sm font-black text-foreground">{{ number_format($service->total_villages) }}</span>
                            <span class="text-[9px] font-bold text-muted-foreground uppercase tracking-tight">VILLAGES</span>
                            <x-ui.icon name="chevron-down" size="2" class="text-muted-foreground" />
                        </button>

                        <!-- Minimal Click Dropdown -->
                        <div 
                            x-show="open" 
                            @click.away="open = false"
                            x-cloak
                            x-transition:enter="transition duration-100"
                            class="absolute left-0 mt-2 w-64 bg-popover border border-border rounded-xl shadow-2xl z-[100] overflow-hidden flex flex-col"
                        >
                            <div class="p-2 border-b border-border bg-muted/30">
                                <input type="text" x-model="search" placeholder="Verify villages..." 
                                    class="w-full px-3 py-1 rounded border border-border bg-background text-[10px] outline-none focus:ring-1 focus:ring-primary">
                            </div>
                            
                            <div class="max-h-60 overflow-y-auto custom-scrollbar p-1">
                                <div x-show="loading" class="py-10 flex flex-col items-center justify-center gap-2">
                                    <div class="size-4 border-2 border-primary/20 border-t-primary rounded-full animate-spin"></div>
                                </div>

                                <template x-for="village in filteredVillages" :key="village.id">
                                    <div class="flex flex-col px-3 py-1 rounded-lg hover:bg-muted">
                                        <span class="text-[10px] font-bold text-foreground" x-text="village.village_name"></span>
                                        <span class="text-[8px] text-muted-foreground" x-text="village.pincode"></span>
                                    </div>
                                </template>
                            </div>
                        </div>
                    </div>
                </x-ui.table-cell>

                <x-ui.table-cell>
                    @if($service->is_active)
                        <span class="text-[9px] font-bold uppercase text-emerald-500">Active</span>
                    @else
                        <span class="text-[9px] font-bold uppercase text-muted-foreground">Inactive</span>
                    @endif
                </x-ui.table-cell>

                @canany(['services.edit', 'services.delete'])
                <x-ui.table-cell class="text-right">
                    <div class="flex items-center justify-end gap-1 transition-opacity">
                        @can('services.edit')
                        <a href="{{ route('services.edit', $service) }}">
                            <x-ui.button variant="ghost" size="icon" class="size-8 hover:bg-primary/5">
                                <x-ui.icon name="edit-2" size="3.5" />
                            </x-ui.button>
                        </a>
                        @endcan

                        @can('services.delete')
                        <form action="{{ route('services.destroy', $service) }}" method="POST" onsubmit="return confirm('Delete?')">
                            @csrf
                            @method('DELETE')
                            <x-ui.button variant="ghost" size="icon" class="size-8 hover:bg-destructive/5 hover:text-destructive">
                                <x-ui.icon name="trash-2" size="3.5" />
                            </x-ui.button>
                        </form>
                        @endcan
                    </div>
                </x-ui.table-cell>
                @endcanany
            </x-ui.table-row>
        @empty
            <x-ui.table-row>
                <x-ui.table-cell colspan="{{ auth()->user()->canAny(['services.edit', 'services.delete']) ? 6 : 4 }}" class="py-12 text-center">
                    <p class="text-muted-foreground text-[10px] font-bold uppercase">No records found</p>
                </x-ui.table-cell>
            </x-ui.table-row>
        @endforelse
    </x-ui.table-body>
</x-ui.table>

@if($services->hasPages())
    <div class="p-4 border-t border-border/40">
        {{ $services->links() }}
    </div>
@endif
