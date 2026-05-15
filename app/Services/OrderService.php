<?php

namespace App\Services;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Customer;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class OrderService
{
    /**
     * Create a new order (Sale or Purchase).
     */
    public function createOrder(array $data)
    {
        return DB::transaction(function () use ($data) {
            $orderNo = 'ORD-' . strtoupper(Str::random(8));
            
            $order = Order::create([
                'order_no'            => $orderNo,
                'type'                => $data['type'],
                'party_id'            => $data['party_id'],
                'warehouse_id'        => $data['warehouse_id'] ?? 1,
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
                    'total_amount'    => $item['total_amount'] ?? ($item['quantity'] * $item['unit_price']),
                ]);
            }

            activity('orders')
                ->performedOn($order)
                ->log("Order #{$orderNo} created ({$data['type']})");

            return $order;
        });
    }

    /**
     * Specialized method for customer "Place Order" (Cart based).
     */
    public function placeCustomerOrder(Customer $customer, array $data)
    {
        $cart = json_decode($data['cart'], true);
        $items = [];

        foreach ($cart as $item) {
            $itemBase = $item['price'] * $item['quantity'];
            $itemDisc = 0;
            if (!empty($item['discountValue']) && (float)$item['discountValue'] > 0) {
                $itemDisc = $item['discountType'] === 'percent'
                    ? $itemBase * ((float)$item['discountValue'] / 100)
                    : min((float)$item['discountValue'], $itemBase);
            }

            $items[] = [
                'product_id'      => $item['id'],
                'quantity'        => $item['quantity'],
                'unit_price'      => $item['price'],
                'discount_amount' => $itemDisc,
                'total_amount'    => $itemBase - $itemDisc,
            ];
        }

        // Capture full address text
        $shippingAddr = \App\Models\PartyAddress::find($data['address_id']);
        $billingAddr  = \App\Models\PartyAddress::find($data['billing_address_id'] ?? $data['address_id']);

        return $this->createOrder([
            'type'                => 'sale',
            'party_id'            => $customer->id,
            'warehouse_id'        => $data['warehouse_id'] ?? null,
            'shipping_address_id' => $data['address_id'] ?? null,
            'billing_address_id'  => $data['billing_address_id'] ?? null,
            'shipping_address'    => $this->formatAddress($shippingAddr),
            'billing_address'     => $this->formatAddress($billingAddr),
            'total_amount'        => $data['subtotal'],
            'tax_amount'          => $data['tax_amount'],
            'discount_amount'     => (float)$data['order_discount_amount'] + (float)$data['coupon_discount'],
            'net_amount'          => $data['grand_total'],
            'items'               => $items,
        ]);
    }

    /**
     * Specialized method for customer "Update Order" (Cart based).
     */
    public function updateCustomerOrder(Order $order, array $data)
    {
        $cart = json_decode($data['cart'], true);
        $items = [];

        foreach ($cart as $item) {
            $itemBase = $item['price'] * $item['quantity'];
            $itemDisc = 0;
            if (!empty($item['discountValue']) && (float)$item['discountValue'] > 0) {
                $itemDisc = $item['discountType'] === 'percent'
                    ? $itemBase * ((float)$item['discountValue'] / 100)
                    : min((float)$item['discountValue'], $itemBase);
            }

            $items[] = [
                'product_id'      => $item['id'],
                'quantity'        => $item['quantity'],
                'unit_price'      => $item['price'],
                'discount_amount' => $itemDisc,
                'total_amount'    => $itemBase - $itemDisc,
            ];
        }

        // Capture full address text
        $shippingAddr = \App\Models\PartyAddress::find($data['address_id']);
        $billingAddr  = \App\Models\PartyAddress::find($data['billing_address_id'] ?? $data['address_id']);

        return DB::transaction(function () use ($order, $data, $items, $shippingAddr, $billingAddr) {
            $order->update([
                'warehouse_id'        => $data['warehouse_id'] ?? null,
                'shipping_address_id' => $data['address_id'] ?? null,
                'billing_address_id'  => $data['billing_address_id'] ?? null,
                'shipping_address'    => $this->formatAddress($shippingAddr),
                'billing_address'     => $this->formatAddress($billingAddr),
                'total_amount'        => $data['subtotal'],
                'tax_amount'          => $data['tax_amount'],
                'discount_amount'     => (float)$data['order_discount_amount'] + (float)$data['coupon_discount'],
                'net_amount'          => $data['grand_total'],
                'updated_by'          => auth()->id(),
            ]);

            // Clear old items and insert new ones
            $order->items()->delete();
            foreach ($items as $item) {
                $order->items()->create($item);
            }

            activity('orders')
                ->performedOn($order)
                ->log("Order #{$order->order_no} updated via cart");

            return $order;
        });
    }

    /**
     * Format Address Model to string
     */
    protected function formatAddress($address)
    {
        if (!$address) return null;
        
        $parts = [
            $address->label,
            $address->address_line_1,
            $address->address_line_2,
            $address->village?->village_name,
            $address->village?->district_name,
            $address->village?->state_name,
            $address->village?->pincode
        ];

        return implode(", ", array_filter($parts));
    }

    /**
     * Get order details for receipt.
     */
    public function getOrderForReceipt(int $orderId)
    {
        return Order::with(['party', 'items.product', 'warehouse'])->findOrFail($orderId);
    }
}
