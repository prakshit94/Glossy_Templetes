<?php

namespace App\Services;

use App\Models\InventoryAdjustment;
use App\Models\Order;
use App\Models\Product;
use App\Models\Stock;
use App\Models\StockMovement;
use App\Models\StockReservation;
use App\Models\StockTransfer;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * InventoryService – SINGLE SOURCE OF TRUTH for all stock mutations.
 *
 * Rules:
 *  - Every public mutating method wraps its own DB::transaction.
 *  - Private helpers (getStockForUpdate, logMovement, etc.) must NEVER
 *    start their own transactions – they are always called from within
 *    an already-open transaction.
 *  - No other service or controller may touch `stocks.quantity`,
 *    `stocks.reserved_qty`, `stocks.committed_qty`, or
 *    `stocks.in_transit_qty` directly.
 */
class InventoryService
{
    // ─────────────────────────────────────────────────────────────────────────
    //  Internal helpers (must only be called inside an active transaction)
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * Merge any duplicate stock rows for a product/warehouse pair.
     * Called inside an existing transaction – does NOT open its own.
     */
    private function mergeDuplicateStocks(int $productId, int $warehouseId): void
    {
        $stocks = Stock::where('warehouse_id', $warehouseId)
            ->where('product_id', $productId)
            ->orderBy('id')
            ->get();

        if ($stocks->count() <= 1) {
            return;
        }

        $primary = $stocks->first();
        $primary->quantity       = $stocks->sum('quantity');
        $primary->reserved_qty   = $stocks->sum('reserved_qty');
        $primary->committed_qty  = $stocks->sum('committed_qty');
        $primary->in_transit_qty = $stocks->sum('in_transit_qty');
        $primary->save();

        DB::table('stocks')
            ->whereIn('id', $stocks->skip(1)->pluck('id')->all())
            ->delete();
    }

    /**
     * Fetch (or create) the stock row with a write-lock.
     * Must only be called inside an active transaction.
     */
    private function getStockForUpdate(int $productId, int $warehouseId): Stock
    {
        // Merge duplicates first (within the same transaction)
        $this->mergeDuplicateStocks($productId, $warehouseId);

        $stock = Stock::where('warehouse_id', $warehouseId)
            ->where('product_id', $productId)
            ->lockForUpdate()
            ->first();

        if ($stock) {
            return $stock;
        }

        // Create a brand-new row if it doesn't exist yet
        return Stock::create([
            'warehouse_id'   => $warehouseId,
            'product_id'     => $productId,
            'quantity'       => 0,
            'reserved_qty'   => 0,
            'committed_qty'  => 0,
            'in_transit_qty' => 0,
        ]);
    }

    /**
     * Write a StockMovement audit record.
     * Must only be called inside an active transaction.
     */
    private function logMovement(
        int     $productId,
        int     $warehouseId,
        float   $quantity,
        string  $type,
        ?string $referenceType = null,
        ?int    $referenceId   = null
    ): void {
        StockMovement::create([
            'product_id'     => $productId,
            'warehouse_id'   => $warehouseId,
            'quantity'       => $quantity,
            'type'           => $type,
            'reference_type' => $referenceType,
            'reference_id'   => $referenceId,
            'status'         => 'active',
            'performed_by'   => auth()->id(),
        ]);
    }

    private function ensurePositive(float $quantity, string $field = 'quantity'): void
    {
        if ($quantity <= 0) {
            throw ValidationException::withMessages([
                $field => 'Quantity must be greater than zero.',
            ]);
        }
    }

    /**
     * Synchronize product status based on aggregate inventory levels.
     * Called at the end of any stock mutation.
     */
    private function syncProductStatus(int $productId): void
    {
        $product = \App\Models\Product::find($productId);
        if (!$product) return;

        // Never auto-activate a draft product
        if ($product->status === 'draft') return;

        $totalAvailable = \App\Models\Stock::where('product_id', $productId)
            ->get()
            ->sum(fn($s) => (float) $s->quantity - (float) $s->reserved_qty);

        $newStatus = $product->status;

        if ($totalAvailable <= 0 && !$product->allow_overselling) {
            $newStatus = 'out_of_stock';
        } else {
            // If it was out of stock but now has stock or overselling is enabled, activate it
            if ($product->status === 'out_of_stock') {
                $newStatus = 'active';
            }
        }

        if ($newStatus !== $product->status) {
            $product->update(['status' => $newStatus]);
        }
    }

