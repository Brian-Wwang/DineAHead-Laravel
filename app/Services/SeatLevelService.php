<?php

namespace App\Services;

use App\Models\SeatLevel;
use Illuminate\Support\Facades\Auth;

class SeatLevelService
{
    /**
     * 创建 SeatLevel
     */
    public function create(array $data): void
    {
        $user = Auth::user();

        $seat = new SeatLevel();
        $seat->name = $data['name'];
        $seat->description = $data['description'] ?? null;
        $seat->is_active = $data['is_active'] ?? true;
        $seat->created_by = $user->id;
        $seat->created_by_name = $user->name;
        $seat->save();
    }

    /**
     * 更新 SeatLevel
     */
    public function update(array $data): void
    {
        $user = Auth::user();

        $seat = SeatLevel::findOrFail($data['id']);
        $seat->name = $data['name'] ?? $seat->name;
        $seat->description = $data['description'] ?? $seat->description;
        $seat->is_active = $data['is_active'] ?? $seat->is_active;
        $seat->updated_by = $user->id;
        $seat->updated_by_name = $user->name;
        $seat->save();
    }

    /**
     * 删除（软删）PriceLevel
     */
    public function delete(int $id): void
    {
        $user = Auth::user();

        $seat = SeatLevel::findOrFail($id);
        $seat->updated_by = $user->id;
        $seat->updated_by_name = $user->name;
        $seat->save();
        $seat->delete();
    }
}
