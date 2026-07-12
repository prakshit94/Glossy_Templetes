@if($orders->hasPages())
    <div class="p-4 border-b border-border/40 bg-muted/10 flex justify-end items-center">
        {{ $orders->links() }}
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
                <x-ui.table-head x-show="visibleColumns.order_identity" class="text-[10px] font-black uppercase tracking-widest text-muted-foreground/70 whitespace-nowrap">Order Identity</x-ui.table-head>
                <x-ui.table-head x-show="visibleColumns.transaction_type" class="text-[10px] font-black uppercase tracking-widest text-muted-foreground/70 whitespace-nowrap">Transaction Type</x-ui.table-head>
                <x-ui.table-head x-show="visibleColumns.associated_party" class="text-[10px] font-black uppercase tracking-widest text-muted-foreground/70 whitespace-nowrap">Associated Party</x-ui.table-head>
                <x-ui.table-head x-show="visibleColumns.fulfillment_node" class="text-[10px] font-black uppercase tracking-widest text-muted-foreground/70 whitespace-nowrap">Fulfillment Node</x-ui.table-head>
                <x-ui.table-head x-show="visibleColumns.created_by" class="text-[10px] font-black uppercase tracking-widest text-muted-foreground/70 whitespace-nowrap">
                    Created By
                </x-ui.table-head>
                <x-ui.table-head x-show="visibleColumns.lifecycle_status" class="text-[10px] font-black uppercase tracking-widest text-muted-foreground/70 whitespace-nowrap text-center">Lifecycle Status</x-ui.table-head>
                <x-ui.table-head x-show="visibleColumns.carrier_details" class="text-[10px] font-black uppercase tracking-widest text-muted-foreground/70 whitespace-nowrap">Carrier Details</x-ui.table-head>
                <x-ui.table-head x-show="visibleColumns.ordered_products" class="text-[10px] font-black uppercase tracking-widest text-muted-foreground/70 whitespace-nowrap">Ordered Products</x-ui.table-head>
                <x-ui.table-head x-show="visibleColumns.financial_total" class="text-[10px] font-black uppercase tracking-widest text-muted-foreground/70 text-right whitespace-nowrap">Financial Total</x-ui.table-head>
                <x-ui.table-head x-show="visibleColumns.actions" class="text-right text-[10px] font-black uppercase tracking-widest text-muted-foreground/70 pr-5">Actions</x-ui.table-head>
            </x-ui.table-row>
        </x-ui.table-header>
        <x-ui.table-body>
            @forelse($orders as $order)
                @php
                    $hasOutOfStock = false;
                    if ($order->status === 'pending') {
                        foreach ($order->items as $item) {
                            $prod = $item->product;
                            if ($prod && $item->quantity > $prod->available_stock) {
                                $hasOutOfStock = true;
                                break;
                            }
                        }
                    }
                @endphp
                <x-ui.table-row
                    x-bind:class="selectedItems.includes({{ $order->id }}) ? 'bg-primary/[0.06] ring-1 ring-inset ring-primary/15 relative z-40' : 'hover:bg-primary/[0.03] hover:z-50 relative'"
                    class="border-b border-border/40 group/row transition-colors duration-200 {{ $hasOutOfStock ? 'border-l-4 border-l-red-500 bg-red-500/[0.04] hover:bg-red-500/[0.06]' : '' }}">
                    
                    <x-ui.table-cell class="pl-5 align-middle">
                        <input type="checkbox" name="order_ids[]" value="{{ $order->id }}" 
                            :checked="selectedItems.includes({{ $order->id }})" 
                            @change="toggleItem({{ $order->id }}, '{{ $order->status }}')"
                            data-status="{{ $order->status }}"
                            data-shipment-id="{{ $order->shipments->first()?->id }}"
                            data-order-no="{{ $order->order_no }}"
                            data-driver-id="{{ $order->shipments->first()?->deliveryTracking?->driver_id ?? '' }}"
                            data-transport-id="{{ $order->shipments->first()?->deliveryTracking?->transport_id ?? '' }}"
                            class="rounded-md border-border bg-background text-primary focus:ring-primary/25 shadow-sm">
                    </x-ui.table-cell>

                    <x-ui.table-cell x-show="visibleColumns.order_identity" class="align-middle">
                        <div class="flex items-center gap-4 py-0.5">
                            <div class="shrink-0">
                                <div class="size-11 rounded-2xl bg-gradient-to-br from-primary/25 to-primary/5 border border-primary/15 flex items-center justify-center text-primary shadow-inner ring-1 ring-primary/10 group-hover/row:scale-[1.02] transition-transform duration-300">
                                    <x-ui.icon name="package" size="4.5" />
                                </div>
                            </div>
                            <div class="flex flex-col min-w-0">
                                <div class="flex items-center gap-2">
                                    <span x-data="{ copied: false }" @click.prevent.stop="navigator.clipboard.writeText('{{ $order->order_no }}'); copied = true; setTimeout(() => copied = false, 2000)" class="cursor-pointer text-sm font-black tracking-tight text-foreground uppercase truncate hover:text-primary transition-colors flex items-center gap-1.5 relative group/copy w-max">
                                        {{ $order->order_no }}
                                        <x-ui.icon name="copy" size="3" class="opacity-0 group-hover/copy:opacity-100 transition-opacity text-primary" />
                                        <span x-show="copied" x-cloak class="absolute -top-6 left-0 bg-foreground text-background text-[9px] font-bold px-2 py-0.5 rounded shadow-lg pointer-events-none normal-case tracking-normal">Copied!</span>
                                    </span>
                                    <span class="text-[9px] font-black uppercase px-1.5 py-0.5 rounded bg-muted text-muted-foreground border border-border/40 whitespace-nowrap">ID: {{ $order->id }}</span>
                                </div>
                                <span class="text-[10px] font-bold text-muted-foreground/65 tabular-nums">
                                    {{ optional($order->order_date)->format('M d, Y') }} at {{ optional($order->order_date)->format('h:i A') }}
                                </span>
                            </div>
                        </div>
                    </x-ui.table-cell>

                    <x-ui.table-cell x-show="visibleColumns.transaction_type" class="align-middle">
                        @php
                            $typeVariant = $order->type === 'sale' ? 'default' : 'outline';
                            $typeColor = $order->type === 'sale' ? 'text-blue-500 bg-blue-500/5' : 'text-purple-500 bg-purple-500/5';
                        @endphp
                        <div class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-xl border border-border/50 {{ $typeColor }}">
                            <x-ui.icon :name="$order->type === 'sale' ? 'arrow-up-right' : 'arrow-down-left'" size="3" />
                            <span class="text-[9px] font-black uppercase tracking-widest">{{ $order->type }}</span>
                        </div>
                    </x-ui.table-cell>

                    <x-ui.table-cell x-show="visibleColumns.associated_party" class="align-middle">
                        <div class="flex items-center gap-2">
                            <div class="size-7 rounded-lg bg-muted/40 flex items-center justify-center text-muted-foreground">
                                <x-ui.icon name="user" size="3" />
                            </div>
                            <span class="text-[11px] font-bold text-foreground/80 truncate max-w-[140px]">{{ $order->party?->name ?? 'Internal Node' }}</span>
                        </div>
                    </x-ui.table-cell>

                    <x-ui.table-cell x-show="visibleColumns.fulfillment_node" class="align-middle">
                        <div class="flex items-center gap-2">
                            <div class="size-7 rounded-lg bg-emerald-500/10 flex items-center justify-center text-emerald-600">
                                <x-ui.icon name="database" size="3" />
                            </div>
                            <span class="text-[11px] font-bold text-foreground/80 truncate max-w-[120px]">{{ $order->warehouse?->name ?? 'Main Hub' }}</span>
                        </div>
                    </x-ui.table-cell>

                    <x-ui.table-cell x-show="visibleColumns.created_by" class="align-middle">
                        <div class="flex items-center gap-2">
                            <div class="size-7 rounded-lg bg-sky-500/10 flex items-center justify-center text-sky-600">
                                <x-ui.icon name="user-circle" size="3" />
                            </div>
                            <div class="flex flex-col min-w-0">
                                <span class="text-[11px] font-bold text-foreground/80 truncate max-w-[120px]">
                                    {{ $order->creator?->name ?? 'System' }}
                                </span>
                                @if($order->creator?->email)
                                    <span class="text-[9px] text-muted-foreground truncate max-w-[140px]">
                                        {{ $order->creator->email }}
                                    </span>
                                @endif
                            </div>
                        </div>
                    </x-ui.table-cell>

                    <x-ui.table-cell x-show="visibleColumns.lifecycle_status" class="align-middle text-center">
    @php
        $lifecycleStatus = $order->lifecycleStatus();
        $transitions = [];

        if ($lifecycleStatus === 'future_order') {
            $transitions[] = [
                'status' => 'cancelled',
                'label' => 'Cancel Order',
                'type' => 'cancel',
                'icon' => 'x-circle',
                'color' => 'red'
            ];

        } elseif ($lifecycleStatus === 'pending') {
            $transitions[] = [
                'status' => 'confirmed',
                'label' => 'Confirm Order',
                'type' => 'upcoming',
                'icon' => 'check-circle',
                'color' => 'indigo'
            ];

            $transitions[] = [
                'status' => 'cancelled',
                'label' => 'Cancel Order',
                'type' => 'cancel',
                'icon' => 'x-circle',
                'color' => 'red'
            ];

        } elseif ($lifecycleStatus === 'confirmed') {

            $transitions[] = [
                'status' => 'processing',
                'label' => 'Mark Processing',
                'type' => 'upcoming',
                'icon' => 'loader',
                'color' => 'amber'
            ];

            $transitions[] = [
                'status' => 'ready_to_ship',
                'label' => 'Mark Ready to Ship',
                'type' => 'upcoming',
                'icon' => 'package',
                'color' => 'indigo'
            ];

            $transitions[] = [
                'status' => 'pending',
                'label' => 'Revert to Pending',
                'type' => 'revert',
                'icon' => 'corner-up-left',
                'color' => 'gray'
            ];

            $transitions[] = [
                'status' => 'cancelled',
                'label' => 'Cancel Order',
                'type' => 'cancel',
                'icon' => 'x-circle',
                'color' => 'red'
            ];

        } elseif ($lifecycleStatus === 'processing') {

            $transitions[] = [
                'status' => 'ready_to_ship',
                'label' => 'Mark Ready to Ship',
                'type' => 'upcoming',
                'icon' => 'package',
                'color' => 'indigo'
            ];

            $transitions[] = [
                'status' => 'confirmed',
                'label' => 'Revert to Confirmed',
                'type' => 'revert',
                'icon' => 'corner-up-left',
                'color' => 'gray'
            ];

            $transitions[] = [
                'status' => 'cancelled',
                'label' => 'Cancel Order',
                'type' => 'cancel',
                'icon' => 'x-circle',
                'color' => 'red'
            ];

        } elseif ($lifecycleStatus === 'ready_to_ship') {

            $transitions[] = [
                'status' => 'dispatched',
                'label' => 'Dispatch Order',
                'type' => 'upcoming',
                'icon' => 'truck',
                'color' => 'blue'
            ];

            $transitions[] = [
                'status' => 'processing',
                'label' => 'Revert to Processing',
                'type' => 'revert',
                'icon' => 'corner-up-left',
                'color' => 'gray'
            ];

        } elseif (in_array($lifecycleStatus, ['dispatched', 'shipped'], true)) {

            $transitions[] = [
                'status' => 'delivered',
                'label' => 'Deliver Order',
                'type' => 'upcoming',
                'icon' => 'check',
                'color' => 'emerald'
            ];

            $transitions[] = [
                'status' => 'ready_to_ship',
                'label' => 'Revert to Ready to Ship',
                'type' => 'revert',
                'icon' => 'corner-up-left',
                'color' => 'gray'
            ];

        } elseif ($lifecycleStatus === 'delivered') {

            $transitions[] = [
                'status' => 'dispatched',
                'label' => 'Revert to Dispatched',
                'type' => 'revert',
                'icon' => 'corner-up-left',
                'color' => 'gray'
            ];

        } elseif ($lifecycleStatus === 'cancelled') {

            $transitions[] = [
                'status' => 'pending',
                'label' => 'Revert to Pending',
                'type' => 'revert',
                'icon' => 'corner-up-left',
                'color' => 'gray'
            ];
        }

        $statusColors = [
            'future_order' => ['bg' => 'bg-purple-500/10', 'text' => 'text-purple-600', 'border' => 'border-purple-500/20'],
            'pending' => ['bg' => 'bg-orange-500/10', 'text' => 'text-orange-600', 'border' => 'border-orange-500/20'],
            'confirmed' => ['bg' => 'bg-sky-500/10', 'text' => 'text-sky-600', 'border' => 'border-sky-500/20'],
            'processing' => ['bg' => 'bg-blue-500/10', 'text' => 'text-blue-600', 'border' => 'border-blue-500/20'],
            'ready_to_ship' => ['bg' => 'bg-indigo-500/10', 'text' => 'text-indigo-600', 'border' => 'border-indigo-500/20'],
            'dispatched' => ['bg' => 'bg-teal-500/10', 'text' => 'text-teal-600', 'border' => 'border-teal-500/20'],
            'shipped' => ['bg' => 'bg-teal-500/10', 'text' => 'text-teal-600', 'border' => 'border-teal-500/20'],
            'delivered' => ['bg' => 'bg-emerald-500/10', 'text' => 'text-emerald-600', 'border' => 'border-emerald-500/20'],
            'cancelled' => ['bg' => 'bg-red-500/10', 'text' => 'text-red-600', 'border' => 'border-red-500/20'],
            'returned' => ['bg' => 'bg-rose-500/10', 'text' => 'text-rose-600', 'border' => 'border-rose-500/20'],
            'return_requested' => ['bg' => 'bg-orange-500/10', 'text' => 'text-orange-600', 'border' => 'border-orange-500/20'],
        ];

        $colorClasses = $statusColors[$lifecycleStatus] ?? ['bg' => 'bg-muted/40', 'text' => 'text-muted-foreground', 'border' => 'border-border/50'];
        $canUseLifecycleDropdown = auth()->user()?->canAny([
            'orders.confirm',
            'orders.processing',
            'orders.ship',
            'orders.dispatch',
            'orders.deliver',
            'orders.cancel',
            'orders.revert_status',
        ]);

        $missingFields = [];
        if ($lifecycleStatus === 'future_order') {
            if (empty($order->warehouse_id)) $missingFields[] = 'Warehouse';
            if (empty($order->shipping_address_id) && empty($order->shipping_address) && empty($order->billing_address_id) && empty($order->billing_address)) $missingFields[] = 'Address';
        }
    @endphp

    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg shadow-sm ring-1 ring-black/5 dark:ring-white/10 {{ $colorClasses['bg'] }} {{ $colorClasses['text'] }} {{ $colorClasses['border'] }}">
        <span class="uppercase text-[9px] font-black tracking-[0.12em]">
            {{ $order->statusLabel() }}
        </span>
    </span>

    @if($lifecycleStatus === 'future_order')
        <div class="mt-2 flex flex-col items-center gap-1.5 w-full">
            @if($order->future_order_date)
                <div class="text-[9px] font-bold text-purple-600 bg-purple-500/10 px-2 py-0.5 rounded border border-purple-500/20 whitespace-nowrap">
                    <x-ui.icon name="calendar" size="2.5" class="inline mr-1 -mt-0.5" />
                    Confirm on: {{ \Carbon\Carbon::parse($order->future_order_date)->format('M d, Y') }}
                </div>
            @endif
            @if(count($missingFields) > 0)
                <div class="text-[9px] font-bold text-rose-600 bg-rose-500/10 px-2 py-0.5 rounded border border-rose-500/20 whitespace-nowrap">
                    <x-ui.icon name="alert-circle" size="2.5" class="inline mr-1 -mt-0.5" />
                    Missing: {{ implode(', ', $missingFields) }}
                </div>
            @endif
        </div>
    @elseif(in_array($lifecycleStatus, ['pending', 'dispatched', 'shipped']))
        @php
            $latestLog = $order->verificationLogs->first();
            $followUpLog = $order->verificationLogs->filter(fn($log) => !empty($log->follow_up_at))->first();
        @endphp
        @if($latestLog || $followUpLog)
            <div class="mt-2 flex flex-col items-center gap-1.5 w-full">
                @if($followUpLog)
                    <div class="text-[9px] font-bold text-amber-600 bg-amber-500/10 px-2 py-0.5 rounded border border-amber-500/20 whitespace-nowrap" title="{{ $followUpLog->outcome_label ?? 'Next Follow-up' }}">
                        <x-ui.icon name="{{ $followUpLog->outcome === 'reschedule_delivery' ? 'calendar' : 'phone-call' }}" size="2.5" class="inline mr-1 -mt-0.5" />
                        Recall: {{ \Carbon\Carbon::parse($followUpLog->follow_up_at)->format('M d, Y h:i A') }}
                    </div>
                @endif
                
                @if($latestLog && (!$followUpLog || $latestLog->id !== $followUpLog->id))
                    <div class="text-[9px] font-bold text-muted-foreground bg-muted/50 px-2 py-0.5 rounded border border-border/50 whitespace-nowrap max-w-[130px] truncate" title="{{ $latestLog->remark ?? $latestLog->outcome_label }}">
                        <x-ui.icon name="info" size="2.5" class="inline mr-1 -mt-0.5" />
                        Log: {{ $latestLog->outcome_label }}
                    </div>
                @endif
            </div>
        @endif
    @endif
