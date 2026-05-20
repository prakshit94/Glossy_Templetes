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
                <th class="p-5 text-[10px] font-black uppercase tracking-widest text-muted-foreground/80">Delivery No</th>
                <th class="p-5 text-[10px] font-black uppercase tracking-widest text-muted-foreground/80">Shipment No</th>
                <th class="p-5 text-[10px] font-black uppercase tracking-widest text-muted-foreground/80">Destination</th>
                <th class="p-5 text-[10px] font-black uppercase tracking-widest text-muted-foreground/80">Driver</th>
                <th class="p-5 text-[10px] font-black uppercase tracking-widest text-muted-foreground/80">Vehicle</th>
                <th class="p-5 text-[10px] font-black uppercase tracking-widest text-muted-foreground/80">Status</th>
                <th class="p-5 text-[10px] font-black uppercase tracking-widest text-muted-foreground/80">Calls</th>
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
                        <span class="text-xs font-mono font-black text-foreground">{{ $r->delivery_number }}</span>
                    </td>
                    <td class="p-5">
                        @if($r->shipment)
                            <a href="{{ url('order-tracking/' . $r->shipment_id) }}" class="text-xs font-mono font-bold text-primary hover:underline">
                                {{ $r->shipment->shipment_no }}
                            </a>
                        @else
                            <span class="text-xs text-muted-foreground">—</span>
                        @endif
                    </td>
                    <td class="p-5">
                        <span class="text-xs font-medium text-foreground line-clamp-2 max-w-[200px]">{{ $r->destination }}</span>
                    </td>
                    <td class="p-5">
                        <span class="text-xs font-semibold text-muted-foreground">{{ $r->driver_name }}</span>
                    </td>
                    <td class="p-5">
                        <span class="text-xs font-semibold text-muted-foreground">
                            {{ $r->transport ? $r->transport->name . ' (' . $r->transport->vehicle_number . ')' : '—' }}
                        </span>
                    </td>
                    <td class="p-5">
                        @php
                            $st = strtolower((string) $r->status);
                            $badgeClass = match ($st) {
                                'delivered' => 'bg-emerald-500/10 border-emerald-500/20 text-emerald-500',
                                'out_for_delivery' => 'bg-blue-500/10 border-blue-500/20 text-blue-500',
                                'failed' => 'bg-red-500/10 border-red-500/20 text-red-500',
                                default => 'bg-amber-500/10 border-amber-500/20 text-amber-500',
                            };
                        @endphp
                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full border text-[10px] font-black uppercase tracking-widest {{ $badgeClass }} shadow-sm">
                            <span class="size-1.5 rounded-full bg-current @if($st === 'out_for_delivery') animate-pulse @endif"></span>
                            {{ str_replace('_', ' ', $st) }}
                        </span>
                    </td>
                    <td class="p-5">
                        @php
                            $callCount = $r->verificationLogs?->count() ?? 0;
                            $lastCall = $r->verificationLogs?->first();
                        @endphp
                        <div class="flex flex-col gap-1">
                            <span class="text-[10px] font-black text-foreground">{{ $callCount }} logged</span>
                            @if($lastCall)
                                <span class="text-[9px] font-bold text-muted-foreground line-clamp-1 max-w-[140px]" title="{{ $lastCall->outcome_label }}">{{ $lastCall->outcome_label }}</span>
                            @endif
                        </div>
                    </td>
                    <td class="p-5 text-right">
                        <div class="flex items-center justify-end gap-2">
                            <button type="button"
                                @click="openDeliveryVerification({{ $r->id }})"
                                class="rounded-xl font-bold uppercase tracking-widest text-[9px] h-8 px-3 bg-primary/10 text-primary border border-primary/20 hover:bg-primary/20 transition-all inline-flex items-center">
                                <x-ui.icon name="phone" size="3" class="mr-1" /> Verify
                            </button>
                            @if($st === 'out_for_delivery')
                                <form action="{{ route('delivery.deliver', $r->id) }}" method="POST">
                                    @csrf
                                    <x-ui.button type="submit" variant="default" size="sm" class="rounded-xl font-bold uppercase tracking-widest text-[9px] h-8 bg-emerald-500 hover:bg-emerald-600 border-none shadow-sm shadow-emerald-500/15">
                                        <x-ui.icon name="check" size="3" class="mr-1" /> Mark Delivered
                                    </x-ui.button>
                                </form>
                            @endif

                            <div class="opacity-0 group-hover:opacity-100 transition-opacity duration-300 flex gap-2">
                                <form action="{{ route('delivery.destroy', $r) }}" method="POST" onsubmit="return confirm('Delete this delivery assignment?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="size-9 rounded-xl bg-background/50 border border-border/60 flex items-center justify-center text-muted-foreground hover:text-destructive hover:border-destructive/40 transition-all shadow-sm hover:scale-105 active:scale-95">
                                        <x-ui.icon name="trash-2" size="4" />
                                    </button>
                                </form>
                            </div>
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="9" class="p-20 text-center">
                        <div class="flex flex-col items-center gap-3">
                            <div class="size-16 rounded-3xl bg-muted/10 flex items-center justify-center text-muted-foreground/20">
                                <x-ui.icon name="truck-2" size="8" />
                            </div>
                            <div class="text-sm font-black text-muted-foreground uppercase tracking-widest">No active deliveries</div>
                            <p class="text-xs text-muted-foreground/60 max-w-[200px]">Assign ready shipments to drivers and vehicles to start delivery.</p>
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
