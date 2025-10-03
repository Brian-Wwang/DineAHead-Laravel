<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\PriceLevelService;
use App\Models\PriceLevel;

class PriceLevelController extends Controller
{
    protected PriceLevelService $priceLevelService;

    public function __construct(PriceLevelService $priceLevelService)
    {
        $this->priceLevelService = $priceLevelService;
    }

    // 🟢 Public List：只获取 is_active 为 true 的数据
    public function public()
    {
        $list = PriceLevel::where('is_active', true)
            ->orderBy('id', 'desc')
            ->get();

        return api_response($list);
    }

    // 🟡 获取所有数据（后台）
    public function list()
    {
        $list = PriceLevel::orderBy('id', 'desc')->get();
        return api_response($list);
    }

    // 🟢 创建
    public function create(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        $this->priceLevelService->create($request->all());

        return api_response();
    }

    // 🟠 更新
    public function update(Request $request)
    {
        $request->validate([
            'id' => 'required|exists:price_levels,id',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        $this->priceLevelService->update($request->all());

        return api_response();
    }

    // 🔴 删除（软删）
    public function delete(Request $request)
    {
        $request->validate([
            'id' => 'required|exists:price_levels,id',
        ]);

        $this->priceLevelService->delete($request->id);

        return api_response();
    }
}