    // ─────────────────────────────────────────────────────────────────────────
    //  Public read helpers
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * Get (or create) a stock record – safe for read-only use.
     */
    public function getStock(int $productId, int $warehouseId): Stock
    {
        return DB::transaction(function () use ($productId, $warehouseId) {
            return $this->getStockForUpdate($productId, $warehouseId);
        });
    }

    /**
     * Available qty = total qty − reserved qty.
     * This is the number a customer can actually order.
     */
    public function getAvailableQty(int $productId, int $warehouseId): float
    {
        $stock = Stock::where('product_id', $productId)
            ->where('warehouse_id', $warehouseId)
            ->first();

        if (!$stock) {
            return 0.0;
        }

        return max(0.0, (float) $stock->quantity - (float) $stock->reserved_qty);
    }

    // ─────────────────────────────────────────────────────────────────────────
    //  Stock mutations (each owns its own transaction)
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * Hard-set a stock quantity (used by adjustments & imports).
     */
    public function setStock(int $productId, int $warehouseId, float $newQuantity): Stock
    {
        if ($newQuantity < 0) {
            throw ValidationException::withMessages([
                'quantity' => 'Stock quantity cannot be negative.',
            ]);
        }

        return DB::transaction(function () use ($productId, $warehouseId, $newQuantity) {
            $stock = $this->getStockForUpdate($productId, $warehouseId);

            if ($stock->reserved_qty > $newQuantity) {
                throw ValidationException::withMessages([
                    'quantity' => 'New quantity cannot be lower than reserved stock.',
                ]);
            }

            $diff = $newQuantity - (float) $stock->quantity;
            $stock->quantity = $newQuantity;
            $stock->save();

            $this->logMovement(
                $productId,
                $warehouseId,
                abs($diff),
                'adjustment'
            );

            $this->syncProductStatus($productId);

            return $stock->refresh();
        });
    }

    /**
     * Add stock (e.g. purchase received, transfer in).
     */
    public function addStock(
        int    $productId,
        int    $warehouseId,
        float  $quantity,
        string $referenceType = null,
        int    $referenceId   = null
    ): Stock {
        $this->ensurePositive($quantity);

        return DB::transaction(function () use ($productId, $warehouseId, $quantity, $referenceType, $referenceId) {
            $stock = $this->getStockForUpdate($productId, $warehouseId);
            $stock->quantity = (float) $stock->quantity + $quantity;
            $stock->save();

            $this->logMovement($productId, $warehouseId, $quantity, 'in', $referenceType, $referenceId);

            $this->syncProductStatus($productId);

            return $stock->refresh();
        });
    }

    /**
     * Deduct stock (e.g. sale shipped, transfer out).
     */
    public function deductStock(
        int    $productId,
        int    $warehouseId,
        float  $quantity,
        string $referenceType = null,
        int    $referenceId   = null
    ): Stock {
        $this->ensurePositive($quantity);

        return DB::transaction(function () use ($productId, $warehouseId, $quantity, $referenceType, $referenceId) {
            $stock = $this->getStockForUpdate($productId, $warehouseId);

            if ((float) $stock->quantity < $quantity) {
                throw ValidationException::withMessages([
                    'quantity' => 'Insufficient stock quantity.',
                ]);
            }

            $newQty = (float) $stock->quantity - $quantity;

            if ($stock->reserved_qty > $newQty) {
                throw ValidationException::withMessages([
                    'quantity' => 'Cannot deduct below reserved stock.',
                ]);
            }

            $stock->quantity = $newQty;
            $stock->save();

            $this->logMovement($productId, $warehouseId, $quantity, 'out', $referenceType, $referenceId);

            $this->syncProductStatus($productId);

            return $stock->refresh();
        });
    }

