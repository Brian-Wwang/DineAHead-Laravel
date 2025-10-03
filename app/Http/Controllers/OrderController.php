<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\OrderService;

class OrderController extends Controller
{
    protected OrderService $orderService;

    public function __construct(OrderService $service)
    {
        $this->orderService = $service;
    }

    public function index(Request $request)
    {
        $user = $request->user();
        $list = \App\Models\Order::where('store_id', $user->store->id)
            ->with(['items.menu','reservation'])
            ->orderBy('id','desc')
            ->get();
        return api_response($list);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'reservation_id'          => 'nullable|exists:reservations,id',
            'transaction_id'          => 'nullable|string|max:255',
            'remark'                  => 'nullable|string|max:255',
            'items'                   => 'required|array|min:1',
            'items.*.menu_id'         => 'required|exists:menus,id',
            'items.*.quantity'        => 'required|integer|min:1',
            'items.*.unit_price'      => 'required|numeric|min:0',
        ]);

        $order = $this->orderService->create($validated, $request->user());
        return api_response($order, 'Order created');
    }

    public function cancel(Request $request)
    {
        $validated = $request->validate([
            'id' => 'required|exists:orders,id'
        ]);
        $order = $this->orderService->cancel($validated['id'], $request->user());
        return api_response($order, 'Order cancelled');
    }

    public function markPaid(Request $request)
    {
        $validated = $request->validate([
            'transaction_id' => 'required|string|exists:orders,transaction_id'
        ]);
        $order = $this->orderService->markAsPaid($validated['transaction_id']);
        return api_response($order, 'Order marked as paid');
    }
}
