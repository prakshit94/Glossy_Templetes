<x-layouts.app pageTitle="Create Return Request">
    <div class="p-6 lg:p-10">
        <div class="max-w-5xl mx-auto">
            <x-ui.card class="overflow-hidden border-border/60 shadow-2xl bg-card/30 backdrop-blur-2xl rounded-3xl">
                <x-ui.card-header class="border-b border-border/40 bg-muted/10 p-6">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <div class="size-12 rounded-2xl bg-primary/10 border border-primary/20 text-primary flex items-center justify-center shadow-inner">
                                <x-ui.icon name="corner-down-left" size="6" />
                            </div>
                            <div>
                                <h3 class="text-lg font-black text-foreground tracking-tight">Create New Return</h3>
                                <p class="text-[10px] uppercase font-bold text-muted-foreground tracking-widest">Select order and items</p>
                            </div>
                        </div>
                        <a href="{{ route('returns.index') }}">
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

                    @if(!$order)
                        <form action="{{ route('returns.create') }}" method="GET" class="space-y-8">
                            <div class="space-y-2 max-w-xl mx-auto">
                                <label class="text-[10px] font-black uppercase tracking-widest text-muted-foreground/80 ml-1 text-center block">Select Order to Return</label>
                                <select name="order_id" required class="w-full h-12 px-4 rounded-2xl border border-border bg-background/50 focus:bg-background focus:ring-2 focus:ring-primary/20 transition-all text-sm font-medium">
                                    <option value="">-- Select Order --</option>
                                    @foreach($orders as $ord)
                                        <option value="{{ $ord->id }}">
                                            {{ $ord->order_no }} - {{ $ord->party->company_name ?? ($ord->party->firstname . ' ' . $ord->party->lastname) }} ({{ ucfirst($ord->status) }})
                                        </option>
                                    @endforeach
                                </select>
                                <p class="text-center text-[10px] font-bold text-muted-foreground mt-2">Only shipped or completed orders can be returned.</p>
                            </div>

                            <div class="flex items-center justify-center pt-4">
                                <x-ui.button type="submit" class="h-12 px-8 rounded-2xl font-black uppercase tracking-[0.2em] text-xs shadow-xl shadow-primary/20">
                                    Continue <x-ui.icon name="arrow-right" size="4" class="ml-2" />
                                </x-ui.button>
                            </div>
                        </form>
                    @else
                        <form action="{{ route('returns.store') }}" method="POST" class="space-y-8">
                            @csrf
                            <input type="hidden" name="order_id" value="{{ $order->id }}">

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 bg-muted/5 p-6 rounded-2xl border border-border/50">
                                <div class="space-y-1">
                                    <h4 class="text-[10px] font-black uppercase tracking-widest text-muted-foreground/80">Customer</h4>
                                    <p class="text-sm font-black text-foreground">{{ $order->party->company_name ?? ($order->party->firstname . ' ' . $order->party->lastname) }}</p>
                                    <p class="text-xs font-bold text-muted-foreground/80">{{ $order->party->phone }}</p>
                                </div>
                                <div class="space-y-1 flex flex-col md:items-end md:text-right">
                                    <h4 class="text-[10px] font-black uppercase tracking-widest text-muted-foreground/80">Order Info</h4>
                                    <p class="text-sm font-black text-foreground">Date: {{ $order->order_date->format('M d, Y') }}</p>
                                    <p class="text-xs font-bold text-muted-foreground/80 uppercase">Status: <span class="text-primary">{{ $order->status }}</span></p>
                                </div>
                            </div>

                            <div class="space-y-4" x-data="{
                                items: @js($order->items->map(function($i) { return ['id' => $i->id, 'name' => $i->product->name, 'sku' => $i->product->sku, 'price' => $i->unit_price, 'max' => $i->quantity, 'qty' => 0]; })),
                                get totalRefund() {
                                    return this.items.reduce((sum, item) => sum + (item.qty * item.price), 0);
                                }
                            }">
                                <div class="flex items-center justify-between px-1">
                                    <h4 class="text-[10px] font-black uppercase tracking-widest text-primary">Select Items to Return</h4>
                                </div>

                                <div class="overflow-x-auto rounded-2xl border border-border/60">
                                    <table class="w-full text-left border-collapse">
                                        <thead>
                                            <tr class="border-b border-border/40 bg-muted/5">
                                                <th class="px-4 py-3 text-[10px] font-black uppercase tracking-widest text-muted-foreground">Product</th>
                                                <th class="px-4 py-3 text-[10px] font-black uppercase tracking-widest text-muted-foreground text-right">Unit Price</th>
                                                <th class="px-4 py-3 text-[10px] font-black uppercase tracking-widest text-muted-foreground text-right">Order Qty</th>
                                                <th class="px-4 py-3 text-[10px] font-black uppercase tracking-widest text-primary text-right">Return Qty</th>
                                                <th class="px-4 py-3 text-[10px] font-black uppercase tracking-widest text-muted-foreground text-right">Refund</th>
                                            </tr>
                                        </thead>
                                        <tbody class="divide-y divide-border/40">
                                            <template x-for="(item, index) in items" :key="item.id">
                                                <tr class="hover:bg-muted/10 transition-colors">
                                                    <td class="px-4 py-3">
                                                        <div class="font-bold text-sm text-foreground" x-text="item.name"></div>
                                                        <div class="text-[10px] font-bold tracking-widest text-muted-foreground uppercase mt-0.5" x-text="'SKU: ' + item.sku"></div>
                                                        <input type="hidden" :name="'items['+index+'][order_item_id]'" :value="item.id" x-bind:disabled="item.qty <= 0">
                                                    </td>
                                                    <td class="px-4 py-3 text-right text-sm font-bold text-muted-foreground" x-text="'₹' + item.price.toFixed(2)"></td>
                                                    <td class="px-4 py-3 text-right text-sm font-black text-foreground tabular-nums" x-text="item.max"></td>
                                                    <td class="px-4 py-3 text-right">
                                                        <input type="number" :name="'items['+index+'][quantity]'" x-model.number="item.qty" min="0" :max="item.max" step="1" 
                                                            class="w-24 h-10 px-3 rounded-xl border border-border bg-background/50 text-sm font-semibold text-right focus:ring-primary/20 transition-all" 
                                                            x-bind:disabled="item.qty <= 0 && false">
                                                    </td>
                                                    <td class="px-4 py-3 text-right text-sm font-black text-foreground tabular-nums" x-text="'₹' + (item.qty * item.price).toFixed(2)"></td>
                                                </tr>
                                            </template>
                                        </tbody>
                                        <tfoot>
                                            <tr class="bg-muted/10 border-t border-border/40">
                                                <td colspan="4" class="px-4 py-4 text-right text-[10px] font-black uppercase tracking-widest text-muted-foreground">Estimated Total Refund:</td>
                                                <td class="px-4 py-4 text-right text-base font-black text-primary tabular-nums" x-text="'₹' + totalRefund.toFixed(2)"></td>
                                            </tr>
                                        </tfoot>
                                    </table>
                                </div>

                                <div class="space-y-2 mt-6">
                                    <label class="text-[10px] font-black uppercase tracking-widest text-muted-foreground/80 ml-1">Return Reason</label>
                                    <textarea name="reason" rows="3" required
                                        class="w-full px-4 py-3 rounded-2xl border border-border bg-background/50 focus:bg-background focus:ring-2 focus:ring-primary/20 transition-all text-sm font-medium"></textarea>
                                </div>
                                
                                <div class="flex items-center justify-end gap-3 pt-6">
                                    <a href="{{ route('returns.create') }}">
                                        <x-ui.button variant="outline" type="button" class="h-12 px-6 rounded-2xl font-bold uppercase tracking-widest text-[10px]">
                                            Change Order
                                        </x-ui.button>
                                    </a>
                                    <x-ui.button type="submit" x-bind:disabled="totalRefund <= 0" class="h-12 px-8 rounded-2xl font-black uppercase tracking-[0.2em] text-xs shadow-xl shadow-primary/20">
                                        Submit Return
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