    /**
     * Reserve stock for a confirmed sale order.
     * Creates a StockReservation record AND increments reserved_qty.
     */
    public function reserveStock(
        int    $productId,
        int    $warehouseId,
        float  $quantity,
        int    $orderId = null
    ): Stock {
        $this->ensurePositive($quantity);

        return DB::transaction(function () use ($productId, $warehouseId, $quantity, $orderId) {
            $stock        = $this->getStockForUpdate($productId, $warehouseId);
            $product      = Product::find($productId);
            $rawAvailable = (float) $stock->quantity - (float) $stock->reserved_qty;
            $maxReservable = $rawAvailable;
            if ($product?->allow_overselling) {
                $maxReservable += (float) ($product->overselling_qty ?: 999);
            }

            if ($maxReservable < $quantity) {
                throw ValidationException::withMessages([
                    'quantity' => "Not enough stock (including oversell allowance) to reserve. Available: {$maxReservable}, Requested: {$quantity}.",
                ]);
            }

            $stock->reserved_qty = (float) $stock->reserved_qty + $quantity;
            $stock->save();

            // Write a reservation record so we can trace it back to the order
            StockReservation::create([
                'product_id'   => $productId,
                'warehouse_id' => $warehouseId,
                'order_id'     => $orderId,
                'quantity'     => $quantity,
                'status'       => 'active',
            ]);

            $this->logMovement($productId, $warehouseId, $quantity, 'reserve', Order::class, $orderId);

            $this->syncProductStatus($productId);

            return $stock->refresh();
        });
    }

    /**
     * Release a reservation (order cancelled / stock freed).
     * Marks StockReservation as cancelled AND decrements reserved_qty.
     */
    public function releaseReservedStock(
        int    $productId,
        int    $warehouseId,
        float  $quantity,
        int    $orderId = null,
        string $reason  = 'cancelled'
    ): Stock {
        $this->ensurePositive($quantity);

        return DB::transaction(function () use ($productId, $warehouseId, $quantity, $orderId, $reason) {
            $stock = $this->getStockForUpdate($productId, $warehouseId);

            if ((float) $stock->reserved_qty < $quantity) {
                throw ValidationException::withMessages([
                    'quantity' => 'Reserved stock cannot go below zero.',
                ]);
            }

            $stock->reserved_qty = (float) $stock->reserved_qty - $quantity;
            $stock->save();

            // Mark the linked reservation record
            if ($orderId) {
                StockReservation::where('order_id', $orderId)
                    ->where('product_id', $productId)
                    ->where('warehouse_id', $warehouseId)
                    ->where('status', 'active')
                    ->orderBy('id')
                    ->first()
                    ?->update(['status' => $reason === 'used' ? 'used' : 'cancelled']);
            }

            $this->logMovement($productId, $warehouseId, $quantity, 'release', Order::class, $orderId);

            $this->syncProductStatus($productId);

            return $stock->refresh();
        });
    }

    /**
     * Inner work of a transfer — must only be called inside an existing transaction.
     * Avoids nested DB::transaction when called from receiveTransfer.
     */
    private function _executeTransfer(
        int   $productId,
        int   $fromWarehouseId,
        int   $toWarehouseId,
        float $quantity,
        int   $transferId = null
    ): void {
        // Lock both rows in deterministic order to prevent deadlocks
        $ids = [$fromWarehouseId, $toWarehouseId];
        sort($ids);
        foreach ($ids as $wid) {
            $this->getStockForUpdate($productId, $wid);
        }

        $from = $this->getStockForUpdate($productId, $fromWarehouseId);
        if ((float) $from->quantity < $quantity) {
            throw ValidationException::withMessages([
                'quantity' => 'Insufficient stock to transfer.',
            ]);
        }
        $from->quantity = (float) $from->quantity - $quantity;
        $from->save();

        $to = $this->getStockForUpdate($productId, $toWarehouseId);
        $to->quantity = (float) $to->quantity + $quantity;
        $to->save();

        $this->logMovement($productId, $fromWarehouseId, $quantity, 'transfer', StockTransfer::class, $transferId);
        $this->logMovement($productId, $toWarehouseId,   $quantity, 'in',       StockTransfer::class, $transferId);
        $this->syncProductStatus($productId);
    }

    /**
     * Transfer stock between two warehouses atomically.
     */
    public function transferStock(
        int   $productId,
        int   $fromWarehouseId,
        int   $toWarehouseId,
        float $quantity,
        int   $transferId = null
    ): void {
        $this->ensurePositive($quantity);

        DB::transaction(function () use ($productId, $fromWarehouseId, $toWarehouseId, $quantity, $transferId) {
            $this->_executeTransfer($productId, $fromWarehouseId, $toWarehouseId, $quantity, $transferId);
        });
    }

