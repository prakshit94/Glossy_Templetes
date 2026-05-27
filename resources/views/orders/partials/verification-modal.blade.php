@php
    $verificationOutcomes = \App\Models\OrderVerificationLog::OUTCOMES;
@endphp

<x-ui.modal id="order-verification-modal" maxWidth="lg">
    <div class="p-6 lg:p-8 max-h-[90vh] overflow-y-auto custom-scrollbar" x-data="{ outcome: '' }" x-on:open-modal.window="if ($event.detail.name === 'order-verification-modal') { outcome = $event.detail.outcome || ''; }">
        <div class="flex items-center justify-between mb-6 sticky top-0 bg-card/95 backdrop-blur-md z-10 pb-4 border-b border-border/40">
            <div class="flex items-center gap-3">
                <div class="size-10 rounded-2xl bg-primary/10 border border-primary/20 text-primary flex items-center justify-center shadow-inner">
                    <x-ui.icon name="phone" size="5" />
                </div>
                <div>
                    <h3 class="text-sm font-black text-foreground uppercase tracking-widest">Order Verification</h3>
                    <p class="text-[10px] text-muted-foreground font-bold tracking-tight">
                        Order {{ $order->order_no }}
                    </p>
                </div>
            </div>
            <button type="button" @click="$dispatch('close-modal', { name: 'order-verification-modal' })" class="size-8 rounded-lg hover:bg-muted flex items-center justify-center transition-colors">
                <x-ui.icon name="x" size="4" />
            </button>
        </div>

        <form method="POST" action="{{ route('orders.verification.store', $order) }}" class="space-y-4 mb-4 p-5 rounded-2xl border border-primary/20 bg-primary/5">
            @csrf
            <h4 class="text-[10px] font-black uppercase tracking-widest text-primary flex items-center gap-2">
                <x-ui.icon name="phone" size="3.5" /> Log Verification Call
            </h4>
            <div class="grid grid-cols-1 gap-4">
                <div class="space-y-2">
                    <label class="text-[10px] font-black uppercase tracking-widest text-muted-foreground/80 ml-1">Call Outcome</label>
                    <select name="outcome" x-model="outcome" required class="w-full h-11 px-4 rounded-xl border border-border bg-background/80 text-xs font-bold focus:ring-2 focus:ring-primary/20 outline-none">
                        <option value="" disabled selected>Select outcome...</option>
                        @foreach($verificationOutcomes as $value => $label)
                            <option value="{{ $value }}">{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                
                <div class="space-y-2">
                    <label class="text-[10px] font-black uppercase tracking-widest text-muted-foreground/80 ml-1">Remarks</label>
                    <textarea name="remark" rows="3" placeholder="Add call notes, customer response..."
                        class="w-full px-4 py-3 rounded-xl border border-border bg-background/80 text-xs font-medium focus:ring-2 focus:ring-primary/20 outline-none resize-none"></textarea>
                </div>
                
                <div class="space-y-2">
                    <label class="text-[10px] font-black uppercase tracking-widest text-muted-foreground/80 ml-1">Next Follow-up (optional)</label>
                    <input type="datetime-local" name="follow_up_at"
                        class="w-full h-11 px-4 rounded-xl border border-border bg-background/80 text-xs font-medium focus:ring-2 focus:ring-primary/20 outline-none">
                </div>
            </div>
            
            <div class="flex justify-end gap-3 pt-2">
                <x-ui.button type="button" variant="outline" @click="$dispatch('close-modal', { name: 'order-verification-modal' })" class="rounded-xl font-black uppercase tracking-widest text-[10px]">Close</x-ui.button>
                <x-ui.button type="submit" class="rounded-xl font-black uppercase tracking-widest text-[10px] shadow-lg shadow-primary/25" x-bind:disabled="!outcome">
                    <x-ui.icon name="check" size="3" class="mr-1.5" /> Save Verification
                </x-ui.button>
            </div>
        </form>
    </div>
</x-ui.modal>
