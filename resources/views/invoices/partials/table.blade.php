@php
    /** @var \Illuminate\Support\Collection|\Illuminate\Contracts\Pagination\Paginator|null $invoices */
    $invoices = $invoices ?? collect();
    if ($invoices instanceof \Illuminate\Pagination\AbstractPaginator) {
        $invoiceRows = $invoices->getCollection();
    } else {
        $invoiceRows = $invoices;
    }
@endphp

@if($invoices instanceof \Illuminate\Pagination\AbstractPaginator && $invoices->hasPages())
    <div class="p-4 border-b border-border/40 flex justify-end items-center">
        {{ $invoices->links() }}
    </div>
@endif

<x-ui.table>
    <x-ui.table-header class="bg-muted/20">
        <x-ui.table-row class="border-b border-border/60">
            <x-ui.table-head class="text-[10px] font-black uppercase tracking-widest text-muted-foreground/60 w-36">Invoice</x-ui.table-head>
            <x-ui.table-head class="text-[10px] font-black uppercase tracking-widest text-muted-foreground/60">Customer / Bill to</x-ui.table-head>
            <x-ui.table-head class="text-[10px] font-black uppercase tracking-widest text-muted-foreground/60 text-right">Amount</x-ui.table-head>
            <x-ui.table-head class="text-[10px] font-black uppercase tracking-widest text-muted-foreground/60">Due date</x-ui.table-head>
            <x-ui.table-head class="text-[10px] font-black uppercase tracking-widest text-muted-foreground/60">Status</x-ui.table-head>
            <x-ui.table-head class="text-right text-[10px] font-black uppercase tracking-widest text-muted-foreground/60">Actions</x-ui.table-head>
        </x-ui.table-row>
    </x-ui.table-header>
    <x-ui.table-body>
        @forelse($invoiceRows as $invoice)
            @php
                $inv = is_array($invoice) ? (object) $invoice : $invoice;
                $number = data_get($inv, 'number') ?? data_get($inv, 'invoice_number', '—');
                $customer = data_get($inv, 'customer_name')
                    ?? data_get($inv, 'bill_to')
                    ?? data_get($inv, 'customer.name')
                    ?? '—';
                $total = data_get($inv, 'total') ?? data_get($inv, 'amount', 0);
                $due = data_get($inv, 'due_at') ?? data_get($inv, 'due_date');
                $dueLabel = $due
                    ? \Illuminate\Support\Carbon::parse($due)->format('M j, Y')
                    : '—';
                $status = strtolower((string) (data_get($inv, 'status', 'draft')));
            @endphp
            <x-ui.table-row class="border-b border-border/40 group hover:bg-primary/[0.02] transition-colors">
                <x-ui.table-cell>
                    <div class="flex items-center gap-3">
                        <div class="size-10 rounded-xl bg-gradient-to-br from-primary/20 to-primary/5 border border-primary/10 flex items-center justify-center text-primary shadow-inner shrink-0">
                            <x-ui.icon name="finance" size="4" />
                        </div>
                        <div class="flex flex-col min-w-0">
                            <span class="text-sm font-black tracking-tight text-foreground truncate">{{ $number }}</span>
                            <span class="text-[9px] font-mono font-bold text-muted-foreground/40 truncate">
                                {{ data_get($inv, 'reference') ?? data_get($inv, 'po_number', 'Ref pending') }}
                            </span>
                        </div>
                    </div>
                </x-ui.table-cell>
                <x-ui.table-cell>
                    <div class="flex flex-col gap-1 max-w-[220px]">
                        <span class="text-sm font-bold text-foreground truncate">{{ $customer }}</span>
                        @if(data_get($inv, 'customer_email'))
                            <span class="text-[11px] font-medium text-muted-foreground/60 truncate lowercase">{{ data_get($inv, 'customer_email') }}</span>
                        @endif
                    </div>
                </x-ui.table-cell>
                <x-ui.table-cell class="text-right">
                    <span class="text-sm font-black tabular-nums text-foreground">${{ number_format((float) $total, 2) }}</span>
                    @php $currency = data_get($inv, 'currency'); @endphp
                    @if($currency && strtoupper((string) $currency) !== 'USD')
                        <span class="block text-[9px] font-bold text-muted-foreground/50 uppercase tracking-widest">{{ $currency }}</span>
                    @endif
                </x-ui.table-cell>
                <x-ui.table-cell>
                    <div class="flex items-center gap-2">
                        <x-ui.icon name="calendar" size="3.5" class="text-muted-foreground/40 shrink-0" />
                        <span class="text-xs font-bold text-foreground/90">{{ $dueLabel }}</span>
                    </div>
                </x-ui.table-cell>
                <x-ui.table-cell>
                    @php
                        $badgeVariant = match ($status) {
                            'paid', 'settled' => 'success',
                            'overdue', 'void', 'cancelled' => 'destructive',
                            'sent', 'open', 'partial' => 'default',
                            default => 'outline',
                        };
                    @endphp
                    <x-ui.badge :variant="$badgeVariant" className="uppercase text-[9px] font-black tracking-[0.1em] px-2.5 py-1 rounded-lg shadow-sm">
                        {{ $status }}
                    </x-ui.badge>
                </x-ui.table-cell>
                <x-ui.table-cell class="text-right">
                    <div class="flex justify-end gap-1.5">
                        <x-ui.button variant="ghost" size="icon" type="button" className="size-8 text-muted-foreground hover:text-primary hover:bg-primary/10 rounded-xl border border-transparent hover:border-primary/20 transition-all" onclick="alert('Wire view / PDF when InvoiceController is ready.')">
                            <x-ui.icon name="eye" size="4" />
                        </x-ui.button>
                        <x-ui.button variant="ghost" size="icon" type="button" className="size-8 text-muted-foreground hover:text-primary hover:bg-primary/10 rounded-xl border border-transparent hover:border-primary/20 transition-all" onclick="alert('Wire download when storage is ready.')">
                            <x-ui.icon name="download" size="4" />
                        </x-ui.button>
                    </div>
                </x-ui.table-cell>
            </x-ui.table-row>
        @empty
            <x-ui.table-row>
                <x-ui.table-cell colspan="6" class="h-60 text-center">
                    <div class="flex flex-col items-center justify-center gap-3 opacity-40">
                        <x-ui.icon :name="$moduleIcon ?? 'finance'" size="12" />
                        <p class="text-sm font-black uppercase tracking-[0.2em]">No invoice records found</p>
                        <p class="text-[10px] font-semibold text-muted-foreground uppercase tracking-widest max-w-md px-6">
                            Pass <span class="font-mono text-foreground/60">$invoices</span> (models, arrays, or a paginator) from the controller to fill this table.
                        </p>
                    </div>
                </x-ui.table-cell>
            </x-ui.table-row>
        @endforelse
    </x-ui.table-body>
</x-ui.table>

@if($invoices instanceof \Illuminate\Pagination\AbstractPaginator && $invoices->hasPages())
    <div class="p-4 border-t border-border/40 bg-muted/5 flex justify-end items-center rounded-b-3xl">
        {{ $invoices->links() }}
    </div>
@endif
