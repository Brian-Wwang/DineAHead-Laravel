<?php

namespace App\Services;

use App\Models\Reservation;
use App\Models\ReservationSlot;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Table;
use App\Enums\ReservationStatus;
use App\Enums\PaymentStatus;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class StoreReservationService
{
    /**
     * 商家创建 Reservation（仅预定桌，不带用户ID）
     */
    public function storeCreate(array $data)
    {
        return DB::transaction(function () use ($data) {
            $storeId = Auth::user()->store->id;

            // 确认桌子属于该商家
            $table = Table::where('id', $data['table_id'])
                ->where('store_id', $storeId)
                ->where('is_active', true)
                ->firstOrFail();

            // 创建 Reservation（注意 user_id 不填，保持 null）
            $reservation = Reservation::create([
                'store_id' => $storeId,
                'table_id' => $table->id,
                'user_id'  => null,
                'status'   => ReservationStatus::Pending,
                'remark'   => $data['remark'] ?? null,
            ]);

            ReservationSlot::create([
                'reservation_id' => $reservation->id,
                'slot_start'     => $data['slot_start'],
                'slot_end'       => $data['slot_end'],
            ]);

            return $reservation->load('slots');
        });
    }

    /**
     * 商家创建 Reservation + Order（不带用户ID）
     */
    public function storeCreateWithOrder(array $data)
    {
        return DB::transaction(function () use ($data) {
            $storeId = Auth::user()->store->id;

            $resData = $data['reservation'];

            $table = Table::where('id', $resData['table_id'])
                ->where('store_id', $storeId)
                ->firstOrFail();

            // 创建 Reservation
            $reservation = Reservation::create([
                'store_id' => $storeId,
                'table_id' => $table->id,
                'user_id'  => null, // 🚫 不需要 user_id
                'status'   => ReservationStatus::Pending,
                'remark'   => $resData['remark'] ?? null,
            ]);

            ReservationSlot::create([
                'reservation_id' => $reservation->id,
                'slot_start'     => $resData['slot_start'],
                'slot_end'       => $resData['slot_end'],
            ]);

            // 如果有菜单项，生成订单
            if (!empty($data['order']['items'])) {
                $order = Order::create([
                    'reservation_id' => $reservation->id,
                    'store_id'       => $storeId,
                    'user_id'        => null, // 🚫 不需要 user_id
                    'transaction_id' => $data['order']['transaction_id'] ?? uniqid('txn_'),
                    'payment_status' => PaymentStatus::Unpaid,
                    'total_price'    => 0,
                ]);

                $total = 0;
                foreach ($data['order']['items'] as $item) {
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
                $reservation->setRelation('order', $order->load('items.menu'));
            }

            return $reservation->load(['slots','order']);
        });
    }


    /**
     * 商家更新 Reservation（只能操作自己店铺下的）
     */
    public function storeUpdate(int $reservationId, array $data)
    {
        return DB::transaction(function () use ($reservationId, $data) {
            $storeId = Auth::user()->store->id;

            $reservation = Reservation::where('id', $reservationId)
                ->where('store_id', $storeId)
                ->firstOrFail();

            if (isset($data['remark'])) {
                $reservation->remark = $data['remark'];
            }
            if (isset($data['status'])) {
                $reservation->status = $data['status'];
            }
            $reservation->save();

            // 更新时段
            if (isset($data['slot_start']) && isset($data['slot_end'])) {
                ReservationSlot::where('reservation_id', $reservation->id)->delete();

                ReservationSlot::create([
                    'reservation_id' => $reservation->id,
                    'slot_start'     => $data['slot_start'],
                    'slot_end'       => $data['slot_end'],
                ]);
            }

            return $reservation->load('slots');
        });
    }
}
