<x-layouts.app pageTitle="Create Refund Request">
    <div class="p-6 lg:p-10">
        <div class="max-w-5xl mx-auto">
            <x-ui.card class="overflow-hidden border-border/60 shadow-2xl bg-card/30 backdrop-blur-2xl rounded-3xl">
                <x-ui.card-header class="border-b border-border/40 bg-muted/10 p-6">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <div class="size-12 rounded-2xl bg-primary/10 border border-primary/20 text-primary flex items-center justify-center shadow-inner">
                                <x-ui.icon name="refresh-cw" size="6" />
                            </div>
                            <div>
                                <h3 class="text-lg font-black text-foreground tracking-tight">Create New Refund</h3>
                                <p class="text-[10px] uppercase font-bold text-muted-foreground tracking-widest">Process payment reversal</p>
                            </div>
                        </div>
                        <a href="{{ route('refunds.index') }}">
                            <x-ui.button variant="outline" size="sm" class="rounded-xl font-bold uppercase tracking-widest text-[10px] border-border hover:bg-muted transition-colors">
                                <x-ui.icon name="arrow-left" size="3" class="mr-2" />
                                Back to list
                            </x-ui.button>
                        </a>
                    </div>
                </x-ui.card-header>

                <x-ui.card-content class="p-8">
                    @if(session('error'))
                        <div class="mb-6 rounded-2xl border border-destructive/20 bg-destructive/10 px-4 py-3 text-sm font-semibold text-destructive">
                            {{ session('error') }}
                        </div>
                    @endif

                    @if($errors->any())
                        <div class="mb-6 rounded-2xl border border-destructive/20 bg-destructive/10 px-4 py-3 text-sm font-semibold text-destructive">
                            <ul class="list-disc list-inside">
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    @if(!$payment)
                        <form action="{{ route('refunds.create') }}" method="GET" class="space-y-8">
                            <div class="space-y-2 max-w-xl mx-auto">
                                <label class="text-[10px] font-black uppercase tracking-widest text-muted-foreground/80 ml-1 text-center block">Select Payment to Refund</label>
                                <select name="payment_id" required class="w-full h-12 px-4 rounded-2xl border border-border bg-background/50 focus:bg-background focus:ring-2 focus:ring-primary/20 transition-all text-sm font-medium">
                                    <option value="">-- Select Completed Payment --</option>
                                    @foreach($payments as $pmt)
                                        <option value="{{ $pmt->id }}">
                                            {{ $pmt->payment_no }} - ₹{{ number_format($pmt->amount, 2) }} 
                                            @if($pmt->order)
                                                (Order: {{ $pmt->order->order_no }})
                                            @endif
                                        </option>
                                    @endforeach
                                </select>
                                <p class="text-center text-[10px] font-bold text-muted-foreground mt-2">Only completed payments can be refunded.</p>
                            </div>

                            <div class="flex items-center justify-center pt-4">
                                <x-ui.button type="submit" class="h-12 px-8 rounded-2xl font-black uppercase tracking-[0.2em] text-xs shadow-xl shadow-primary/20">
                                    Continue <x-ui.icon name="arrow-right" size="4" class="ml-2" />
                                </x-ui.button>
                            </div>
                        </form>
                    @else
                        @php
                            $existingRefunds = $payment->refunds()->whereIn('status', ['pending', 'processed'])->sum('amount');
                            $maxRefundable = $payment->amount - $existingRefunds;
                        @endphp
                        <form action="{{ route('refunds.store') }}" method="POST" class="space-y-8">
                            @csrf
                            <input type="hidden" name="payment_id" value="{{ $payment->id }}">

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 bg-muted/5 p-6 rounded-2xl border border-border/50">
                                <div class="space-y-1">
                                    <h4 class="text-[10px] font-black uppercase tracking-widest text-muted-foreground/80">Payment Info</h4>
                                    <p class="text-sm font-black text-foreground">{{ $payment->payment_no }}</p>
                                    <p class="text-xs font-bold text-muted-foreground/80 uppercase">Original Amount: <span class="text-emerald-500">₹{{ number_format($payment->amount, 2) }}</span></p>
                                    <p class="text-xs font-bold text-muted-foreground/80 uppercase">Method: {{ $payment->payment_method }}</p>
                                </div>
                                @if($payment->order)
                                    <div class="space-y-1 flex flex-col md:items-end md:text-right">
                                        <h4 class="text-[10px] font-black uppercase tracking-widest text-muted-foreground/80">Associated Order</h4>
                                        <p class="text-sm font-black text-foreground">{{ $payment->order->order_no }}</p>
                                        <p class="text-xs font-bold text-muted-foreground/80">{{ $payment->order->party->company_name ?? ($payment->order->party->firstname . ' ' . $payment->order->party->lastname) }}</p>
                                    </div>
                                @endif
                            </div>

                            <div class="space-y-4">
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    <div class="space-y-2">
                                        <label class="text-[10px] font-black uppercase tracking-widest text-muted-foreground/80 ml-1">Refund Amount (₹)</label>
                                        <input type="number" name="amount" required min="0.01" max="{{ $maxRefundable }}" step="0.01" value="{{ old('amount', $maxRefundable) }}"
                                            class="w-full h-12 px-4 rounded-2xl border border-border bg-background/50 focus:bg-background focus:ring-2 focus:ring-primary/20 transition-all text-sm font-medium">
                                        <p class="text-[10px] font-bold text-muted-foreground ml-1">Maximum available to refund: <strong class="text-primary">₹{{ number_format($maxRefundable, 2) }}</strong></p>
                                    </div>
                                    <div class="space-y-2">
                                        <label class="text-[10px] font-black uppercase tracking-widest text-muted-foreground/80 ml-1">Refund Reason (Optional)</label>
                                        <textarea name="reason" rows="2"
                                            class="w-full px-4 py-3 rounded-2xl border border-border bg-background/50 focus:bg-background focus:ring-2 focus:ring-primary/20 transition-all text-sm font-medium">{{ old('reason') }}</textarea>
                                    </div>
                                </div>
                                
                                <div class="flex items-center justify-end gap-3 pt-6">
                                    <a href="{{ route('refunds.create') }}">
                                        <x-ui.button variant="outline" type="button" class="h-12 px-6 rounded-2xl font-bold uppercase tracking-widest text-[10px]">
                                            Change Payment
                                        </x-ui.button>
                                    </a>
                                    <x-ui.button type="submit" class="h-12 px-8 rounded-2xl font-black uppercase tracking-[0.2em] text-xs shadow-xl shadow-primary/20">
                                        Submit Refund
                                    </x-ui.button>
                                </div>
                            </div>
                        </form>
                    @endif
                </x-ui.card-content>
            </x-ui.card>
        </div>
    </div>
</x-layouts.app>
