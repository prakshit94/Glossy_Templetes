@php
    $records = $records ?? collect();
    $rows = $records instanceof \Illuminate\Pagination\AbstractPaginator ? $records->getCollection() : $records;
@endphp

@if($records instanceof \Illuminate\Pagination\AbstractPaginator && $records->hasPages())
    <div class="p-4 border-b border-border/40 flex justify-end items-center">
        {{ $records->links() }}
    </div>
@endif

<x-ui.table>
    <x-ui.table-header class="bg-muted/20">
        <x-ui.table-row class="border-b border-border/60">
            <x-ui.table-head class="text-[10px] font-black uppercase tracking-widest text-muted-foreground/60">Code</x-ui.table-head>
            <x-ui.table-head class="text-[10px] font-black uppercase tracking-widest text-muted-foreground/60">Discount</x-ui.table-head>
            <x-ui.table-head class="text-[10px] font-black uppercase tracking-widest text-muted-foreground/60 text-center">Uses</x-ui.table-head>
            <x-ui.table-head class="text-[10px] font-black uppercase tracking-widest text-muted-foreground/60">Valid until</x-ui.table-head>
            <x-ui.table-head class="text-[10px] font-black uppercase tracking-widest text-muted-foreground/60">Status</x-ui.table-head>
            <x-ui.table-head class="text-right text-[10px] font-black uppercase tracking-widest text-muted-foreground/60">Actions</x-ui.table-head>
        </x-ui.table-row>
    </x-ui.table-header>
    <x-ui.table-body>
        @forelse($rows as $record)
            @php $r = is_array($record) ? (object) $record : $record; @endphp
            <x-ui.table-row class="border-b border-border/40 group hover:bg-primary/[0.02] transition-colors">
                <x-ui.table-cell>
                    <span class="text-sm font-mono font-black text-primary">{{ data_get($r, 'code', '—') }}</span>
                </x-ui.table-cell>
                <x-ui.table-cell>
                    <span class="text-sm font-black">{{ data_get($r, 'type') === 'percentage' ? data_get($r, 'value').'%' : '₹'.data_get($r, 'value') }}</span>
                </x-ui.table-cell>
                <x-ui.table-cell class="text-center">
                    <span class="text-sm font-black tabular-nums">{{ data_get($r, 'used_count', '0') }} / {{ data_get($r, 'usage_limit') ?? '∞' }}</span>
                </x-ui.table-cell>
                <x-ui.table-cell>
                    @php $vu = data_get($r, 'expiry_date'); @endphp
                    <span class="text-xs font-bold">{{ $vu ? \Illuminate\Support\Carbon::parse($vu)->format('M j, Y') : 'Never' }}</span>
                </x-ui.table-cell>
                <x-ui.table-cell>
                    @php
                        $isActive = data_get($r, 'is_active');
                        $badgeVariant = $isActive ? 'success' : 'destructive';
                        $statusText = $isActive ? 'ACTIVE' : 'INACTIVE';
                    @endphp
                    <x-ui.badge :variant="$badgeVariant" className="uppercase text-[9px] font-black tracking-[0.1em] px-2.5 py-1 rounded-lg shadow-sm">
                        {{ $statusText }}
                    </x-ui.badge>
                </x-ui.table-cell>
                <x-ui.table-cell class="text-right">
                    <div class="flex justify-end gap-1.5">
                        <a href="{{ route('coupons.edit', $r->id) }}" class="inline-flex items-center justify-center size-8 text-muted-foreground hover:text-primary hover:bg-primary/10 rounded-xl border border-transparent hover:border-primary/20 transition-all">
                            <x-ui.icon name="edit-3" size="4" />
                        </a>
                        <form action="{{ route('coupons.destroy', $r->id) }}" method="POST" class="inline" onsubmit="return confirm('Delete this coupon?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="inline-flex items-center justify-center size-8 text-muted-foreground hover:text-destructive hover:bg-destructive/10 rounded-xl border border-transparent hover:border-destructive/20 transition-all">
                                <x-ui.icon name="trash-2" size="4" />
                            </button>
                        </form>
                    </div>
                </x-ui.table-cell>
            </x-ui.table-row>

        @empty
            <x-ui.table-row>
                <x-ui.table-cell colspan="6" class="h-60 text-center">
                    <div class="flex flex-col items-center justify-center gap-3 opacity-40">
                        <x-ui.icon :name="$moduleIcon ?? 'package'" size="12" />
                        <p class="text-sm font-black uppercase tracking-[0.2em]">No coupons found</p>
                        <p class="text-[10px] font-semibold text-muted-foreground uppercase tracking-widest max-w-md px-6">
                            Pass <span class="font-mono text-foreground/60">$records</span> from the controller (models, arrays, or paginator).
                        </p>
                    </div>
                </x-ui.table-cell>
            </x-ui.table-row>
        @endforelse
    </x-ui.table-body>
</x-ui.table>
@if($records instanceof \Illuminate\Pagination\AbstractPaginator && $records->hasPages())
    <div class="p-4 border-t border-border/40 bg-muted/5 flex justify-end items-center rounded-b-3xl">
        {{ $records->links() }}
    </div>
@endif
