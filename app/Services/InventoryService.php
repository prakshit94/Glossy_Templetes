<?php

namespace App\Services;

use App\Models\InventoryAdjustment;
use App\Models\Order;
use App\Models\Stock;
use App\Models\StockTransfer;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class InventoryService
{
    private function normalizeDuplicateStocks(int $productId, int $warehouseId): void
    {
        $stocks = Stock::where('warehouse_id', $warehouseId)
            ->where('product_id', $productId)
            ->orderBy('id')
            ->get();

        if ($stocks->count() <= 1) {
            return;
        }

        $primary = $stocks->first();
        $primary->quantity = $stocks->sum('quantity');
        $primary->reserved_qty = $stocks->sum('reserved_qty');
        $primary->committed_qty = $stocks->sum('committed_qty');
        $primary->in_transit_qty = $stocks->sum('in_transit_qty');
        $primary->save();

        DB::table('stocks')->whereIn('id', $stocks->skip(1)->pluck('id')->all())->delete();
    }

    /**
     * Get or create stock record for a product in a warehouse.
     */
    public function getStock(int $productId, int $warehouseId): Stock
    {
        return DB::transaction(function () use ($productId, $warehouseId) {
            $this->normalizeDuplicateStocks($productId, $warehouseId);

            return Stock::firstOrCreate(
                ['warehouse_id' => $warehouseId, 'product_id' => $productId],
                ['quantity' => 0, 'reserved_qty' => 0, 'committed_qty' => 0, 'in_transit_qty' => 0]
            );
        });
    }

    /**
     * Get a stock row with write lock for safe concurrent updates.
     */
    private function getStockForUpdate(int $productId, int $warehouseId): Stock
    {
        $this->normalizeDuplicateStocks($productId, $warehouseId);

        $stock = Stock::where('warehouse_id', $warehouseId)
            ->where('product_id', $productId)
            ->lockForUpdate()
            ->first();

        if ($stock) {
            return $stock;
        }

        return $this->getStock($productId, $warehouseId);
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
     * Update stock quantity exactly.
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

            $stock->quantity = $newQuantity;
            $stock->save();

            return $stock->refresh();
        });
    }

    /**
     * Add to stock quantity.
     */
    public function addStock(int $productId, int $warehouseId, float $quantity): Stock
    {
        $this->ensurePositive($quantity);

        return DB::transaction(function () use ($productId, $warehouseId, $quantity) {
            $stock = $this->getStockForUpdate($productId, $warehouseId);
            $stock->quantity = (float) $stock->quantity + $quantity;
            $stock->save();

            return $stock->refresh();
        });
    }

    /**
     * Deduct from stock quantity.
     */
    public function deductStock(int $productId, int $warehouseId, float $quantity): Stock
    {
        $this->ensurePositive($quantity);

        return DB::transaction(function () use ($productId, $warehouseId, $quantity) {
            $stock = $this->getStockForUpdate($productId, $warehouseId);
            if ((float) $stock->quantity < $quantity) {
                throw ValidationException::withMessages([
                    'quantity' => 'Insufficient stock quantity.',
                ]);
            }

            $stock->quantity = (float) $stock->quantity - $quantity;
            if ($stock->reserved_qty > $stock->quantity) {
                throw ValidationException::withMessages([
                    'quantity' => 'Cannot deduct below reserved stock.',
                ]);
            }

            $stock->save();
            return $stock->refresh();
        });
    }

    /**
     * Transfer stock from one warehouse to another.
     */
    public function transferStock(int $productId, int $fromWarehouseId, int $toWarehouseId, float $quantity): void
    {
        $this->ensurePositive($quantity);
        DB::transaction(function() use ($productId, $fromWarehouseId, $toWarehouseId, $quantity) {
            $this->deductStock($productId, $fromWarehouseId, $quantity);
            $this->addStock($productId, $toWarehouseId, $quantity);
        });
    }

    /**
     * Reserve stock for orders.
     */
    public function reserveStock(int $productId, int $warehouseId, float $quantity): Stock
    {
        $this->ensurePositive($quantity);

        return DB::transaction(function () use ($productId, $warehouseId, $quantity) {
            $stock = $this->getStockForUpdate($productId, $warehouseId);
            $availableQty = (float) $stock->quantity - (float) $stock->reserved_qty;
            if ($availableQty < $quantity) {
                throw ValidationException::withMessages([
                    'quantity' => 'Not enough available stock to reserve.',
                ]);
            }

            $stock->reserved_qty = (float) $stock->reserved_qty + $quantity;
            $stock->save();

            return $stock->refresh();
        });
    }

    /**
     * Release reserved stock.
     */
    public function releaseReservedStock(int $productId, int $warehouseId, float $quantity): Stock
    {
        $this->ensurePositive($quantity);

        return DB::transaction(function () use ($productId, $warehouseId, $quantity) {
            $stock = $this->getStockForUpdate($productId, $warehouseId);
            if ((float) $stock->reserved_qty < $quantity) {
                throw ValidationException::withMessages([
                    'quantity' => 'Reserved stock cannot go below zero.',
                ]);
            }

            $stock->reserved_qty = (float) $stock->reserved_qty - $quantity;
            $stock->save();

            return $stock->refresh();
        });
    }

    public function applyAdjustment(InventoryAdjustment $adjustment): void
    {
        DB::transaction(function () use ($adjustment) {
            $adjustment = InventoryAdjustment::with('items')->lockForUpdate()->findOrFail($adjustment->id);
            if ($adjustment->status !== 'pending') {
                throw ValidationException::withMessages(['status' => 'Only pending adjustments can be approved.']);
            }

            foreach ($adjustment->items as $item) {
                $this->setStock((int) $item->product_id, (int) $adjustment->warehouse_id, (float) $item->new_qty);
            }

            $adjustment->update(['status' => 'approved']);
        });
    }

    public function receiveTransfer(StockTransfer $transfer): void
    {
        DB::transaction(function () use ($transfer) {
            $transfer = StockTransfer::with('items')->lockForUpdate()->findOrFail($transfer->id);
            if ($transfer->status !== 'sent') {
                throw ValidationException::withMessages(['status' => 'Only sent transfers can be received.']);
            }

            foreach ($transfer->items as $item) {
                $this->transferStock(
                    (int) $item->product_id,
                    (int) $transfer->from_warehouse_id,
                    (int) $transfer->to_warehouse_id,
                    (float) $item->quantity
                );
            }

            $transfer->update([
                'status' => 'received',
                'received_at' => now(),
            ]);
        });
    }

    public function confirmOrder(Order $order): void
    {
        DB::transaction(function () use ($order) {
            $order = Order::with('items')->lockForUpdate()->findOrFail($order->id);
            if ($order->status !== 'pending') {
                throw ValidationException::withMessages(['status' => 'Only pending orders can be confirmed.']);
            }

            if ($order->type === 'sale') {
                foreach ($order->items as $item) {
                    $this->reserveStock((int) $item->product_id, (int) $order->warehouse_id, (float) $item->quantity);
                }
            }

            $order->update(['status' => 'confirmed']);
        });
    }

    public function shipOrder(Order $order): void
    {
        DB::transaction(function () use ($order) {
            $order = Order::with('items')->lockForUpdate()->findOrFail($order->id);
            if ($order->status !== 'confirmed') {
                throw ValidationException::withMessages(['status' => 'Only confirmed orders can be shipped.']);
            }

            foreach ($order->items as $item) {
                if ($order->type === 'sale') {
                    $this->releaseReservedStock((int) $item->product_id, (int) $order->warehouse_id, (float) $item->quantity);
                    $this->deductStock((int) $item->product_id, (int) $order->warehouse_id, (float) $item->quantity);
                } else {
                    $this->addStock((int) $item->product_id, (int) $order->warehouse_id, (float) $item->quantity);
                }
            }

            $order->update(['status' => 'shipped']);
        });
    }
}
