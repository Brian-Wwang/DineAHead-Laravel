<?php

namespace App\Services;

use App\Models\Reservation;
use App\Models\ReservationSlot;
use Illuminate\Support\Facades\DB;
use App\Enums\ReservationStatus;
use App\Models\OrderItem;
use App\Models\Order;
use App\Models\Table;
use Carbon\Carbon;

class ReservationService
{
    /**
     * 创建 Reservation（仅预定桌）
     */
    public function create(array $data, $user)
    {
        return DB::transaction(function () use ($data, $user) {
            // 查桌子，必须启用
            $table = Table::where('id', $data['table_id'])
                ->where('is_active', true)
                ->firstOrFail();

            // 检查冲突（查 reservation_slots）
            $conflict = ReservationSlot::where(function ($q) use ($data) {
                    $q->whereBetween('slot_start', [$data['slot_start'], $data['slot_end']])
                      ->orWhereBetween('slot_end', [$data['slot_start'], $data['slot_end']]);
                })
                ->whereHas('reservation', function ($q) use ($table) {
                    $q->where('table_id', $table->id)
                      ->where('status', '!=', ReservationStatus::Cancelled->value);
                })
                ->exists();

            if ($conflict) {
                throw new \Exception('该桌在所选时间段已被预定');
            }

            // 创建 Reservation
            $reservation = Reservation::create([
                'store_id'        => $table->store_id, // ✅ 从 table 获取
                'table_id'        => $table->id,
                'user_id'         => $user->id,
                'status'          => ReservationStatus::Pending,
                'remark'          => $data['remark'] ?? null,
                // 'created_by'      => $user->id,
                // 'created_by_name' => $user->name,
            ]);

            // 创建 ReservationSlot
            ReservationSlot::create([
                'reservation_id' => $reservation->id,
                'slot_start'     => $data['slot_start'],
                'slot_end'       => $data['slot_end'],
            ]);

            return $reservation->load('slots');
        });
    }

    /**
     * 取消 Reservation
     */
    public function cancel(int $id, $user)
    {
        return DB::transaction(function () use ($id, $user) {
            $reservation = Reservation::findOrFail($id);

            if ($reservation->store_id !== $user->store->id) {
                throw new \Exception('无权限取消此预定');
            }

            $reservation->update([
                'status'          => ReservationStatus::Cancelled,
                'updated_by'      => $user->id,
                'updated_by_name' => $user->name,
            ]);

            return $reservation;
        });
    }

    /**
     * 更新 Reservation 状态（只允许 Completed / Cancelled）
     */
    public function updateStatus(int $id, int $status, $user)
    {
        return DB::transaction(function () use ($id, $status, $user) {
            $reservation = Reservation::findOrFail($id);

            if ($reservation->store_id !== $user->store->id) {
                throw new \Exception('无权限操作该预定');
            }

            // 只允许更新到最终状态
            if (!in_array($status, [
                ReservationStatus::Completed->value,
                ReservationStatus::Cancelled->value
            ])) {
                throw new \Exception('只能更新为已完成或已取消');
            }

            $reservation->update([
                'status'          => $status,
                'updated_by'      => $user->id,
                'updated_by_name' => $user->name,
            ]);

            // 返回更新后的完整数据
            return $reservation->load(['slots', 'order.items.options.values']);
        });
    }

    /**
     * 创建 Reservation + Order（预定桌 + 点菜单）
     */
    public function createWithOrder(array $data, $user)
    {
        return DB::transaction(function () use ($data, $user) {
            $resData = $data['reservation'];
            $table   = Table::findOrFail($resData['table_id']); // ✅ 拿 store_id

            // 1. 创建 Reservation
            $reservation = Reservation::create([
                'store_id'   => $table->store_id,
                'table_id'   => $table->id,
                'user_id'    => $user->id,
                'status'     => ReservationStatus::Pending,
                'remark'     => $resData['remark'] ?? null,
            ]);

            ReservationSlot::create([
                'reservation_id' => $reservation->id,
                'slot_start'     => $resData['slot_start'],
                'slot_end'       => $resData['slot_end'],
            ]);

            // 2. 如果有菜单 → 创建订单
            if (!empty($data['order']['items'])) {
                $order = Order::create([
                    'reservation_id' => $reservation->id,
                    'store_id'       => $table->store_id,
                    'user_id'        => $user->id,
                    'transaction_id' => $data['order']['transaction_id'] ?? uniqid('txn_'),
                    'payment_status' => \App\Enums\PaymentStatus::Unpaid,
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

            return $reservation->load(['slots', 'order']);
        });
    }

    /**
     * 获取可用时段
     */
    public function getAvailableSlots(int $tableId, string $date): array
    {
        $table = Table::with('store')
            ->where('id', $tableId)
            ->where('is_active', true)
            ->firstOrFail();

        $store = $table->store;

        // 生成营业时间范围（开店+1h，关店-1h）
        $openTime  = Carbon::parse($date.' '.$store->time_start)->addHour();
        $closeTime = Carbon::parse($date.' '.$store->time_close)->subHour();

        if ($openTime >= $closeTime) {
            return [];
        }

        // 生成 slots（每 30 分钟一个 start）
        $slots = [];
        $cursor = $openTime->copy();
        while ($cursor->lt($closeTime)) {
            $slots[] = [
                'start'     => $cursor->format('Y-m-d H:i:s'),
                'available' => true,
            ];
            $cursor->addMinutes(30);
        }

        // 查当天已有 slot
        $reservedSlots = ReservationSlot::whereDate('slot_start', $date)
            ->whereHas('reservation', function ($q) use ($tableId) {
                $q->where('table_id', $tableId)
                  ->where('status', '!=', ReservationStatus::Cancelled->value);
            })
            ->get();

        // 检查冲突：每个 slot 默认持续 1 小时
        foreach ($slots as &$slot) {
            $slotStart = Carbon::parse($slot['start']);
            $slotEnd   = $slotStart->copy()->addHour();

            foreach ($reservedSlots as $resSlot) {
                $resStart = Carbon::parse($resSlot->slot_start);
                $resEnd   = Carbon::parse($resSlot->slot_end);

                if ($resStart < $slotEnd && $resEnd > $slotStart) {
                    $slot['available'] = false;
                    break;
                }
            }
        }

        return $slots;
    }

    // 🔹 更新 Reservation
    public function update(int $reservationId, array $data, $user)
    {
        return DB::transaction(function () use ($reservationId, $data, $user) {
            $reservation = Reservation::where('id', $reservationId)
                ->where('user_id', $user->id) // 限制只能更新自己的
                ->firstOrFail();

            // 如果有 remark / status 需要更新
            if (isset($data['remark'])) {
                $reservation->remark = $data['remark'];
            }
            if (isset($data['status'])) {
                $reservation->status = $data['status'];
            }
            $reservation->save();

            // 如果有更新 slots
            if (isset($data['slot_start']) && isset($data['slot_end'])) {
                // 删除旧的 slot
                ReservationSlot::where('reservation_id', $reservation->id)->delete();

                // 新建 slot
                ReservationSlot::create([
                    'reservation_id' => $reservation->id,
                    'slot_start'     => $data['slot_start'],
                    'slot_end'       => $data['slot_end'],
                ]);
            }

            return $reservation->load('slots');
        });
    }

    // 🔹 详情 Service
    public function detail(int $reservationId, $user)
    {
        return Reservation::with(['slots', 'table', 'store'])
            ->where('id', $reservationId)
            ->where('user_id', $user->id) // 只允许查看自己的
            ->firstOrFail();
    }
}
