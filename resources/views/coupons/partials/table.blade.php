@php
    $records = $records ?? collect();
    $rows = $records instanceof \Illuminate\Pagination\AbstractPaginator ? $records->getCollection() : $records;
@endphp

@if($records instanceof \Illuminate\Pagination\AbstractPaginator && $records->hasPages())
    <div class="px-6 py-4 border-b border-border/40 bg-muted/10 flex flex-col md:flex-row md:items-center md:justify-between gap-3">
        <p class="text-[11px] font-bold text-muted-foreground uppercase tracking-widest">
            Showing {{ $records->firstItem() ?? 0 }}-{{ $records->lastItem() ?? 0 }} of {{ $records->total() }}
        </p>
        {{ $records->links() }}
    </div>
@endif

<x-ui.table>
    <x-ui.table-header class="bg-muted/20">
        <x-ui.table-row class="border-b border-border/60">
            <x-ui.table-head class="text-[10px] font-black uppercase tracking-widest text-muted-foreground/60">Code</x-ui.table-head>
            <x-ui.table-head class="text-[10px] font-black uppercase tracking-widest text-muted-foreground/60">Discount</x-ui.table-head>
            <x-ui.table-head class="text-[10px] font-black uppercase tracking-widest text-muted-foreground/60 text-center">Usage</x-ui.table-head>
            <x-ui.table-head class="text-[10px] font-black uppercase tracking-widest text-muted-foreground/60">Validity</x-ui.table-head>
            <x-ui.table-head class="text-[10px] font-black uppercase tracking-widest text-muted-foreground/60">Status</x-ui.table-head>
            <x-ui.table-head class="text-right text-[10px] font-black uppercase tracking-widest text-muted-foreground/60">Actions</x-ui.table-head>
        </x-ui.table-row>
    </x-ui.table-header>
    <x-ui.table-body>
        @forelse($rows as $record)
            @php $r = is_array($record) ? (object) $record : $record; @endphp
            <x-ui.table-row class="border-b border-border/40 group hover:bg-primary/[0.02] transition-colors" data-coupon-row="{{ strtolower(trim(($r->code ?? '') . ' ' . ($r->type ?? ''))) }}">
                <x-ui.table-cell>
                    <div class="space-y-1">
                        <span class="inline-flex items-center text-sm font-mono font-black text-primary bg-primary/5 border border-primary/10 px-3 py-1 rounded-xl">{{ data_get($r, 'code', '—') }}</span>
                        <p class="text-[10px] font-bold uppercase tracking-widest text-muted-foreground">
                            {{ data_get($r, 'type') === 'percentage' ? 'Percentage Rule' : 'Fixed Value Rule' }}
                        </p>
                    </div>
                </x-ui.table-cell>
                <x-ui.table-cell>
                    <div class="space-y-1">
                        <span class="text-lg font-black text-foreground">{{ data_get($r, 'type') === 'percentage' ? rtrim(rtrim(number_format((float) data_get($r, 'value', 0), 2), '0'), '.') . '%' : '₹' . number_format((float) data_get($r, 'value', 0), 2) }}</span>
                        @if((float) data_get($r, 'min_spend', 0) > 0)
                            <p class="text-[10px] font-bold uppercase tracking-widest text-muted-foreground">Min spend ₹{{ number_format((float) data_get($r, 'min_spend', 0), 2) }}</p>
                        @endif
                    </div>
                </x-ui.table-cell>
                <x-ui.table-cell class="text-center">
                    <span class="text-sm font-black tabular-nums text-foreground">{{ data_get($r, 'used_count', '0') }}</span>
                    <p class="text-[10px] font-bold uppercase tracking-widest text-muted-foreground">of {{ data_get($r, 'usage_limit') ?? 'Unlimited' }}</p>
                </x-ui.table-cell>
                <x-ui.table-cell>
                    @php $vu = data_get($r, 'expiry_date'); @endphp
                    <div class="space-y-1">
                        <span class="text-sm font-bold text-foreground">{{ $vu ? \Illuminate\Support\Carbon::parse($vu)->format('M j, Y') : 'Never expires' }}</span>
                        <p class="text-[10px] font-bold uppercase tracking-widest text-muted-foreground">{{ $vu ? \Illuminate\Support\Carbon::parse($vu)->diffForHumans() : 'Open ended' }}</p>
                    </div>
                </x-ui.table-cell>
                <x-ui.table-cell>
                    @php
                        $isActive = data_get($r, 'is_active');
                        $badgeVariant = $isActive ? 'success' : 'destructive';
                        $statusText = $isActive ? 'ACTIVE' : 'INACTIVE';
                    @endphp
                    <x-ui.badge :variant="$badgeVariant" className="uppercase text-[9px] font-black tracking-[0.2em] px-3 py-1 rounded-xl shadow-sm">
                        {{ $statusText }}
                    </x-ui.badge>
                </x-ui.table-cell>
                <x-ui.table-cell class="text-right">
                    <div class="flex justify-end gap-1.5 opacity-80 group-hover:opacity-100 transition-opacity">
                        <a href="{{ route('coupons.edit', $r->id) }}" class="inline-flex items-center justify-center size-9 text-muted-foreground hover:text-primary hover:bg-primary/10 rounded-2xl border border-transparent hover:border-primary/20 transition-all">
                            <x-ui.icon name="edit-3" size="4" />
                        </a>
                        <form action="{{ route('coupons.destroy', $r->id) }}" method="POST" class="inline" onsubmit="return confirm('Delete this coupon?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="inline-flex items-center justify-center size-9 text-muted-foreground hover:text-destructive hover:bg-destructive/10 rounded-2xl border border-transparent hover:border-destructive/20 transition-all">
                                <x-ui.icon name="trash-2" size="4" />
                            </button>
                        </form>
                    </div>
                </x-ui.table-cell>
            </x-ui.table-row>
        @empty
            <x-ui.table-row>
                <x-ui.table-cell colspan="6" class="h-60 text-center">
                    <div class="flex flex-col items-center justify-center gap-4 opacity-40">
                        <div class="size-16 rounded-[1.5rem] bg-primary/5 border border-primary/10 flex items-center justify-center">
                            <x-ui.icon name="gift" size="10" class="text-primary/40" />
                        </div>
                        <div class="space-y-1">
                            <p class="text-sm font-black uppercase tracking-[0.2em]">No coupons found</p>
                            <p class="text-[10px] font-semibold text-muted-foreground uppercase tracking-widest">Create a new coupon to start managing discounts.</p>
                        </div>
                    </div>
                </x-ui.table-cell>
            </x-ui.table-row>
        @endforelse
    </x-ui.table-body>
</x-ui.table>

@if($records instanceof \Illuminate\Pagination\AbstractPaginator && $records->hasPages())
    <div class="px-6 py-4 border-t border-border/40 bg-muted/10 flex flex-col md:flex-row md:items-center md:justify-between gap-3 rounded-b-[2rem]">
        <p class="text-[11px] font-bold text-muted-foreground uppercase tracking-widest">
            Page {{ $records->currentPage() }} of {{ $records->lastPage() }}
        </p>
        {{ $records->links() }}
    </div>
@endif