    // ─────────────────────────────────────────────────────────────────────────
    //  High-level order lifecycle (called by OrderController)
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * Confirm a pending sale order → reserve stock for each item.
     */
    public function confirmOrder(Order $order): void
    {
        DB::transaction(function () use ($order) {
            /** @var Order $order */
            $order = Order::with('items')->lockForUpdate()->findOrFail($order->id);

            if ($order->status !== 'pending') {
                throw ValidationException::withMessages([
                    'status' => 'Only pending orders can be confirmed.',
                ]);
            }

            if (!$order->warehouse_id) {
                throw ValidationException::withMessages([
                    'warehouse_id' => 'Order must have a warehouse assigned before confirmation.',
                ]);
            }

            if ($order->type === 'sale') {
                foreach ($order->items as $item) {
                    // Reserve stock – uses its own internal lock, same transaction
                    $stock        = $this->getStockForUpdate((int) $item->product_id, (int) $order->warehouse_id);
                    $product      = Product::find((int) $item->product_id);
                    $rawAvailable = (float) $stock->quantity - (float) $stock->reserved_qty;
                    $maxReservable = $rawAvailable;
                    if ($product?->allow_overselling) {
                        $maxReservable += (float) ($product->overselling_qty ?: 999);
                    }

                    if ($maxReservable < (float) $item->quantity) {
                        throw ValidationException::withMessages([
                            'quantity' => "Insufficient stock (including oversell allowance) for product ID {$item->product_id}. Available: {$maxReservable}.",
                        ]);
                    }

                    $stock->reserved_qty = (float) $stock->reserved_qty + (float) $item->quantity;
                    $stock->save();

                    StockReservation::create([
                        'product_id'   => $item->product_id,
                        'warehouse_id' => $order->warehouse_id,
                        'order_id'     => $order->id,
                        'quantity'     => $item->quantity,
                        'status'       => 'active',
                    ]);

                    $this->logMovement(
                        (int) $item->product_id,
                        (int) $order->warehouse_id,
                        (float) $item->quantity,
                        'reserve',
                        Order::class,
                        $order->id
                    );
                }
            }

            $order->update(['status' => 'confirmed', 'updated_by' => auth()->id()]);

            // Sync status for all items in the order
            foreach ($order->items as $item) {
                $this->syncProductStatus((int) $item->product_id);
            }

            activity('orders')
                ->performedOn($order)
                ->causedBy(auth()->user())
                ->withProperties(['order_no' => $order->order_no, 'warehouse_id' => $order->warehouse_id, 'items_count' => $order->items->count()])
                ->log("Order #{$order->order_no} confirmed — stock reserved in warehouse #{$order->warehouse_id}");
        });
    }

