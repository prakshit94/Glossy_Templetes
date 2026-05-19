@php
    $invoices = $invoices ?? new \Illuminate\Pagination\LengthAwarePaginator([], 0, 15);
@endphp

@if($invoices->hasPages())
    <div class="p-4 border-b border-border/40 bg-muted/10 flex justify-end items-center">
        {{ $invoices->links() }}
    </div>
@endif

<div class="relative">
    <div class="pointer-events-none absolute inset-x-8 top-0 h-px bg-gradient-to-r from-transparent via-primary/15 to-transparent hidden sm:block"></div>

    <x-ui.table>
        <x-ui.table-header class="bg-muted/30">
            <x-ui.table-row class="border-b border-border/60">
                <x-ui.table-head class="w-12 pl-5">
                    <input type="checkbox" x-model="allSelected" @change="toggleAll"
                        class="rounded-md border-border bg-background text-primary focus:ring-primary/25 shadow-sm">
                </x-ui.table-head>
                <x-ui.table-head class="text-[10px] font-black uppercase tracking-widest text-muted-foreground/70 whitespace-nowrap">Invoice Identity</x-ui.table-head>
                <x-ui.table-head class="text-[10px] font-black uppercase tracking-widest text-muted-foreground/70 whitespace-nowrap">Associated Order</x-ui.table-head>
                <x-ui.table-head class="text-[10px] font-black uppercase tracking-widest text-muted-foreground/70 whitespace-nowrap">Warehouse Node</x-ui.table-head>
                <x-ui.table-head class="text-[10px] font-black uppercase tracking-widest text-muted-foreground/70 whitespace-nowrap">Associated Party</x-ui.table-head>
                <x-ui.table-head class="text-[10px] font-black uppercase tracking-widest text-muted-foreground/70 whitespace-nowrap text-center">Payment Status</x-ui.table-head>
                <x-ui.table-head class="text-[10px] font-black uppercase tracking-widest text-muted-foreground/70 text-right whitespace-nowrap">Financial Total</x-ui.table-head>
                <x-ui.table-head class="text-right text-[10px] font-black uppercase tracking-widest text-muted-foreground/70 pr-5">Actions</x-ui.table-head>
            </x-ui.table-row>
        </x-ui.table-header>
        <x-ui.table-body>
            @forelse($invoices as $invoice)
                <x-ui.table-row
                    x-bind:class="selectedItems.includes({{ $invoice->id }}) ? 'bg-primary/[0.06] ring-1 ring-inset ring-primary/15' : 'hover:bg-primary/[0.03]'"
                    class="border-b border-border/40 group/row transition-colors duration-200">
                    
                    <x-ui.table-cell class="pl-5 align-middle">
                        <input type="checkbox" name="invoice_ids[]" value="{{ $invoice->id }}" 
                            :checked="selectedItems.includes({{ $invoice->id }})" 
                            @change="toggleItem({{ $invoice->id }})"
                            class="rounded-md border-border bg-background text-primary focus:ring-primary/25 shadow-sm">
                    </x-ui.table-cell>

                    <x-ui.table-cell class="align-middle">
                        <div class="flex items-center gap-4 py-0.5">
                            <div class="shrink-0">
                                <div class="size-11 rounded-2xl bg-gradient-to-br from-blue-500/25 to-blue-500/5 border border-blue-500/15 flex items-center justify-center text-blue-500 shadow-inner ring-1 ring-blue-500/10 group-hover/row:scale-[1.02] transition-transform duration-300">
                                    <x-ui.icon name="file-text" size="4.5" />
                                </div>
                            </div>
                            <div class="flex flex-col min-w-0">
                                <div class="flex items-center gap-2">
                                    <span x-data="{ copied: false }" @click.prevent.stop="navigator.clipboard.writeText('{{ $invoice->invoice_no }}'); copied = true; setTimeout(() => copied = false, 2000)" class="cursor-pointer text-sm font-black tracking-tight text-foreground uppercase truncate hover:text-blue-500 transition-colors flex items-center gap-1.5 relative group/copy w-max">
                                        {{ $invoice->invoice_no }}
                                        <x-ui.icon name="copy" size="3" class="opacity-0 group-hover/copy:opacity-100 transition-opacity text-blue-500" />
                                        <span x-show="copied" x-cloak class="absolute -top-6 left-0 bg-foreground text-background text-[9px] font-bold px-2 py-0.5 rounded shadow-lg pointer-events-none normal-case tracking-normal">Copied!</span>
                                    </span>
                                    <span class="text-[9px] font-black uppercase px-1.5 py-0.5 rounded bg-muted text-muted-foreground border border-border/40 whitespace-nowrap">ID: {{ $invoice->id }}</span>
                                </div>
                                <span class="text-[10px] font-bold text-muted-foreground/65 tabular-nums">
                                    {{ optional($invoice->invoice_date)->format('M d, Y') }} at {{ optional($invoice->invoice_date)->format('h:i A') }}
                                </span>
                            </div>
                        </div>
                    </x-ui.table-cell>

                    <x-ui.table-cell class="align-middle">
                        <div class="flex items-center gap-2">
                            <div class="size-7 rounded-lg bg-primary/10 flex items-center justify-center text-primary">
                                <x-ui.icon name="package" size="3" />
                            </div>
                            <div class="flex flex-col">
                                @if($invoice->order)
                                    <span x-data="{ copied: false }" @click.prevent.stop="navigator.clipboard.writeText('{{ $invoice->order->order_no }}'); copied = true; setTimeout(() => copied = false, 2000)" class="cursor-pointer text-[11px] font-black text-foreground/80 hover:text-primary transition-colors flex items-center gap-1 relative group/copy w-max">
                                        {{ $invoice->order->order_no }}
                                        <x-ui.icon name="copy" size="2.5" class="opacity-0 group-hover/copy:opacity-100 transition-opacity text-primary" />
                                        <span x-show="copied" x-cloak class="absolute -top-6 left-0 bg-foreground text-background text-[9px] font-bold px-2 py-0.5 rounded shadow-lg pointer-events-none normal-case tracking-normal">Copied!</span>
                                    </span>
                                @else
                                    <span class="text-[11px] font-black text-foreground/80">N/A</span>
                                @endif
                                <span class="text-[9px] font-bold text-muted-foreground/60">{{ optional($invoice->order?->order_date)->format('M d, Y') }}</span>
                            </div>
                        </div>
                    </x-ui.table-cell>

                    <x-ui.table-cell class="align-middle">
                        <div class="flex items-center gap-2">
                            <div class="size-7 rounded-lg bg-emerald-500/10 flex items-center justify-center text-emerald-600">
                                <x-ui.icon name="database" size="3" />
                            </div>
                            <span class="text-[11px] font-bold text-foreground/80 truncate max-w-[120px]">{{ $invoice->order?->warehouse?->name ?? 'Main Hub' }}</span>
                        </div>
                    </x-ui.table-cell>

                    <x-ui.table-cell class="align-middle">
                        <div class="flex items-center gap-2">
                            <div class="size-7 rounded-lg bg-muted/40 flex items-center justify-center text-muted-foreground">
                                <x-ui.icon name="user" size="3" />
                            </div>
                            <span class="text-[11px] font-bold text-foreground/80 truncate max-w-[140px]">{{ $invoice->order?->party?->name ?? 'Internal Node' }}</span>
                        </div>
                    </x-ui.table-cell>

                    <x-ui.table-cell class="align-middle text-center">
                        @php
                            $statusColor = match($invoice->status) {
                                'paid' => 'emerald',
                                'partially_paid' => 'amber',
                                'unpaid' => 'orange',
                                'cancelled' => 'red',
                                default => 'primary'
                            };
                        @endphp
                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-xl border border-{{ $statusColor }}-500/20 bg-{{ $statusColor }}-500/10 text-{{ $statusColor }}-600 text-[9px] font-black uppercase tracking-widest">
                            {{ str_replace('_', ' ', $invoice->status) }}
                        </span>
                    </x-ui.table-cell>

                    <x-ui.table-cell class="text-right align-middle">
                        <div class="flex flex-col items-end gap-1">
                            <span class="text-sm font-black text-foreground tracking-tight">₹{{ number_format((float) $invoice->net_amount, 2) }}</span>
                            <div class="flex items-center gap-2 text-[10px] font-bold mt-0.5">
                                <span class="text-emerald-500 bg-emerald-500/10 px-1.5 py-0.5 rounded uppercase tracking-wider border border-emerald-500/20">Paid: ₹{{ number_format((float) $invoice->paid_amount, 2) }}</span>
                                <span class="text-orange-500 bg-orange-500/10 px-1.5 py-0.5 rounded uppercase tracking-wider border border-orange-500/20">Due: ₹{{ number_format((float) $invoice->due_amount, 2) }}</span>
                            </div>
                        </div>
                    </x-ui.table-cell>

                    <x-ui.table-cell class="text-right align-middle pr-5">
                        <div class="flex justify-end gap-1">
                            @if($invoice->status !== 'paid' && $invoice->status !== 'cancelled' && $invoice->order_id)
                                <x-ui.button variant="ghost" size="icon" 
                                    @click="$dispatch('open-modal', { name: 'record-payment-modal', data: { order_id: '{{ $invoice->order_id }}', order_no: '{{ $invoice->order?->order_no }}', invoice_no: '{{ $invoice->invoice_no }}', total_amount: {{ $invoice->net_amount }}, paid_amount: {{ $invoice->paid_amount }}, due_amount: {{ $invoice->due_amount }} } })"
                                    title="Record Payment"
                                    className="size-9 text-muted-foreground hover:text-emerald-500 hover:bg-emerald-500/10 rounded-xl border border-transparent hover:border-emerald-500/20 transition-all">
                                    <x-ui.icon name="credit-card" size="4" />
                                </x-ui.button>
                            @endif
                            <a href="{{ route('orders.show', $invoice->order_id) }}" title="Visual Order Dossier">
                                <x-ui.button variant="ghost" size="icon" className="size-9 text-muted-foreground hover:text-primary hover:bg-primary/10 rounded-xl border border-transparent hover:border-primary/20 transition-all">
                                    <x-ui.icon name="eye" size="4" />
                                </x-ui.button>
                            </a>
                            <a href="{{ route('orders.invoice-pdf', $invoice->order_id) }}" target="_blank" title="Download Invoice PDF">
                                <x-ui.button variant="ghost" size="icon" className="size-9 text-muted-foreground hover:text-blue-500 hover:bg-blue-500/10 rounded-xl border border-transparent hover:border-blue-500/20 transition-all">
                                    <x-ui.icon name="file-text" size="4" />
                                </x-ui.button>
                            </a>
                        </div>
                    </x-ui.table-cell>
                </x-ui.table-row>
            @empty
                <x-ui.table-row>
                    <x-ui.table-cell colspan="8" class="h-72 text-center align-middle p-0">
                        <div class="flex flex-col items-center justify-center gap-5 py-12 px-6">
                            <div class="size-24 rounded-3xl bg-gradient-to-br from-primary/25 via-primary/8 to-transparent border border-primary/20 flex items-center justify-center text-primary shadow-inner ring-1 ring-primary/10">
                                <x-ui.icon name="file-text" size="12" />
                            </div>
                            <div class="space-y-2 max-w-md text-center">
                                <p class="text-sm font-black uppercase tracking-[0.2em] text-foreground">No invoices in ledger</p>
                                <p class="text-[11px] text-muted-foreground font-medium leading-relaxed">Adjust your filters or search parameters to locate generated invoices.</p>
                            </div>
                            <x-ui.button variant="outline" size="sm" onclick="location.reload()" class="rounded-xl border-border/60 font-bold uppercase tracking-widest text-[10px] h-10 px-6">
                                Refresh Ledger
                            </x-ui.button>
                        </div>
                    </x-ui.table-cell>
                </x-ui.table-row>
            @endforelse
        </x-ui.table-body>
    </x-ui.table>
</div>

@if($invoices->hasPages())
    <div class="p-4 border-t border-border/40 bg-muted/10 flex justify-end items-center rounded-b-3xl">
        {{ $invoices->links() }}
    </div>
@endif
