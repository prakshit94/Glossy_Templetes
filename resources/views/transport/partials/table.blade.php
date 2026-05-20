@php
    $records = $records ?? collect();
    $rows = $records instanceof \Illuminate\Pagination\AbstractPaginator ? $records->getCollection() : $records;
@endphp

<div class="overflow-x-auto">
    <table class="w-full text-left border-collapse">
        <thead>
            <tr class="bg-muted/5 border-b border-border/40">
                <th class="p-5 w-10">
                    <input type="checkbox" x-model="allSelected" @change="toggleAll" 
                        class="rounded border-border bg-background text-primary focus:ring-primary/20">
                </th>
                <th class="p-5 text-[10px] font-black uppercase tracking-widest text-muted-foreground/80">Vehicle Name</th>
                <th class="p-5 text-[10px] font-black uppercase tracking-widest text-muted-foreground/80">Vehicle Plate</th>
                <th class="p-5 text-[10px] font-black uppercase tracking-widest text-muted-foreground/80">Type</th>
                <th class="p-5 text-[10px] font-black uppercase tracking-widest text-muted-foreground/80">Capacity</th>
                <th class="p-5 text-[10px] font-black uppercase tracking-widest text-muted-foreground/80">Status</th>
                <th class="p-5 text-[10px] font-black uppercase tracking-widest text-muted-foreground/80 text-right">Actions</th>
            </tr>
        </thead>
        <tbody>
            @forelse($rows as $record)
                @php $r = is_array($record) ? (object) $record : $record; @endphp
                <tr x-bind:class="selectedRecords.includes({{ $r->id }}) ? 'bg-primary/5' : 'hover:bg-primary/[0.02] transition-colors'" class="border-b border-border/40 group">
                    <td class="p-5">
                        <input type="checkbox" name="ids[]" value="{{ $r->id }}" :checked="selectedRecords.includes({{ $r->id }})" @change="toggleRecord({{ $r->id }})"
                            class="rounded border-border bg-background text-primary focus:ring-primary/20">
                    </td>
                    <td class="p-5">
                        <div class="flex items-center gap-4">
                            <div class="size-10 rounded-xl bg-gradient-to-tr from-primary/20 to-primary/5 border border-primary/10 text-primary flex items-center justify-center shadow-inner shrink-0">
                                <x-ui.icon name="truck" size="4" />
                            </div>
                            <div>
                                <div class="text-sm font-black text-foreground">{{ $r->name }}</div>
                                <div class="text-[10px] font-bold text-muted-foreground tracking-tight uppercase">ID: TRP-{{ str_pad($r->id, 4, '0', STR_PAD_LEFT) }}</div>
                            </div>
                        </div>
                    </td>
                    <td class="p-5">
                        <span class="text-xs font-mono font-bold bg-muted/30 px-2 py-1 rounded-lg text-foreground border border-border/20">{{ $r->vehicle_number }}</span>
                    </td>
                    <td class="p-5">
                        <span class="text-xs font-semibold text-muted-foreground uppercase tracking-wider">{{ $r->type ?? 'General' }}</span>
                    </td>
                    <td class="p-5">
                        <span class="text-xs font-bold text-foreground">{{ number_format($r->capacity_weight, 0) }} kg</span>
                    </td>
                    <td class="p-5">
                        @php
                            $st = strtolower((string) $r->status);
                            $badgeClass = match ($st) {
                                'available' => 'bg-emerald-500/10 border-emerald-500/20 text-emerald-500',
                                'on_delivery' => 'bg-blue-500/10 border-blue-500/20 text-blue-500',
                                'maintenance' => 'bg-amber-500/10 border-amber-500/20 text-amber-500',
                                default => 'bg-red-500/10 border-red-500/20 text-red-500',
                            };
                        @endphp
                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full border text-[10px] font-black uppercase tracking-widest {{ $badgeClass }} shadow-sm">
                            <span class="size-1.5 rounded-full bg-current @if($st === 'available' || $st === 'on_delivery') animate-pulse @endif"></span>
                            {{ str_replace('_', ' ', $st) }}
                        </span>
                    </td>
                    <td class="p-5 text-right">
                        <div class="flex items-center justify-end gap-2 opacity-0 group-hover:opacity-100 transition-opacity duration-300">
                            <button @click.stop="openEditModal({{ json_encode($r) }})" class="size-9 rounded-xl bg-background/50 border border-border/60 flex items-center justify-center text-muted-foreground hover:text-primary hover:border-primary/40 transition-all shadow-sm hover:scale-105 active:scale-95">
                                <x-ui.icon name="edit-3" size="4" />
                            </button>
                            <form action="{{ route('transport.destroy', $r) }}" method="POST" onsubmit="return confirm('Permanently delete this vehicle?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="size-9 rounded-xl bg-background/50 border border-border/60 flex items-center justify-center text-muted-foreground hover:text-destructive hover:border-destructive/40 transition-all shadow-sm hover:scale-105 active:scale-95">
                                    <x-ui.icon name="trash-2" size="4" />
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" class="p-20 text-center">
                        <div class="flex flex-col items-center gap-3">
                            <div class="size-16 rounded-3xl bg-muted/10 flex items-center justify-center text-muted-foreground/20">
                                <x-ui.icon name="truck" size="8" />
                            </div>
                            <div class="text-sm font-black text-muted-foreground uppercase tracking-widest">No transport vehicles found</div>
                            <p class="text-xs text-muted-foreground/60 max-w-[200px]">Start by adding your first vehicle to the transport fleet.</p>
                        </div>
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

@if($records instanceof \Illuminate\Pagination\AbstractPaginator && $records->hasPages())
    <div class="p-6 border-t border-border/40 bg-muted/5">
        {{ $records->links() }}
    </div>
@endif