</x-ui.table-cell>

                    <x-ui.table-cell x-show="visibleColumns.carrier_details" class="align-middle">
                        @php
                            $shipment = $order->shipments->first();
                        @endphp
                        @if($shipment)
                            <div class="flex flex-col min-w-0">
                                <span class="text-[11px] font-bold text-foreground/80 break-words">{{ $shipment->carrier_name ?? 'Pending Carrier' }}</span>
                                @if($shipment->tracking_no)
                                    <span class="text-[9px] text-muted-foreground font-black uppercase tracking-wider mt-0.5 flex items-center gap-1">
                                        <x-ui.icon name="truck" size="2.5" /> {{ $shipment->tracking_no }}
                                    </span>
                                @else
                                    <span class="text-[9px] text-muted-foreground font-semibold mt-0.5">No Tracking ID</span>
                                @endif
                            </div>
                        @else
                            <span class="text-[10px] text-muted-foreground/50 font-medium italic">Unassigned</span>
                        @endif
                    </x-ui.table-cell>

                    <x-ui.table-cell x-show="visibleColumns.ordered_products" class="align-middle py-3">
                        <div x-data="{ show: false, mouseX: 0, mouseY: 0 }"
                             @mouseenter="show = true; mouseX = $event.clientX; mouseY = $event.clientY;"
                             @mousemove="mouseX = $event.clientX; mouseY = $event.clientY;"
                             @mouseleave="show = false"
                             class="relative inline-flex items-center justify-center size-10 rounded-xl bg-primary/10 text-primary border border-primary/20 hover:bg-primary/20 hover:scale-105 transition-all cursor-help shadow-sm ring-1 ring-black/5">
                            
                            <x-ui.icon name="shopping-bag" size="4.5" />
                            <span class="absolute -top-1.5 -right-1.5 flex h-4 min-w-4 items-center justify-center rounded-full bg-primary text-[9px] font-black text-primary-foreground px-1 ring-2 ring-background shadow-sm">
                                {{ $order->items->count() }}
                            </span>
                            
                            <!-- Teleported Tooltip for ALL items -->
                            <template x-teleport="body">
                                <div x-show="show" x-cloak
                                     x-transition:enter="transition ease-out duration-200"
                                     x-transition:enter-start="opacity-0 translate-y-2 scale-95"
                                     x-transition:enter-end="opacity-100 translate-y-0 scale-100"
                                     x-transition:leave="transition ease-in duration-100"
                                     x-transition:leave-start="opacity-100"
                                     x-transition:leave-end="opacity-0"
                                     :style="'left: ' + (mouseX - 160 > 10 ? mouseX - 160 : 10) + 'px; top: ' + (mouseY + 20) + 'px;'"
                                     class="fixed z-[99999] w-max max-w-xs sm:max-w-sm bg-popover/95 backdrop-blur-xl text-popover-foreground py-3 px-4 rounded-2xl border border-border/70 shadow-[0_20px_50px_-12px_rgba(0,0,0,0.5)] ring-1 ring-black/5 pointer-events-none">
                                    
                                    <div class="flex items-center justify-between gap-6 border-b border-border/50 pb-2 mb-2">
                                        <span class="text-primary tracking-widest font-black uppercase text-[10px]">Ordered Items</span>
                                        <span class="text-[9px] font-black tabular-nums px-2 py-0.5 rounded-md bg-muted text-muted-foreground">{{ $order->items->count() }} Types</span>
                                    </div>
                                    <div class="flex flex-col gap-2 max-h-[250px] overflow-y-auto pr-1 custom-scrollbar">
                                        @foreach($order->items as $item)
                                            @php
                                                $prod = $item->product;
                                                $isItemOOS = false;
                                                if ($order->status === 'pending' && $prod && $item->quantity > $prod->available_stock) {
                                                    $isItemOOS = true;
                                                }
                                                $fullName = $prod ? $prod->name : 'Item #'.$item->product_id;
                                                $qtyStr = (int) $item->quantity;
                                            @endphp
                                            <div class="flex items-start justify-between gap-4 p-2 rounded-lg {{ $isItemOOS ? 'bg-red-500/10 border border-red-500/20' : 'bg-muted/30' }}">
                                                <div class="flex flex-col min-w-0 text-left">
                                                    <span class="text-[11px] font-bold text-foreground leading-tight break-words">{{ $fullName }}</span>
                                                    @if($isItemOOS)
                                                        <span class="text-red-500 text-[9px] font-black uppercase tracking-wider flex items-center gap-1 mt-1">
                                                            <x-ui.icon name="alert-triangle" size="2.5" /> Out of Stock (Available: {{ $prod ? $prod->available_stock : 0 }})
                                                        </span>
                                                    @endif
                                                </div>
                                                <div class="shrink-0 flex items-center gap-1">
                                                    <span class="text-muted-foreground text-[9px] font-bold uppercase">Qty</span>
                                                    <span class="text-[11px] font-black tabular-nums text-foreground bg-background border border-border/50 px-1.5 py-0.5 rounded-md">{{ $qtyStr }}</span>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            </template>
                        </div>
                    </x-ui.table-cell>

                    <x-ui.table-cell x-show="visibleColumns.financial_total" class="text-right align-middle">
                        <div class="flex flex-col items-end">
                            <span class="text-sm font-black text-foreground tracking-tight">₹{{ number_format((float) $order->net_amount, 2) }}</span>
                            <span class="text-[9px] font-bold text-muted-foreground/60">{{ $order->items->count() }} items itemized</span>
                        </div>
                    </x-ui.table-cell>

                    <x-ui.table-cell x-show="visibleColumns.actions" class="text-right align-middle pr-5">
                        @php
                            $canReturnFromLedger = !in_array($order->status, ['returned', 'return_requested'])
                                && in_array($order->status, \App\Models\Order::inTransitStatuses(), true)
                                && !$order->returns()->where('status', '!=', 'rejected')->exists();
                        @endphp
                        <div class="flex justify-end gap-1">
                            @if($canReturnFromLedger)
                                <button type="button"
                                    title="Create Return"
                                    class="inline-flex items-center justify-center size-9 rounded-xl border border-transparent text-muted-foreground hover:text-amber-600 hover:bg-amber-500/10 transition-all"
                                    @click="openReturnModal({
                                        id: {{ $order->id }},
                                        orderNo: @js($order->order_no),
                                        items: @js($order->items->map(function ($i) {
                                            return [
                                                'id' => $i->id,
                                                'name' => $i->product?->name ?? 'Item #'.$i->product_id,
                                                'sku' => $i->product?->sku ?? '—',
                                                'price' => (float) $i->unit_price,
                                                'max' => (float) $i->quantity,
                                            ];
                                        }))
                                    })">
                                    <x-ui.icon name="corner-down-left" size="4" />
                                </button>
                            @endif
                            @can('orders.view')
                                <a href="{{ route('orders.show', $order) }}" title="Visual Dossier">
                                    <x-ui.button variant="ghost" size="icon" className="size-9 text-muted-foreground hover:text-primary hover:bg-primary/10 rounded-xl border border-transparent hover:border-primary/20 transition-all">
                                        <x-ui.icon name="eye" size="4" />
                                    </x-ui.button>
                                </a>
                            @endcan
                            @if($order->invoice)
                                @can('orders.invoice_pdf')
                                    <a href="{{ route('orders.invoice-pdf', $order) }}" target="_blank" title="Download Invoice">
                                        <x-ui.button variant="ghost" size="icon" className="size-9 text-muted-foreground hover:text-blue-500 hover:bg-blue-500/10 rounded-xl border border-transparent hover:border-blue-500/20 transition-all">
                                            <x-ui.icon name="file-text" size="4" />
                                        </x-ui.button>
                                    </a>
                                @endcan
                            @else
                                @can('orders.generate_invoice')
                                    <form action="{{ route('orders.generate-invoice', $order) }}" method="POST" class="inline">
                                        @csrf
                                        <x-ui.button type="submit" variant="ghost" size="icon" className="size-9 text-muted-foreground hover:text-indigo-500 hover:bg-indigo-500/10 rounded-xl border border-transparent hover:border-indigo-500/20 transition-all" title="Generate Invoice">
                                            <x-ui.icon name="file-plus" size="4" />
                                        </x-ui.button>
                                    </form>
                                @endcan
                            @endif
                            @can('orders.cod')
                                <a href="{{ route('orders.cod-pdf', $order) }}" target="_blank" title="Download COD PDF">
                                    <x-ui.button variant="ghost" size="icon" className="size-9 text-muted-foreground hover:text-emerald-500 hover:bg-emerald-500/10 rounded-xl border border-transparent hover:border-emerald-500/20 transition-all">
                                        <x-ui.icon name="printer" size="4" />
                                    </x-ui.button>
                                </a>
                            @endcan
                            @can('orders.edit')
                                <a href="{{ route('orders.edit', $order) }}" title="Modify Structure">
                                    <x-ui.button variant="ghost" size="icon" className="size-9 text-muted-foreground hover:text-amber-500 hover:bg-amber-500/10 rounded-xl border border-transparent hover:border-amber-500/20 transition-all">
                                        <x-ui.icon name="edit-3" size="4" />
                                    </x-ui.button>
                                </a>
                            @endcan
                        </div>
                    </x-ui.table-cell>
                </x-ui.table-row>
            @empty
                <x-ui.table-row>
                    <x-ui.table-cell x-bind:colspan="Object.values(visibleColumns).filter(Boolean).length + 1" class="h-72 text-center align-middle p-0">
                        <div class="flex flex-col items-center justify-center gap-5 py-12 px-6">
                            <div class="size-24 rounded-3xl bg-gradient-to-br from-primary/25 via-primary/8 to-transparent border border-primary/20 flex items-center justify-center text-primary shadow-inner ring-1 ring-primary/10">
                                <x-ui.icon name="package" size="12" />
                            </div>
                            <div class="space-y-2 max-w-md text-center">
                                <p class="text-sm font-black uppercase tracking-[0.2em] text-foreground">No orders in ledger</p>
                                <p class="text-[11px] text-muted-foreground font-medium leading-relaxed">Adjust your filters, search queries, or geographical parameters to locate orders.</p>
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

@if($orders->hasPages())
    <div class="p-4 border-t border-border/40 bg-muted/10 flex justify-end items-center rounded-b-3xl">
        {{ $orders->links() }}
    </div>
@endif
