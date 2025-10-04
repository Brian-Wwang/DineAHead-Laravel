<?php

namespace App\Services;

use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Support\Facades\DB;
use App\Enums\PaymentStatus;

class OrderService
{
    public function create(array $data, $user)
    {
        return DB::transaction(function () use ($data, $user) {
            $order = Order::create([
                'store_id'       => $user->store->id,
                'user_id'        => $user->id,
                'reservation_id' => $data['reservation_id'] ?? null,
                'transaction_id' => $data['transaction_id'] ?? null,
                'total_price'    => 0,
                'remark'         => $data['remark'] ?? null,
                'status'         => 0,
                'payment_status' => PaymentStatus::Unpaid,
            ]);

            $total = 0;
            foreach ($data['items'] as $item) {
                $subtotal = $item['unit_price'] * $item['quantity'];
                $total += $subtotal;

                OrderItem::create([
                    'order_id'   => $order->id,
                    'menu_id'    => $item['menu_id'],
                    'quantity'   => $item['quantity'],
                    'unit_price' => $item['unit_price'],
                    'subtotal'   => $subtotal,
                ]);
            }

            $order->update(['total_price' => $total]);

            return $order->load('items.menu');
        });
    }

    public function cancel(int $id, $user)
    {
        return DB::transaction(function () use ($id, $user) {
            $order = Order::findOrFail($id);

            if ($order->store_id !== $user->store->id) {
                throw new \Exception('无权限取消此订单');
            }

            $order->update(['status' => 3]); // cancelled

            return $order;
        });
    }

    public function markAsPaid(string $transactionId)
    {
        return DB::transaction(function () use ($transactionId) {
            $order = Order::where('transaction_id', $transactionId)->firstOrFail();
            $order->update([
                'status'         => 2, // paid
                'payment_status' => PaymentStatus::Paid,
            ]);
            return $order;
        });
    }
}