    /**
     * Ship a confirmed order → release reservation + deduct actual stock.
     * For purchase orders → add stock.
     */
    public function shipOrder(Order $order, ?string $carrierName = null, ?string $trackingNo = null): void
    {
        DB::transaction(function () use ($order, $carrierName, $trackingNo) {
            /** @var Order $order */
            $order = Order::with('items')->lockForUpdate()->findOrFail($order->id);

            if (!in_array($order->status, ['confirmed', 'processing'])) {
                throw ValidationException::withMessages([
                    'status' => 'Only confirmed or processing orders can be shipped.',
                ]);
            }

            if (!$order->warehouse_id) {
                throw ValidationException::withMessages([
                    'warehouse_id' => 'Order must have a warehouse assigned.',
                ]);
            }

            foreach ($order->items as $item) {
                $productId   = (int) $item->product_id;
                $warehouseId = (int) $order->warehouse_id;
                $qty         = (float) $item->quantity;

                if ($order->type === 'sale') {
                    // 1. Acquire write-lock on stock row
                    $stock = $this->getStockForUpdate($productId, $warehouseId);

                    if ((float) $stock->reserved_qty < $qty) {
                        throw ValidationException::withMessages([
                            'quantity' => "Reserved stock mismatch for product ID {$productId}.",
                        ]);
                    }

                    $product = Product::find($productId);
                    if (!$product?->allow_overselling && (float) $stock->quantity < $qty) {
                        throw ValidationException::withMessages([
                            'quantity' => "Insufficient physical stock for product ID {$productId}.",
                        ]);
                    }

                    // 2. Decrement reserved + on-hand; increment dispatched (permanent audit counter)
                    $stock->reserved_qty   = (float) $stock->reserved_qty - $qty;
                    $stock->quantity       = (float) $stock->quantity      - $qty;
                    $stock->dispatched_qty = (float) $stock->dispatched_qty + $qty;
                    $stock->save();

                    // 3. Mark the linked StockReservation as 'used'
                    StockReservation::where('order_id', $order->id)
                        ->where('product_id', $productId)
                        ->where('warehouse_id', $warehouseId)
                        ->where('status', 'active')
                        ->orderBy('id')
                        ->first()
                        ?->update(['status' => 'used']);

                    $this->logMovement($productId, $warehouseId, $qty, 'out', Order::class, $order->id);

                } else {
                    // Purchase order → receive stock into warehouse
                    $stock = $this->getStockForUpdate($productId, $warehouseId);
                    $stock->quantity = (float) $stock->quantity + $qty;
                    $stock->save();

                    $this->logMovement($productId, $warehouseId, $qty, 'in', Order::class, $order->id);
                }
            }

            // Create Shipment record automatically
            $shipment = \App\Models\Shipment::create([
                'shipment_no'  => 'SHP-' . strtoupper(\Illuminate\Support\Str::random(8)),
                'order_id'     => $order->id,
                'status'       => 'shipped',
                'shipped_at'   => now(),
                'carrier_name' => $carrierName,
                'tracking_no'  => $trackingNo,
            ]);

            // Log initial tracking event
            \App\Models\ShipmentTrackingEvent::create([
                'shipment_id' => $shipment->id,
                'event_name'  => 'Shipped',
                'location'    => $order->warehouse?->name ?? 'Warehouse',
                'description' => 'The order has been shipped from the warehouse.',
                'occurred_at' => now(),
            ]);

            $order->update(['status' => 'shipped', 'updated_by' => auth()->id()]);

            // Sync status for all items in the order
            foreach ($order->items as $item) {
                $this->syncProductStatus((int) $item->product_id);
            }

            activity('orders')
                ->performedOn($order)
                ->causedBy(auth()->user())
                ->withProperties(array_filter([
                    'order_no'     => $order->order_no,
                    'shipment_no'  => $shipment->shipment_no,
                    'carrier_name' => $carrierName,
                    'tracking_no'  => $trackingNo,
                ]))
                ->log("Order #{$order->order_no} shipped — inventory deducted, shipment #{$shipment->shipment_no} created");
        });
    }

    /**
     * Cancel an order → release any active reservations.
     */
    public function cancelOrder(Order $order): void
    {
        DB::transaction(function () use ($order) {
            /** @var Order $order */
            $order = Order::with('items')->lockForUpdate()->findOrFail($order->id);

            if (in_array($order->status, ['shipped', 'delivered', 'cancelled'], true)) {
                throw ValidationException::withMessages([
                    'status' => 'This order cannot be cancelled.',
                ]);
            }

            // Only release reservations if the order was confirmed or processing (reserved stock)
            if (in_array($order->status, ['confirmed', 'processing']) && $order->type === 'sale' && $order->warehouse_id) {
                foreach ($order->items as $item) {
                    $productId   = (int) $item->product_id;
                    $warehouseId = (int) $order->warehouse_id;
                    $qty         = (float) $item->quantity;

                    $stock = $this->getStockForUpdate($productId, $warehouseId);
                    $releaseQty = min($qty, (float) $stock->reserved_qty);

                    if ($releaseQty > 0) {
                        $stock->reserved_qty = (float) $stock->reserved_qty - $releaseQty;
                        $stock->save();

                        StockReservation::where('order_id', $order->id)
                            ->where('product_id', $productId)
                            ->where('warehouse_id', $warehouseId)
                            ->where('status', 'active')
                            ->orderBy('id')
                            ->first()
                            ?->update(['status' => 'cancelled']);

                        $this->logMovement($productId, $warehouseId, $releaseQty, 'release', Order::class, $order->id);
                    }
                }
            }

            $order->update(['status' => 'cancelled', 'updated_by' => auth()->id()]);

            // Sync status for all items in the order
            foreach ($order->items as $item) {
                $this->syncProductStatus((int) $item->product_id);
            }

            activity('orders')
                ->performedOn($order)
                ->causedBy(auth()->user())
                ->withProperties(['order_no' => $order->order_no, 'previous_status' => $order->getOriginal('status')])
                ->log("Order #{$order->order_no} cancelled — reserved stock released");
        });
    }

