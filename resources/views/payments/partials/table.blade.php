@php
    $payments = $payments ?? new \Illuminate\Pagination\LengthAwarePaginator([], 0, 15);
@endphp

@if($payments->hasPages())
    <div class="p-4 border-b border-border/40 bg-muted/10 flex justify-end items-center">
        {{ $payments->links() }}
    </div>
@endif

<div class="relative" x-data="{
    editingPayment: null,
    deletingPayment: null,
    openEdit(payment) {
        this.editingPayment = { ...payment, payment_date: payment.payment_date ? payment.payment_date.substring(0, 16) : '' };
    },
    openDelete(payment) {
        this.deletingPayment = payment;
    }
}">
    <div class="pointer-events-none absolute inset-x-8 top-0 h-px bg-gradient-to-r from-transparent via-primary/15 to-transparent hidden sm:block"></div>

    <x-ui.table>
        <x-ui.table-header class="bg-muted/30">
            <x-ui.table-row class="border-b border-border/60">
                <x-ui.table-head class="w-12 pl-5">
                    <input type="checkbox" x-model="allSelected" @change="toggleAll"
                        class="rounded-md border-border bg-background text-primary focus:ring-primary/25 shadow-sm">
                </x-ui.table-head>
                <x-ui.table-head class="text-[10px] font-black uppercase tracking-widest text-muted-foreground/70 whitespace-nowrap">Payment Reference</x-ui.table-head>
                <x-ui.table-head class="text-[10px] font-black uppercase tracking-widest text-muted-foreground/70 whitespace-nowrap">Associated Entity</x-ui.table-head>
                <x-ui.table-head class="text-[10px] font-black uppercase tracking-widest text-muted-foreground/70 whitespace-nowrap">Method & Gateway TXN</x-ui.table-head>
                <x-ui.table-head class="text-[10px] font-black uppercase tracking-widest text-muted-foreground/70 whitespace-nowrap text-center">Settlement Status</x-ui.table-head>
                <x-ui.table-head class="text-[10px] font-black uppercase tracking-widest text-muted-foreground/70 text-right whitespace-nowrap">Amount</x-ui.table-head>
                <x-ui.table-head class="text-right text-[10px] font-black uppercase tracking-widest text-muted-foreground/70 pr-5">Actions</x-ui.table-head>
            </x-ui.table-row>
        </x-ui.table-header>
        <x-ui.table-body>
            @forelse($payments as $payment)
                <x-ui.table-row
                    x-bind:class="selectedItems.includes({{ $payment->id }}) ? 'bg-primary/[0.06] ring-1 ring-inset ring-primary/15' : 'hover:bg-primary/[0.03]'"
                    class="border-b border-border/40 group/row transition-colors duration-200">
                    
                    <x-ui.table-cell class="pl-5 align-middle">
                        <input type="checkbox" name="payment_ids[]" value="{{ $payment->id }}" 
                            :checked="selectedItems.includes({{ $payment->id }})" 
                            @change="toggleItem({{ $payment->id }})"
                            class="rounded-md border-border bg-background text-primary focus:ring-primary/25 shadow-sm">
                    </x-ui.table-cell>

                    <x-ui.table-cell class="align-middle">
                        <div class="flex items-center gap-4 py-0.5">
                            <div class="shrink-0">
                                <div class="size-11 rounded-2xl bg-gradient-to-br from-blue-500/25 via-blue-500/10 to-blue-500/5 border border-blue-500/15 flex items-center justify-center text-blue-500 shadow-inner ring-1 ring-blue-500/10 group-hover/row:scale-[1.02] transition-transform duration-300">
                                    <x-ui.icon name="credit-card" size="4.5" />
                                </div>
                            </div>
                            <div class="flex flex-col min-w-0">
                                <div class="flex items-center gap-2">
                                    <a href="{{ route('payments.show', $payment->id) }}" class="text-sm font-black tracking-tight text-foreground uppercase truncate hover:text-primary transition-colors">{{ $payment->payment_no }}</a>
                                    <span class="text-[9px] font-black uppercase px-1.5 py-0.5 rounded bg-muted text-muted-foreground border border-border/40 whitespace-nowrap">ID: {{ $payment->id }}</span>
                                </div>
                                <span class="text-[10px] font-bold text-muted-foreground/65 tabular-nums mt-0.5">
                                    {{ optional($payment->payment_date)->format('M d, Y') }} at {{ optional($payment->payment_date)->format('h:i A') }}
                                </span>
                            </div>
                        </div>
                    </x-ui.table-cell>

                    <x-ui.table-cell class="align-middle">
                        <div class="space-y-1">
                            @if($payment->order)
                                <div class="flex items-center gap-1.5">
                                    <span class="text-[10px] font-black uppercase px-1.5 py-0.5 rounded bg-primary/10 text-primary border border-primary/20">Order</span>
                                    <a href="{{ route('orders.show', $payment->order->id) }}" class="text-xs font-bold text-foreground/80 hover:text-primary truncate">{{ $payment->order->order_no }}</a>
                                </div>
                                <div class="text-[11px] font-bold text-muted-foreground truncate max-w-[180px]">
                                    {{ $payment->order->party?->name ?? 'Internal Node' }}
                                </div>
                            @endif
                            @if($payment->invoice)
                                <div class="flex items-center gap-1.5">
                                    <span class="text-[10px] font-black uppercase px-1.5 py-0.5 rounded bg-purple-500/10 text-purple-600 border border-purple-500/20">Invoice</span>
                                    <span class="text-[11px] font-mono text-muted-foreground">{{ $payment->invoice->invoice_no }}</span>
                                </div>
                            @endif
                        </div>
                    </x-ui.table-cell>

                    <x-ui.table-cell class="align-middle">
                        <div class="space-y-1">
                            <div class="flex items-center gap-2">
                                <span class="text-[11px] font-black text-foreground">{{ $payment->payment_method ?: 'Standard' }}</span>
                            </div>
                            <span class="text-[10px] font-mono font-bold bg-muted/60 px-2 py-0.5 rounded-lg text-muted-foreground inline-block">
                                {{ $payment->transaction_id ?: 'TXN-PENDING' }}
                            </span>
                        </div>
                    </x-ui.table-cell>

                    <x-ui.table-cell class="align-middle text-center">
                        @php
                            $statusColor = match($payment->status) {
                                'completed' => 'emerald',
                                'pending' => 'amber',
                                'failed' => 'red',
                                'refunded' => 'orange',
                                default => 'primary'
                            };
                        @endphp
                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-xl border border-{{ $statusColor }}-500/20 bg-{{ $statusColor }}-500/10 text-{{ $statusColor }}-600 text-[9px] font-black uppercase tracking-widest">
                            {{ $payment->status }}
                        </span>
                    </x-ui.table-cell>

                    <x-ui.table-cell class="text-right align-middle">
                        <span class="text-sm font-black text-foreground tracking-tight tabular-nums">₹{{ number_format((float) $payment->amount, 2) }}</span>
                    </x-ui.table-cell>

                    <x-ui.table-cell class="text-right align-middle pr-5">
                        <div class="flex justify-end gap-1">
                            <a href="{{ route('payments.show', $payment->id) }}" title="View Settlement Dossier">
                                <x-ui.button variant="ghost" size="icon" className="size-9 text-muted-foreground hover:text-primary hover:bg-primary/10 rounded-xl border border-transparent hover:border-primary/20 transition-all">
                                    <x-ui.icon name="eye" size="4" />
                                </x-ui.button>
                            </a>
                            <button type="button" @click="openEdit({{ json_encode($payment) }})" title="Update Payment Details">
                                <x-ui.button variant="ghost" size="icon" className="size-9 text-muted-foreground hover:text-blue-500 hover:bg-blue-500/10 rounded-xl border border-transparent hover:border-blue-500/20 transition-all">
                                    <x-ui.icon name="edit" size="4" />
                                </x-ui.button>
                            </button>
                            <button type="button" @click="openDelete({{ json_encode($payment) }})" title="Void Transaction">
                                <x-ui.button variant="ghost" size="icon" className="size-9 text-muted-foreground hover:text-red-500 hover:bg-red-500/10 rounded-xl border border-transparent hover:border-red-500/20 transition-all">
                                    <x-ui.icon name="trash" size="4" />
                                </x-ui.button>
                            </button>
                        </div>
                    </x-ui.table-cell>
                </x-ui.table-row>
            @empty
                <x-ui.table-row>
                    <x-ui.table-cell colspan="7" class="h-72 text-center align-middle p-0">
                        <div class="flex flex-col items-center justify-center gap-5 py-12 px-6">
                            <div class="size-24 rounded-3xl bg-gradient-to-br from-primary/25 via-primary/8 to-transparent border border-primary/20 flex items-center justify-center text-primary shadow-inner ring-1 ring-primary/10">
                                <x-ui.icon name="credit-card" size="12" />
                            </div>
                            <div class="space-y-2 max-w-md text-center">
                                <p class="text-sm font-black uppercase tracking-[0.2em] text-foreground">No payments recorded</p>
                                <p class="text-[11px] text-muted-foreground font-medium leading-relaxed">Adjust filters or record a new transaction to begin tracking gateway settlements.</p>
                            </div>
                        </div>
                    </x-ui.table-cell>
                </x-ui.table-row>
            @endforelse
        </x-ui.table-body>
    </x-ui.table>

    <!-- Edit Payment Modal -->
    <template x-if="editingPayment">
        <div class="fixed inset-0 z-[250] flex items-center justify-center bg-black/60 backdrop-blur-sm p-4 text-left">
            <div @click.away="editingPayment = null" class="bg-card border border-border/60 rounded-3xl shadow-2xl max-w-xl w-full overflow-hidden flex flex-col animate-in fade-in zoom-in-95 duration-200">
                <div class="p-6 border-b border-border/40 bg-muted/10 flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <div class="size-10 rounded-2xl bg-blue-500/10 text-blue-500 flex items-center justify-center border border-blue-500/20">
                            <x-ui.icon name="edit" size="5" />
                        </div>
                        <div>
                            <h3 class="text-base font-black tracking-tight text-foreground" x-text="'Update Payment #' + editingPayment.payment_no"></h3>
                            <p class="text-[10px] text-muted-foreground font-bold uppercase tracking-widest">Gateway Reconciliation · Audit Ledger</p>
                        </div>
                    </div>
                    <button type="button" @click="editingPayment = null" class="size-8 rounded-xl bg-background border border-border/60 hover:bg-muted text-muted-foreground hover:text-foreground flex items-center justify-center transition-all">
                        <x-ui.icon name="x" size="4" />
                    </button>
                </div>
                <form :action="'/payments/' + editingPayment.id" method="POST" class="p-6 space-y-5">
                    @csrf
                    @method('PUT')
                    
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-black uppercase tracking-widest text-muted-foreground mb-2">Payment Amount (₹)</label>
                            <input type="number" step="0.01" name="amount" :value="editingPayment.amount" required class="w-full h-11 px-4 rounded-xl border border-border bg-background text-sm font-black focus:ring-2 focus:ring-primary/20 outline-none">
                        </div>
                        <div>
                            <label class="block text-xs font-black uppercase tracking-widest text-muted-foreground mb-2">Payment Method</label>
                            <select name="payment_method" :value="editingPayment.payment_method" required class="w-full h-11 px-4 rounded-xl border border-border bg-background text-sm font-bold focus:ring-2 focus:ring-primary/20 outline-none">
                                <option value="UPI">UPI / QR Code</option>
                                <option value="Cash">Cash</option>
                                <option value="Card">Credit / Debit Card</option>
                                <option value="Net Banking">Net Banking</option>
                                <option value="Wallet">Wallet / Others</option>
                            </select>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-black uppercase tracking-widest text-muted-foreground mb-2">Transaction ID / Reference</label>
                            <input type="text" name="transaction_id" :value="editingPayment.transaction_id" class="w-full h-11 px-4 rounded-xl border border-border bg-background text-xs font-bold focus:ring-2 focus:ring-primary/20 outline-none">
                        </div>
                        <div>
                            <label class="block text-xs font-black uppercase tracking-widest text-muted-foreground mb-2">Payment Date</label>
                            <input type="datetime-local" name="payment_date" :value="editingPayment.payment_date" required class="w-full h-11 px-4 rounded-xl border border-border bg-background text-xs font-bold focus:ring-2 focus:ring-primary/20 outline-none">
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-black uppercase tracking-widest text-muted-foreground mb-2">Settlement Status</label>
                        <select name="status" :value="editingPayment.status" required class="w-full h-11 px-4 rounded-xl border border-border bg-background text-sm font-bold focus:ring-2 focus:ring-primary/20 outline-none">
                            <option value="completed">Completed</option>
                            <option value="pending">Pending</option>
                            <option value="failed">Failed</option>
                            <option value="refunded">Refunded</option>
                        </select>
                    </div>

                    <div class="pt-4 border-t border-border/40 flex items-center justify-end gap-3">
                        <button type="button" @click="editingPayment = null" class="h-11 px-6 rounded-2xl bg-muted hover:bg-muted/80 text-xs font-black uppercase tracking-widest text-muted-foreground transition-all">
                            Cancel
                        </button>
                        <button type="submit" class="h-11 px-8 rounded-2xl bg-blue-500 text-white text-xs font-black uppercase tracking-widest shadow-lg shadow-blue-500/20 hover:shadow-blue-500/40 hover:-translate-y-0.5 transition-all duration-300">
                            Save Changes
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </template>

    <!-- Delete Confirmation Modal -->
    <template x-if="deletingPayment">
        <div class="fixed inset-0 z-[250] flex items-center justify-center bg-black/60 backdrop-blur-sm p-4 text-left">
            <div @click.away="deletingPayment = null" class="bg-card border border-border/60 rounded-3xl shadow-2xl max-w-md w-full overflow-hidden flex flex-col animate-in fade-in zoom-in-95 duration-200">
                <div class="p-6 border-b border-border/40 bg-red-500/10 flex items-center gap-4">
                    <div class="size-12 rounded-2xl bg-red-500/20 text-red-500 flex items-center justify-center border border-red-500/30 shrink-0">
                        <x-ui.icon name="alert-triangle" size="6" />
                    </div>
                    <div>
                        <h3 class="text-base font-black tracking-tight text-foreground">Void Transaction?</h3>
                        <p class="text-[10px] text-muted-foreground font-bold uppercase tracking-widest mt-0.5">Permanent Ledger Removal</p>
                    </div>
                </div>
                <div class="p-6 space-y-3">
                    <p class="text-xs text-muted-foreground leading-relaxed">
                        Are you certain you wish to void payment <span class="font-bold text-foreground" x-text="'#' + deletingPayment.payment_no"></span> for <span class="font-bold text-foreground" x-text="'₹' + deletingPayment.amount"></span>? This will permanently remove the record from settlement audits.
                    </p>
                </div>
                <form :action="'/payments/' + deletingPayment.id" method="POST" class="p-6 pt-0 flex items-center justify-end gap-3">
                    @csrf
                    @method('DELETE')
                    <button type="button" @click="deletingPayment = null" class="h-11 px-6 rounded-2xl bg-muted hover:bg-muted/80 text-xs font-black uppercase tracking-widest text-muted-foreground transition-all">
                        Cancel
                    </button>
                    <button type="submit" class="h-11 px-8 rounded-2xl bg-red-500 text-white text-xs font-black uppercase tracking-widest shadow-lg shadow-red-500/20 hover:shadow-red-500/40 hover:-translate-y-0.5 transition-all duration-300">
                        Void Payment
                    </button>
                </form>
            </div>
        </div>
    </template>
</div>

@if($payments->hasPages())
    <div class="p-4 border-t border-border/40 bg-muted/10 flex justify-end items-center rounded-b-3xl">
        {{ $payments->links() }}
    </div>
@endif
