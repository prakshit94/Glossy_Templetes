<x-layouts.app pageTitle="Order Receipt: {{ $order->order_no }}">
    <div class="max-w-4xl mx-auto px-6 py-10 print:px-0 print:py-0">
        
        {{-- Receipt Card --}}
        <div class="bg-card border border-border/50 rounded-3xl shadow-2xl overflow-hidden print:shadow-none print:border-none">
            
            {{-- Header --}}
            <div class="p-8 md:p-12 border-b border-border/40 bg-gradient-to-br from-primary/5 to-transparent flex flex-col md:flex-row justify-between gap-8">
                <div>
                    <div class="flex items-center gap-3 mb-6">
                        <div class="size-12 rounded-2xl bg-primary text-primary-foreground flex items-center justify-center shadow-lg shadow-primary/30">
                            <x-ui.icon name="package" size="6" />
                        </div>
                        <h2 class="text-2xl font-black tracking-tight">AGROSTAR</h2>
                    </div>
                    
                    <div class="space-y-1">
                        <p class="text-[10px] font-black uppercase tracking-widest text-muted-foreground">Order Number</p>
                        <p class="text-xl font-black text-foreground">{{ $order->order_no }}</p>
                    </div>
                </div>
                
                <div class="text-right md:text-right space-y-4">
                    <div>
                        <p class="text-[10px] font-black uppercase tracking-widest text-muted-foreground mb-1">Date</p>
                        <p class="text-sm font-bold">{{ $order->order_date->format('F d, Y') }}</p>
                    </div>
                    <div>
                        <p class="text-[10px] font-black uppercase tracking-widest text-muted-foreground mb-1">Status</p>
                        <span class="inline-block px-3 py-1 rounded-lg bg-emerald-500/10 text-emerald-600 text-[10px] font-black uppercase tracking-widest border border-emerald-500/20">
                            {{ str_replace('_', ' ', $order->status) }}
                        </span>
                    </div>
                </div>
            </div>
            
            {{-- Billing Info --}}
            <div class="grid grid-cols-1 md:grid-cols-2 gap-12 p-8 md:p-12 bg-muted/5">
                <div>
                    <h4 class="text-[11px] font-black uppercase tracking-[0.2em] text-muted-foreground mb-4">Customer Details</h4>
                    <p class="text-lg font-black text-foreground mb-1">{{ $order->party?->name }}</p>
                    <p class="text-sm text-muted-foreground">{{ $order->party?->email }}</p>
                    <p class="text-sm text-muted-foreground">{{ $order->party?->phone }}</p>
                    @if($order->party?->gst_no)
                        <p class="text-xs font-mono text-primary mt-2">GST: {{ $order->party->gst_no }}</p>
                    @endif
                </div>
                <div>
                    <h4 class="text-[11px] font-black uppercase tracking-[0.2em] text-muted-foreground mb-4">Warehouse Info</h4>
                    <p class="text-sm font-bold text-foreground mb-1">{{ $order->warehouse?->name }}</p>
                    <p class="text-xs text-muted-foreground">{{ $order->warehouse?->address }}</p>
                </div>
            </div>
            
            {{-- Items Table --}}
            <div class="px-8 md:px-12 py-8">
                <table class="w-full text-left">
                    <thead class="border-b border-border/40 text-[10px] uppercase font-black tracking-widest text-muted-foreground">
                        <tr>
                            <th class="pb-4">Product Description</th>
                            <th class="pb-4 text-center">Qty</th>
                            <th class="pb-4 text-right">Unit Price</th>
                            <th class="pb-4 text-right">Discount</th>
                            <th class="pb-4 text-right">Amount</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-border/30">
                        @foreach($order->items as $item)
                            <tr class="group">
                                <td class="py-6">
                                    <p class="text-sm font-bold text-foreground">{{ $item->product?->name }}</p>
                                    <p class="text-[10px] text-muted-foreground font-mono mt-0.5">{{ $item->product?->sku }}</p>
                                </td>
                                <td class="py-6 text-center">
                                    <span class="text-sm font-bold">{{ number_format($item->quantity, 0) }}</span>
                                </td>
                                <td class="py-6 text-right">
                                    <span class="text-sm text-muted-foreground">₹{{ number_format($item->unit_price, 2) }}</span>
                                </td>
                                <td class="py-6 text-right">
                                    <span class="text-sm text-emerald-600">- ₹{{ number_format($item->discount_amount, 2) }}</span>
                                </td>
                                <td class="py-6 text-right">
                                    <span class="text-sm font-black text-foreground">₹{{ number_format($item->total_amount, 2) }}</span>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            
            {{-- Summary --}}
            <div class="p-8 md:p-12 bg-muted/5 flex justify-end border-t border-border/40">
                <div class="w-full max-w-xs space-y-3">
                    <div class="flex justify-between text-sm font-medium text-muted-foreground">
                        <span>Subtotal</span>
                        <span class="text-foreground">₹{{ number_format($order->total_amount, 2) }}</span>
                    </div>
                    <div class="flex justify-between text-sm font-medium text-emerald-600">
                        <span>Discount Total</span>
                        <span>- ₹{{ number_format($order->discount_amount, 2) }}</span>
                    </div>
                    <div class="flex justify-between text-sm font-medium text-muted-foreground">
                        <span>Tax Amount</span>
                        <span class="text-foreground">₹{{ number_format($order->tax_amount, 2) }}</span>
                    </div>
                    <div class="h-px bg-border/60 my-4"></div>
                    <div class="flex justify-between items-center">
                        <span class="text-base font-black uppercase tracking-widest">Grand Total</span>
                        <span class="text-3xl font-black text-primary">₹{{ number_format($order->net_amount, 2) }}</span>
                    </div>
                </div>
            </div>
            
            {{-- Footer --}}
            <div class="p-8 md:p-12 border-t border-border/40 text-center">
                <p class="text-xs text-muted-foreground mb-4">This is a computer generated document. No signature is required.</p>
                <div class="flex items-center justify-center gap-4">
                    <x-ui.button @click="window.print()" class="rounded-xl h-10 px-6 gap-2 print:hidden shadow-lg shadow-primary/20">
                        <x-ui.icon name="printer" size="4" /> Print Receipt
                    </x-ui.button>
                    <x-ui.button variant="outline" onclick="window.history.back()" class="rounded-xl h-10 px-6 gap-2 print:hidden">
                        <x-ui.icon name="arrow-left" size="4" /> Back to Profile
                    </x-ui.button>
                </div>
            </div>
            
        </div>
    </div>
    
    <style type="text/css">
        @media print {
            body * {
                visibility: hidden;
            }
            .print\:hidden {
                display: none !important;
            }
            .max-w-4xl, .max-w-4xl * {
                visibility: visible;
            }
            .max-w-4xl {
                position: absolute;
                left: 0;
                top: 0;
                width: 100%;
            }
        }
    </style>
</x-layouts.app>
