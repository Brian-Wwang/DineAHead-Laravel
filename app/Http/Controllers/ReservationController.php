<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\ReservationService;
use App\Services\StoreReservationService;
use App\Models\Reservation;

class ReservationController extends Controller
{
    protected ReservationService $reservationService;
    protected StoreReservationService $storeReservationService;

    public function __construct(ReservationService $service, StoreReservationService $storeService)
    {
        $this->reservationService = $service;
        $this->storeReservationService = $storeService;
    }

    /**
     * 用户端：获取列表
     */
    public function list(Request $request)
    {
        $user = $request->user();
        $list = Reservation::where('store_id', $user->store->id)
            ->with(['slots','order'])
            ->get();
        return api_response($list);
    }

    /**
     * 用户端：创建预订（可附带订单）
     */
    public function create(Request $request)
    {
        $validated = $request->validate([
            // Reservation 部分
            'reservation.table_id'    => 'required|exists:tables,id',
            'reservation.slot_start'  => 'required|date',
            'reservation.slot_end'    => 'required|date|after:reservation.slot_start',
            'reservation.remark'      => 'nullable|string|max:255',

            // Order 部分（可选）
            'order.items'                => 'nullable|array',
            'order.items.*.menu_id'      => 'required_with:order.items|exists:menus,id',
            'order.items.*.quantity'     => 'required_with:order.items|integer|min:1',
            'order.items.*.unit_price'   => 'required_with:order.items|numeric|min:0',
            'order.items.*.options'      => 'nullable|array',
            'order.items.*.options.*.platform_option_value_id' => 'nullable|exists:platform_option_values,id',
            'order.items.*.options.*.store_option_value_id'    => 'nullable|exists:store_option_values,id',
            'order.items.*.options.*.extra_price'              => 'nullable|numeric|min:0',

            'order.total_price'   => 'nullable|numeric|min:0',
            'order.transaction_id'=> 'nullable|string|max:255'
        ]);

        $user = $request->user();

        if (!empty($validated['order'])) {
            $reservation = $this->reservationService->createWithOrder($validated, $user);
        } else {
            $reservation = $this->reservationService->create($validated['reservation'], $user);
        }

        return api_response($reservation, 'Reservation created');
    }

    /**
     * 用户端：取消预订
     */
    public function cancel(Request $request)
    {
        $validated = $request->validate([
            'id' => 'required|exists:reservations,id'
        ]);
        $reservation = $this->reservationService->cancel($validated['id'], $request->user());
        return api_response($reservation, 'Reservation cancelled');
    }

    /**
     * 用户端：更新预订状态
     */
    public function updateStatus(Request $request)
    {
        $validated = $request->validate([
            'id'     => 'required|integer|exists:reservations,id',
            'status' => 'required|integer|in:10,20,30,40,50', // ReservationStatus 枚举
        ]);

        $reservation = $this->reservationService->updateStatus(
            $validated['id'],
            $validated['status'],
            $request->user()
        );

        return api_response($reservation, 'Reservation status updated');
    }

    /**
     * 用户端：获取某桌的可用时段
     */
    public function availableSlots(Request $request)
    {
        $validated = $request->validate([
            'table_id' => 'required|exists:tables,id',
            'date'     => 'required|date_format:Y-m-d',
        ]);

        $slots = $this->reservationService->getAvailableSlots(
            $validated['table_id'],
            $validated['date']
        );

        return api_response($slots);
    }

    // ===============================
    // 🔹 以下是商家端接口
    // ===============================

    /**
     * 商家端：仅创建 Reservation（不带订单）
     */
   public function storeCreate(Request $request) {
    $validated = $request->validate([
        // Reservation 部分
        'reservation.table_id'    => 'required|exists:tables,id',
        'reservation.slot_start'  => 'required|date',
        'reservation.slot_end'    => 'required|date|after:reservation.slot_start',
        'reservation.remark'      => 'nullable|string|max:255',
        'reservation.user_id'     => 'nullable|exists:users,id',

        // Order 部分（可选）
        'order.items'                => 'nullable|array|min:1',
        'order.items.*.menu_id'      => 'required_with:order.items|exists:menus,id',
        'order.items.*.quantity'     => 'required_with:order.items|integer|min:1',
        'order.items.*.unit_price'   => 'required_with:order.items|numeric|min:0',
        'order.items.*.options'      => 'nullable|array',
        'order.items.*.options.*.platform_option_value_id' => 'nullable|exists:platform_option_values,id',
        'order.items.*.options.*.store_option_value_id'    => 'nullable|exists:store_option_values,id',
        'order.items.*.options.*.extra_price'              => 'nullable|numeric|min:0',

        'order.transaction_id'       => 'nullable|string|max:255'
    ]);

    // 判断是否有 order
    if (!empty($validated['order'])) {
        $reservation = $this->storeReservationService->storeCreateWithOrder($validated);
    } else {
        $reservation = $this->storeReservationService->storeCreate($validated['reservation']);
    }

    return api_response($reservation, 'Store created reservation');
}


    /**
     * 商家端：更新 Reservation
     */
    public function storeUpdate(Request $request)
    {
        $validated = $request->validate([
            'id'         => 'required|exists:reservations,id',
            'remark'     => 'nullable|string|max:255',
            'status'     => 'nullable|integer|in:10,20,30,40,50',
            'slot_start' => 'nullable|date',
            'slot_end'   => 'nullable|date|after:slot_start',
        ]);

        $reservation = $this->storeReservationService->storeUpdate($validated['id'], $validated);

        return api_response($reservation, 'Store updated reservation');
    }
}
