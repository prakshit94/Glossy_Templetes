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
            <x-ui.table-head class="text-[10px] font-black uppercase tracking-widest text-muted-foreground/60">Department</x-ui.table-head>
            <x-ui.table-head class="text-[10px] font-black uppercase tracking-widest text-muted-foreground/60">Role</x-ui.table-head>
            <x-ui.table-head class="text-[10px] font-black uppercase tracking-widest text-muted-foreground/60">Email</x-ui.table-head>
            <x-ui.table-head class="text-[10px] font-black uppercase tracking-widest text-muted-foreground/60">Hired</x-ui.table-head>
            <x-ui.table-head class="text-[10px] font-black uppercase tracking-widest text-muted-foreground/60">Status</x-ui.table-head>
            <x-ui.table-head class="text-right text-[10px] font-black uppercase tracking-widest text-muted-foreground/60">Actions</x-ui.table-head>
        </x-ui.table-row>
    </x-ui.table-header>
    <x-ui.table-body>
        @forelse($rows as $record)
            @php $r = is_array($record) ? (object) $record : $record; @endphp
            <x-ui.table-row class="border-b border-border/40 group hover:bg-primary/[0.02] transition-colors">
                <x-ui.table-cell>
                    <div class="flex items-center gap-3">
                        <div class="size-10 rounded-xl bg-gradient-to-br from-primary/20 to-primary/5 border border-primary/10 flex items-center justify-center text-primary shadow-inner shrink-0 font-black">
                            {{ strtoupper(substr((string) data_get($r, 'name', '?'), 0, 1)) }}
                        </div>
                        <span class="text-sm font-black">{{ data_get($r, 'name', '—') }}</span>
                    </div>
                </x-ui.table-cell>
                <x-ui.table-cell>
                    <span class="text-xs font-bold">{{ data_get($r, 'department_name') ?? data_get($r, 'department.name', '—') }}</span>
                </x-ui.table-cell>
                <x-ui.table-cell>
                    <span class="text-xs text-muted-foreground">{{ data_get($r, 'role') ?? data_get($r, 'job_title', '—') }}</span>
                </x-ui.table-cell>
                <x-ui.table-cell>
                    <span class="text-[11px] lowercase text-muted-foreground truncate block max-w-[180px]">{{ data_get($r, 'email', '—') }}</span>
                </x-ui.table-cell>
                <x-ui.table-cell>
                    @php $h = data_get($r, 'hired_at') ?? data_get($r, 'hire_date'); @endphp
                    <span class="text-xs font-bold">{{ $h ? \Illuminate\Support\Carbon::parse($h)->format('M j, Y') : '—' }}</span>
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
                        <p class="text-sm font-black uppercase tracking-[0.2em]">No employees found</p>
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
