<?php

namespace App\Services;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Customer;
use App\Models\PartyAddress;
use App\Services\InventoryService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

/**
 * OrderService – SINGLE SOURCE OF TRUTH for order creation and mutation.
 *
 * Inventory side-effects (reserve / deduct) are delegated entirely to
 * InventoryService so there is no duplication of stock logic here.
 */
class OrderService
{
    public function __construct(protected InventoryService $inventoryService) {}

    // ─────────────────────────────────────────────────────────────────────────
    //  Create
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * Create a new order (sale or purchase).
     *
     * @param  array $data
     * @return Order
     */
    public function createOrder(array $data): Order
    {
        return DB::transaction(function () use ($data) {
            $orderNo = 'ORD-' . strtoupper(Str::random(8));

            $order = Order::create([
                'order_no'            => $orderNo,
                'type'                => $data['type'],
                'party_id'            => $data['party_id'],
                'warehouse_id'        => $data['warehouse_id'] ?? null,
                'shipping_address_id' => $data['shipping_address_id'] ?? null,
                'billing_address_id'  => $data['billing_address_id'] ?? null,
                'shipping_address'    => $data['shipping_address'] ?? null,
                'billing_address'     => $data['billing_address'] ?? null,
                'order_date'          => $data['order_date'] ?? now(),
                'total_amount'        => $data['total_amount'] ?? 0,
                'tax_amount'          => $data['tax_amount'] ?? 0,
                'discount_amount'     => $data['discount_amount'] ?? 0,
                'net_amount'          => $data['net_amount'] ?? 0,
                'status'              => 'pending',
                'created_by'          => auth()->id(),
                'updated_by'          => auth()->id(),
            ]);

            foreach ($data['items'] as $item) {
                $order->items()->create([
                    'product_id'      => $item['product_id'],
                    'quantity'        => $item['quantity'],
                    'unit_price'      => $item['unit_price'],
                    'discount_amount' => $item['discount_amount'] ?? 0,
                    'tax_amount'      => $item['tax_amount'] ?? 0,
                    'total_amount'    => $item['total_amount']
                        ?? (($item['quantity'] * $item['unit_price']) - ($item['discount_amount'] ?? 0)),
                ]);
            }

            $order->load('items');
            $itemsTotal = (float) $order->items->sum('total_amount');
            $taxAmount  = (float) ($data['tax_amount'] ?? 0);
            $discount   = (float) ($data['discount_amount'] ?? 0);

            $order->update([
                'total_amount'    => $data['total_amount'] ?? $itemsTotal,
                'tax_amount'      => $taxAmount,
                'discount_amount' => $discount,
                'net_amount'      => $data['net_amount'] ?? max(0, $itemsTotal - $discount + $taxAmount),
            ]);

            activity('orders')
                ->performedOn($order)
                ->log("Order #{$orderNo} created ({$data['type']})");

            return $order->refresh();
        });
    }

    // ─────────────────────────────────────────────────────────────────────────
    //  Customer "Place Order" (cart-based)
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * Place a new customer order from the cart payload.
     */
    public function placeCustomerOrder(Customer $customer, array $data): Order
    {
        $cart  = json_decode($data['cart'], true);
        $items = $this->buildItemsFromCart($cart);

        if (empty($items)) {
            throw ValidationException::withMessages([
                'cart' => 'Cart is empty or contains invalid items.',
            ]);
        }

        if (empty($data['warehouse_id'])) {
            throw ValidationException::withMessages([
                'warehouse_id' => 'A warehouse must be selected to place an order.',
            ]);
        }

        $shippingAddr = PartyAddress::find($data['address_id']);
        $billingAddr  = PartyAddress::find($data['billing_address_id'] ?? $data['address_id']);

        $order = $this->createOrder([
            'type'                => 'sale',
            'party_id'            => $customer->id,
            'warehouse_id'        => $data['warehouse_id'],
            'shipping_address_id' => $data['address_id'] ?? null,
            'billing_address_id'  => $data['billing_address_id'] ?? $data['address_id'] ?? null,
            'shipping_address'    => $this->formatAddress($shippingAddr),
            'billing_address'     => $this->formatAddress($billingAddr),
            'order_date'          => now(),
            'total_amount'        => $data['subtotal'],
            'tax_amount'          => $data['tax_amount'],
            'discount_amount'     => (float) ($data['order_discount_amount'] ?? 0)
                                    + (float) ($data['coupon_discount'] ?? 0),
            'net_amount'          => $data['grand_total'],
            'items'               => $items,
        ]);

        if (!empty($data['coupon_code'])) {
            \App\Models\Coupon::where('code', strtoupper($data['coupon_code']))->increment('used_count');
        }

        return $order;
    }

