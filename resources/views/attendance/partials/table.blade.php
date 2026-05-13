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
            <x-ui.table-head class="text-[10px] font-black uppercase tracking-widest text-muted-foreground/60">Employee</x-ui.table-head>
            <x-ui.table-head class="text-[10px] font-black uppercase tracking-widest text-muted-foreground/60">Date</x-ui.table-head>
            <x-ui.table-head class="text-[10px] font-black uppercase tracking-widest text-muted-foreground/60">Check in</x-ui.table-head>
            <x-ui.table-head class="text-[10px] font-black uppercase tracking-widest text-muted-foreground/60">Check out</x-ui.table-head>
            <x-ui.table-head class="text-[10px] font-black uppercase tracking-widest text-muted-foreground/60">Hours</x-ui.table-head>
            <x-ui.table-head class="text-[10px] font-black uppercase tracking-widest text-muted-foreground/60">Status</x-ui.table-head>
            <x-ui.table-head class="text-right text-[10px] font-black uppercase tracking-widest text-muted-foreground/60">Actions</x-ui.table-head>
        </x-ui.table-row>
    </x-ui.table-header>
    <x-ui.table-body>
        @forelse($rows as $record)
            @php $r = is_array($record) ? (object) $record : $record; @endphp
            <x-ui.table-row class="border-b border-border/40 group hover:bg-primary/[0.02] transition-colors">
                <x-ui.table-cell>
                    <span class="text-sm font-black">{{ data_get($r, 'employee_name') ?? data_get($r, 'employee.name', '—') }}</span>
                </x-ui.table-cell>
                <x-ui.table-cell>
                    @php $ad = data_get($r, 'date') ?? data_get($r, 'attendance_date'); @endphp
                    <span class="text-xs font-bold">{{ $ad ? \Illuminate\Support\Carbon::parse($ad)->format('M j, Y') : '—' }}</span>
                </x-ui.table-cell>
                <x-ui.table-cell>
                    <span class="text-xs font-mono">{{ data_get($r, 'check_in') ?? data_get($r, 'clock_in', '—') }}</span>
                </x-ui.table-cell>
                <x-ui.table-cell>
                    <span class="text-xs font-mono">{{ data_get($r, 'check_out') ?? data_get($r, 'clock_out', '—') }}</span>
                </x-ui.table-cell>
                <x-ui.table-cell>
                    <span class="text-sm font-black tabular-nums">{{ data_get($r, 'hours') ?? data_get($r, 'total_hours', '—') }}</span>
                </x-ui.table-cell>
                <x-ui.table-cell>
                    @php
                        $st = strtolower((string) data_get($r, 'status', '—'));
                        $badgeVariant = match ($st) {
                            'active', 'paid', 'completed', 'sent', 'resolved', 'closed', 'approved', 'delivered', 'published', 'scheduled' => 'success',
                            'inactive', 'cancelled', 'void', 'failed', 'rejected', 'overdue', 'expired' => 'destructive',
                            'pending', 'draft', 'open', 'processing', 'partial', 'in_progress' => 'default',
                            default => 'outline',
                        };
                    @endphp
                    <x-ui.badge :variant="$badgeVariant" className="uppercase text-[9px] font-black tracking-[0.1em] px-2.5 py-1 rounded-lg shadow-sm">
                        {{ str_replace('_', ' ', $st) }}
                    </x-ui.badge>
                </x-ui.table-cell>
                <x-ui.table-cell class="text-right">
                    <div class="flex justify-end gap-1.5">
                        <x-ui.button variant="ghost" size="icon" type="button" className="size-8 text-muted-foreground hover:text-primary hover:bg-primary/10 rounded-xl border border-transparent hover:border-primary/20 transition-all" onclick="alert('Wire details when the module backend is ready.')">
                            <x-ui.icon name="eye" size="4" />
                        </x-ui.button>
                        <x-ui.button variant="ghost" size="icon" type="button" className="size-8 text-muted-foreground hover:text-primary hover:bg-primary/10 rounded-xl border border-transparent hover:border-primary/20 transition-all" onclick="alert('Wire edit when the module backend is ready.')">
                            <x-ui.icon name="edit-3" size="4" />
                        </x-ui.button>
                    </div>
                </x-ui.table-cell>
            </x-ui.table-row>

        @empty
            <x-ui.table-row>
                <x-ui.table-cell colspan="7" class="h-60 text-center">
                    <div class="flex flex-col items-center justify-center gap-3 opacity-40">
                        <x-ui.icon :name="$moduleIcon ?? 'package'" size="12" />
                        <p class="text-sm font-black uppercase tracking-[0.2em]">No attendance records found</p>
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
