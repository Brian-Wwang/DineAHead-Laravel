<?php

namespace App\Http\Controllers;

use App\Models\Menu;
use Illuminate\Http\Request;

class MenuController extends Controller
{
    /**
     * 客户端获取公开菜单（仅 is_active=true）
     */
    public function public(Request $request)
    {
        $request->validate([
            'store_id' => 'required|integer|exists:stores,id',
        ]);

        $menus = Menu::where('store_id', $request->store_id)
            ->where('is_active', true)
            ->whereNull('deleted_at')
            ->orderBy('id', 'asc')
            ->get();

        return response()->json([
            'success' => true,
            'data'    => $menus
        ]);
    }

    /**
     * 商家查看自己的菜单，通过 token 获取 store_id
     */
    public function list(Request $request)
    {
        $storeId = $request->user()->store->id;

        $menus = Menu::where('store_id', $storeId)
            ->whereNull('deleted_at')
            ->orderBy('id', 'asc')
            ->get();

        return response()->json([
            'success' => true,
            'data'    => $menus
        ]);
    }

    /**
     * 创建菜单
     */
    public function create(Request $request)
    {
        $data = $request->validate([
            'name'            => 'required|string|max:255',
            'description'     => 'nullable|string',
            'price' => 'required|decimal:0,2|gt:0', // 0～2 位小数，且 > 0
            'image'           => 'nullable|url',
            'discount_type'   => 'nullable|integer|in:0,10,20', // 0=none,10=percentage,20=actual
            'discount_amount' => 'decimal:0,2|min:0',
        ]);

        $data['store_id'] = $request->user()->store->id;

        $menu = Menu::create($data);

        return response()->json([
            'success' => true,
            'message' => 'Menu created successfully',
            'data'    => $menu
        ], 201);
    }

    /**
     * 更新菜单
     */
    public function update(Request $request)
    {
        $request->validate([
            'id'              => 'required|integer|exists:menus,id',
            'name'            => 'sometimes|string|max:255',
            'description'     => 'nullable|string',
            'price' => 'required|decimal:0,2|gt:0', // 0～2 位小数，且 > 0
            'image'           => 'nullable|url',
            'discount_type'   => 'nullable|integer|in:0,10,20',
            'discount_amount' => 'decimal:0,2|min:0',
            'is_active'       => 'boolean',
        ]);

        $menu = Menu::where('id', $request->id)
            ->where('store_id', $request->user()->store->id)
            ->firstOrFail();

        $menu->update($request->only([
            'name','description','price','image',
            'discount_type','discount_amount','is_active'
        ]));

        return response()->json([
            'success' => true,
            'message' => 'Menu updated successfully',
            'data'    => $menu->fresh()
        ]);
    }

    /**
     * 删除菜单（软删除）
     */
    public function delete(Request $request)
    {
        $request->validate([
            'id' => 'required|integer|exists:menus,id',
        ]);

        $menu = Menu::where('id', $request->id)
            ->where('store_id', $request->user()->store->id)
            ->firstOrFail();

        $menu->delete();

        return response()->json([
            'success' => true,
            'message' => 'Menu deleted successfully'
        ]);
    }
}
