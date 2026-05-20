@php
    $verificationOutcomes = \App\Models\DeliveryVerificationLog::OUTCOMES;
@endphp

{{--
    Delivery verification — POSTs to delivery.verification.store.
    Parent x-data must define: openDeliveryVerification(id), verificationFormUrl, deliveryId, deliveryNo, etc.
--}}
<x-ui.modal id="delivery-verification-modal" maxWidth="3xl">
    <div class="p-6 lg:p-8 max-h-[90vh] overflow-y-auto custom-scrollbar">
        <div class="flex items-center justify-between mb-6 sticky top-0 bg-card/95 backdrop-blur-md z-10 pb-4 border-b border-border/40">
            <div class="flex items-center gap-3">
                <div class="size-10 rounded-2xl bg-primary/10 border border-primary/20 text-primary flex items-center justify-center shadow-inner">
                    <x-ui.icon name="phone" size="5" />
                </div>
                <div>
                    <h3 class="text-sm font-black text-foreground uppercase tracking-widest">Delivery Verification</h3>
                    <p class="text-[10px] text-muted-foreground font-bold tracking-tight">
                        <span x-text="deliveryNo"></span> · Shipment <span x-text="shipmentNo"></span>
                    </p>
                </div>
            </div>
            <button type="button" @click="$dispatch('close-modal', { name: 'delivery-verification-modal' })" class="size-8 rounded-lg hover:bg-muted flex items-center justify-center transition-colors">
                <x-ui.icon name="x" size="4" />
            </button>
        </div>

        <div x-show="deliveryId" x-cloak>
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
                <x-ui.card class="p-5 border-border/50 bg-muted/5 rounded-2xl space-y-3">
                    <h4 class="text-[10px] font-black uppercase tracking-widest text-primary flex items-center gap-2">
                        <x-ui.icon name="package" size="3.5" /> Order & Dispatch
                    </h4>
                    <div class="space-y-2 text-xs">
                        <div class="flex justify-between gap-4">
                            <span class="text-muted-foreground font-bold uppercase tracking-wider text-[10px]">Order No</span>
                            <span class="font-mono font-black text-foreground" x-text="orderNo"></span>
                        </div>
                        <div class="flex justify-between gap-4">
                            <span class="text-muted-foreground font-bold uppercase tracking-wider text-[10px]">Order Date</span>
                            <span class="font-semibold text-foreground" x-text="orderDate"></span>
                        </div>
                        <div class="flex justify-between gap-4">
                            <span class="text-muted-foreground font-bold uppercase tracking-wider text-[10px]">Net Amount</span>
                            <span class="font-black text-primary" x-text="orderAmount"></span>
                        </div>
                        <div class="flex justify-between gap-4">
                            <span class="text-muted-foreground font-bold uppercase tracking-wider text-[10px]">Status</span>
                            <span class="font-black uppercase text-[10px] tracking-widest" x-text="deliveryStatus"></span>
                        </div>
                        <div class="flex justify-between gap-4">
                            <span class="text-muted-foreground font-bold uppercase tracking-wider text-[10px]">Driver</span>
                            <span class="font-semibold text-foreground" x-text="driverName"></span>
                        </div>
                        <div class="flex justify-between gap-4">
                            <span class="text-muted-foreground font-bold uppercase tracking-wider text-[10px]">Vehicle</span>
                            <span class="font-semibold text-foreground" x-text="vehicle"></span>
                        </div>
                    </div>
                </x-ui.card>

                <x-ui.card class="p-5 border-border/50 bg-muted/5 rounded-2xl space-y-3">
                    <h4 class="text-[10px] font-black uppercase tracking-widest text-primary flex items-center gap-2">
                        <x-ui.icon name="user" size="3.5" /> Customer Contact
                    </h4>
                    <p class="text-sm font-black text-foreground" x-text="partyName"></p>
                    <template x-if="phones.length > 0">
                        <div class="space-y-1.5">
                            <template x-for="(phone, idx) in phones" :key="idx">
                                <a :href="'tel:' + phone.value" class="flex items-center gap-2 text-xs font-bold text-primary hover:underline">
                                    <x-ui.icon name="phone" size="3.5" />
                                    <span x-text="phone.label + ': ' + phone.value"></span>
                                </a>
                            </template>
                        </div>
                    </template>
                    <template x-if="phones.length === 0">
                        <p class="text-xs text-muted-foreground italic">No phone numbers on file</p>
                    </template>
                    <template x-if="emails.length > 0">
                        <div class="space-y-1 pt-2 border-t border-border/40">
                            <template x-for="(email, idx) in emails" :key="'e-' + idx">
                                <p class="text-xs font-medium text-muted-foreground flex items-center gap-2">
                                    <x-ui.icon name="mail" size="3" />
                                    <span x-text="email"></span>
                                </p>
                            </template>
                        </div>
                    </template>
                </x-ui.card>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-8">
                <x-ui.card class="p-5 border-border/50 rounded-2xl">
                    <h5 class="text-[9px] font-black uppercase tracking-widest text-muted-foreground mb-3 flex items-center gap-1.5">
                        <x-ui.icon name="map-pin" size="3" /> Shipping Address
                    </h5>
                    <template x-if="shipping && shipping.line1">
                        <div class="space-y-2 text-xs">
                            <p class="font-bold text-foreground" x-text="shipping.label || 'Shipping'"></p>
                            <p class="text-muted-foreground font-medium" x-text="shipping.line1 + (shipping.line2 ? ', ' + shipping.line2 : '')"></p>
                            <div class="pt-2 border-t border-border/40 space-y-1.5">
                                <template x-for="row in [
                                    ['Village', shipping.village],
                                    ['Post Office', shipping.post_office],
                                    ['Taluka', shipping.taluka],
                                    ['District', shipping.district],
                                    ['State', shipping.state],
                                    ['Pincode', shipping.pincode],
                                    ['Country', shipping.country]
                                ]" :key="row[0]">
                                    <div class="flex justify-between gap-2" x-show="row[1]">
                                        <span class="text-[10px] uppercase tracking-widest text-muted-foreground font-bold" x-text="row[0]"></span>
                                        <span class="font-bold text-foreground text-right" x-text="row[1]"></span>
                                    </div>
                                </template>
                            </div>
                        </div>
                    </template>
                    <template x-if="(!shipping || !shipping.line1) && legacyShipping">
                        <p class="text-xs text-muted-foreground leading-relaxed" x-text="legacyShipping"></p>
                    </template>
                    <template x-if="(!shipping || !shipping.line1) && !legacyShipping">
                        <p class="text-xs text-muted-foreground italic">No shipping address</p>
                    </template>
                </x-ui.card>

                <x-ui.card class="p-5 border-border/50 rounded-2xl">
                    <h5 class="text-[9px] font-black uppercase tracking-widest text-muted-foreground mb-3 flex items-center gap-1.5">
                        <x-ui.icon name="file-text" size="3" /> Billing Address
                    </h5>
                    <template x-if="billing && billing.line1">
                        <div class="space-y-2 text-xs">
                            <p class="font-bold text-foreground" x-text="billing.label || 'Billing'"></p>
                            <p class="text-muted-foreground font-medium" x-text="billing.line1 + (billing.line2 ? ', ' + billing.line2 : '')"></p>
                            <div class="pt-2 border-t border-border/40 space-y-1.5">
                                <template x-for="row in [
                                    ['Village', billing.village],
                                    ['Post Office', billing.post_office],
                                    ['Taluka', billing.taluka],
                                    ['District', billing.district],
                                    ['State', billing.state],
                                    ['Pincode', billing.pincode],
                                    ['Country', billing.country]
                                ]" :key="row[0]">
                                    <div class="flex justify-between gap-2" x-show="row[1]">
                                        <span class="text-[10px] uppercase tracking-widest text-muted-foreground font-bold" x-text="row[0]"></span>
                                        <span class="font-bold text-foreground text-right" x-text="row[1]"></span>
                                    </div>
                                </template>
                            </div>
                        </div>
                    </template>
                    <template x-if="!billing || !billing.line1">
                        <p class="text-xs text-muted-foreground italic">No billing address</p>
                    </template>
                </x-ui.card>
            </div>

            <form method="POST" :action="verificationFormUrl" class="space-y-4 mb-8 p-5 rounded-2xl border border-primary/20 bg-primary/5" x-show="verificationFormUrl">
                @csrf
                <h4 class="text-[10px] font-black uppercase tracking-widest text-primary flex items-center gap-2">
                    <x-ui.icon name="phone" size="3.5" /> Log Verification Call
                </h4>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="space-y-2 md:col-span-2">
                        <label class="text-[10px] font-black uppercase tracking-widest text-muted-foreground/80 ml-1">Call Outcome</label>
                        <select name="outcome" x-model="outcome" required class="w-full h-11 px-4 rounded-xl border border-border bg-background/80 text-xs font-bold focus:ring-2 focus:ring-primary/20 outline-none">
                            <option value="" disabled selected>Select outcome...</option>
                            @foreach($verificationOutcomes as $value => $label)
                                <option value="{{ $value }}">{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="rounded-2xl border border-primary/25 bg-primary/[0.07] px-4 py-3 space-y-1" x-show="outcome === 'return_order'" x-cloak>
                        <p class="text-[10px] font-black uppercase tracking-widest text-primary flex items-center gap-2">
                            <x-ui.icon name="rotate-ccw" size="3.5" />
                            Return request
                        </p>
                        <p class="text-xs text-foreground/90 leading-relaxed">
                            Saving with <strong>Return Order</strong> creates a return on the <strong>Returns</strong> page — all order items at full quantity, status <strong>requested</strong>, same as <a href="{{ route('returns.create') }}" class="text-primary font-bold hover:underline">Create Return</a>.
                        </p>
                    </div>
                    <div class="space-y-2 md:col-span-2">
                        <label class="text-[10px] font-black uppercase tracking-widest text-muted-foreground/80 ml-1">Remarks</label>
                        <textarea name="remark" x-model="remark" rows="3" placeholder="Add call notes, customer response, or return reason..."
                            class="w-full px-4 py-3 rounded-xl border border-border bg-background/80 text-xs font-medium focus:ring-2 focus:ring-primary/20 outline-none resize-none"></textarea>
                    </div>
                    <div class="space-y-2">
                        <label class="text-[10px] font-black uppercase tracking-widest text-muted-foreground/80 ml-1">Next Follow-up (optional)</label>
                        <input type="datetime-local" name="follow_up_at" x-model="followUpAt"
                            class="w-full h-11 px-4 rounded-xl border border-border bg-background/80 text-xs font-medium focus:ring-2 focus:ring-primary/20 outline-none">
                    </div>
                </div>
                <div class="flex justify-end gap-3 pt-2">
                    <x-ui.button type="button" variant="outline" @click="$dispatch('close-modal', { name: 'delivery-verification-modal' })" class="rounded-xl font-black uppercase tracking-widest text-[10px]">Close</x-ui.button>
                    <x-ui.button type="submit" class="rounded-xl font-black uppercase tracking-widest text-[10px] shadow-lg shadow-primary/25" x-bind:disabled="!outcome">
                        <x-ui.icon name="check" size="3" class="mr-1.5" /> Save Verification
                    </x-ui.button>
                </div>
            </form>

            <div class="space-y-3">
                <h4 class="text-[10px] font-black uppercase tracking-widest text-muted-foreground flex items-center gap-2">
                    <x-ui.icon name="clock" size="3.5" /> Verification History
                </h4>
                <template x-if="history.length === 0">
                    <p class="text-xs text-muted-foreground italic py-6 text-center rounded-xl border border-dashed border-border/60">No verification calls logged yet.</p>
                </template>
                <div class="space-y-2 max-h-48 overflow-y-auto pr-1">
                    <template x-for="(entry, idx) in history" :key="idx">
                        <div class="p-4 rounded-xl border border-border/50 bg-muted/10">
                            <div class="flex flex-wrap items-center justify-between gap-2 mb-2">
                                <span class="text-[10px] font-black uppercase tracking-widest text-primary" x-text="entry.outcome_label"></span>
                                <span class="text-[9px] font-bold text-muted-foreground" x-text="entry.created_at"></span>
                            </div>
                            <p class="text-xs text-foreground/90 font-medium" x-show="entry.remark" x-text="entry.remark"></p>
                            <p class="text-[10px] text-muted-foreground mt-2" x-show="entry.follow_up_at">
                                <span class="font-black uppercase tracking-wider">Follow-up:</span>
                                <span x-text="entry.follow_up_at"></span>
                            </p>
                            <p class="text-[9px] text-muted-foreground/70 mt-1 uppercase tracking-wider font-bold" x-text="'By ' + (entry.user_name || 'Staff')"></p>
                        </div>
                    </template>
                </div>
            </div>
        </div>
    </div>
</x-ui.modal>
