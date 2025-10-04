<?php

namespace App\Services;

use App\Models\PriceLevel;
use Illuminate\Support\Facades\Auth;

class PriceLevelService
{
    /**
     * 创建 PriceLevel
     */
    public function create(array $data): void
    {
        $user = Auth::user();

        $priceLevel = new PriceLevel();
        $priceLevel->name = $data['name'];
        $priceLevel->description = $data['description'] ?? null;
        $priceLevel->is_active = $data['is_active'] ?? true;
        $priceLevel->created_by = $user->id;
        $priceLevel->created_by_name = $user->name;
        $priceLevel->save();
    }

    /**
     * 更新 PriceLevel
     */
    public function update(array $data): void
    {
        $user = Auth::user();

        $priceLevel = PriceLevel::findOrFail($data['id']);
        $priceLevel->name = $data['name'] ?? $priceLevel->name;
        $priceLevel->description = $data['description'] ?? $priceLevel->description;
        $priceLevel->is_active = $data['is_active'] ?? $priceLevel->is_active;
        $priceLevel->updated_by = $user->id;
        $priceLevel->updated_by_name = $user->name;
        $priceLevel->save();
    }

    /**
     * 删除（软删）PriceLevel
     */
    public function delete(int $id): void
    {
        $user = Auth::user();

        $priceLevel = PriceLevel::findOrFail($id);
        $priceLevel->updated_by = $user->id;
        $priceLevel->updated_by_name = $user->name;
        $priceLevel->save();
        $priceLevel->delete();
    }
}