    // ─────────────────────────────────────────────────────────────────────────
    //  Adjustment & Transfer lifecycle
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * Apply an approved stock adjustment.
     */
    public function applyAdjustment(InventoryAdjustment $adjustment): void
    {
        DB::transaction(function () use ($adjustment) {
            $adjustment = InventoryAdjustment::with('items')
                ->lockForUpdate()
                ->findOrFail($adjustment->id);

            if ($adjustment->status !== 'pending') {
                throw ValidationException::withMessages([
                    'status' => 'Only pending adjustments can be approved.',
                ]);
            }

            foreach ($adjustment->items as $item) {
                $productId   = (int) $item->product_id;
                $warehouseId = (int) $adjustment->warehouse_id;
                $newQty      = (float) $item->new_qty;

                $stock = $this->getStockForUpdate($productId, $warehouseId);

                if ($stock->reserved_qty > $newQty) {
                    throw ValidationException::withMessages([
                        'quantity' => "New qty for product ID {$productId} is below reserved qty.",
                    ]);
                }

                $diff = $newQty - (float) $stock->quantity;
                $stock->quantity = $newQty;
                $stock->save();

                $this->logMovement(
                    $productId,
                    $warehouseId,
                    abs($diff),
                    'adjustment',
                    InventoryAdjustment::class,
                    $adjustment->id
                );
            }

            $adjustment->update(['status' => 'approved']);

            // Sync status for all items in the adjustment
            foreach ($adjustment->items as $item) {
                $this->syncProductStatus((int) $item->product_id);
            }

            activity('inventory')
                ->performedOn($adjustment)
                ->causedBy(auth()->user())
                ->withProperties([
                    'reference_no'  => $adjustment->reference_no,
                    'warehouse_id'  => $adjustment->warehouse_id,
                    'items_count'   => $adjustment->items->count(),
                ])
                ->log("Stock adjustment {$adjustment->reference_no} approved — stock levels updated for {$adjustment->items->count()} product(s)");

        });
    }

    /**
     * Receive a stock transfer (status: sent → received).
     */
    public function receiveTransfer(StockTransfer $transfer): void
    {
        DB::transaction(function () use ($transfer) {
            $transfer = StockTransfer::with('items')
                ->lockForUpdate()
                ->findOrFail($transfer->id);

            if ($transfer->status !== 'sent') {
                throw ValidationException::withMessages([
                    'status' => 'Only sent transfers can be received.',
                ]);
            }

            // Use _executeTransfer directly to avoid nested DB::transactions
            foreach ($transfer->items as $item) {
                $this->_executeTransfer(
                    (int) $item->product_id,
                    (int) $transfer->from_warehouse_id,
                    (int) $transfer->to_warehouse_id,
                    (float) $item->quantity,
                    $transfer->id
                );
            }

            $transfer->update([
                'status'      => 'received',
                'received_at' => now(),
            ]);

            activity('inventory')
                ->performedOn($transfer)
                ->withProperties(['items_count' => $transfer->items->count()])
                ->causedBy(auth()->user())
                ->log("Transfer #{$transfer->transfer_no} received — stock moved from warehouse {$transfer->from_warehouse_id} to {$transfer->to_warehouse_id}");
        });
    }

    /**
     * Cancel a stock transfer that is in 'sent' state.
     * Returns stock to the source warehouse.
     */
    public function cancelSentTransfer(StockTransfer $transfer): void
    {
        DB::transaction(function () use ($transfer) {
            $transfer = StockTransfer::with('items')
                ->lockForUpdate()
                ->findOrFail($transfer->id);

            if ($transfer->status === 'draft') {
                $transfer->update(['status' => 'cancelled']);
                return;
            }

            if ($transfer->status !== 'sent') {
                throw ValidationException::withMessages([
                    'status' => 'Only draft or sent transfers can be cancelled.',
                ]);
            }

            // 'sent' means stock was already logically moved out of the source
            // but not yet received – nothing to do on DB stock because stock
            // is only moved on receiveTransfer. Just mark cancelled.
            $transfer->update(['status' => 'cancelled']);
        });
    }

