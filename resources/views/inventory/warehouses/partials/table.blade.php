<x-ui.table>
    <x-ui.table-header class="bg-muted/30">
        <x-ui.table-row class="border-b border-border/60">
            <x-ui.table-head>Warehouse Name & Code</x-ui.table-head>
            <x-ui.table-head>Address / State</x-ui.table-head>
            <x-ui.table-head class="text-center">Stock Items</x-ui.table-head>
            <x-ui.table-head class="text-center">Status</x-ui.table-head>
            <x-ui.table-head class="text-right">Actions</x-ui.table-head>
        </x-ui.table-row>
    </x-ui.table-header>

    <x-ui.table-body>
        @forelse($warehouses as $warehouse)
            <x-ui.table-row class="hover:bg-muted/20 transition-colors group">
                <x-ui.table-cell>
                    <div class="flex items-center gap-4">
                        <div class="size-12 rounded-2xl bg-gradient-to-br from-primary/20 to-primary/5 border border-primary/10 flex items-center justify-center shadow-inner group-hover:scale-110 transition-transform duration-500">
                            <x-ui.icon name="warehouse" size="5" class="text-primary/60" />
                        </div>
                        <div class="flex flex-col min-w-0">
                            <span class="font-bold text-foreground block leading-tight">{{ $warehouse->name }}</span>
                            <span class="text-[10px] font-black uppercase tracking-widest text-muted-foreground/60 mt-0.5">{{ $warehouse->code }}</span>
                        </div>
                    </div>
                </x-ui.table-cell>
                
                <x-ui.table-cell class="text-xs font-medium text-muted-foreground">{{ $warehouse->address ?? 'N/A' }}{{ $warehouse->state ? ', ' . $warehouse->state : '' }}</x-ui.table-cell>

                <x-ui.table-cell class="text-center font-black text-xs">
                    {{ number_format($warehouse->stocks_count) }}
                </x-ui.table-cell>

                <x-ui.table-cell class="text-center">
                    <x-ui.badge variant="{{ $warehouse->status === 'active' ? 'success' : 'outline' }}" class="rounded-lg px-2 py-0.5 text-[10px] font-black uppercase tracking-widest">
                        {{ $warehouse->status }}
                    </x-ui.badge>
                </x-ui.table-cell>

                <x-ui.table-cell class="text-right">
                    <div class="flex justify-end gap-2">
                        <a href="{{ route('warehouses.show', $warehouse) }}">
                            <x-ui.button variant="ghost" size="sm" class="h-8 w-8 p-0 rounded-xl hover:bg-primary/10 hover:text-primary transition-colors">
                                <x-ui.icon name="eye" size="4" />
                            </x-ui.button>
                        </a>
                        <a href="{{ route('warehouses.edit', $warehouse) }}">
                            <x-ui.button variant="ghost" size="sm" class="h-8 w-8 p-0 rounded-xl hover:bg-primary/10 hover:text-primary transition-colors">
                                <x-ui.icon name="edit-2" size="4" />
                            </x-ui.button>
                        </a>
                        <form action="{{ route('warehouses.destroy', $warehouse) }}" method="POST" onsubmit="return confirm('Delete this warehouse?')">
                            @csrf
                            @method('DELETE')
                            <x-ui.button type="submit" variant="ghost" size="sm" class="h-8 w-8 p-0 rounded-xl hover:bg-red-500/10 hover:text-red-500 transition-colors">
                                <x-ui.icon name="trash-2" size="4" />
                            </x-ui.button>
                        </form>
                    </div>
                </x-ui.table-cell>
            </x-ui.table-row>
        @empty
            <x-ui.table-row>
                <x-ui.table-cell colspan="5" class="h-40 text-center">
                    <div class="flex flex-col items-center justify-center gap-2 opacity-50">
                        <x-ui.icon name="inbox" size="10" />
                        <p class="text-sm font-black uppercase tracking-widest">No warehouses found</p>
                    </div>
                </x-ui.table-cell>
            </x-ui.table-row>
        @endforelse
    </x-ui.table-body>
</x-ui.table>

@if($warehouses->hasPages())
    <div class="p-4 border-t border-border/40 bg-muted/5 flex justify-end items-center">
        {{ $warehouses->links() }}
    </div>
@endif