    // ─────────────────────────────────────────────────────────────────────────
    //  Customer "Update Order" (cart-based)
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * Update an existing customer order from the cart payload.
     *
     * - Only 'pending' orders may be updated without inventory side-effects.
     * - If the order is 'confirmed', reservations are released first and
     *   re-applied after the new items are saved.
     */
    public function updateCustomerOrder(Order $order, array $data): Order
    {
        $cart  = json_decode($data['cart'], true);
        $items = $this->buildItemsFromCart($cart);

        if (empty($items)) {
            throw ValidationException::withMessages([
                'cart' => 'Cart is empty or contains invalid items.',
            ]);
        }

        $shippingAddr = PartyAddress::find($data['address_id']);
        $billingAddr  = PartyAddress::find($data['billing_address_id'] ?? $data['address_id']);

        return DB::transaction(function () use ($order, $data, $items, $shippingAddr, $billingAddr) {
            // Reload with lock
            $order = Order::with('items')->lockForUpdate()->findOrFail($order->id);

            if (!in_array($order->status, ['pending', 'confirmed'], true)) {
                throw ValidationException::withMessages([
                    'status' => 'Only pending or confirmed orders can be updated.',
                ]);
            }

            // If already confirmed, release all reservations before recalculating
            if ($order->status === 'confirmed' && $order->type === 'sale' && $order->warehouse_id) {
                foreach ($order->items as $item) {
                    $this->inventoryService->releaseReservedStock(
                        (int) $item->product_id,
                        (int) $order->warehouse_id,
                        (float) $item->quantity,
                        $order->id,
                        'cancelled'
                    );
                }
            }

            $order->update([
                'warehouse_id'        => $data['warehouse_id'] ?? $order->warehouse_id,
                'shipping_address_id' => $data['address_id'] ?? null,
                'billing_address_id'  => $data['billing_address_id'] ?? $data['address_id'] ?? null,
                'shipping_address'    => $this->formatAddress($shippingAddr),
                'billing_address'     => $this->formatAddress($billingAddr),
                'total_amount'        => $data['subtotal'],
                'tax_amount'          => $data['tax_amount'],
                'discount_amount'     => (float) ($data['order_discount_amount'] ?? 0)
                                        + (float) ($data['coupon_discount'] ?? 0),
                'net_amount'          => $data['grand_total'],
                'updated_by'          => auth()->id(),
            ]);

            // Replace items
            $order->items()->delete();
            foreach ($items as $item) {
                $order->items()->create($item);
            }

            // Re-apply reservations if it was confirmed
            if ($order->status === 'confirmed' && $order->type === 'sale' && $order->warehouse_id) {
                $order->load('items');
                foreach ($order->items as $item) {
                    $this->inventoryService->reserveStock(
                        (int) $item->product_id,
                        (int) $order->warehouse_id,
                        (float) $item->quantity,
                        $order->id
                    );
                }
            }

            activity('orders')
                ->performedOn($order)
                ->log("Order #{$order->order_no} updated via cart");

            return $order->refresh();
        });
    }

    public function updateStatus(Order $order, string $status): Order
    {
        $allowedStatuses = ['processing', 'delivered'];
        
        if (!in_array($status, $allowedStatuses)) {
            throw ValidationException::withMessages([
                'status' => "Status '{$status}' cannot be updated via this method.",
            ]);
        }

        $order->update([
            'status'     => $status,
            'updated_by' => auth()->id(),
        ]);

        if ($status === 'delivered') {
            $order->shipments()->where('status', '!=', 'delivered')->update([
                'status' => 'delivered',
                'delivered_at' => now(),
            ]);
        }

        activity('orders')
            ->performedOn($order)
            ->log("Order #{$order->order_no} status updated to {$status}");

        return $order->refresh();
    }

    // ─────────────────────────────────────────────────────────────────────────
    //  Helpers
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * Build the items array from the decoded cart JSON.
     */
    private function buildItemsFromCart(array $cart): array
    {
        $items = [];

        foreach ($cart as $item) {
            if (empty($item['id']) || empty($item['quantity']) || empty($item['price'])) {
                continue;
            }

            $itemBase = (float) $item['price'] * (float) $item['quantity'];
            $itemDisc = 0.0;

            if (!empty($item['discountValue']) && (float) $item['discountValue'] > 0) {
                $itemDisc = $item['discountType'] === 'percent'
                    ? $itemBase * ((float) $item['discountValue'] / 100)
                    : min((float) $item['discountValue'], $itemBase);
            }

            $items[] = [
                'product_id'      => $item['id'],
                'quantity'        => (float) $item['quantity'],
                'unit_price'      => (float) $item['price'],
                'discount_amount' => $itemDisc,
                'tax_amount'      => (float) ($item['tax_amount'] ?? 0),
                'total_amount'    => $itemBase - $itemDisc,
            ];
        }

        return $items;
    }

    /**
     * Format a PartyAddress model to a single-line string.
     */
    protected function formatAddress(?PartyAddress $address): ?string
    {
        if (!$address) {
            return null;
        }

        $parts = array_filter([
            $address->label,
            $address->address_line_1,
            $address->address_line_2,
            $address->village?->village_name,
            $address->village?->district_name,
            $address->village?->state_name,
            $address->village?->pincode,
        ]);

        return implode(', ', $parts) ?: null;
    }

    /**
     * Fetch a full order for receipt printing.
     */
    public function getOrderForReceipt(int $orderId): Order
    {
        return Order::with(['party', 'items.product', 'warehouse'])
            ->findOrFail($orderId);
    }
}
