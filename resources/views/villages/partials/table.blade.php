<x-ui.table>
    <x-ui.table-header class="bg-muted/30">
        <x-ui.table-row>
            @canany(['villages.edit', 'villages.delete'])
            <x-ui.table-head class="w-10">
                <input type="checkbox" x-model="allSelected" @change="toggleAll" 
                    class="rounded border-border bg-background text-primary focus:ring-primary/20">
            </x-ui.table-head>
            @endcanany
            <x-ui.table-head class="w-12 text-left">#</x-ui.table-head>
            <x-ui.table-head>Village Name</x-ui.table-head>
            <x-ui.table-head>Pincode</x-ui.table-head>
            <x-ui.table-head>Taluka/District</x-ui.table-head>
            <x-ui.table-head>State</x-ui.table-head>
            <x-ui.table-head>Services</x-ui.table-head>
            @canany(['villages.edit', 'villages.delete'])
            <x-ui.table-head class="text-right">Actions</x-ui.table-head>
            @endcanany
        </x-ui.table-row>
    </x-ui.table-header>

    <x-ui.table-body>
        @forelse($villages as $village)
            <x-ui.table-row x-bind:class="selectedItems.includes({{ $village->id }}) ? 'bg-primary/5' : 'hover:bg-muted/20 transition-colors'" class="group">
                @canany(['villages.edit', 'villages.delete'])
                <x-ui.table-cell>
                    <input type="checkbox" name="item_ids[]" value="{{ $village->id }}" 
                        :checked="selectedItems.includes({{ $village->id }})" 
                        @change="if($event.target.checked) selectedItems.push({{ $village->id }}); else selectedItems = selectedItems.filter(i => i !== {{ $village->id }})"
                        class="rounded border-border bg-background text-primary focus:ring-primary/20">
                </x-ui.table-cell>
                @endcanany
                <x-ui.table-cell class="text-left">
                    <span class="text-[10px] font-mono font-medium text-muted-foreground/70">
                        {{ sprintf('%03d', ($villages->currentPage() - 1) * $villages->perPage() + $loop->iteration) }}
                    </span>
                </x-ui.table-cell>
                <x-ui.table-cell>
                    <div class="flex flex-col">
                        <span class="text-sm font-bold text-foreground">{{ $village->village_name }}</span>
                        <span class="text-[10px] font-mono text-muted-foreground italic tracking-tight">{{ $village->post_so_name }}</span>
                    </div>
                </x-ui.table-cell>
                <x-ui.table-cell>
                    <x-ui.badge variant="outline" className="font-mono text-xs bg-primary/5 border-primary/10 text-primary">
                        {{ $village->pincode }}
                    </x-ui.badge>
                </x-ui.table-cell>
                <x-ui.table-cell>
                    <div class="flex flex-col">
                        <span class="text-xs font-bold text-foreground/80">{{ $village->taluka_name }}</span>
                        <span class="text-[10px] text-muted-foreground">{{ $village->district_name }}</span>
                    </div>
                </x-ui.table-cell>
                <x-ui.table-cell>
                    <span class="text-xs font-medium">{{ $village->state_name }}</span>
                </x-ui.table-cell>
                <x-ui.table-cell>
                    <div class="flex flex-wrap gap-1 max-w-[200px]">
                        @foreach($village->mappings as $mapping)
                            @if($mapping->is_available)
                                <x-ui.badge variant="outline" className="text-[8px] px-1.5 py-0 rounded bg-emerald-500/10 border-emerald-500/20 text-emerald-600 font-black uppercase tracking-tighter" title="{{ $mapping->service->name }}">
                                    {{ $mapping->service->code }}
                                </x-ui.badge>
                            @endif
                        @endforeach
                    </div>
                </x-ui.table-cell>
                
                @canany(['villages.edit', 'villages.delete'])
                <x-ui.table-cell class="text-right">
                    <div class="flex justify-end gap-1 opacity-0 group-hover:opacity-100 transition-all duration-300">
                        @can('villages.edit')
                        <a href="{{ route('villages.edit', $village) }}">
                            <x-ui.button variant="ghost" size="icon" className="size-8 text-muted-foreground hover:text-primary hover:bg-primary/5">
                                <x-ui.icon name="edit" size="3.5" />
                            </x-ui.button>
                        </a>
                        @endcan

                        @can('villages.delete')
                        <form action="{{ route('villages.destroy', $village) }}" method="POST" onsubmit="return confirm('Delete this village?')">
                            @csrf
                            @method('DELETE')
                            <x-ui.button variant="ghost" size="icon" type="submit" className="size-8 text-muted-foreground hover:text-red-500 hover:bg-red-500/5">
                                <x-ui.icon name="trash" size="3.5" />
                            </x-ui.button>
                        </form>
                        @endcan
                    </div>
                </x-ui.table-cell>
                @endcanany
            </x-ui.table-row>
        @empty
            <x-ui.table-row>
                <x-ui.table-cell colspan="8" class="h-40 text-center">
                    <div class="flex flex-col items-center justify-center gap-2 opacity-50">
                        <x-ui.icon name="map" size="10" />
                        <p class="text-sm">No villages found</p>
                    </div>
                </x-ui.table-cell>
            </x-ui.table-row>
        @endforelse
    </x-ui.table-body>
</x-ui.table>

@if($villages->hasPages())
    <div class="p-4 border-t border-border/40 bg-muted/5 flex justify-end items-center">
        {{ $villages->links() }}
    </div>
@endif
