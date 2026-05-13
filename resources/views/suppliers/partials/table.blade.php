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
            <x-ui.table-head class="text-[10px] font-black uppercase tracking-widest text-muted-foreground/60">Supplier</x-ui.table-head>
            <x-ui.table-head class="text-[10px] font-black uppercase tracking-widest text-muted-foreground/60">Contact</x-ui.table-head>
            <x-ui.table-head class="text-[10px] font-black uppercase tracking-widest text-muted-foreground/60">Email</x-ui.table-head>
            <x-ui.table-head class="text-[10px] font-black uppercase tracking-widest text-muted-foreground/60">Phone</x-ui.table-head>
            <x-ui.table-head class="text-[10px] font-black uppercase tracking-widest text-muted-foreground/60">Status</x-ui.table-head>
            <x-ui.table-head class="text-right text-[10px] font-black uppercase tracking-widest text-muted-foreground/60">Actions</x-ui.table-head>
        </x-ui.table-row>
    </x-ui.table-header>
    <x-ui.table-body>
        @forelse($rows as $record)
            @php $r = is_array($record) ? (object) $record : $record; @endphp
            <x-ui.table-row class="border-b border-border/40 group hover:bg-primary/[0.02] transition-colors">
                <x-ui.table-cell>
                    <div class="flex flex-col min-w-0">
                        <span class="text-sm font-black truncate">{{ data_get($r, 'name') ?? data_get($r, 'company', '—') }}</span>
                        <span class="text-[9px] font-mono text-muted-foreground/40">{{ data_get($r, 'code', '—') }}</span>
                    </div>
                </x-ui.table-cell>
                <x-ui.table-cell>
                    <span class="text-xs font-medium">{{ data_get($r, 'contact_name') ?? data_get($r, 'contact', '—') }}</span>
                </x-ui.table-cell>
                <x-ui.table-cell>
                    <span class="text-[11px] lowercase text-muted-foreground truncate block max-w-[180px]">{{ data_get($r, 'email', '—') }}</span>
                </x-ui.table-cell>
                <x-ui.table-cell>
                    <span class="text-xs font-mono">{{ data_get($r, 'phone') ?? data_get($r, 'mobile', '—') }}</span>
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
                <x-ui.table-cell colspan="6" class="h-60 text-center">
                    <div class="flex flex-col items-center justify-center gap-3 opacity-40">
                        <x-ui.icon :name="$moduleIcon ?? 'package'" size="12" />
                        <p class="text-sm font-black uppercase tracking-[0.2em]">No suppliers found</p>
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
