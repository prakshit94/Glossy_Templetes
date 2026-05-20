{{--
    Update return lifecycle status — POSTs to route('returns.status').
    Parent x-data must define: statusUpdateUrl, statusReturnNo, statusCurrent, statusPending, statusFinalized (bool).
    Optional: openReturnStatusModal(id, returnNo, current, isFinalized) on index.
--}}
<x-ui.modal id="return-status-modal" maxWidth="md">
    <div class="p-6 sm:p-8 space-y-6 max-h-[90vh] overflow-y-auto custom-scrollbar">
        <div class="flex items-start gap-4">
            <div class="size-12 shrink-0 rounded-2xl bg-primary/10 border border-primary/20 text-primary flex items-center justify-center shadow-inner">
                <x-ui.icon name="refresh-cw" size="6" />
            </div>
            <div class="min-w-0 flex-1">
                <h3 class="text-lg font-black text-foreground tracking-tight">Update return status</h3>
                <p class="text-xs text-muted-foreground mt-1 font-medium">
                    Return <span class="font-bold text-foreground tabular-nums" x-text="statusReturnNo"></span>
                </p>
            </div>
        </div>

        <form method="POST" :action="statusUpdateUrl" class="space-y-5" x-show="statusUpdateUrl" x-cloak>
            @csrf

            <div class="grid grid-cols-2 gap-3 rounded-2xl border border-border/60 bg-muted/20 p-4">
                <div>
                    <p class="text-[9px] font-black uppercase tracking-widest text-muted-foreground mb-1.5">Current</p>
                    <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-[10px] font-black uppercase tracking-wider ring-1 ring-black/5 dark:ring-white/10 bg-muted/80 text-foreground" x-text="statusCurrent"></span>
                </div>
                <div>
                    <p class="text-[9px] font-black uppercase tracking-widest text-muted-foreground mb-1.5">New status</p>
                    <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-[10px] font-black uppercase tracking-wider ring-1 ring-primary/20 bg-primary/10 text-primary" x-text="statusPending"></span>
                </div>
            </div>

            <div class="space-y-2">
                <label for="return-status-select" class="text-[10px] font-black uppercase tracking-widest text-muted-foreground/90 ml-0.5">Choose next status</label>
                <select id="return-status-select" name="status" x-model="statusPending"
                    class="w-full h-12 px-4 rounded-2xl border border-border bg-background/80 focus:bg-background focus:ring-2 focus:ring-primary/20 transition-all text-sm font-semibold appearance-none cursor-pointer">
                    <option value="requested">Requested — Awaiting review</option>
                    <option value="received">Received — Goods at warehouse</option>
                    <option value="inspected">Inspected — Quality checked</option>
                    <option value="completed">Completed — Restock &amp; refunds</option>
                    <option value="rejected">Rejected — Return denied</option>
                </select>
            </div>

            <div class="rounded-2xl border border-emerald-500/25 bg-emerald-500/[0.07] px-4 py-3 space-y-1" x-show="statusPending === 'completed'" x-cloak>
                <p class="text-[10px] font-black uppercase tracking-widest text-emerald-700 dark:text-emerald-400 flex items-center gap-2">
                    <x-ui.icon name="package" size="3.5" />
                    Inventory impact
                </p>
                <p class="text-xs text-emerald-800/90 dark:text-emerald-200/90 leading-relaxed">
                    Completing this return <strong>restores returned quantities to stock</strong> and may create <strong>refund requests</strong> against eligible payments (same as the detail page).
                </p>
            </div>

            <div class="rounded-2xl border border-amber-500/30 bg-amber-500/[0.06] px-4 py-3 space-y-1" x-show="statusPending === 'rejected'" x-cloak>
                <p class="text-[10px] font-black uppercase tracking-widest text-amber-800 dark:text-amber-300 flex items-center gap-2">
                    <x-ui.icon name="alert-triangle" size="3.5" />
                    Final state
                </p>
                <p class="text-xs text-amber-900/85 dark:text-amber-100/85 leading-relaxed">
                    Rejected closes the return <strong>without</strong> restocking or automatic refunds.
                </p>
            </div>

            <div class="flex flex-col-reverse sm:flex-row sm:items-center sm:justify-end gap-2 pt-2 border-t border-border/40">
                <x-ui.button type="button" variant="outline" size="sm" class="rounded-xl font-bold uppercase tracking-widest text-[10px] h-10 w-full sm:w-auto"
                    @click="$dispatch('close-modal', { name: 'return-status-modal' }); statusPending = statusCurrent;">
                    Cancel
                </x-ui.button>
                <x-ui.button type="submit" size="sm" class="rounded-xl font-bold uppercase tracking-widest text-[10px] h-10 w-full sm:w-auto shadow-lg shadow-primary/15"
                    x-bind:disabled="statusPending === statusCurrent">
                    <x-ui.icon name="check" size="3.5" class="mr-1.5" />
                    Apply status
                </x-ui.button>
            </div>
        </form>
    </div>
</x-ui.modal>