    // ─────────────────────────────────────────────────────────────────────────
    //  Revert transitions (called ONLY from bulkStatus — replaces raw DB writes)
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * Revert a confirmed/processing sale order back to pending.
     * Releases active reservations and moves status to 'pending'.
     */
    public function revertOrderToPending(Order $order): void
    {
        DB::transaction(function () use ($order) {
            $order = Order::with('items')->lockForUpdate()->findOrFail($order->id);

            if (!in_array($order->status, ['confirmed', 'processing', 'cancelled'], true)) {
                throw ValidationException::withMessages([
                    'status' => 'Cannot revert this order to pending.',
                ]);
            }

            if (in_array($order->status, ['confirmed', 'processing']) && $order->type === 'sale' && $order->warehouse_id) {
                foreach ($order->items as $item) {
                    $productId   = (int) $item->product_id;
                    $warehouseId = (int) $order->warehouse_id;
                    $qty         = (float) $item->quantity;

                    $stock      = $this->getStockForUpdate($productId, $warehouseId);
                    $releaseQty = min($qty, (float) $stock->reserved_qty);

                    if ($releaseQty > 0) {
                        $stock->reserved_qty = (float) $stock->reserved_qty - $releaseQty;
                        $stock->save();

                        StockReservation::where('order_id', $order->id)
                            ->where('product_id', $productId)
                            ->where('warehouse_id', $warehouseId)
                            ->where('status', 'active')
                            ->orderBy('id')
                            ->first()
                            ?->update(['status' => 'cancelled']);

                        $this->logMovement($productId, $warehouseId, $releaseQty, 'release', Order::class, $order->id);
                        $this->syncProductStatus($productId);
                    }
                }
            }

            $order->update(['status' => 'pending', 'updated_by' => auth()->id()]);
        });
    }

    /**
     * Revert a shipped order back to processing.
     * Restores physical stock + reservations and removes the shipment record.
     */
    public function revertOrderToProcessing(Order $order): void
    {
        DB::transaction(function () use ($order) {
            $order = Order::with('items')->lockForUpdate()->findOrFail($order->id);

            if ($order->status !== 'shipped') {
                throw ValidationException::withMessages([
                    'status' => 'Only shipped orders can be reverted to processing.',
                ]);
            }

            // Remove shipment records
            foreach ($order->shipments as $shp) {
                $shp->events()->delete();
                $shp->delete();
            }

            if ($order->type === 'sale' && $order->warehouse_id) {
                foreach ($order->items as $item) {
                    $productId   = (int) $item->product_id;
                    $warehouseId = (int) $order->warehouse_id;
                    $qty         = (float) $item->quantity;

                    $stock = $this->getStockForUpdate($productId, $warehouseId);
                    $stock->quantity       = (float) $stock->quantity + $qty;
                    $stock->reserved_qty   = (float) $stock->reserved_qty + $qty;
                    $stock->dispatched_qty = max(0.0, (float) $stock->dispatched_qty - $qty);
                    $stock->save();

                    StockReservation::where('order_id', $order->id)
                        ->where('product_id', $productId)
                        ->where('warehouse_id', $warehouseId)
                        ->update(['status' => 'active']);

                    $this->logMovement($productId, $warehouseId, $qty, 'in', Order::class, $order->id);
                    $this->syncProductStatus($productId);
                }
            } elseif ($order->type === 'purchase' && $order->warehouse_id) {
                foreach ($order->items as $item) {
                    $productId   = (int) $item->product_id;
                    $warehouseId = (int) $order->warehouse_id;
                    $qty         = (float) $item->quantity;

                    $stock           = $this->getStockForUpdate($productId, $warehouseId);
                    $stock->quantity  = max(0.0, (float) $stock->quantity - $qty);
                    $stock->save();

                    $this->logMovement($productId, $warehouseId, $qty, 'out', Order::class, $order->id);
                    $this->syncProductStatus($productId);
                }
            }

            $order->update(['status' => 'processing', 'updated_by' => auth()->id()]);
        });
    }
}
