<?php

namespace App\Http\Controllers;

use App\Models\Table;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Validation\Rule;

class TableController extends Controller
{
    public function list(Request $request) {
      $storeId = $request->user()->store->id;

      $tables = Table::where('store_id', $storeId)
          ->whereNull('deleted_at')
          ->get();

      return api_response($tables);
    }

    public function public(Request $request) {
      $request->validate([
          'store_id' => 'required|integer|exists:stores,id'
      ]);

      $tables = Table::where('store_id', $request->store_id)
          ->whereNull('deleted_at')
          ->where('is_active', true)
          ->get();

      // 按 location_type_name 分组
      $grouped = $tables->groupBy('location_type_name')
          ->map(function ($items, $locationType) {
              return [
                  'location_type_name' => $locationType,
                  'children' => $items->values()  // 保持原始表的数据
              ];
          })
          ->values(); // 重置为顺序数组

      return api_response($grouped);
  }


    public function create(Request $request) {
      $data = $request->validate([
        'name'        => 'required|string|max:255',
        'description' => 'nullable|string',
        'images'      => 'nullable|array',
        'images.*'    => 'url',
        // ✅ 新增：location_type 必须是 10 / 20 / 30
        'location_type' => ['required', Rule::in([10, 20, 30])],

        // ✅ 新增：seat_level_id 必须存在于 seat_levels 表
        'seat_level_id' => ['required', Rule::exists('seat_levels', 'id')],
        'is_active' => 'boolean'
      ]);

      $data['store_id'] = $request->user()->store->id;

      Table::create($data);
      return api_response();
    }

    public function update(Request $request) {
      $request->validate([
        'id'             => 'required|integer|exists:tables,id',
        'name'           => 'sometimes|string|max:255',
        'description'    => 'nullable|string',
        'images'         => 'nullable|array',
        'images.*'       => 'url',
        'is_active'      => 'boolean',

        // ✅ 业务字段校验
        'location_type'  => ['required', Rule::in([10, 20, 30])],
        'seat_level_id'  => ['nullable', Rule::exists('seat_levels', 'id')],
      ]);

      // 1) 取当前登录用户的 store_id
      $storeId = optional($request->user()->store)->id;
      if (!$storeId) {
          // 用户还没有门店
          return api_response(null, 'Store not found for current user', 404, false);
      }

      // 2) 读取目标桌台
      $table = Table::findOrFail($request->id);

      // 3) 显式校验归属
      if ((int)$table->store_id !== (int)$storeId) {
          return api_response(null, 'Forbidden: table does not belong to your store', 403, false);
      }

      // 4) 更新字段（把新增的两个业务字段也写入）
      $payload = $request->only([
          'name',
          'description',
          'images',
          'is_active',
          'location_type',
          'seat_level_id',
      ]);

      // 如果模型 casts 里已配置 'images' => 'array'，可直接传数组；否则可手动 json_encode：
      // if (isset($payload['images']) && is_array($payload['images'])) {
      //     $payload['images'] = json_encode($payload['images']);
      // }

      $table->fill($payload)->save();

      return api_response();
    }

    public function delete(Request $request) {
      $request->validate([
        'id' => 'required|integer|exists:tables,id',
      ]);

      $table = Table::where('id', $request->id)
          ->where('store_id', $request->user()->store->id)
          ->firstOrFail();

      $table->delete();
      return api_response();
    }

    public function updateStatus(Request $request) {
      $request->validate([
        'id'     => 'required|integer|exists:tables,id',
        'status' => 'required|integer|in:0,1,2,3'
      ]);

      $table = Table::where('id', $request->id)
        ->where('store_id', $request->user()->store->id)
        ->firstOrFail();

      $table->update(['status' => $request->status]);

      return response()->json([
        'success' => true,
        'data'    => $table->fresh()
      ]);
      return api_response();
    }
}
